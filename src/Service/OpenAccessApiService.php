<?php

namespace Drupal\smithsonian_open_access;

use Drupal\smithsonian_open_access\Service\OpenAccessApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides a form for testing the Smithsonian Open Access API.
 */
class OpenAccessApi {

  /**
   * The Smithsonian Open Access API service.
   *
   * @var OpenAccessApiService
   */
  protected $openAccessApiService;

  /**
   * Constructs an OpenAccessApi object.
   *
   * @param OpenAccessApiService $openAccessApiService
   *   The Smithsonian Open Access API service.
   */
  public function __construct(OpenAccessApiService $openAccessApiService) {
    $this->openAccessApiService = $openAccessApiService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): OpenAccessApi
  {
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
