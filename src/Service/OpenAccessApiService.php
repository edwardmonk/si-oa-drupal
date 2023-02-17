<?php

namespace Drupal\smithsonian_open_access\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Provides a service for accessing the Smithsonian Open Access API.
 */
class OpenAccessApiService
{

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
    protected $client;

    /**
     * The logger.
     *
     * @var \Drupal\Core\Logger\LoggerChannelInterface
     */
    protected $logger;

    /**
     * Constructs a new OpenAccessApiService object.
     *
     * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
     *   The configuration factory.
     * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
     *   The logger factory.
     */
    public function __construct(ConfigFactoryInterface $configFactory, LoggerChannelFactoryInterface $loggerFactory)
    {
        $config = $configFactory->get('smithsonian_open_access.settings');

        $this->baseUrl = $config->get('api_base_url');
        $this->apiKey = $config->get('api_key');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
        ]);

        $this->logger = $loggerFactory->get('smithsonian_open_access');
    }

    /**
     * Makes a search request to the Smithsonian Open Access API.
     *
     * @param string $query
     *   The search query.
     *
     * @return array
     *   The search results.
     */
    public function search($query)
    {
        $results = [];

        try {
            $response = $this->client->get('/search', [
                'query' => [
                    'api_key' => $this->apiKey,
                    'q' => $query,
                    'rows' => 10,
                ],
            ]);

            $data = json_decode($response->getBody(), TRUE);
            $results = $data['response']['docs'];
        } catch (ClientException $e) {
            $this->logger->error('Failed to search the Smithsonian Open Access API. Error: @error', ['@error' => $e->getMessage()]);
        }

        return $results;
    }

}
