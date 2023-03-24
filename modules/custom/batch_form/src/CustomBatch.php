<?php

namespace Drupal\batch_form;

use Drupal\Core\File\FileSystemInterface;
use Drupal\node\Entity\Node;

class CustomBatch {

  public static function batchOperation($row, &$context) {
    
    $values = \Drupal::entityQuery('node')->condition('title', $row[0])->execute();
     $node_not_exists = empty($values);
     $results = array();
      if($node_not_exists){
        /*if node does not exist create new node*/
        $node = \Drupal::entityTypeManager()->getStorage('node')->create([
          'type'       => 'student_data', //===here news is the content type mechine name
          'title'      => $row[0],
          'field_student_name' => $row[1],
          'field_student_roll_no_' => $row[2],
          'field_student_email' => $row[3],
          // 'field_student_phone_no_' => $row[4],
          // 'field_st' => $row[5],
          
        ]);
        $results = $node->save();
      }else{
        /*if node exist update the node*/
        $nid = reset($values);
        $node = \Drupal\node\Entity\Node::load($nid);
        $node->setTitle($row[0]);
        $node->set("field_student_name", $row[1]);
        $node->set("field_student_roll_no_", $row[2]);
        $node->set("field_student_email", $row[3]);
        // $node->set("field_student_phone_no_", $row[4]);
        // $node->set("field_st", $row[5]);
        //$node->set("field_name", 'New value');
        $results = $node->save();
      }
    $context['results'][] = $results;
  }

  public static function batchFinished($success, $results, $operations) {
    if($success){
      $message =\Drupal::translation()->formatPlural(count($results),
       'One node is created successfully',
       '@count nodes are created successfully',
      );
    }
    else{
      $message = t('Some error occured during batch process');
    }
    \Drupal::messenger()->addMessage($message);
  }
}
