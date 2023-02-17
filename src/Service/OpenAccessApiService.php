<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smithsonian_open_access\Service\OpenAccessApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
   * Constructs an OpenAccessApiTestForm object.
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
    return 'smithsonian_open_access_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['results'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Results'),
      '#rows' => 20,
      '#description' => $this->t('The JSON API response.'),
      '#attributes' => [
        'class' => ['open-access-results'],
      ],
    ];

    $form['query'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Query'),
      '#description' => $this->t('Enter a query string to search the Smithsonian Open Access API.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'wrapper' => 'open-access-results-wrapper',
        'effect' => 'fade',
      ],
    ];

    $form['#prefix'] = '<div id="open-access-test-form-wrapper">';
    $form['#suffix'] = '</div>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Nothing to do here.
  }

  /**
   * Ajax callback for the test form submit.
   */
  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $results = [];
    $query = $form_state->getValue('query');
    if (!empty($query)) {
      $results = $this->openAccessApiService->search($query);
    }

    return new JsonResponse($results);
  }

}
