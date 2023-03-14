<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smithsonian_open_access\Api;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SmithsonianOpenAccessTestForm extends FormBase {

  protected $api;

  public function __construct(Api $api) {
    $this->api = $api;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('smithsonian_open_access.api')
    );
  }

  public function getFormId() {
    return 'smithsonian_open_access_test_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search_phrase'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search phrase'),
      '#description' => $this->t('Enter a search phrase to query the Smithsonian Open Access API.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    $results_markup = $form_state->get('search_results') ?: '';
    $form['results'] = [
      '#type' => 'markup',
      '#markup' => $results_markup,
      '#prefix' => '<div id="search-results">',
      '#suffix' => '</div>',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $search_phrase = $form_state->getValue('search_phrase');
    $results = $this->api->search($search_phrase);

    if ($results) {
      $results_json = json_encode($results, JSON_PRETTY_PRINT);
      $form_state->set('search_results', '<pre>' . $this->t('Search results:') . "\n" . $results_json . '</pre>');
    } else {
      $form_state->set('search_results', $this->t('No results found.'));
    }

    $form_state->setRebuild();
  }

}
