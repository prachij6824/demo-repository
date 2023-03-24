<?php

namespace Drupal\batch_form\Form;

use Drupal\Component\Utility\Environment;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Url;
use Drupal\Core\Link;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Implements form to upload a file and start the batch on form submit.
 *
 * @see \Drupal\Core\Form\FormBase
 * @see \Drupal\Core\Form\ConfigFormBase
 */
class CsvImportForm extends FormBase {

 /**
  * {@inheritdoc}
  */
  public function getFormId() {
    return 'batch_process_form';
  }

 /**
  * {@inheritdoc}
  */
  

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = array(
      '#attributes' => array('enctype' => 'multipart/form-data'),
    );
    $form['file_upload_details'] = array(
      '#markup' => t('<b>The File</b>'),
    );
    $validators = array(
      'file_validate_extensions' => array('csv'),
    );
    $form['excel_file'] = array(
      '#type' => 'managed_file',
      '#name' => 'excel_file',
      '#title' => t('File *'),
      '#size' => 20,
      '#description' => t('Excel format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://content/excel_files/',
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    return $form;
  }
  /**
   * Validate the file upload.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {    
    if ($form_state->getValue('excel_file') == NULL) {
      $form_state->setErrorByName('excel_file', $this->t('upload proper File'));
    }
  }
   /**
   * {@inheritdoc}
   */

  public function submitForm(array &$form, FormStateInterface $form_state) {
      // dd($form_state->getValue('excel_file')[0]);
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($form_state->getValue('excel_file')[0]);    
	  $full_path = $file->get('uri')->value;
	  $file_name = basename($full_path);
    $file->setPermanent();
    $file->save();
    $inputFileName = \Drupal::service('file_system')->realpath('public://content/excel_files/'.$file_name);
    $spreadsheet = IOFactory::load($inputFileName);
    $sheetData = $spreadsheet->getActiveSheet();

    $rows = array();  
      foreach ($sheetData->getRowIterator() as $row) {
       //echo "<pre>";print_r($row);exit;
       $cellIterator = $row->getCellIterator();
       $cellIterator->setIterateOnlyExistingCells(FALSE); 
       $cells = [];
        foreach ($cellIterator as $cell) {
          $cells[] = $cell->getValue();
        }
       $rows[] = $cells;
      }
      //  dd($rows);
      array_shift($rows);
      foreach($rows as $row){
        // echo($rows);exit();
        $operations = [
          ['\Drupal\batch_form\CustomBatch::batchOperation',[$row]],
        ];
        //  dd($row);
        $batch = [
          'title' =>t('Inserting..'),
          'operations' => $operations,
          'finished' => '\Drupal\batch_form\CustomBatch::batchFinished',
        ];
        //  dd( $row);
        batch_set($batch); 
       \Drupal::messenger()->addMessage('Imported successfully');
      } 
  } 
}
