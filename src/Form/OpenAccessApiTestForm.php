<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\smithsonian_open_access\Service\OpenAccessApi;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for testing the Smithsonian Open Access API.
 */
class OpenAccessApiTestForm extends FormBase {

  /**
   * The Smithsonian Open Access API service.
   *
   * @var \Drupal\smithsonian_open_access\Service\OpenAccessApiService
   */
  protected $openAccessApiService;

  /**
   * Constructs a new OpenAccessApiTestForm instance.
   *
   * @param \Drupal\smithsonian_open_access\Service\OpenAccessApiService $openAccessApiService
   *   The Smithsonian Open Access API service.
   */
  public function __construct(OpenAccessApiService $openAccessApiService) {
    $this->openAccessApiService = $openAccessApiService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('smithsonian_open_access.api_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smithsonian_open_access_open_access_api_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['query'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API query'),
      '#description' => $this->t('Enter the query to send to the Smithsonian Open Access API.'),
      '#default_value' => '',
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => [$this, 'submitFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['results'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="open-access-api-test-results">',
      '#suffix' => '</div>',
      '#markup' => '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Ajax callback for the submit button.
   */
  public function submitFormAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $query = $form_state->getValue('query');
    $result = $this->openAccessApiService->queryApi($query);

    $output = json_encode($result, JSON_PRETTY_PRINT);
    $response->addCommand(new HtmlCommand('#open-access-api-test-results', $output));
    $response->addCommand(new InvokeCommand(NULL, 'scrollTop', [0]));

    return $response;
  }

}
