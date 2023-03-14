<?php

namespace Drupal\smithsonian_open_access;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class Api {
  protected $configFactory;
  protected $client;
  protected $logger;

  public function __construct(ConfigFactoryInterface $config_factory, Client $client, LoggerInterface $logger) {
    $this->configFactory = $config_factory;
    $this->client = $client;
    $this->logger = $logger;
  }

  private function performApiRequest($endpoint, $query_params) {
    try {
      $config = $this->configFactory->get('smithsonian_open_access.settings');
      $base_uri = $config->get('base_uri') ?: 'https://api.si.edu/openaccess/api/v1.0/';
      $api_key = $config->get('api_key');

      $query_params['api_key'] = $api_key;

      $response = $this->client->get($base_uri . $endpoint, [
        'query' => $query_params,
      ]);

      if ($response->getStatusCode() == 200) {
        return $response;
      } else {
        throw new \Exception('Error while performing API request.');
      }
    } catch (RequestException $e) {
      $this->logger->error('Error while performing API request: @error', ['@error' => $e->getMessage()]);
      return null;
    }
  }

  public function validateApiKey($api_key) {
    $config = $this->configFactory->get('smithsonian_open_access.settings');
    $search_endpoint = $config->get('search_endpoint') ?: 'search';

    $response = $this->performApiRequest($search_endpoint, [
      'api_key' => $api_key,
      'q' => 'smithsonian',
      'rows' => 1,
    ]);

    if ($response) {
      return ['success' => true];
    } else {
      return ['success' => false, 'error' => 'Invalid API key'];
    }
  }

  public function search($query, $start = 0, $rows = 10, $sort = 'relevancy', $type = 'edanmdm', $row_group = 'objects') {
    $config = $this->configFactory->get('smithsonian_open_access.settings');
    $search_endpoint = $config->get('search_endpoint') ?: 'search';

    $params = [
      'q' => $query,
      'start' => $start,
      'rows' => $rows,
      'sort' => $sort,
      'type' => $type,
      'row_group' => $row_group,
    ];

    $response = $this->performApiRequest($search_endpoint, $params);

    if ($response->getStatusCode() == 200) {
      $data = json_decode($response->getBody(), true);
      return $data;
    } else {
      throw new \Exception('Error while performing the search request: ' . $response->getReasonPhrase());
    }
  }



}



