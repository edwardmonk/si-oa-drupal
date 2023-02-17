<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smithsonian_open_access\Service\OpenAccessApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Provides a form for testing the Smithsonian Open Access API.
 */
class OpenAccessApiTestForm extends FormBase {

  /**
   * The Smithsonian Open Access API service.
   *
   * @var \Drupal\smithsonian_open_access\OpenAccessApiService
   */
  protected $openAccessApiService;

  /**
   * Constructs a new OpenAccessApiTestForm instance.
   *
   * @param \Drupal\smithsonian_open_access\OpenAccessApiService $openAccessApiService
   *   The Smithsonian Open Access API service.
   */
  public function __construct(OpenAccessApiService $openAccessApiService) {
    $this->openAccessApiService = $openAccessApiService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('smithsonian_open_access.api_service')
    );
  }

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
    $form['object_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Object ID'),
      '#description' => $this->t('Enter the ID of the object to retrieve.'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Retrieve object'),
      '#ajax' => [
        'callback' => '::displayObject',
        'event' => 'click',
      ],
    ];

    $form['object'] = [
      '#type' => 'markup',
      '#markup' => '<div id="object-display"></div>',
    ];

    return $form;
  }

  /**
   * Ajax callback to display the retrieved object.
   */
  public function displayObject(array &$form, FormStateInterface $form_state) {
    $object_id = $form_state->getValue('object_id');
    $object = $this->openAccessApiService->getObject($object_id);

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#object-display', $object));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
