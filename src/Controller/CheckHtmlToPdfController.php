<?php

namespace Drupal\html_to_pdf\Controller;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;

/**
 * Provides the class for CheckHtmlToPdfController.
 *
 * Provides the custom access for nodes that need to show local task.
 *
 * @file html_to_pdf.routing.yml
 *
 */
class CheckHtmlToPdfController {

  /**
   * Method for getting the Entity config storage.
   *
   * @return array
   *   Node bundles.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getEntityConfig(): array {
    $entity = \Drupal::entityTypeManager()
      ->getStorage('pdf_config_entity')
      ->loadMultiple();
    $true_entity = [];
    foreach ($entity as $key => $value) {
      $true_entity[] = $value->id();
    }
    return $true_entity;
  }

  /**
   * Checks if node bundle have access.
   *
   * @param string $node
   *   Node id.
   *
   * @return \Drupal\Core\Access\AccessResult|\Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultNeutral
   *   Exception.
   */
  public function checkAccess(string $node) {
    try {
      $getEntityConfig = $this->getEntityConfig();
    }
    catch (InvalidPluginDefinitionException
    | PluginNotFoundException $e) {
    }
    $actualNode = Node::load($node);
    if (isset($getEntityConfig)) {
      if (in_array($actualNode->bundle(), $getEntityConfig)) {
        $getEntityConfig = $actualNode->bundle();
      }
    };
    if (isset($getEntityConfig)) {
      return AccessResult::allowedif($actualNode->bundle() === $getEntityConfig);
    }
  }

}
