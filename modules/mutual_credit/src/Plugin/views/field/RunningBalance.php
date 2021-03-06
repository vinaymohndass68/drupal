<?php

namespace Drupal\mcapi\Plugin\views\field;

use Drupal\mcapi\Element\WorthsView;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to provide running balance for a given transaction.
 *
 * @note reads from the transaction index table
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("transaction_running_balance")
 *
 * @todo make an option for this field to sort on, serial or xid, or created
 */
class RunningBalance extends Worth {

  private $transactionStorage;
  private $fAlias;

  /**
   * Constructs a Handler object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param EntityTypeManager $entity_type_manager
   *   The EntityTypeManager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->transactionStorage = $entity_type_manager->getStorage('mcapi_transaction');
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->additional_fields['running_created'] = ['field' => 'created', 'table' =>  $this->table];
  }


  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function query() {
    // Determine the wallet id for which we want the balance.
    // It could from one of two args, or a filter.
    $this->addAdditionalFields();
    $this->fAlias = $this->getFirstWalletFieldAlias();
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $transaction = $this->getEntity($values);
    if (!$transaction) {
      //something wrong here
      throw new \Exception('No entity in views result');
    }
    $vals = [];
    foreach ($transaction->worth->currencies() as $curr_id) {
      // @todo running balance means sorting by the same field the view is sorted by.
      $raw = $this->transactionStorage->runningBalance(
        $values->{$this->fAlias},
        $curr_id,
        ['created' => $values->{$this->aliases['running_created']}]
      );
      $vals[] = ['curr_id' => $curr_id, 'value' => $raw];
    }
    $transaction->worth->setValue($vals);
    $options = [
      'label' => 'hidden',
      'context' => WorthsView::MODE_TRANSACTION,
      'settings' => [],
    ];
    return $transaction->worth->view($options);
  }

  /**
   * helpers
   */
  private function getFirstWalletFieldAlias() {
    $q = $this->view->getQuery();
    foreach ($q->tables as $table_name => $relations) {
      foreach (array_keys($relations) as $alias) {
        $info = $q->getTableInfo($alias);
        if ($info['table'] == 'mcapi_wallet' && $info['join']->leftField == 'wallet_id') {
          foreach ($q->fields as $falias => $f_info) {
            if ($f_info['table'] == $info['alias']) {
              return $falias;
            }
          }
        }
        elseif(isset($q->fields['wallet_id'])) {
          return 'wallet_id';
        }
      }
    }
    throw new \Exception('Running balance requires that there be a relationship with the wallet_id field');
  }

}
