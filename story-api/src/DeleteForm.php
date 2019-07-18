<?php

namespace Drupal\story;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class DeleteForm extends ConfirmFormBase {
  protected $id;

  function getFormId() {
    return 'story_delete';
  }

  function getQuestion() {
    return t('Are you sure you want to delete Hightlight %id?', array('%id' => $this->id));
  }

  function getConfirmText() {
    return t('Delete');
  }

  function getCancelUrl() {
    return new Url('story_list');
  }

  function buildForm(array $form, FormStateInterface $form_state) {
    $this->id = \Drupal::request()->get('id');
    return parent::buildForm($form, $form_state);
  }

  function submitForm(array &$form, FormStateInterface $form_state) {
    BdContactStorage::delete($this->id);
    //watchdog('story', 'Deleted BD Contact Submission with id %id.', array('%id' => $this->id));
    \Drupal::logger('story')->notice('@type: deleted %title.',
        array(
            '@type' => $this->id,
            '%title' => $this->id,
        ));
    drupal_set_message(t('Story has been deleted.', array('%id' => $this->id)));
    $form_state->setRedirect('story_list');
  }
}
