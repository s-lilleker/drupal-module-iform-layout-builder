<?php

namespace Drupal\iform_layout_builder\Plugin\Block;

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
class DataEntrySubmitButtonsBlock extends IndiciaLayoutBuilderBlockBase {

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
    $ctrls = '<button type="submit" class="btn btn-primary">Save</button>';
    if (!empty(\data_entry_helper::$entity_to_load['sample:id'])) {
      $node = $this->getCurrentNode();
      if ($node->field_form_type->value !== 'single') {
        $ctrls .= '<button type="submit" class="btn btn-danger" name="action" value="DELETE">Delete record</button>';
      }
      else {
        $ctrls .= '<button type="submit" class="btn btn-danger" name="action" value="DELETE">Delete all records</button>';
      }
    }
    return [
      '#markup' => new FormattableMarkup($ctrls, []),
    ];
  }

}