/**
 * @file
 * JavaScript behaviors for message element integration.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Behavior for handler message close.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformMessageClose = {
    attach: function (context) {
      $(context).find('.js-webform-message--close').once('webform-message--close').each(function () {
        var $element = $(this);

        var id = $element.attr('data-message-id');
        var storage = $element.attr('data-message-storage');
        var effect = $element.attr('data-message-close-effect') || 'hide';
        switch (effect) {
          case 'slide': effect = 'slideUp'; break;

          case 'fade': effect = 'fadeOut'; break;
        }

        // Check storage status.
        if (isClosed($element, storage, id)) {
          return;
        }

        // Only show element if it's style is not set to 'display: none'.
        if ($element.attr('style') !== 'display: none;') {
          $element.show();
        }

        $element.find('.js-webform-message__link').on('click', function (event) {
          $element[effect]();
          setClosed($element, storage, id);
          $element.trigger('close');
          event.preventDefault();
        });
      });
    }
  };

  function isClosed($element, storage, id) {
    if (!id || !storage) {
      return false;
    }

    switch (storage) {
      case 'local':
        if (window.localStorage) {
          return localStorage.getItem('Drupal.webform.message.' + id) || false;
        }
        return false;

      case 'session':
        if (window.sessionStorage) {
          return sessionStorage.getItem('Drupal.webform.message.' + id) || false;
        }
        return false;

      default:
        return false;
    }
  }

  function setClosed($element, storage, id) {
    if (!id || !storage) {
      return;
    }

    switch (storage) {
      case 'local':
        if (window.localStorage) {
          localStorage.setItem('Drupal.webform.message.' + id, true);
        }
        break;

      case 'session':
        if (window.sessionStorage) {
          sessionStorage.setItem('Drupal.webform.message.' + id, true);
        }
        break;

      case 'user':
      case 'state':
      case 'custom':
        $.get($element.find('.js-webform-message__link').attr('href'));
        return true;
    }
  }

})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for help.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Handles disabling help dialog for mobile devices.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for disabling help dialog for mobile devices.
   */
  Drupal.behaviors.webformHelpDialog = {
    attach: function (context) {
      $(context).find('.button-webform-play').once('webform-help-dialog').on('click', function (event) {
        if ($(window).width() < 768) {
          event.stopImmediatePropagation();
        }
      }).each(function () {
        // Must make sure that this click event handler is execute first and
        // before the Ajax dialog handler.
        // @see http://stackoverflow.com/questions/2360655/jquery-event-handlers-always-execute-in-order-they-were-bound-any-way-around-t
        var handlers = $._data(this, 'events')['click'];
        var handler = handlers.pop();
        // Move it at the beginning.
        handlers.splice(0, 0, handler);
      });
    }
  };

})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for Ajax.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.webform = Drupal.webform || {};
  Drupal.webform.ajax = Drupal.webform.ajax || {};
  // Allow scrollTopOffset to be custom defined or based on whether there is a
  // floating toolbar.
  Drupal.webform.ajax.scrollTopOffset = Drupal.webform.ajax.scrollTopOffset || ($('#toolbar-administration').length ? 140 : 10);

  /**
   * Provide Webform Ajax link behavior.
   *
   * Display fullscreen progress indicator instead of throbber.
   * Copied from: Drupal.behaviors.AJAX
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior to a.webform-ajax-link.
   */
  Drupal.behaviors.webformAjaxLink = {
    attach: function (context) {
      $('.webform-ajax-link').once('webform-ajax-link').each(function () {
        var element_settings = {};
        element_settings.progress = {type: 'fullscreen'};

        // For anchor tags, these will go to the target of the anchor rather
        // than the usual location.
        var href = $(this).attr('href');
        if (href) {
          element_settings.url = href;
          element_settings.event = 'click';
        }
        element_settings.dialogType = $(this).data('dialog-type');
        element_settings.dialogRenderer = $(this).data('dialog-renderer');
        element_settings.dialog = $(this).data('dialog-options');
        element_settings.base = $(this).attr('id');
        element_settings.element = this;
        Drupal.ajax(element_settings);

        // For anchor tags with 'data-hash' attribute, add the hash to current
        // pages location.
        // @see \Drupal\webform_ui\WebformUiEntityElementsForm::getElementRow
        // @see Drupal.behaviors.webformFormTabs
        var hash = $(this).data('hash');
        if (hash) {
          $(this).on('click', function () {
            location.hash = $(this).data('hash');
          });
        }

        // Close all open modal dialogs when opening off-canvas dialog.
        if (element_settings.dialogRenderer === 'off_canvas') {
          $(this).on('click', function () {
            $(".ui-dialog.webform-ui-dialog:visible").find('.ui-dialog-content').dialog('close');
          });
        }
      });
    }
  };

  /**
   * Provide Ajax callback for confirmation back to link.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior to confirmation back to link.
   */
  Drupal.behaviors.webformConfirmationBackAjax = {
    attach: function (context) {
      $('.js-webform-confirmation-back-link-ajax', context)
        .once('webform-confirmation-back-ajax')
        .click(function (event) {
          var $form = $(this).parents('form');

          // Trigger the Ajax call back for the hidden submit button.
          // @see \Drupal\webform\WebformSubmissionForm::getCustomForm
          $form.find('.js-webform-confirmation-back-submit-ajax').click();

          // Move the progress indicator from the submit button to after this link.
          // @todo Figure out a better way to set a progress indicator.
          var $progress_indicator = $form.find('.ajax-progress');
          if ($progress_indicator) {
            $(this).after($progress_indicator);
          }

          // Cancel the click event.
          event.preventDefault();
          event.stopPropagation();
        });
    }
  };

  /****************************************************************************/
  // Ajax commands.
  /****************************************************************************/

  /**
   * Track the updated table row key.
   */
  var updateKey;

  /**
   * Track the add element key.
   */
  var addElement;

  /**
   * Command to insert new content into the DOM.
   *
   * @param {Drupal.Ajax} ajax
   *   {@link Drupal.Ajax} object created by {@link Drupal.ajax}.
   * @param {object} response
   *   The response from the Ajax request.
   * @param {string} response.data
   *   The data to use with the jQuery method.
   * @param {string} [response.method]
   *   The jQuery DOM manipulation method to be used.
   * @param {string} [response.selector]
   *   A optional jQuery selector string.
   * @param {object} [response.settings]
   *   An optional array of settings that will be used.
   * @param {number} [status]
   *   The XMLHttpRequest status.
   */
  Drupal.AjaxCommands.prototype.webformInsert = function (ajax, response, status) {
    // Insert the HTML.
    this.insert(ajax, response, status);

    // Add element.
    if (addElement) {
      var addSelector = (addElement === '_root_')
        ? '#webform-ui-add-element'
        : '[data-drupal-selector="edit-webform-ui-elements-' + addElement  + '-add"]';
      $(addSelector).click();
    }

    // If not add element, then scroll to and highlight the updated table row.
    if (!addElement && updateKey) {
      var $element = $('tr[data-webform-key="' + updateKey + '"]');

      // Highlight the updated element's row.
      $element.addClass('color-success');
      setTimeout(function () {$element.removeClass('color-success')}, 3000);

      // Focus first tabbable item for the updated elements and handlers.
      $element.find(':tabbable:not(.tabledrag-handle)').eq(0).focus();

      // Scroll to elements that are not visible.
      if (!isScrolledIntoView($element)) {
        $('html, body').animate({scrollTop: $element.offset().top - Drupal.webform.ajax.scrollTopOffset}, 500);
      }
    }
    else {
      // Focus main content.
      $('#main-content').focus();
    }

    // Display main page's status message in a floating container.
    var $wrapper = $(response.selector);
    if ($wrapper.parents('.ui-dialog').length === 0) {
      var $messages = $wrapper.find('.messages');
      // If 'add element' don't show any messages.
      if (addElement) {
        $messages.remove();
      }
      else if ($messages.length) {
        var $floatingMessage = $('#webform-ajax-messages');
        if ($floatingMessage.length === 0) {
          $floatingMessage = $('<div id="webform-ajax-messages" class="webform-ajax-messages"></div>');
          $('body').append($floatingMessage);
        }
        if ($floatingMessage.is(":animated")) {
          $floatingMessage.stop(true, true);
        }
        $floatingMessage.html($messages).show().delay(3000).fadeOut(1000);
      }
    }

    updateKey = null; // Reset element update.
    addElement = null; // Reset add element.
  };

  /**
   * Scroll to top ajax command.
   *
   * @param {Drupal.Ajax} [ajax]
   *   A {@link Drupal.ajax} object.
   * @param {object} response
   *   Ajax response.
   * @param {string} response.selector
   *   Selector to use.
   *
   * @see Drupal.AjaxCommands.prototype.viewScrollTop
   */
  Drupal.AjaxCommands.prototype.webformScrollTop = function (ajax, response) {
    // Scroll to the top of the view. This will allow users
    // to browse newly loaded content after e.g. clicking a pager
    // link.
    var offset = $(response.selector).offset();
    // We can't guarantee that the scrollable object should be
    // the body, as the view could be embedded in something
    // more complex such as a modal popup. Recurse up the DOM
    // and scroll the first element that has a non-zero top.
    var scrollTarget = response.selector;
    while ($(scrollTarget).scrollTop() === 0 && $(scrollTarget).parent()) {
      scrollTarget = $(scrollTarget).parent();
    }

    if (response.target == 'page' && $(scrollTarget).length && $(scrollTarget)[0].tagName === 'HTML') {
      // Scroll to top when scroll target is the entire page.
      // @see https://stackoverflow.com/questions/123999/how-to-tell-if-a-dom-element-is-visible-in-the-current-viewport
      var rect = $(scrollTarget)[0].getBoundingClientRect();
      if (!(rect.top >= 0 && rect.left >= 0 && rect.bottom <= $(window).height() && rect.right <= $(window).width())) {
        $(scrollTarget).animate({scrollTop: 0}, 500);
      }
    }
    else {
      // Only scroll upward.
      if (offset.top - Drupal.webform.ajax.scrollTopOffset < $(scrollTarget).scrollTop()) {
        $(scrollTarget).animate({scrollTop: (offset.top - Drupal.webform.ajax.scrollTopOffset)}, 500);
      }
    }

    // Focus on the form wrapper content bookmark if
    // .js-webform-autofocus is not enabled.
    // @see \Drupal\webform\Form\WebformAjaxFormTrait::buildAjaxForm
    var $form = $(response.selector + '-content').find('form');
    if (!$form.hasClass('js-webform-autofocus')) {
      $(response.selector + '-content').focus();
    }
  };

  /**
   * Command to refresh the current webform page.
   *
   * @param {Drupal.Ajax} [ajax]
   *   {@link Drupal.Ajax} object created by {@link Drupal.ajax}.
   * @param {object} response
   *   The response from the Ajax request.
   * @param {string} response.url
   *   The URL to redirect to.
   * @param {number} [status]
   *   The XMLHttpRequest status.
   */
  Drupal.AjaxCommands.prototype.webformRefresh = function (ajax, response, status) {
    // Get URL path name.
    // @see https://stackoverflow.com/questions/6944744/javascript-get-portion-of-url-path
    var a = document.createElement('a');
    a.href = response.url;
    if (a.pathname == window.location.pathname && $('.webform-ajax-refresh').length) {
      updateKey = (response.url.match(/[\?|&]update=([^&]+)($|&)/)) ? RegExp.$1 : null;
      addElement = (response.url.match(/[\?|&]add_element=([^&]+)($|&)/)) ? RegExp.$1 : null;
      $('.webform-ajax-refresh').click();
    }
    else {
      this.redirect(ajax, response, status);
    }
  };

  /**
   * Command to close a dialog.
   *
   * If no selector is given, it defaults to trying to close the modal.
   *
   * @param {Drupal.Ajax} [ajax]
   * @param {object} response
   * @param {string} response.selector
   * @param {bool} response.persist
   * @param {number} [status]
   */
  Drupal.AjaxCommands.prototype.webformCloseDialog = function (ajax, response, status) {
    if ($('#drupal-off-canvas').length) {
      // Close off-canvas system tray which is not triggered by close dialog
      // command.
      // @see Drupal.behaviors.offCanvasEvents
      $('#drupal-off-canvas').remove();
      $('body').removeClass('js-tray-open');
      // Remove all *.off-canvas events
      $(document).off('.off-canvas');
      $(window).off('.off-canvas');
      var edge = document.documentElement.dir === 'rtl' ? 'left' : 'right';
      var $mainCanvasWrapper = $('[data-off-canvas-main-canvas]');
      $mainCanvasWrapper.css('padding-' + edge, 0);
    }
    else {
      // https://stackoverflow.com/questions/15763909/jquery-ui-dialog-check-if-exists-by-instance-method
      if ($(response.selector).hasClass('ui-dialog-content')) {
        this.closeDialog(ajax, response, status);
      }
    }
  };

  /**
   * Triggers audio UAs to read the supplied text.
   *
   * @param {Drupal.Ajax} [ajax]
   *   A {@link Drupal.ajax} object.
   * @param {object} response
   *   Ajax response.
   * @param {string} response.text
   *   A string to be read by the UA.
   * @param {string} [response.priority='polite']
   *   A string to indicate the priority of the message. Can be either
   *   'polite' or 'assertive'.
   *
   * @see Drupal.announce
   */
  Drupal.AjaxCommands.prototype.webformAnnounce = function (ajax, response) {
    // Delay the announcement.
    setTimeout(function () {Drupal.announce(response.text, response.priority);}, 200);
  };

  /****************************************************************************/
  // Helper functions.
  /****************************************************************************/

  /**
   * Determine if element is visible in the viewport.
   *
   * @param element
   *   An element.
   *
   * @returns {boolean}
   *   TRUE if element is visible in the viewport.
   *
   * @see https://stackoverflow.com/questions/487073/check-if-element-is-visible-after-scrolling
   */
  function isScrolledIntoView(element) {
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

    var elemTop = $(element).offset().top;
    var elemBottom = elemTop + $(element).height();

    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
  }

})(jQuery, Drupal);
;
