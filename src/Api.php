<?php

namespace Drupal\smithsonian_open_access;

use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientFactory;

class Api {

  protected $configFactory;

  protected $httpClient;

  /**
   * Constructs a new Api object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ClientInterface $httpClient) {
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
  }

  /**
   * Performs a search against the Smithsonian Open Access API search endpoint.
   *
   * @param string $query
   *   The search query.
   *
   * @return array|null
   *   An array of search results as JSON from the Smithsonian Open Access API.
   */
  public function search(string $query): ?array {
    // Log a message indicating that the function has been called.
    \Drupal::logger('smithsonian_open_access')->debug('Api called with search query: @query', ['@query' => $query]);

    $api_key = $this->config->get('api_key');
    $search_endpoint = $this->config->get('search_endpoint');

    try {
      $response = $this->httpClient->request('GET', $search_endpoint, [
        'query' => [
          'api_key' => $api_key,
          'q' => $query,
        ],
      ]);

      \Drupal::logger('smithsonian_open_access')->debug('Api query: @query', ['@query' => $query]);
      \Drupal::logger('smithsonian_open_access')->debug('Api API response: @response', ['@response' => print_r($response, TRUE)]);

      if ($response->getStatusCode() === 200) {
        return json_decode($response->getBody(), TRUE);
      }
      else {
        return NULL;
      }
    }
    catch (RequestException $e) {
      \Drupal::logger('smithsonian_open_access')->error($e->getMessage());
      return NULL;
    }
  }

}
