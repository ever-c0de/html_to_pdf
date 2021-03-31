<?php

namespace Drupal\html_to_pdf\Form;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the class for PdfSettingsForm.
 *
 * Implements the form with checkboxes.
 *
 * Form for choosing content types of site that can be
 * converted to PDF format
 */
class PdfSettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId(): string {
    return 'pdf_settings_form';
  }

  /**
   * Method for getting all nodes bundles.
   *
   * @return array
   *   Bundles array.
   */
  public function getContentTypes(): array {
    $contentTypes = \Drupal::service('entity.manager')
      ->getStorage('node_type')
      ->loadMultiple();
    $contentTypesList = [];
    foreach ($contentTypes as $contentType) {
      $contentTypesList[$contentType->id()] = $contentType->label();
    }
    return $contentTypesList;
  }

  /**
   * Method for getting current config of bundles.
   *
   * @return array
   *   Array of bundles.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getEntityConfig(): array {
    return \Drupal::entityTypeManager()
      ->getStorage('pdf_config_entity')
      ->loadMultiple();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  : array {
    $content_types = $this->getContentTypes();
    try {
      $entity = $this->getEntityConfig();
    }
    catch (InvalidPluginDefinitionException
    | PluginNotFoundException $e) {
    }
    $default_value = [];
    if (!empty($entity)) {
      foreach ($entity as $key => $value) {
        $default_value[$value->id()] = $value->label();
      }
    }

    $form['html_to_pdf_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('HTML to PDF Settings'),
      '#description' => $this
        ->t('Choose content types that can be converted to PDF.'),
      '#weight' => '0',
    ];

    $form['content_types'] = [
      '#type' => 'checkboxes',
      '#name' => 'main_checkboxes',
      '#title' => $this->t('Content types'),
      '#options' => $content_types,
      '#default_value' => $default_value,
      '#weight' => '0',
    ];

    $form['save'] = [
      '#type' => 'submit',
      '#title' => $this->t('Save'),
      '#value' => $this->t('Save'),
      '#description' => $this->t('Save selected content-types.'),
      '#weight' => '0',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  : array {
    try {
      $entity = $this->getEntityConfig();
    }
    catch (InvalidPluginDefinitionException
    | PluginNotFoundException $e) {
    }
    if (!empty($entity)) {
      foreach ($entity as $key => $value) {
        $options[$value->id()] = $value->label();
      }
    }
    $elements = array_diff(($form_state->getValues()['content_types']), [0]);

    if ($elements != 0 or $elements != $options) {
      try {
        $nodes = \Drupal::entityTypeManager()
          ->getStorage('pdf_config_entity')->loadMultiple();
      }
      catch (InvalidPluginDefinitionException
      | PluginNotFoundException $e) {
      }
      try {
        if (!empty($nodes)) {
          \Drupal::entityTypeManager()
            ->getStorage('pdf_config_entity')
            ->delete($nodes);
        }
      }
      catch (InvalidPluginDefinitionException
      | PluginNotFoundException
      | EntityStorageException $e) {
      }
      foreach ($elements as $item => $value) {
        if (!$value == 0) {
          try {
            \Drupal::entityTypeManager()
              ->getStorage('pdf_config_entity')
              ->create([
                'id' => $value,
                'label' => $value,
              ])->save();
          }
          catch (InvalidPluginDefinitionException
          | PluginNotFoundException
          | EntityStorageException $e) {
          }
        }
      }
    }
    \Drupal::service('plugin.manager.menu.local_task')
      ->clearCachedDefinitions();

    $messenger = \Drupal::messenger();
    $messenger->addMessage('Your settings have been saved!', $messenger::TYPE_STATUS);
    return $form;
  }

}
