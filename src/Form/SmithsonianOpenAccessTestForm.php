<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form for testing Smithsonian Open Access search.
 */
class SmithsonianOpenAccessTestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smithsonian_open_access.test_search';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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

    // Call the hook to perform the search.
    $results = \Drupal::moduleHandler()->invokeAll('smithsonian_open_access_search', [$search_query]);

    // Display the search results.
    $output = '';
    foreach ($results as $result) {
      $output .= '<p>' . $result->title . '</p>';
    }
    $form['results']['#markup'] = $output;
  }

}
