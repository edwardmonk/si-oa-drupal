<?php

namespace Drupal\smithsonian_open_access;

use GuzzleHttp\ClientInterface;

class Api {

  protected $httpClient;
  protected $baseUri;
  protected $endpoints;

  /**
   * Api constructor.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   * @param array $config
   *   The configuration array.
   */
  public function __construct(ClientInterface $httpClient, array $config) {
    $this->httpClient = $httpClient;
    $this->baseUri = $config['base_uri'];
    $this->endpoints = $config['endpoints'];
  }

  /**
   * Performs a search against the Smithsonian Open Access API.
   *
   * @param string $query
   *   The search query.
   *
   * @return array
   *   The search results.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function search(string $query): array {
    $response = $this->httpClient->request('GET', $this->baseUri . $this->endpoints['search'], [
      'query' => [
        'api_key' => $this->endpoints['api_key'],
        'q' => $query,
      ],
    ]);

    return json_decode($response->getBody(), TRUE);
  }

  /**
   * Retrieves a specific content item from the Smithsonian Open Access API.
   *
   * @param string $id
   *   The ID of the content item to retrieve.
   *
   * @return array
   *   The content item.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getContent(string $id): array {
    $response = $this->httpClient->request('GET', $this->baseUri . str_replace('{id}', $id, $this->endpoints['content']), [
      'query' => [
        'api_key' => $this->endpoints['api_key'],
      ],
    ]);

    return json_decode($response->getBody(), TRUE);
  }

  /**
   * Retrieves statistics from the Smithsonian Open Access API.
   *
   * @return array
   *   The statistics.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getStats(): array {
    $response = $this->httpClient->request('GET', $this->baseUri . $this->endpoints['stats'], [
      'query' => [
        'api_key' => $this->endpoints['api_key'],
      ],
    ]);

    return json_decode($response->getBody(), TRUE);
  }

}
