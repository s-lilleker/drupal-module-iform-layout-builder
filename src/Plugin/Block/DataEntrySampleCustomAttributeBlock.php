<?php

namespace Drupal\iform_layout_builder\Plugin\Block;

use data_entry_helper;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Sample custom attribute' block for data entry.
 *
 * @Block(
 *   id = "data_entry_sample_custom_attribute_block",
 *   admin_label = @Translation("Indicia data entry custom sample value block"),
 *   category = @Translation("Indicia form control")*
 * )
 */
class DataEntrySampleCustomAttributeBlock extends IndiciaCustomAttributeBlockBase {

  protected function getAttrEntityName() {
    return 'sample';
  }

  protected function getControlConfigFields() {
    return parent::getControlConfigFields();
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
    $blockConfig = $this->getConfiguration();
    iform_load_helpers(['data_entry_helper']);
    return [
      '#markup' => new FormattableMarkup($this->getControl($blockConfig), []),
      '#cache' => [
        // No cache please.
        'max-age' => 0,
      ],
    ];

  }

}