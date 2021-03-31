<?php

namespace Drupal\html_to_pdf\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Pdf config entity entity.
 *
 * @ConfigEntityType(
 *   id = "pdf_config_entity",
 *   label = @Translation("Pdf config entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\html_to_pdf\PdfConfigEntityListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\html_to_pdf\PdfConfigEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "pdf_config_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 * )
 */
class PdfConfigEntity extends ConfigEntityBase implements PdfConfigEntityInterface {

  /**
   * The Pdf config entity ID.
   *
   * @var string
   */
  protected string $id;

  /**
   * The Pdf config entity label.
   *
   * @var string
   */
  protected string $label;

}
