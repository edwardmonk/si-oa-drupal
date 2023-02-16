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
    return 'smithsonian_open_access_api_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#description' => $this->t('Enter a search term to test the API.'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test API'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $client = new Client();
    $config = \Drupal::config('smithsonian_open_access.settings');
    $base_url = $config->get('api_base_url');
    $api_key = $config->get('api_key');
    $search = $form_state->getValue('search');

    $url = $base_url . '/search?q=' . urlencode($search) . '&api_key=' . $api_key;

    try {
      $response = $client->get($url);
      $response_data = $response->getBody()->getContents();
      $response_json = json_decode($response_data);

      $form_state->set('last_api_response', $response_data);
      $form_state->setValue('response', $response_data);

    } catch (\Exception $e) {
      $response_json = ['error' => $e->getMessage()];

      $form_state->set('last_api_response', json_encode($response_json));

      $form_state->setValue('response', json_encode($response_json));
    }
  }

}