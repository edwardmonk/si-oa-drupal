<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Client;

/**
 *  * Defines a form to configure the Smithsonian Open Access API connection.
 *   */
class OpenAccessApiConnectionForm extends ConfigFormBase {

  /**
 *    * {@inheritdoc}
 *       */
  public function getFormId() {
    return 'smithsonian_open_access_open_access_api_connection_form';
  }

  /**
 *    * {@inheritdoc}
 *       */
  protected function getEditableConfigNames() {
    return ['smithsonian_open_access.open_access_api_connection'];
  }

  /**
 *    * {@inheritdoc}
 *       */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('smithsonian_open_access.open_access_api_connection');

    $form['api_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Base URL'),
      '#description' => $this->t('Enter the base URL for the Smithsonian Open Access API.'),
      '#default_value' => $config->get('api_base_url'),
      '#required' => TRUE,
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('Enter the API key for the Smithsonian Open Access API.'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    $form['search_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Test Smithsonian Open Access API'),
      '#open' => FALSE,
      '#tree' => TRUE,
      '#parents' => ['search_form'],
    ];

    $form['search_form'] += $this->getSearchForm();

    $form['response'] = [
      '#type' => 'textarea',
      '#title' => $this->t('API Response'),
      '#default_value' => $config->get('last_api_response') ?: '',
      '#rows' => 20,
      '#attributes' => [
        'readonly' => 'readonly',
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

 /**
 *    * Submits the search form.
 *       */
 public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('smithsonian_open_access.open_access_api_connection');
    $config->set('api_base_url', $form_state->getValue('api_base_url'));
    $config->set('api_key', $form_state->getValue('api_key'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
 *    * Returns a search form.
 *       */
  protected function getSearchForm() {
    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#description' => $this->t('Enter a search term to test the API.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test'),
      '#submit' => ['::submitSearchForm'],
    ];

    return $form;
  }

public function submitSearchForm(array &$form, FormStateInterface $form_state) {
    $client = new Client();
    $config = $this->config('smithsonian_open_access.open_access_api_connection');
    $base_url = $config->get('api_base_url');
    $api_key = $config->get('api_key');
    $search = $form_state->getValue(['search_form', 'search']);

    $url = $base_url . '/search?q=' . urlencode($search) . '&api_key=' . $api_key;

    try {
      $response = $client->get($url);
      $response_data = $response->getBody()->getContents();
      $response_json = json_decode($response_data);

      $form_state->set('last_api_response', $response_data);

      $form['response']['#default_value'] = $response_data;
    } catch (\Exception $e) {
      $response_json = ['error' => $e->getMessage()];

      $form_state->set('last_api_response', json_encode($response_json));

      $form['response']['#default_value'] = json_encode($response_json);
    }

    $this->messenger()->addMessage($this->t('API response: @response', [
      '@response' => $form_state->get('last_api_response'),
    ]));
  }

}
