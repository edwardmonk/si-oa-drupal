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
   * @param \GuzzleHttp\Client $http_client
   *   The Guzzle client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(Client $http_client, ConfigFactoryInterface $config_factory) {
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

/**
 * Updates the last API response in the configuration.
 *
 * @param array $response_data
 *   The API response data.
 */
public function updateLastApiResponse(array $response_data) {
  $config = $this->configFactory->getEditable('smithsonian_open_access.open_access_api_connection');
  $config->set('last_api_response', json_encode($response_data))
    ->save();
}


}
