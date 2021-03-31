<?php

namespace Drupal\html_to_pdf\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\File\Entity\File;
use Drupal\node\Entity\Node;
use mikehaertl\wkhtmlto\Pdf;

/**
 * Provides the class for PdfController that extends ControllerBase.
 *
 * Implements methods for generating PDF page from node.
 *
 * @see \Drupal\Core\Controller\ControllerBase
 */
class PdfController extends ControllerBase {

  /**
   * Method for getting values for custom template.
   *
   * With this method you can get links to images from Node fields. They
   * will be storage at $links array.
   *
   * If type of field is image than we unset this field to have proper node to
   * render without images.
   *
   * @param string $node
   *   Number of node from route.
   *
   * @return array
   *   Markup for custom template.
   */
  public function templateTest(string $node): array {
    $actualNode = Node::load($node);
    $fields = $actualNode->getFields();

    $links = [];
    foreach ($fields as $field) {
      $type = $field->getFieldDefinition()->getType();
      if ($type == 'image') {
        // Get the field image id.
        $field_id = $field->getValue();
        // Load file image with id.
        $file = File::load($field_id[0]['target_id']);
        // Create link to image from file and push to $links array.
        $links[] = $file->url('canonical');
        // Unset the field.
        $actualNode->set($field->getName(), NULL);
      }
    }
    // Render node to have content markup for theme.
    $entity_type = 'node';
    $view_mode = 'full';
    $builder = \Drupal::service('entity_type.manager')
      ->getViewBuilder($entity_type);
    $storage = \Drupal::service('entity.manager')
      ->getStorage($entity_type);
    $content = $storage
      ->load($actualNode->id());
    $build = $builder
      ->view($content, $view_mode);
    $output = \Drupal::service('renderer')
      ->render($build);
    return [
      '#theme' => 'html_to_pdf',
      '#content' => $output,
      '#links' => $links,
    ];
  }

  /**
   * Method for generating PDF version of node.
   *
   * @param $node
   *  Number of node from route file.
   *
   * @file html_to_pdf.routing.yml
   *
   * @return mixed
   */
  public function createHtmlToPdfPage(string $node): string {
    // Set options for PDF generator.
    $options = [
      'no-outline',
      'disable-smart-shrinking',
    ];
    $binary = $this->getBinary();

    $pdf = new Pdf();
    $pdf->setOptions($options);
    $pdf->binary = $binary;
    // Get template with values for render.
    $node_template = $this->templateTest($node);
    // Render node with custom template and convert to HTML string.
    $rendered_html = \Drupal::service('renderer')
      ->renderRoot($node_template)
      ->__toString();
    // Set source for render.
    $pdf->addPage($rendered_html);
    // Show PDF version for user as page.
    if (!$pdf->send()) {
      $error = $pdf->getError();
    }
    return $node;
  }

  /**
   * Method for getting the path to binary file.
   *
   * @return string
   *   Path to binary.
   */
  protected function getBinary(): string {
    return DRUPAL_ROOT . '/vendor/bin/wkhtmltopdf-amd64';
  }

}
