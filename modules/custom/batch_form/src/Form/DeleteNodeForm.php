<?php

namespace Drupal\batch_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DeleteNodeForm.
 *
 * @package Drupal\batch_form\Form
 */
class DeleteNodeForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'delete_node_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['delete_node'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Delete Node'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'events')
      ->sort('created', 'ASC')
      ->execute();
    $operations = [
       ['\Drupal\batch_form\DeleteNode::deleteNodeExample',[$nids]],
      ];
    $batch = [
      'title' => t('Deleting Node...'),
      'operations' => $operations,
      'finished' => '\Drupal\batch_form\DeleteNode::deleteNodeExampleFinishedCallback',
    ];
    batch_set($batch);
  }
}
