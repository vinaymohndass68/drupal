<?php

namespace Drupal\mcapi_rules\Forms;

use Drupal\mcapi\Entity\Wallet;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form builder to for transaction fees
 *
 * @temp
 */
class Fees extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mcapi_fees_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    drupal_set_message(t('This feature will be removed when rules integration is complete'));

    $config = $this->configFactory()->get('mcapi.fees');
    $form['curr_id'] = [
      '#title' => $this->t('Currency'),
      '#type' => 'mcapi_currency_select',
      '#default_value' => $config->get('curr_id'),
      '#weight' => 1,
    ];
    $form['types'] = [
      '#title' => $this->t('Transaction types'),
      '#description' => $this->t("The fees transaction will be of type 'Automated'"),
      '#type' => 'mcapi_types',
      '#default_value' => $config->get('types'),
      '#weight' => 3,
      '#multiple' => TRUE,
    ];
    $form['rate'] = [
      '#title' => $this->t('Per cent'),
      '#type' => 'number',
      '#default_value' => $config->get('rate'),
      '#min' => 0,
      '#max' => 100,
      '#weight' => 5,
    ];
    $form['round_up'] = [
      '#title' => $this->t('Round the calculation up'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('round_up'),
      '#weight' => 7,
    ];
    $form['payers'] = [
      '#title' => $this->t('Payer of the fee'),
      '#type' => 'checkboxes',
      '#options' => [
        'payer' => $this->t("The transaction's payer"),
        'payee' => $this->t("The transaction's payee"),
      ],
      'payer' => [
        '#default_value' => $config->get('payer'),
      ],
      'payee' => [
        '#default_value' => $config->get('payer'),
      ],
      '#weight' => 9,
    ];
    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textfield',
      '#default_value' => $config->get('description'),
      '#weight' => 11,
    ];
    $form['target'] = [
      '#title' => $this->t('Recipient wallet'),
      '#type' => 'wallet_entity_auto',
      '#default_value' => Wallet::load($config->get('target')),
      '#weight' => 13,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   *
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('mcapi.fees');
    $config
      ->set('curr_id', $form_state->getValue('curr_id'))
      ->set('rate', $form_state->getValue('rate'))
      ->set('types', array_filter($form_state->getValue('types')))
      ->set('round_up', $form_state->getValue('round_up'))
      ->set('payer', $form_state->getValue('payers')['payer'])
      ->set('payee', $form_state->getValue('payers')['payee'])
      ->set('target', $form_state->getValue('target'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   *
   */
  protected function getEditableConfigNames() {
    return ['mcapi.fees'];
  }

}
