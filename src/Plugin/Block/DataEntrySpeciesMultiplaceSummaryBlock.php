<?php

namespace Drupal\iform_layout_builder\Plugin\Block;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Multiplace Species Input Summary' block.
 *
 * @Block(
 *   id = "data_entry_species_multiplace_summary_block",
 *   admin_label = @Translation("Indicia data entry species multiplace summary block"),
 *   layout_builder_label = @Translation("Summary of the entered species"),
 *   category = @Translation("Indicia form control")
 * )
 */
class DataEntrySpeciesMultiplaceSummaryBlock extends IndiciaControlBlockBase {

  protected function getControlConfigFields() {
    return [
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    iform_load_helpers(['data_entry_helper']);
    $ctrl = \data_entry_helper::multiple_places_species_checklist_summary();
    return [
      '#markup' => new FormattableMarkup($ctrl, []),
      '#attached' => [
        'library' => [
          'iform_layout_builder/block.base',
        ],
      ],
      '#cache' => [
        // No cache please.
        'max-age' => 0,
      ],
    ];

  }

}