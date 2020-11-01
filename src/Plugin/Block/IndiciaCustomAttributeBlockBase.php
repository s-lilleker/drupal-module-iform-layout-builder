<?php

namespace Drupal\iform_layout_builder\Plugin\Block;

use Drupal\iform_layout_builder\Indicia\SurveyStructure;
use Drupal\Core\Form\FormStateInterface;

abstract class IndiciaCustomAttributeBlockBase extends IndiciaControlBlockBase {

  abstract protected function getAttrEntityName();

  protected function getControlConfigFields() {
    $surveyStructure = new SurveyStructure();
    $existingAttributes = $surveyStructure->getExistingCustomAttributeCaptions($this->getAttrEntityName());
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $attrAdmin = $user->hasPermission('administer indicia attributes');
    $fieldList = [
      'create_or_existing' => [
        '#title' => 'Attribute',
        '#type' => 'select',
        '#options' => [
          'new' => 'Create a new attribute',
          'existing' => 'Use a pre-existing attribute',
        ],
        '#required' => TRUE,
        '#attributes' => [
          'id' => 'option_create_or_existing',
        ],
      ],
      'admin_name' => [
        '#description' => 'Name used to uniquely identify this control to other people building forms.',
        '#required' => TRUE,
      ],
      'admin_description' => [
        '#type' => 'textarea',
        '#description' => 'Description saved for this control on the Indicia warehouse.',
      ],
      'label' => [
        '#title' => 'Form label',
        '#description' => 'Label shown for the control when inputting records.',
      ],
      'helpText' => [
        '#title' => 'Help text',
        '#type' => 'textarea',
        '#description' => 'Tip shown beneath the control.',
      ],
      'suffix' => [
        '#label' => 'Control suffix',
        '#description' => 'Suffix shown after control (e.g the unit).',
      ],
      'required' => [
        '#description' => 'Tick this box to make inputting a value required.',
        '#type' => 'checkbox',
      ],
      'data_type' => [
        '#title' => 'Attribute type',
        '#type' => 'select',
        '#options' => [
          'B' => 'Boolean (on/off)',
          'I' => 'Integer',
          'F' => 'Float',
          'T' => 'Text',
          'L' => 'Lookup',
        ],
        '#empty_value' => '',
        '#states' => [
          // Enable this control only if the option 'Create a new attribute' is selected above.
          'enabled' => [
            ':input[id="option_create_or_existing"]' => ['value' => 'new'],
          ],
          'required' => [
            ':input[id="option_create_or_existing"]' => ['value' => 'new'],
          ],
        ],
        '#attributes' => [
          'id' => 'option_data_type',
        ],
      ],
      'text_options_control' => [
        '#title' => 'Text control options',
        '#type' => 'select',
        '#options' => [
          'text_input' => 'Single line',
          'textarea' => 'Multiple lines',
        ],
        '#states' => [
          // Show this control only if the attribute type Text is selected above.
          'visible' => [
            ':input[id="option_data_type"]' => ['value' => 'T'],
          ],
        ],
      ],
      'number_options_min' => [
        '#title' => 'Minimum value allowed',
        '#type' => 'number',
        '#step' => 'any',
        '#states' => [
          // Show this control only if the attribute type is numeric.
          'visible' => [
            ':input[id="option_data_type"]' => [['value' => 'I'], ['value' => 'F']],
          ],
        ],
      ],
      'number_options_max' => [
        '#title' => 'Maximum value allowed',
        '#type' => 'number',
        '#step' => 'any',
        '#states' => [
          // Show this control only if the attribute type is numeric.
          'visible' => [
            ':input[id="option_data_type"]' => [['value' => 'I'], ['value' => 'F']],
          ],
        ],
      ],
      'lookup_options_terms' => [
        '#title' => 'Lookup control terms',
        '#description' => 'Enter one option per line.',
        '#type' => 'textarea',
        '#states' => [
          // Show this control only if the attribute type Lookup is selected.
          'visible' => [
            ':input[id="option_data_type"]' => ['value' => 'L'],
          ],
        ],
      ],
      'lookup_options_control' => [
        '#title' => 'Lookup control',
        '#description' => 'Choose which control to use for presenting the options.',
        '#type' => 'select',
        '#options' => [
          'select' => 'Drop down (select)',
          'radio_group' => 'Radio buttons (allows single choice)',
          'checkbox_group' => 'Checkboxes (allows multiple choices)'
        ],
        '#states' => [
          // Show this control only if the attribute type Lookup is selected.
          'visible' => [
            ':input[id="option_data_type"]' => ['value' => 'L'],
          ],
        ],
      ],
      'existing_attribute_id' => [
        '#title' => 'Existing attribute',
        '#description' => 'Select the pre-existing attribute.',
        '#type' => 'select',
        '#options' => $existingAttributes,
        '#states' => [
          // Show this control only if the option 'Use a pre-existing attribute' is selected above.
          'visible' => [
            ':input[id="option_create_or_existing"]' => ['value' => 'existing'],
          ],
        ],
      ],
    ];
    if (!$attrAdmin) {
      // Controls only visible if attribute administrator or creating new control.
      $fieldList['admin_name']['states'] = [
        'visible' => [
          ':input[id="option_create_or_existing"]' => ['value' => 'new'],
        ],
      ];
      $fieldList['admin_description']['states'] = [
        'visible' => [
          ':input[id="option_create_or_existing"]' => ['value' => 'new'],
        ],
      ];
      // Controls only enabled if attribute administrator or creating new control.
      $fieldList['lookup_options_terms']['states'] = [
        // Enable this control only if the option 'Create a new attribute' is selected.
        'enabled' => [
          ':input[id="option_create_or_existing"]' => ['value' => 'new'],
        ],
      ];
      $fieldList['number_options_min']['states'] = [
        // Enable this control only if the option 'Create a new attribute' is selected.
        'enabled' => [
          ':input[id="option_create_or_existing"]' => ['value' => 'new'],
        ],
      ];
      $fieldList['number_options_max']['states'] = [
        // Enable this control only if the option 'Create a new attribute' is selected.
        'enabled' => [
          ':input[id="option_create_or_existing"]' => ['value' => 'new'],
        ],
      ];
    }
    return $fieldList;
  }

