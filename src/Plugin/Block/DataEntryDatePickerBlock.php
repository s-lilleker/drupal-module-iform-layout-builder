<?php

namespace Drupal\iform_layout_builder\Plugin\Block;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Date Picker' block.
 *
 * @Block(
 *   id = "data_entry_date_picker_block",
 *   admin_label = @Translation("Indicia data entry date picker block"),
 *   category = @Translation("Indicia form control")
 * )
 */
class DataEntryDatePickerBlock extends IndiciaControlBlockBase {

  protected function getControlConfigFields() {
    return [
      'label' => [
        '#description' => 'Label shown for the form control.',
      ],
      'helpText' => [
        '#title' => 'Help text',
        '#description' => 'Tip shown beneath the control.',
      ],
      'allowVagueDates' => [
        '#title' => 'Enable entry of vague dates',
        '#type' => 'checkbox',
      ],
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
    $blockConfig = $this->getConfiguration();
    $configFieldList = $this->getControlConfigFields();
    $ctrlOptions = [
      'fieldname' => 'sample:date',
    ];
    foreach ($configFieldList as $opt => $cfg) {
      if (isset($blockConfig["option_$opt"])) {
        $ctrlOptions[$opt] = $blockConfig["option_$opt"];
      }
    }
    try {
      $ctrl = \data_entry_helper::date_picker($ctrlOptions);
    }
    catch (\Exception $e) {
      $ctrl = '<div class="alert alert-warning">Invalid control: ' . $e->getMessage() . '</div>';
    }
    return [
      '#markup' => new FormattableMarkup($ctrl, []),
      '#cache' => [
        // No cache please.
        'max-age' => 0,
      ],
    ];

  }

}