<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form to configure the Smithsonian Open Access API connection.
 */
class OpenAccessApiConnectionForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smithsonian_open_access_open_access_api_connection_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['smithsonian_open_access.open_access_api_connection'];
  }

  /**
   * {@inheritdoc}
   */
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

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the API base URL and API key to the module's configuration.
    $config = $this->config('smithsonian_open_access.open_access_api_connection');
    $config->set('api_base_url', $form_state->getValue('api_base_url'));
    $config->set('api_key', $form_state->getValue('api_key'));
    $config->save();

    // Display a status message indicating that the settings were saved.
    drupal_set_message($this->t('API connection settings have been saved.'));

    parent::submitForm($form, $form_state);
  }

}
