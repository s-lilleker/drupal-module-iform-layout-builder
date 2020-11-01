<?php

namespace Drupal\iform_layout_builder\Plugin\Block;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Species List' block.
 *
 * @Block(
 *   id = "data_entry_species_list_block",
 *   admin_label = @Translation("Indicia data entry species list block"),
 *   layout_builder_label = @Translation("Grid for entering list of species"),
 *   category = @Translation("Indicia form control")
 * )
 */
class DataEntrySpeciesListBlock extends IndiciaControlBlockBase {

  protected function getControlConfigFields() {
    return [
      'label' => [
        '#description' => 'Label shown for the form control.',
      ],
      'helpText' => [
        '#title' => 'Help text',
        '#description' => 'Tip shown beneath the control.',
      ],
      'scratchpadListId' => [
        '#title' => 'Species list to pre-load',
        '#description' => 'List of species available for selection when the form loads.',
        '#type' => 'select',
        'populateOptions' => [
          'table' => 'scratchpad_list',
          'valueField' => 'id',
          'captionField' => 'title',
        ],
      ],
      'allowAdditionalTaxa' => [
        '#title' => 'Allow additional species',
        '#description' => 'Allow additional species to be added to the bottom of the list by searching.',
        '#type' => 'checkbox',
      ],
      'commentsColumn' => [
        '#title' => 'Allow comments for each record',
        '#type' => 'checkbox',
        '#description' => 'Tick to add a column to the list for inputting a comment against each record.',
        '#default_value' => TRUE,
      ],
      'mediaColumn' => [
        '#title' => 'Allow images upload',
        '#type' => 'checkbox',
        '#description' => 'Tick to add a column to the list for uploading images to support each record.',
        '#default_value' => TRUE,
      ]
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
  public function blockValidate($form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('option_allowAdditionalTaxa')) && empty($form_state->getValue('option_scratchpadListId'))) {
      $form_state->setError('option_scratchpadListId', $this->t('Either select a species list to pre-load, or allow additional speceis to be added.'));
    }
    return parent::blockValidate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    iform_load_helpers(['data_entry_helper']);
    $connection = iform_get_connection_details();
    $readAuth = \data_entry_helper::get_read_auth($connection['website_id'], $connection['password']);
    $blockConfig = $this->getConfiguration();
    $configFieldList = $this->getControlConfigFields();
    $node = $this->getCurrentNode();
    $ctrlOptions = [
      'columns' => 1,
      'extraParams' => $readAuth,
      'editTaxaNames' => TRUE,
      'survey_id' => $node ? $node->field_survey_id->value : NULL,
      'occurrenceComment' => isset($blockConfig["option_commentsColumn"]) && $blockConfig["option_commentsColumn"] === 1,
      'occurrenceImages' => isset($blockConfig["option_mediaColumn"]) && $blockConfig["option_mediaColumn"] === 1,
    ];
    if ($blockConfig['option_allowAdditionalTaxa'] === 1) {
      $ctrlOptions['lookupListId'] = hostsite_get_config_value('iform', 'master_checklist_id', 0);
    }
    elseif (!empty($blockConfig['option_scratchpadListId'])) {
      $ctrlOptions['listId'] = hostsite_get_config_value('iform', 'master_checklist_id', 0);
    }
    foreach (array_keys($configFieldList) as $opt) {
      if (isset($blockConfig["option_$opt"])) {
        $ctrlOptions[$opt] = $blockConfig["option_$opt"];
      }
    }
    try {
      $ctrl = '';
      if (!empty($blockConfig["option_scratchpadListId"])) {
        require_once \data_entry_helper::client_helper_path() . 'prebuilt_forms/extensions/misc_extensions.php';
        $ctrl .= \extension_misc_extensions::load_species_list_from_scratchpad(
          ['read' => $readAuth],
          [],
          NULL,
          [
            'scratchpad_list_id' => $blockConfig["option_scratchpadListId"],
            'tickAll' => FALSE,
            'showMessage' => FALSE,
          ]
        );
        $ctrlOptions['rowInclusionCheck'] = 'checkbox';
      }
      $ctrl .= \data_entry_helper::species_checklist($ctrlOptions);
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