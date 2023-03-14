<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\HttpFoundation\Request;

/**
 * Configure Smithsonian Open Access settings for this site.
 */
class SmithsonianOpenAccessSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smithsonian_open_access_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'smithsonian_open_access.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('smithsonian_open_access.settings');

    $form['base_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URI'),
      '#default_value' => $config->get('base_uri'),
      '#required' => TRUE,
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    $form['search_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search Endpoint'),
      '#default_value' => $config->get('search_endpoint'),
      '#required' => TRUE,
    ];

    $form['content_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content Endpoint'),
      '#default_value' => $config->get('content_endpoint'),
      '#required' => TRUE,
    ];

    $form['stats_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stats Endpoint'),
      '#default_value' => $config->get('stats_endpoint'),
      '#required' => TRUE,
    ];

    $form['test_api_key'] = [
      '#type' => 'button',
      '#value' => $this->t('Test API Key'),
      '#ajax' => [
        'callback' => '::testApiKey',
        'wrapper' => 'api_key_test_result',
      ],
    ];

    $form['api_key_test_result'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'api_key_test_result'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('smithsonian_open_access.settings')
      ->set('base_uri', $form_state->getValue('base_uri'))
      ->set('search_endpoint', $form_state->getValue('search_endpoint'))
      ->set('content_endpoint', $form_state->getValue('content_endpoint'))
      ->set('stats_endpoint', $form_state->getValue('stats_endpoint'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  public function testApiKey(array &$form, FormStateInterface $form_state, Request $request) {
    // Save the form values.
    $this->submitForm($form, $form_state);

    $api_key = $form_state->getValue('api_key');
    $response = new AjaxResponse();

    $api = \Drupal::service('smithsonian_open_access.api');
    $result = $api->validateApiKey($api_key);

    if ($result['success']) {
      $response->addCommand(new HtmlCommand('#api_key_test_result', $this->t('API Key is valid for use on the search endpoint. All API settings saved.')));
    } else {
      $response->addCommand(new HtmlCommand('#api_key_test_result', $this->t('API Key is not valid for use on the search endpoint. Error: @error', ['@error' => $result['error']])));
    }

    return $response;
  }



}
