<?php

namespace Drupal\contact_with_us\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class ContactWithUsController extends ControllerBase {

  /**
   * Lists all the contact with us.
   */
  public function listContactWithUs() {
    $content = [];

    $headers = [
        ['data' => t('S.N'), 'field' => 'id'],
        ['data' => t('First Name'), 'field' => 'contact_fname'],
        ['data' => t('Last Name'), 'field' => 'contact_lname'],
        ['data' => t('Email'), 'field' => 'contact_email'],
        ['data' => t('Country'), 'field' => 'contact_country', 'sort' => 'desc'],
        ['data' => t('Status'), 'field' => 'status', 'sort' => 'desc'],
        ['data' => t('Action')],
    ];

    $rows = [];
    $i = 20 * \Drupal::request()->get('page') + 1;
    $all_query_data = \Drupal::request()->query->all();
    foreach ($this->contact_list($all_query_data) as $row) {
      $ajax_link_attributes = [
        'attributes' => [
          'class' => 'use-ajax',
          'data-dialog-type' => 'modal',
          'data-dialog-options' => ['width' => 700, 'height' => 400],
        ],
      ];

      $view_url = Url::fromRoute('contact_with_us.view', ['id' => $row->id, 'js' => 'nojs']);
      $approved_url = Url::fromRoute('contact_with_us.approved', ['id' => $row->id], $ajax_link_attributes);
      $reject_url = Url::fromRoute('contact_with_us.reject', ['id' => $row->id], $ajax_link_attributes);

      $manage_button = [
        '#type' => 'dropbutton',
        '#links' => [
          'view' => [
            'title' => t('View'),
            'url' => $view_url,
          ],
          'approve' => [
            'title' => t('Approve'),
            'url' => $approved_url,
          ],
          'reject' => [
            'title' => t('Reject'),
            'url' => $reject_url,
          ],
        ],
      ];

      $rows[] = [
        $row->id,
        $row->contact_fname,
        $row->contact_lname,
        $row->contact_email,
        $row->contact_country,
        ($row->status) == TRUE ? 'Approved' : 'Pending',
        'actions' => [
          'data' => $manage_button,
        ],
      ];
    }
    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#attributes' => ['id' => 'transcodig_video_dashboard'],
      '#empty' => $this->t('No Data Found.'),
    ];
    $content['#cache']['max-age'] = 0;
    $content['pager'] = [
      '#type' => 'pager',
      '#weight' => 20,
    ];

