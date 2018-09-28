<?php

namespace Drupal\mongodb_watchdog;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

/**
 * Class EventController provides query and render logic for Event occurrences.
 */
class EventController {

  /**
   * The name of the anonymous user account.
   *
   * @var string
   */
  protected $anonymous;

  /**
   * The length of the absolute home URL.
   *
   * @var int
   */
  protected $baseLength;

  /**
   * The date.formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The absolute path to the site home.
   *
   * @var string
   */
  protected $front;

  /**
   * An instance cache for user accounts, which are used in a loop.
   *
   * @var array
   */
  protected $userCache = [];

  /**
   * The MongoDB logger service, to load events.
   *
   * @var \Drupal\mongodb_watchdog\Logger
   */
  protected $watchdog;

  /**
   * EventController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config.factory service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The core data.formatter service.
   * @param \Drupal\mongodb_watchdog\Logger $watchdog
   *   The MongoDB logger service, to load events.
   */
  public function __construct(ConfigFactoryInterface $config,
    DateFormatterInterface $date_formatter,
    Logger $watchdog) {
    // Needed for other values so build it first.
    $this->front = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();

    $this->anonymous = $config->get('user.settings')->get('anonymous');
    $this->baseLength = Unicode::strlen($this->front) - 1;
    $this->dateFormatter = $date_formatter;
    $this->watchdog = $watchdog;
  }

  /**
   * Provide a table row representation of an event occurrence.
   *
   * @param \Drupal\mongodb_watchdog\EventTemplate $template
   *   The template for which the occurrence exists.
   * @param \Drupal\mongodb_watchdog\Event $event
   *   The event occurrence to represent.
   *
   * @return array
   *   A render array.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function asTableRow(EventTemplate $template, Event $event) {
    $uid = intval($event->uid);
    if (!isset($this->userCache[$uid])) {
      $this->userCache[$uid] = $uid ? User::load($uid)->toLink() : $this->anonymous;
    }

    $ret = [
      $this->dateFormatter->format($event->timestamp, 'short'),
      $this->userCache[$uid],
      $template->asString($event->variables),
      // Locations generated from Drush/Console will not necessarily match the
      // site home URL, and will not therefore not necessarily be reachable, so
      // we only generate a link if the location is "within" the site.
      (Unicode::strpos($event->location, $this->front) === 0)
      ? Link::fromTextAndUrl(Unicode::substr($event->location, $this->baseLength), Url::fromUri($event->location))
      : $event->location,
      empty($event->referrer) ? '' : Link::fromTextAndUrl($event->referrer, Url::fromUri($event->referrer)),
      $event->hostname,
      isset($event->requestTracking_id)
      ? Link::createFromRoute(t('Request'), 'mongodb_watchdog.reports.request', ['uniqueId' => $event->requestTracking_id])
      : '',
    ];
    return $ret;
  }

  /**
   * Load MongoDB watchdog events for a given event template.
   *
   * @param \Drupal\mongodb_watchdog\EventTemplate $template
   *   The template for which to find events.
   * @param string $skip
   *   The string representation of the number of events to skip.
   * @param int $limit
   *   The limit on the number of events to return.
   *
   * @return \MongoDB\Driver\Cursor
   *   A cursor to the event occurrences.
   */
  public function find(EventTemplate $template, $skip, $limit) {
    $collection = $this->watchdog->eventCollection($template->_id);
    $selector = [];
    $options = [
      'skip' => $skip,
      'limit' => $limit,
      'sort' => ['$natural' => -1],
      'typeMap' => [
        'array' => 'array',
        'document' => 'array',
        'root' => 'Drupal\mongodb_watchdog\Event',
      ],
    ];

    $result = $collection->find($selector, $options);
    return $result;
  }

}
