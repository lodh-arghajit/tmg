<?php

namespace Drupal\tmg_utility\Plugin\WebformElement;

use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Url;
use Drupal\webform\Plugin\WebformElement\Hidden;
use Drupal\webform\Plugin\WebformElement\OptionsBase;
use Drupal\webform\Plugin\WebformElementBase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ValuePerPath
 *
 * @WebformElement(
 *   id = "value_per_path",
 *   label = @Translation("Value Per Path Element"),
 *   description = @Translation("Provides a value per path element."),
 *   category = @Translation("Options elements"),
 * )
 * @package Drupal\prepaypower_forms\Plugin\WebformElement
 */
class ValuePerPath extends Hidden {

  /**
   * An alias manager to find the alias for the current system path.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->aliasManager = $container->get('path_alias.manager');
    $instance->pathMatcher = $container->get('path.matcher');
    $instance->requestStack = $container->get('request_stack');
    $instance->currentPath = $container->get('path.current');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    // Include only the access-view-related base properties.
    $access_properties = $this->defineDefaultBaseProperties();
    $access_properties = array_filter($access_properties, function ($access_default, $access_key) {
      return strpos($access_key, 'access_') === 0;
    }, ARRAY_FILTER_USE_BOTH);

    return [
        // Element settings.
        'title' => '',
        'default_value' => '',
        'value' => '',
        // Administration.
        'prepopulate' => FALSE,
        'private' => FALSE,
      ] + $access_properties;
  }

  /**
   * @inheritDoc
   */
  public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
    $values = $element['#value'] ?? [];
    $element['#type'] = 'hidden';
    $element['#value'] = $this->evaluatePath($values) ?: $element['#value']['default'];
  }

  /**
   * @inheritDoc
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['element']['value'] = [
      '#type' => 'value_per_path',
      '#title' => $this->t('Value Per Path'),
      '#options_description' => $this->hasProperty('options_description_display'),
      '#required' => TRUE,
    ];

    unset($form['display']);
    unset($form['element']['default_value']);

    return $form;
  }

  /**
   * Evaluate paths from the key value field to match with the current page.
   *
   * @param $paths
   *
   * @return false|mixed
   */
  protected function evaluatePath($paths) {
    $return_value = FALSE;
    $request = $this->requestStack->getCurrentRequest();

    // Compare the lowercase path alias (if any) and internal path.
    $path = $request->getPathInfo();


    foreach ($paths as $url => $value) {
      // Convert path to lowercase. This allows comparison of the same path
      // with different case. Ex: /Page, /page, /PAGE.
      $page = mb_strtolower($url);

      // Do not trim a trailing slash if that is the complete path.
      $path = $path === '/' ? $path : rtrim($path, '/');

      $path_alias = $path === '/' ? '/' : mb_strtolower($this->aliasManager->getAliasByPath($path));

      if ($this->pathMatcher->matchPath($path_alias, $page) || (($path != $path_alias) && $this->pathMatcher->matchPath($path, $page))) {
        $return_value = $value;
      }
    }

    return $return_value;
  }
}
