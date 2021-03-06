<?php

namespace Drupal\mcapi_limits\Plugin;

use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for Limits plugins.
 */
abstract class McapiLimitsBase implements McapiLimitsInterface {

  use \Drupal\Core\StringTranslation\StringTranslationTrait;

  public $id;

  public $currency;

  /**
   * Stores the limits settings from the currency, for convenience.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Constructor.
   *
   * @param array $settings
   *   The plugin's settings.
   * @param string $plugin_id
   *   The id of the plugin.
   * @param array $definition
   *   Definition of the plugin.
   */
  public function __construct(array $settings, $plugin_id, array $definition) {
    $this->currency = $settings['currency'];
    $this->id = $plugin_id;
    $this->setConfiguration($this->currency->getThirdPartySettings('mcapi_limits'));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $site_name = \Drupal::config('system.site')->get('name');
    return [
      'override' => 0,
      'skip' => ['auto', 'mass'],
      'prevent' => TRUE,
      // @todo move these to rules actions
      'warning_mail' => [
        'subject' => $this->t('Your are trading beyond your limits!'),
        'body' => implode("\n",
          [
            $this->t("[current-user:name] registered a transaction with you, which produced the following warning:\n[warning]"),
            $this->t("Please take steps to trade back with in your limits."),
            $this->t("The team at %site", ['%site' => $site_name]),
          ]
        ),
      ],
      'prevented_mail' => [
        'subject' => $this->t('Transaction prevented on %site', ['%site' => $site_name]),
        'body' => implode("\n",
          [
            $this->t("[current-user:name] tried to register a transaction with you, but it was prevented with the following warning:\n[warning]"),
            $this->t("Please take steps to trade back with in your limits."),
            $this->t("The team at %site", ['%site' => $site_name]),
          ]
        ),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // We rely in the inserted fields to validate themselves individually, so
    // there is no validation added at the form level.
    $subform['override'] = [
      '#title' => $this->t('Allow wallet-level override'),
      '#description' => $this->t('Settings on the user profiles override these general limits.'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['override'],
      '#weight' => 5,
      '#states' => [
        'invisible' => [
          ':input[name="limits[limits_callback]"]' => ['value' => 'limits_none'],
        ],
      ],
    ];
    $subform['skip'] = [
      '#title' => $this->t('Skip balance limit check for the following transactions'),
      '#description' => $this->t('Especially useful for mass transactions and automated transactions'),
      '#type' => 'checkboxes',
      // Casting it here saves us worrying about the default, which is awkward.
      '#default_value' => array_keys(array_filter($this->configuration['skip'])),
      // Would be nice if this was pluggable, but not needed for now.
      '#options' => [
        'auto' => $this->t("of type 'auto'"),
        'mass' => $this->t("of type 'mass'"),
        'user1' => $this->t("created by user 1"),
        'owner' => $this->t("created by the currency owner"),
      ],
      '#states' => [
        'invisible' => [
          ':input[name="limits[limits_callback]"]' => ['value' => 'limits_none'],
        ],
      ],
      '#weight' => 6,
    ];
    $subform['prevent'] = [
      '#title' => t('Prevent transactions which transgress limits.'),
      '#description' => $this->t('Note that a system event will be fired so rules will be invoked'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['prevent'],
      '#weight' => 12,
    ];
    $subform['warning_mail'] = [
      '#title' => t('Mail notification'),
      '#description' => t('Notify user when the balance limit has been crossed.'),
      '#type' => 'details',
      '#open' => FALSE,
      '#weight' => 15,
      '#states' => [
        'invisible' => [
          ':input[name="plugin_settings[prevent]"]' => ['value' => '0'],
        ],
      ],
    ];
    $mail = $this->configuration['warning_mail'];
    $subform['warning_mail']['subject'] = [
      '#title' => t('Subject'),
      '#type' => 'textfield',
      '#default_value' => $mail ? $mail['subject'] : '',
      '#maxlength' => 180,
    ];
    $subform['warning_mail']['body'] = [
      '#title' => t('Body'),
      '#description' => $this->t('The following tokens are available: @tokens', ['@tokens' => $this->getMailTokens()]),
      '#field_prefix' => $this->t("Hi [user:name]"),
      '#type' => 'textarea',
      '#default_value' => $mail ? $mail['body'] : '',
      '#rows' => 8,
    ];
    $subform['prevented_mail'] = [
      '#title' => t('Mail notification'),
      '#description' => t('Notify user when the balance limit would have been crossed.'),
      '#type' => 'details',
      '#open' => FALSE,
      '#weight' => 15,
      '#states' => [
        'invisible' => [
          ':input[name="plugin_settings[prevent]"]' => ['value' => '1'],
        ],
      ],
    ];
    $subform['prevented_mail']['subject'] = [
      '#title' => t('Subject'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['prevented_mail']['subject'],
      '#maxlength' => 180,
    ];
    $subform['prevented_mail']['body'] = [
      '#title' => t('Body'),
      '#description' => $this->t('The following tokens are available: @tokens', ['@tokens' => $this->getMailTokens()]),
      '#field_prefix' => $this->t("Hi [user:name]"),
      '#type' => 'textarea',
      '#default_value' => @$this->configuration['prevented_mail']['body'],
      '#rows' => 8,
    ];
    return $subform;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = [];
    if ($values['plugin'] != 'none') {
      foreach ($values['plugin_settings'] as $key => $value) {
        if ($key == 'warning_mail_subject') {
          $config['warning_mail']['subject'] = $value;
        }
        elseif ($key == 'warning_mail_body') {
          $config['warning_mail']['body'] = $value;
        }
        else {
          $config[$key] = $value;
        }
      }
    }
    $this->setConfiguration($config);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return ['module' => 'mcapi_limits'];
  }

  /**
   * Get the sets of tokens the notification mail needs.
   */
  private function getMailTokens() {
    $tokens = [
      'current-user' => ['name', 'link'],
      'exceeded-wallet' => ['name', 'link'],
      'mcapi_wallet' => ['name', 'link'],
    ];
    foreach ($tokens as $type => $toks) {
      foreach ($toks as $tok) {
        $strings[] = '[' . $type . ':' . $tok . ']';
      }
    }
    return '[message]' . implode(', ', $strings);
  }

}
