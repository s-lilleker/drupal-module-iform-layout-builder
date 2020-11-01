<?php

namespace Drupal\iform_layout_builder\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class IndiciaControlBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

  protected $routeMatch;

  abstract protected function getControlConfigFields();

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param Drupal\Core\Routing\RouteMatchInterface $route_match
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    // Disable the block title as we use Indicia labels instead.
    $form['label']['#access'] = FALSE;
    $form['label_display']['#access'] = FALSE;
    $form['label_display']['#default_value'] = FALSE;
    return $form;
  }

  /**
  * {@inheritdoc}
  */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form['#id'] = get_called_class();
    $blockConfig = $this->getConfiguration();
    $configFields = $this->getControlConfigFields();
    foreach ($configFields as $configField => $cfg) {
      // Default type may be overridden.
      $formFieldOpts = [
        '#type' => 'textfield',
        '#title' => ucfirst(str_replace('_', ' ', $configField)),
      ];
      // Apply defined control config.
      foreach ($cfg as $option => $value) {
        // Copy native Drupal form field options over.
        if (substr($option, 0, 1) === '#') {
          // Translate text options.
          if (in_array($option, ['#title', '#description', '#empty_option'])) {
            $value = $this->t($value);
          }
          // Also option lists.
          if ($option === '#options') {
            foreach ($value as &$label) {
              $label = $this->t($label);
            }
          }
          $formFieldOpts[$option] = $value;
        }
        else {
          if ($option === 'populateOptions') {
            $lookupOptions = [];
            iform_load_helpers(['helper_base']);
            if (empty($value['table']) || empty($value['valueField']) || empty($value['captionField'])) {
              throw new \Exception('Invalid populateOptions defined for control');
            }
            if (empty($cfg['required'])) {
              $lookupOptions[''] = $this->t('-Select an option-');
            }
            $extraParams = isset($value['extraParams']) ? $value['extraParams'] : [];
            $connection = iform_get_connection_details();
            $readAuth = \helper_base::get_read_auth($connection['website_id'], $connection['password']);
            $data = \helper_base::get_population_data([
              'table' => $value['table'],
              'orderby' => $value['captionField'],
              'extraParams' => $readAuth + $extraParams,
            ]);
            foreach ($data as $item) {
              $lookupOptions[$item[$value['valueField']]] = $this->t($item[$value['captionField']]);
            }
            $formFieldOpts['#options'] = $lookupOptions;
          }
        }
      }
      if (isset($blockConfig["option_$configField"])) {
        // Value already saved for this option for the block.
        $defaultValue = $blockConfig["option_$configField"];
      }
      elseif (isset($formFieldOpts['#default_value'])) {
        // Value not saved, so use default defined for the field.
        $defaultValue = $formFieldOpts['#default_value'];
      }
      else {
        // Set a blank value as not specified.
        $defaultValue = $formFieldOpts['#type'] === 'checkbox' ? '0' : '';
      }
      $formFieldOpts['#default_value'] = isset($blockConfig["option_$configField"]) ? $blockConfig["option_$configField"] : $defaultValue;
      if (!isset($formFieldOpts['#title'])) {
        $formFieldOpts['#title'] = $configField;
      }
      if (isset($formFieldOpts['#type']) && isset($formFieldOpts['#description']) && $formFieldOpts['#type'] === 'checkboxes') {
        // Layout builder block config form doesn't show description.
        $formFieldOpts['#field_suffix'] = $formFieldOpts['#description'];
        unset($formFieldOpts['#description']);
      }
      $form["option_$configField"] = $formFieldOpts;
    }
    $form['#attached'] = [
      'library' => [
        'iform_layout_builder/block.attrblockform',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $configFields = $this->getControlConfigFields();
    foreach (array_keys($configFields) as $configField) {
      $this->setConfigurationValue("option_$configField", $form_state->getValue("option_$configField"));
    }
  }

  protected function getCurrentNode() {
    // If a normal call, the RouteMatch service is the most reliable way.
    $node = $this->routeMatch->getParameter('node');
    if (!$node) {
      \Drupal::logger('iform_layout_builder')->notice('Route match failed to find node');
      \Drupal::logger('iform_layout_builder')->notice('Referrer: ' . $_SERVER['HTTP_REFERER']);
    }
    if (!$node && !empty($_SERVER['HTTP_REFERER'])) {
      // Use referrer if inside a layout builder AJAX call.
      if (preg_match('/node\/(?P<nid>\d+)\//', $_SERVER['HTTP_REFERER'], $matches)) {
        \Drupal::logger('iform_layout_builder')->notice('Preg match: ' . var_export($matches, TRUE));
        $nid = $matches['nid'];
        $node = \Drupal\node\Entity\Node::load($nid);
      }
    }
    return $node;
  }

  protected function getAvailableMapSystems() {
    iform_load_helpers(['helper_base']);
    $systems = \Drupal::config('iform.settings')->get('spatial_systems');
    $systemList = explode(',', $systems);
    $mapSystems = [];
    foreach ($systemList as $code) {
      $label = \lang::get("sref:$code");
      if ($label === "sref:$code") {
        $label = is_numeric($code) ? "EPSG:$code" : $code;
      }
      $mapSystems[$code] = $label;
    }
    return $mapSystems;
  }

}