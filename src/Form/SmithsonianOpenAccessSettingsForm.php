<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smithsonian_open_access\Api\ClientFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for configuring Smithsonian Open Access API settings.
 */
class SmithsonianOpenAccessSettingsForm extends ConfigFormBase {

  /**
   * The base URI for the Smithsonian Open Access API.
   *
   * @var string
   */
  const BASE_URI = 'https://api.si.edu/openaccess';

  /**
   * The search endpoint for the Smithsonian Open Access API.
   *
   * @var string
   */
  const SEARCH_ENDPOINT = 'search';

  /**
   * The content endpoint for the Smithsonian Open Access API.
   *
   * @var string
   */
  const CONTENT_ENDPOINT = 'content';

  /**
   * The stats endpoint for the Smithsonian Open Access API.
   *
   * @var string
   */
  const STATS_ENDPOINT = 'stats';

  /**
   * The API client factory.
   *
   * @var \Drupal\smithsonian_open_access\Api\ClientFactory
   */
  protected $clientFactory;

  /**
   * SmithsonianOpenAccessSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\smithsonian_open_access\Api\ClientFactory $clientFactory
   *   The API client factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ClientFactory $clientFactory) {
    parent::__construct($configFactory);
    $this->clientFactory = $clientFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('smithsonian_open_access.api.client_factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smithsonian_open_access_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'smithsonian_open_access.settings',
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('smithsonian_open_access.settings');

    $form['base_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URI'),
      '#description' => $this->t('The base URI for the Smithsonian Open Access API.'),
      '#default_value' => $config->get('base_uri'),
      '#required' => TRUE,
    ];

    $form['search_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search endpoint'),
      '#description' => $this->t('The search endpoint for the Smithsonian Open Access API.'),
      '#default_value' => $config->get('search_endpoint'),
      '#required' => TRUE,
    ];

    $form['content_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content endpoint'),
      '#description' => $this->t('The content endpoint for the Smithsonian Open Access API.'),
      '#default_value' => $config->get('content_endpoint'),
      '#required' => TRUE,
    ];

    $form['stats_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stats endpoint'),
      '#description' => $this->t('The stats endpoint for the Smithsonian Open Access API.'),
      '#default_value' => $config->get('stats_endpoint'),
      '#required' => TRUE,
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('Your API key for the Smithsonian Open Access API.'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('smithsonian_open_access.settings');
    $config->set('base_uri', $form_state->getValue('base_uri'))
      ->set('search_endpoint', $form_state->getValue('search_endpoint'))
      ->set('content_endpoint', $form_state->getValue('content_endpoint'))
      ->set('stats_endpoint', $form_state->getValue('stats_endpoint'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
