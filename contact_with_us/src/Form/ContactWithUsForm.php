<?php

namespace Drupal\contact_with_us\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\contact_with_us\Controller\ContactWithUsController;

/**
 * Contact us form.
 */
class ContactWithUsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'contact_with_us_form';
  }

  /**
   * Get the current user uid.
   */
  public function currentUserUid() {
    $user_id = \Drupal::currentUser()->id();
    return $user_id;
  }

  /**
   * Builds the simple section order form.
   *
   * @param array $form
   *   Render array representing from.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['contact_fname'] = [
      '#type' => 'textfield',
      '#title' => t('First Name'),
      '#size' => 60,
      '#maxlength' => 60,
      '#required' => TRUE,
    ];
    $form['contact_lname'] = [
      '#type' => 'textfield',
      '#title' => t('Last Name'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];

    $form['contact_email'] = [
      '#type' => 'email',
      '#title' => t('Email'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];
    $form['contact_mobile'] = [
      '#type' => 'textfield',
      '#title' => t('Mobile'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];
    $form['contact_country'] = [
      '#type' => 'textfield',
      '#title' => t('Country'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];
    $form['contact_state'] = [
      '#type' => 'textfield',
      '#title' => t('State'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];
    $form['contact_pincode'] = [
      '#type' => 'textfield',
      '#title' => t('Pincode'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];
    $form['contact_query'] = [
      '#type' => 'textarea',
      '#title' => t('Comments'),
      '#rows' => 5,
      '#cols' => 45,
      '#maxlength' => 1000,
      '#required' => TRUE,
      '#resizable' => 'none',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /**
     * Validate name
     * Only alphabets and space are allowed
        */
    $contact_fname = trim($form_state->getValue('contact_fname'));
    $contact_lname = trim($form_state->getValue('contact_lname'));

    if (!preg_match("/^([a-zA-Z ]+)$/", $contact_fname)) {
      $form_state->setErrorByName('contact_fname', $this->t('Invalid characters in First Name'));
    }

    if (!preg_match("/^([a-zA-Z ]+)$/", $contact_lname)) {
      $form_state->setErrorByName('contact_lname', $this->t('Invalid characters in Last Name'));
    }

    /**
     * Validate email address
     */
    $contact_email = trim($form_state->getValue('contact_email'));

    if ($contact_email !== '' && !\Drupal::service('email.validator')->isValid($contact_email)) {
      $form_state->setErrorByName('contact_email', $this->t('Invalid email address'));
    }

    /**
     * Validate phone for any alphabets
     */
    $contact_mobile = trim($form_state->getValue('contact_mobile'));

    if (preg_match('/[\Aa-z\z]/i', $contact_mobile)) {
      $form_state->setErrorByName('contact_mobile', $this->t('Mobile can not contain alphabets'));
    }

    /**
     * Validate query
     */
    $contact_query = trim($form_state->getValue('contact_query'));

    if ($contact_query == '') {
      $form_state->setErrorByName('contact_query', $this->t('Comments can not be empty'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /**
     * Get user form input
     */
    $contact_fname = trim($form_state->getValue('contact_fname'));
    $contact_lname = trim($form_state->getValue('contact_lname'));
    $contact_email = trim($form_state->getValue('contact_email'));
    $contact_mobile = trim($form_state->getValue('contact_mobile'));
    $contact_country = trim($form_state->getValue('contact_country'));
    $contact_state = trim($form_state->getValue('contact_state'));
    $contact_query = trim($form_state->getValue('contact_query'));
    $contact_pincode = trim($form_state->getValue('contact_pincode'));

    /**
     * Save contact us form data in content type "Contact Us Form"
     */
    $contact_data = [
      'contact_fname' => $contact_fname,
      'contact_lname' => $contact_lname,
      'contact_email' => $contact_email,
      'contact_mobile' => $contact_mobile,
      'contact_country' => $contact_country,
      'contact_state' => $contact_state,
      'contact_query' => $contact_query,
      'contact_pincode' => $contact_pincode,
      'user_id' => 0,
      'status' => 0,
      'created' => REQUEST_TIME,
      'updated' => REQUEST_TIME,
    ];

    ContactWithUsController::insert($contact_data);

    /**
     * Get the email address to which email to be sent
     */
    // $config = $this->config('contact_us.adminsettings');
    // $contact_us_admin_email = trim($config->get('contact_us_admin_email'));
    if ($contact_us_admin_email) {
      /**
       * Send email
       */
      $mail_manager = \Drupal::service('plugin.manager.mail');
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $params['message']['contactusyourname'] = $contactusyourname;
      $params['message']['contactuscompanyname'] = $contactuscompanyname;
      $params['message']['contactusdesignation'] = $contactusdesignation;
      $params['message']['contactusaddress'] = $contactusaddress;
      $params['message']['contactuscountry'] = $contactuscountry;
      $params['message']['contactusemail'] = $contactusemail;
      $params['message']['contactusphone'] = $contactusphone;
      $params['message']['contactusquery'] = $contactusquery;

      $to = $contact_us_admin_email;

      $result = $mail_manager->mail('contact_us', 'contact_us_notify', $to, $langcode, $params, NULL, 'true');
    }

    /**
     * Display thanks message to the visitor
     */
    \Drupal::messenger()->addStatus(t('Thanks ' . $contactusyourname . '! The form has been submitted successfully.'));
  }

}
