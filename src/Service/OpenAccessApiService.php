<?php

namespace Drupal\smithsonian_open_access\Service;

use GuzzleHttp\Client;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Service class for communicating with the Smithsonian Open Access API.
 */
class OpenAccessApiService {

  /**
   * The API base URL.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * The API key.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * The Guzzle client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructs an OpenAccessApiService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \GuzzleHttp\Client $http_client
   *   The Guzzle client.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Client $http_client) {
    $config = $config_factory->get('smithsonian_open_access.open_access_api_connection');
    $this->baseUrl = $config->get('api_base_url');
    $this->apiKey = $config->get('api_key');
    $this->httpClient = $http_client;
  }

  /**
   * Searches the API for the given query string.
   *
   * @param string $query
   *   The search query.
   *
   * @return array
   *   The API response data.
   */
  public function search($query) {
    $url = $this->baseUrl . '/search?q=' . urlencode($query) . '&api_key=' . $this->apiKey;

    try {
      $response = $this->httpClient->get($url);
      $response_data = $response->getBody()->getContents();
      $response_json = json_decode($response_data, TRUE);
    }
    catch (\Exception $e) {
      $response_json = ['error' => $e->getMessage()];
    }

    return $response_json;
  }

}
