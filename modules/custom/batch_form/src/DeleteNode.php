<?php

namespace Drupal\batch_form;


use Drupal\node\Entity\Node;

class DeleteNode {

  public static function deleteNodeExample($nids, &$context){
    $message = 'Deleting Node...';
    $results = array();
    foreach ($nids as $nid) {
      $node = Node::load($nid);
      $results[] = $node->delete();
    }
    $context['message'] = $message;
    $context['results'] = $results;
  }

  public static function deleteNodeExampleFinishedCallback($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One node is deleted successfully.', '@count nodes are deleted successfully.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    \Drupal::messenger()->addStatus($message);
  }
}