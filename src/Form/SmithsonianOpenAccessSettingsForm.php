<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Messenger\MessengerInterface;
use GuzzleHttp\Exception\RequestException;

use Drupal;

/**
 * Class SmithsonianOpenAccessSettingsForm.
 *
 * @package Drupal\smithsonian_open_access\Form
 */
class SmithsonianOpenAccessSettingsForm extends ConfigFormBase
{

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The HTTP client factory service.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClientFactory;

  /**
   * The default search endpoint URL.
   *
   * @var string
   */
  protected $searchEndpoint;

  /**
   * The default object endpoint URL.
   *
   * @var string
   */
  protected $objectEndpoint;

  /**
   * The default metadata endpoint URL.
   *
   * @var string
   */
  protected $metadataEndpoint;

  /**
   * The HTTP client factory.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $http_client_factory;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   *   The HTTP client factory service.
   */
  public static function create(ContainerInterface $container) {
    // Get the config.factory service.
    $config_factory = $container->get('config.factory');
    // Get the http_client_factory service.
    $http_client_factory = $container->get('http_client_factory');
    // Get the messenger service.
    $messenger = $container->get('messenger');
    // Return a new instance of the form.
    return new static($config_factory, $http_client_factory, $messenger);
  }

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ClientFactory $http_client_factory, MessengerInterface $messenger)
  {
    parent::__construct($config_factory);
    $this->httpClientFactory = $http_client_factory;
    $this->messenger = $messenger;

    $config = $this->config('smithsonian_open_access.settings');

    $this->searchEndpoint = $config->get('search_endpoint') ?: 'https://api.si.edu/openaccess/api/v1.0/search';
    $this->objectEndpoint = $config->get('object_endpoint') ?: 'https://api.si.edu/openaccess/api/v1.0/content';
    $this->metadataEndpoint = $config->get('metadata_endpoint') ?: 'https://api.si.edu/openaccess/api/v1.0/metadata';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return ['smithsonian_open_access.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'smithsonian_open_access_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('smithsonian_open_access.settings');

    $form['search_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search Endpoint URL'),
      '#description' => $this->t('Enter the base URL for the Smithsonian Open Access search endpoint.'),
      '#default_value' => $this->searchEndpoint,
      '#required' => TRUE,
    ];

    $form['object_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Object Endpoint URL'),
      '#description' => $this->t('Enter the base URL for the Smithsonian Open Access object endpoint.'),
      '#default_value' => $this->objectEndpoint,
      '#required' => TRUE,
    ];

    $form['metadata_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Metadata Endpoint URL'),
      '#description' => $this->t('Enter the base URL for the Smithsonian Open Access metadata endpoint.'),
      '#default_value' => $this->metadataEndpoint,
      '#required' => TRUE,
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('Enter your Smithsonian Open Access API key from Data.gov.'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    $form['test_api_key'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test API Key'),
      '#submit' => ['::testApiKey'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);
    $api_key = $form_state->getValue('api_key');
    if (empty($api_key)) {
      $form_state->setErrorByName('api_key', $this->t('You must enter an API key.'));
    }
  }

  /**
   * Tests the API key by making a request to the search endpoint.
   */
  public function testApiKey(array &$form, FormStateInterface $form_state) {
    $api_key = $form_state->getValue('api_key');
    $search_endpoint = $form_state->getValue('search_endpoint');
    $object_endpoint = $form_state->getValue('object_endpoint');
    $metadata_endpoint = $form_state->getValue('metadata_endpoint');

    $this->config('smithsonian_open_access.settings')
      ->set('search_endpoint', $search_endpoint)
      ->set('object_endpoint', $object_endpoint)
      ->set('metadata_endpoint', $metadata_endpoint)
      ->set('api_key', $api_key)
      ->save();

    $client = $this->httpClientFactory->fromOptions();
    try {
      $response = $client->request('GET', $search_endpoint, [
        'query' => [
          'api_key' => $api_key,
          'q' => 'smithsonian',
        ],
      ]);
      if ($response->getStatusCode() === 200) {
        $form_state->setRebuild(TRUE);
        $this->messenger()->addMessage($this->t('API key is valid.'));
      }
    }
    catch (RequestException $e) {
      $response = $e->getResponse();
      if ($response && $response->getStatusCode() === 403) {
        $this->messenger()->addError($this->t('Invalid API key.'));
      }
      else {
        $this->messenger()->addError($this->t('An error occurred while testing the API key.  Either the Search Endpoint URL is not correct or the API Key is not valid.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $config = $this->config('smithsonian_open_access.settings');
    $config->set('search_endpoint', $form_state->getValue('search_endpoint'));
    $config->set('object_endpoint', $form_state->getValue('object_endpoint'));
    $config->set('metadata_endpoint', $form_state->getValue('metadata_endpoint'));
    $config->set('api_key', $form_state->getValue('api_key'));
    $config->save();
    parent::submitForm($form, $form_state);
  }
}

