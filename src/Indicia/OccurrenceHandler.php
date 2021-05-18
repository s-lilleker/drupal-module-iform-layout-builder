<?php

namespace Drupal\iform_layout_builder\Indicia;

class OccurrenceHandler extends IndiciaRestClient {

  /**
   * Post a sample/occurrence submission.
   *
   * @param array $data
   *   Posted form data.
   * @param object $entity
   *   Drupal entity defining what type of form it is.
   *
   * @todo Uses data services so convert to REST API.
   */
  public function postRecord($data, $entity) {
    $config = \Drupal::config('iform.settings');
    $auth = \data_entry_helper::get_read_write_auth($config->get('website_id'), $config->get('password'));
    $values = array_merge($data, [
      'website_id' => $config->get('website_id'),
      'survey_id' => $entity->field_survey_id->value,
      'sample:input_form' => trim(\Drupal::service('path.alias_manager')
        ->getAliasByPath('/node/' . $entity->id()), '/'),
    ]);
    // Link to recording group/activity.
    if (!empty($_GET['group_id'])) {
      $values['sample:group_id'] = $_GET['group_id'];
    }
    $zeroAttrs = $this->getAbundanceAttrs($auth['read'], $entity->field_survey_id->value);
    if ($entity->field_form_type->value === 'single') {
      $submission = \data_entry_helper::build_sample_occurrence_submission($values, $zeroAttrs);
    }
    else {
      $submission = \data_entry_helper::build_sample_occurrences_list_submission($values, FALSE, $zeroAttrs);
    }
    $response = \data_entry_helper::forward_post_to('save', $submission, $auth['write_tokens']);
    if (is_array($response) && array_key_exists('success', $response)) {
      \Drupal::messenger()->addMessage("Thank you for the record");
    }
    elseif (isset($response['errors'])) {
      foreach ($response['errors'] as $key => $msg) {
        \Drupal::messenger()->addWarning(str_replace(':', ' ', $key) . ' - ' . $msg);
        // @todo Display errors correctly alongside controls.
      }
    }
  }

  /**
   * Finds the attributes for occurrence abundance.
   *
   * Used for zero abundance processing. Attribute term lists are also loaded.
   *
   * @param array $readAuth
   *   Read authorisation tokens.
   * @param int $surveyId
   *   Survey ID.
   *
   * @return array
   *   List of attribute definitions loaded from DB, keyed by attribute ID.
   */
  private function getAbundanceAttrs($readAuth, $surveyId) {
    $attrOpts = [
      'valuetable' => 'occurrence_attribute_value',
      'attrtable' => 'occurrence_attribute',
      'key' => 'occurrence_id',
      'fieldprefix' => 'occAttr',
      'extraParams' => $readAuth,
      'survey_id' => $surveyId,
    ];
    $attrs = \data_entry_helper::getAttributes($attrOpts, FALSE);
    $abundanceAttrs = [];
    foreach ($attrs as &$attr) {
      if ($attr['system_function'] === 'sex_stage_count') {
        // If we have any lookups, we need to load the terms so we can check
        // the data properly for zero abundance as term Ids are never zero.
        if ($attr['data_type'] === 'L') {
          $attr['terms'] = \data_entry_helper::get_population_data([
            'table' => 'termlists_term',
            'extraParams' => $readAuth + ['termlist_id' => $attr['termlist_id'], 'view' => 'cache', 'columns' => 'id,term'],
            'cachePerUser' => FALSE,
          ]);
        }
        $abundanceAttrs[$attr['attributeId']] = $attr;
      }
    }
    return $abundanceAttrs;
  }

  /**
   * Copies data from a REST response values array to $entity_to_load.
   *
   * Prepares the default values for data entry helper controls according to
   * loaded data for existing records.
   */
  private function copyEntityValueToLoadData($values, $prefix, $entityName) {
    foreach ($values as $key => $value) {
      if (substr($key, 0, 8) === "{$prefix}Attr:") {
        $fieldKey = $key;
        if ($value['multi_value'] === 'f') {
          $value = $value['raw_value'];
        }
        else {
          // Convert format to multi-value format recognised by data entry
          // helper.
          $multiVal = [];
          foreach ($value as $item) {
            $multiVal[] = [
              'fieldname' => "$fieldKey:$item[value_id]",
              'default' => $item['raw_value'],
            ];
          }
          $value = $multiVal;
        }
      }
      else {
        $fieldKey = "$entityName:$key";
        // Taxon label for autocomplete needs a special field name.
        if ($fieldKey === 'occurrence:taxon') {
          $fieldKey = 'occurrence:taxa_taxon_list_id:taxon';
        }
      }
      \data_entry_helper::$entity_to_load[$fieldKey] = $value;
    }

  }

  /**
   * Load a record ready to show on a form for editing.
   *
   * @param int $id
   *   Occurrence ID.
   * @param object $entity
   *   Drupal entity defining what type of form it is.
   */
  public function getRecord($id, $entity) {
    $response = $this->getRestResponse("occurrences/$id", 'GET', NULL, ['verbose' => 1]);
    $occurrence = $response['response']['values'];
    $response = $this->getRestResponse("samples/$occurrence[sample_id]", 'GET', NULL, ['verbose' => 1]);
    $sample = $response['response']['values'];
    \data_entry_helper::$entity_to_load = [];
    $this->copyEntityValueToLoadData($sample, 'smp', 'sample');
    if ($entity->field_form_type->value === 'single') {
      $this->copyEntityValueToLoadData($occurrence, 'occ', 'occurrence');
    }
  }
}