    return $content;
  }

  /**
   * To view an contact details.
   */
  public function viewContact($id, $js = 'nojs') {
    global $base_url;
    $contact = self::getContactData($id);
    if ($id == 'invalid') {
      drupal_set_message(t('Invalid contact record'), 'error');
      return new RedirectResponse(Drupal::url('contact_with_us.list'));
    }
    $rows = [
        [
          ['data' => 'First Name', 'header' => TRUE],
          $contact->contact_fname,
        ],
        [
          ['data' => 'Last Name', 'header' => TRUE],
          $contact->contact_lname,
        ],
        [
          ['data' => 'Email', 'header' => TRUE],
          $contact->contact_email,
        ],
        [
          ['data' => 'Mobile', 'header' => TRUE],
          $contact->contact_mobile,
        ],
        [
          ['data' => 'Country', 'header' => TRUE],
          $contact->contact_country,
        ],
        [
          ['data' => 'State', 'header' => TRUE],
          $contact->contact_state,
        ],
        [
          ['data' => 'Pincode', 'header' => TRUE],
          $contact->contact_pincode,
        ],
        [
          ['data' => 'Comment', 'header' => TRUE],
          $contact->contact_query,
        ],
    ];

    $content['details'] = [
      '#type' => 'table',
      '#rows' => $rows,
      '#attributes' => ['class' => ['contact-detail']],
    ];

    if ($js == 'ajax') {
      $modal_title = t('Contact #@id', ['@id' => $contact->id]);
      $options = [
        'dialogClass' => 'popup-dialog-class',
        'width' => '70%',
        'height' => '80%',
      ];
      $response = new AjaxResponse();
      $response->addCommand(new OpenModalDialogCommand(
        $modal_title, $content, $options));
      return $response;
    }
    else {
      return $content;
    }
  }

  /**
   * Callback for opening the contact reject form in modal.
   */
  public function approved($id = NULL) {
    $uid = \Drupal::currentUser()->id();
    if ($contact == 'invalid') {
      drupal_set_message(t('Invalid contact record'), 'error');
      return new RedirectResponse(Drupal::url('contact_with_us.list'));
    }
    $contact_data = [
      'user_id' => $uid,
      'status' => 1,
      'updated' => REQUEST_TIME,
    ];
    if ($id) {
      $contact = self::update($contact_data, $id);
      \Drupal::messenger()->addStatus(t('Approved Query.'));
    }

    $response = new AjaxResponse();

    $response->addCommand(
      new OpenModalDialogCommand(
        t('Send mail to: @email', ['@email' => 'arun']),
        $modal_form, ['width' => '800']
    ));
    return $response;
  }

  /**
   * Callback for opening the contact reject form in modal.
   */
  public function reject($id = NULL) {
    $uid = \Drupal::currentUser()->id();
    if ($contact == 'invalid') {
      drupal_set_message(t('Invalid contact record'), 'error');
      return new RedirectResponse(Drupal::url('contact_with_us.list'));
    }
    $contact_data = [
      'user_id' => $uid,
      'status' => 0,
      'updated' => REQUEST_TIME,
    ];
    if ($id) {
      $contact = self::update($contact_data, $id);
      // \Drupal::messenger()->addStatus(t('Reject Query.'));
      \Drupal::messenger()->addMessage($this->t('Form Submitted Successfully'), 'status', TRUE);

      $message = [
        '#theme' => 'status_messages',
        '#message_list' => drupal_get_messages(),
      ];

      $messages = \Drupal::service('renderer')->render($message);

    }
    
    $data_json['status_message'] = 'trans_source_id not found';
    
    //$json_data = json_encode($data_json);
    //$response = new Response();
    //$response->headers->set('Content-Type', 'application/json');   
    return new JsonResponse([
      'data' => $data_json,
    ]);
    
    
    //$response = new AjaxResponse();
    //$response->addCommand(new HtmlCommand('#result-message', $messages));
    /*
    $response->addCommand(
    new OpenModalDialogCommand(
    t('Send mail to: @email', ['@email' => 'arun']),
    $modal_form, ['width' => '800']
    ));
     *
     */
    //return $response;
  }

  /**
   * Implement Insert function.
   *
   * @param array $entry
   *
   * @return mixed
   */
  public static function insert(array $entry) {
    $return_value = NULL;
    try {
      $return_value = db_insert('contact_with_us')
        ->fields($entry)
        ->execute();
    }
    catch (\Exception $e) {
      drupal_set_message(t('db_insert failed. Message = %message, query= %query', [
        '%message' => $e->getMessage(),
        '%query' => $e->query_string,
      ]
        ), 'error');
    }
    return $return_value;
  }

  /**
   * Implement Insert function.
   *
   * @param array $entry
   * @param mixed $id
   *
   * @return mixed
   */
  public static function update(array $entry, $id) {
    $return_value = NULL;
    try {
      $return_value = db_update('contact_with_us')
        ->fields($entry)
        ->condition('id', $id)
        ->execute();
    }
    catch (\Exception $e) {
      drupal_set_message(t('db_insert failed. Message = %message, query= %query', [
        '%message' => $e->getMessage(),
        '%query' => $e->query_string,
      ]
        ), 'error');
    }
    return $return_value;
  }

  /**
   * Get the Contact data by id.
   *
   * @param mixed $cid
   *
   * @return mixed
   */
  public static function getContactData($cid) {
    $query = db_select('contact_with_us', 'c');
    $query->fields('c');
    $query->condition('c.id', $cid);
    $result = $query->execute()->fetchAll();
    return $result[0];
  }

  /**
   *
   * @param mixed $args
   * @return mixed
   */
  public function contact_list($args = NULL) {

    $query = db_select('contact_with_us', 'loc');
    $query->fields('loc', ['id', 'contact_fname', 'contact_lname', 'contact_email', 'contact_country', 'status']);
    if (isset($args['title']) != '') {
      $query->condition('loc.contact_fname', "%" . $args['fname'] . "%", 'LIKE');
    }

    $query->orderBy('loc.id', 'DESC');

    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
    $entries = $pager->execute()->fetchAll();
    return $entries;
  }

}
