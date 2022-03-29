<?php

namespace Drupal\tmg_utility\Plugin\WebformHandler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\Utility\WebformElementHelper;
use Drupal\webform\Utility\WebformYaml;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Webform submission handler for removing cookie.
 *
 * @WebformHandler(
 *   id = "redirect_handler",
 *   label = @Translation("Webform redirect handler"),
 *   category = @Translation("Action"),
 *   description = @Translation("Webform redirect handler."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class WebformRedirectHandler extends WebformHandlerBase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The DrupalKernel instance used in the test.
   *
   * @var \Drupal\Core\DrupalKernel
   */
  protected $kernel;


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->requestStack = $container->get('request_stack');
    $instance->kernel = $container->get('kernel');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'redirect_url' => ''
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $configuration = $this->getConfiguration();
    $settings = $configuration['settings'];

    return [
      '#settings' => $settings,
    ] + parent::getSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['redirect_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add url to redirect'),
      '#default_value' => $this->configuration['redirect_url'],
    ];
    return $this->setSettingsParents($form);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->applyFormStateToConfiguration($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state,
                             WebformSubmissionInterface $webform_submission) {
    global $base_url, $base_path;
    $redirect_url = $this->configuration['redirect_url'];

    if (empty($redirect_url)) {
      return;
    }

    if ($redirect_url[0] != "/") {
      $redirect_url = '/' .  $redirect_url;
    }
    $response = new TrustedRedirectResponse($redirect_url);
    $request = $this->requestStack->getCurrentRequest();
    // Save the session so things like messages get saved.
    $request->getSession()->save();
    $response->prepare($request);
    // Make sure to trigger kernel events.
    $this->kernel->terminate($request, $response);
    $response->send();
  }
}
