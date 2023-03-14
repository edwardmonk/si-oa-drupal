<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smithsonian_open_access\Api;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Defines a form for testing Smithsonian Open Access search.
 */
class SmithsonianOpenAccessTestForm extends FormBase {

  protected Api $api;

  /**
   * SmithsonianOpenAccessTestForm constructor.
   *
   * @param Api $api
   *   The Smithsonian Open Access API service.
   */
  public function __construct(Api $api) {
    $this->api = $api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('smithsonian_open_access.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'smithsonian_open_access.search_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form['search_query'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search Query'),
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];
    $form['results'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="results">',
      '#suffix' => '</div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate the search query.
    $search_query = $form_state->getValue('search_query');
    if (empty($search_query)) {
      $form_state->setErrorByName('search_query', $this->t('Search query is required.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $search_query = $form_state->getValue('search_query');

    if (function_exists('hook_smithsonian_open_access_search')) {
      \Drupal::logger('smithsonian_open_access')->debug('hook function found!');
    } else {
      \Drupal::logger('smithsonian_open_access')->debug('hook function not found!');
    }

     // Call the hook to perform the search.
    #$results = \Drupal::moduleHandler()->invokeAll('smithsonian_open_access_search', [$search_query]);
    try {
      \Drupal::logger('smithsonian_open_access')->debug('About to call smithsonian_open_access_search with search query: @query', ['@query' => $search_query]);
      $results = \Drupal::moduleHandler()->invokeAll('smithsonian_open_access_search', [$search_query]);

    #  $results = \Drupal::service('module_handler')->invokeAll('smithsonian_open_access_search', [$search_query]);

    }
    catch (\Exception $e) {
      // Log the error message.
      \Drupal::logger('smithsonian_open_access')->error('Error occurred while invoking hook smithsonian_open_access_search: @message', ['@message' => $e->getMessage()]);
      // Set a message for the user.
      drupal_set_message(t('An error occurred while performing the search. Please try again later.'), 'error');
      return;
    }

    // Debugging statements.
    \Drupal::logger('smithsonian_open_access')->debug('API response: @response', ['@response' => print_r($results, TRUE)]);

    // Display the search results.
    $output = '<pre>' . json_encode($results, JSON_PRETTY_PRINT) . '</pre>';
    $form['results']['#markup'] = $output;
  }




}
