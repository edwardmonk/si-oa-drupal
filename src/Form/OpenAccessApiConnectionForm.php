<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\smithsonian_open_access\Service\OpenAccessApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a form for configuring the Smithsonian Open Access API connection.
 */
class OpenAccessApiConnectionForm extends FormBase {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Smithsonian Open Access API service.
   *
   * @var \Drupal\smithsonian_open_access\OpenAccessApiService
   */
  protected $openAccessApiService;

  /**
   * Constructs a new OpenAccessApiConnectionForm instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \Drupal\smithsonian_open_access\OpenAccessApiService $openAccessApiService
   *   The Smithsonian Open Access API service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, OpenAccessApiService $openAccessApiService) {
    $this->configFactory = $configFactory;
    $this->openAccessApiService = $openAccessApiService;
    $this->messenger = $messenger;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('smithsonian_open_access.api_service'),
      $container->get('messenger')

    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smithsonian_open_access_open_access_api_connection_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('smithsonian_open_access.open_access_api_connection');

    $form['api_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API base URL'),
      '#description' => $this->t('Enter the base URL for the Smithsonian Open Access API.'),
      '#default_value' => $config->get('api_base_url'),
      '#required' => TRUE,
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#description' => $this->t('Enter the API key for the Smithsonian Open Access API.'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('smithsonian_open_access.open_access_api_connection');

    $config->set('api_base_url', $form_state->getValue('api_base_url'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    $this->messenger->addStatus($this->t('The configuration options have been saved.'));

  }

}