  /**
  * {@inheritdoc}
  */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('option_label', microtime(TRUE));
    parent::blockSubmit($form, $form_state);
  }

  /**
  * {@inheritdoc}
  */
  public function blockForm($form, FormStateInterface $form_state) {
    $blockConfig = $this->getConfiguration();
    $this->updateFromWarehouseAttribute($blockConfig);
    return parent::blockForm($form, $form_state);
  }

  /**
   * Ensures that existing attribute configuration loads latest from warehouse.
   *
   * E.g. if warehouse contains new termlist terms, or a changed admin name
   * then update the local copy.
   */
  private function updateFromWarehouseAttribute($blockConfig) {
    if ($blockConfig['option_create_or_existing'] === 'existing') {
      $surveyStructure = new SurveyStructure();
      $existing = $surveyStructure->getAttribute($this->getAttrEntityName(), $blockConfig['option_existing_attribute_id']);
      if ($existing['httpCode'] === 404) {
        \Drupal::logger('iform_layout_builder')->notice('Existing attribute ID not found: @id', ['@id' => $blockConfig['option_existing_attribute_id']]);
        $this->setConfigurationValue('option_create_or_existing', 'new');
      }
      else {
        $this->setConfigurationValue('option_admin_name', $existing['response']['values']['caption']);
        $this->setConfigurationValue('option_data_type', $existing['response']['values']['data_type']);
        $this->setConfigurationValue('option_suffix', $existing['response']['values']['unit']);
        if (isset($existing['response']['terms'])) {
          $this->setConfigurationValue('option_lookup_options_terms', implode("\n", $existing['response']['terms']));
        }
      }
    }
  }

  /**
   * Builds the control for a custom attribute.
   *
   * @param array $blockConfig
   *   Block configuration.
   *
   * @return string
   *   Control HTML.
   */
  protected function getControl($blockConfig) {
    iform_load_helpers(['data_entry_helper']);
    $attrPrefix = $this->getAttrEntityName() === 'sample' ? 'smp' : 'occ';
    if ($blockConfig['option_create_or_existing'] === 'new') {
      $fieldname = "{$attrPrefix}Attr-new";
    }
    else {
      $fieldname = "{$attrPrefix}Attr:$blockConfig[option_existing_attribute_id]";
    }
    $ctrlName = 'text_input';
    switch ($blockConfig['option_data_type']) {
      case 'T':
        $ctrlName = empty($blockConfig['option_text_options_control']) ? 'text_input' : $blockConfig['option_text_options_control'];
        break;

      case 'L':
        $ctrlName = empty($blockConfig['option_lookup_options_control']) ? 'select' : $blockConfig['option_lookup_options_control'];
    }
    $ctrlOptions = [
      'fieldname' => $fieldname,
      'label' => "$blockConfig[option_label]",
    ];
    $helpTexts = [];
    if (!empty($blockConfig['option_helpText'])) {
      $helpTexts[] = $blockConfig['option_helpText'];
    }
    if ($blockConfig['option_create_or_existing'] === 'new') {
      $helpTexts[] = $this->t('This control has not been linked to a warehouse attribute yet.');
    }
    if (count($helpTexts) > 0) {
      $ctrlOptions['helpText'] = implode('<br/>', $helpTexts);
    }
    if ($blockConfig['option_required'] === 1) {
      $ctrlOptions['validation'] = ['required'];
    }
    if ($blockConfig['option_data_type'] === 'L') {
      if (!empty($blockConfig['option_existing_termlist_id'])) {
        $connection = iform_get_connection_details();
        $readAuth = \data_entry_helper::get_read_auth($connection['website_id'], $connection['password']);
        $ctrlOptions['table'] = 'termlists_term';
        $ctrlOptions['valueField'] = 'id';
        $ctrlOptions['captionField'] = 'term';
        $ctrlOptions['blankText'] = $this->t('-Please select-');
        $ctrlOptions['extraParams'] = $readAuth + [
          'termlist_id' => $blockConfig['option_existing_termlist_id'],
          // Form editors get the uncached terms view, users get cached terms table.
          'view' => !empty($_SESSION['iform_layout_builder-no_termlist_cache']) ? 'list' : 'cache',
          'allow_data_entry' => 't',
          'sharing' => 'editing',
          'orderby' => 'sort_order,term',
        ];
      }
      else {
        $ctrlOptions['lookupValues'] = ['' => '-Will be populated from lookup options-'];
      }
    }
    return \data_entry_helper::$ctrlName($ctrlOptions);
  }
}