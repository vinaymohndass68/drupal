<?php

namespace Drupal\mcapi\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Because the transaction collection is also the field ui base route, and
 * because views provides a superior listing to the entity's official
 * list_builder, this alters that view's route to comply with the entity.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($transaction_list = $collection->get('view.mcapi_transactions.admin')) {
      $collection->add('entity.mcapi_transaction.collection', $transaction_list);
      $collection->remove('view.admin.transaction.list');
    }
  }


}
