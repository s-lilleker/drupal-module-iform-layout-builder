<?php

namespace Drupal\iform_layout_builder\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

abstract class IndiciaPhotoBlockBase extends IndiciaControlBlockBase {

  protected function getControl($table) {
    iform_load_helpers(['data_entry_helper']);
    $connection = iform_get_connection_details();
    $readAuth = \data_entry_helper::get_read_auth($connection['website_id'], $connection['password']);
    $blockConfig = $this->getConfiguration();
    $ctrlOptions = array(
      'caption' => $blockConfig['option_label'],
      'table' => $table,
      'readAuth' => $readAuth,
      'resizeWidth' => 1600,
      'resizeHeight' => 1600
    );
    $ctrl = \data_entry_helper::file_box($ctrlOptions);
  }

}