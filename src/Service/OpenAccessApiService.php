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
class OpenAccessApiService extends FormBase {

  /**
   * The Smithsonian Open Access API service.
   *
   * @var \Drupal\smithsonian_open_access\Service\OpenAccessApiService
   */
  protected $openAccessApiService;

  /**
   * Constructs an OpenAccessApiService object.
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
