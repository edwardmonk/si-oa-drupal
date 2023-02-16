<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Client;

/**
 * Provides a form for testing the Smithsonian Open Access API.
 */
class OpenAccessApiTestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smithsonian_open_access_open_access_api_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
     $form['description'] = [
    '#type' => 'item',
    '#markup' => $this->t('Enter a search term to test the API.'),
  ];

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test'),
      '#ajax' => [
        'callback' => '::updateResponseField',
        'wrapper' => 'response-wrapper',
        'method' => 'replace',
      ],
    ];

    $form['search_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['container-inline'],
      ],
    ];

    $form['response_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
          'id' => 'response-wrapper',  
        ],
      ];

    $form['response_wrapper']['response'] = [
      '#type' => 'textarea',
      '#title' => $this->t('API Response'),
      '#rows' => 20,
      '#attributes' => [
        'readonly' => 'readonly',  
        ],
        '#value' => '',
      ];

    $form['response'] = [  '#type' => 'hidden',];

    $form['search_wrapper']['search'] = $form['search'];
    $form['search_wrapper']['submit'] = $form['actions']['submit'];
    unset($form['search']);
    unset($form['actions']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

  /**
   * AJAX callback to update the response field with the API response.
   */
  public function updateResponseField(array &$form, FormStateInterface $form_state) {
    $client = new Client();
    $config = \Drupal::config('smithsonian_open_access.open_access_api_connection');
    $base_url = $config->get('api_base_url');
    $api_key = $config->get('api_key');
    $search = $form_state->getValue('search');

    $url = $base_url . '/search?q=' . urlencode($search) . '&api_key=' . $api_key;

    try {
      $response = $client->get($url);
      $response_data = $response->getBody()->getContents();
      $response_json = json_decode($response_data);
    }
    catch (\Exception $e) {
      $response_json = ['error' => $e->getMessage()];
    }

    $response_value = !empty($response_json) ? json_encode($response_json, JSON_PRETTY_PRINT) : '';

    $form['response_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'response-wrapper',
      ],
    ];

    $form['response_wrapper']['response'] = [
      '#type' => 'textarea',
      '#title' => $this->t('API Response'),
      '#rows' => 20,
      '#attributes' => [
        'readonly' => 'readonly',
      ],
      '#value' => $response_value,
    ];

    return $form['response_wrapper'];
  }

}
