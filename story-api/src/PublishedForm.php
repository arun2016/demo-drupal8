<?php

namespace Drupal\story;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class PublishedForm extends ConfirmFormBase {
  protected $id;

  function getFormId() {
    return 'story_published';
  }

  function getQuestion() {
    return t('Published Highlights %id?', array('%id' => $this->id));
  }

  function getConfirmText() {
            if($_GET['status']==1)
           {
            $pub="Published";
           }else{
            $pub="Unpublished";
               
           } return t($pub);
  }

  function getCancelUrl() {
    return new Url('story_list');
  }

  function buildForm(array $form, FormStateInterface $form_state) {
    $this->id = \Drupal::request()->get('id');
    return parent::buildForm($form, $form_state);
  }

  function submitForm(array &$form, FormStateInterface $form_state) {
   
      
      BdContactStorage::published($this->id,$_GET['status']);
    //watchdog('vod', 'Deleted BD Contact Submission with id %id.', array('%id' => $this->id));
    \Drupal::logger('story')->notice('@type: deleted %title.',
        array(
            '@type' => $this->id,
            '%title' => $this->id,
        ));
    
      if($_GET['status']==1)
           {
    drupal_set_message(t('Story has been published.', array('%id' => $this->id)));
         }else{
    drupal_set_message(t('Story has been unpublished.', array('%id' => $this->id)));
             
           }
    
    
    
    $form_state->setRedirect('story_list');
  }
}
