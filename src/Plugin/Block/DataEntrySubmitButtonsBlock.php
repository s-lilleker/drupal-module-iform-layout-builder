<?php

namespace Drupal\iform_layout_builder\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Submit Buttons' block.
 *
 * @Block(
 *   id = "data_entry_submit_buttons_block",
 *   admin_label = @Translation("Indicia data entry submit buttons block"),
 *   layout_builder_label = @Translation("Form submit buttons"),
 *   category = @Translation("Indicia form control")
 * )
 */
class DataEntrySubmitButtonsBlock extends BlockBase {

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
    $ctrl = '<button type="submit" class="btn btn-primary">Save</button>';
    return [
      '#markup' => new FormattableMarkup($ctrl, []),
    ];
  }

}