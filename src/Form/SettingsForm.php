<?php

/**
 * @file
 * Contains \Drupal\iform_layout_builder\Form\SettingsForm.
 */

namespace Drupal\iform_layout_builder\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Indicia Layout Builder settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iform_layout_builder_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('iform_layout_builder.settings');
    // Load available lists.
    $lists = [];
    iform_load_helpers([]);
    $conn = iform_get_connection_details();
    $readAuth = \helper_base::get_read_auth($conn['website_id'], $conn['password']);
    try {
      $listData = \helper_base::get_population_data([
        'table' => 'scratchpad_list',
        'extraParams' => $readAuth,
      ]);
      foreach ($listData as $item) {
        $lists[$item['id']] = $item['title'];
      }
    }
    catch (\Throwable $e) {
      if (strpos($e->getMessage(), 'Unrecognised entity scratchpad_list') !== FALSE)  {
        $this->messenger()->addWarning($this->t('The warehouse "scratchpad" module must be enabled to use the Indicia Layout Builder. Please contact your warehouse administrator.'));
      }
      else {
        throw $e;
      }
    }
    // Sensitivity scratchpad field.
    $form['sensitivity_scratchpad_list_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Sensitive species list'),
      '#default_value' => $config->get('sensitivity_scratchpad_list_id'),
      '#description' => $this->t('To enable sensitive species blurring on all forms, select the list containing sensitive species here.'),
      '#required' => TRUE,
      '#options' => $lists,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('iform_layout_builder.settings');
    $config->set('sensitivity_scratchpad_list_id', $form_state->getValue('sensitivity_scratchpad_list_id'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }



  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'iform_layout_builder.settings',
    ];
  }

}