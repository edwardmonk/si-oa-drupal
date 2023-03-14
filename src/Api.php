<?php

namespace Drupal\smithsonian_open_access;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;

/**
 * Provides a service for interacting with the Smithsonian Open Access API.
 */
class Api {

  /**
   * The Drupal config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The API base URL.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * The API search endpoint.
   *
   * @var string
   */
  protected $searchEndpoint;

  /**
   * The API content endpoint.
   *
   * @var string
   */
  protected $contentEndpoint;

  /**
   * The API stats endpoint.
   *
   * @var string
   */
  protected $statsEndpoint;

  /**
   * Constructs a new Api object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client) {
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client;
    $this->baseUrl = $this->configFactory->get('smithsonian_open_access.settings')->get('base_url');
    $this->searchEndpoint = $this->configFactory->get('smithsonian_open_access.settings')->get('search_endpoint');
    $this->contentEndpoint = $this->configFactory->get('smithsonian_open_access.settings')->get('content_endpoint');
    $this->statsEndpoint = $this->configFactory->get('smithsonian_open_access.settings')->get('stats_endpoint');
  }

  /**
   * Executes a search query against the Smithsonian Open Access API.
   *
   * @param string $query
   *   The search query string.
   *
   * @return array
   *   An array of search results.
   */
  public function search($query) {
    $url = $this->baseUrl . '/' . $this->searchEndpoint;
    $options = [
      'query' => ['q' => $query],
    ];
    $response = $this->httpClient->get($url, $options);
    $data = $response->getBody()->getContents();
    return json_decode($data, TRUE);
  }

  /**
   * Retrieves content data for a specific record from the Smithsonian Open Access API.
   *
   * @param string $id
   *   The ID of the record to retrieve.
   *
   * @return array
   *   An array of content data for the specified record.
   */
  public function getContent($id) {
    $url = $this->baseUrl . '/' . $this->contentEndpoint . '/' . $id;
    $response = $this->httpClient->get($url);
    $data = $response->getBody()->getContents();
    return json_decode($data, TRUE);
  }

  /**
   * Get stats data from the API.
   *
   * @return array
   *   An associative array containing the response data.
   *
   * @throws \Exception
   */
  public function getStats(): array {
    $url = $this->buildUrl('/stats');

    try {
      $response = $this->httpClient->get($url);
    } catch (RequestException $e) {
      // Log the error message.
      $this->logger->error('Error occurred while calling @url: @message', [
        '@url' => $url,
        '@message' => $e->getMessage(),
      ]);

      throw new \Exception($this->t('An error occurred while calling the API. Please try again later.'));
    }

    $data = json_decode($response->getBody(), TRUE);
    $data = $this->transformData($data);

    return $data;
  }

  /**
   * Transform the raw API response data into a more usable format.
   *
   * @param array $data
   *   The raw API response data.
   *
   * @return array
   *   The transformed data.
   */
  protected function transformData(array $data): array {
    $result = [];

    if (!empty($data['results'])) {
      foreach ($data['results'] as $item) {
        $result[] = [
          'title' => $item['title'],
          'url' => $item['url'],
          'thumbnail' => $item['thumbnail'],
        ];
      }
    }

    return $result;
  }
}


