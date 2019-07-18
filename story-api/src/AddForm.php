<?php

/**
 * @file
 * Contains \Drupal\story\AddForm.
 */

namespace Drupal\story;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\story\BdContactStorage;

class AddForm extends FormBase {

  protected $id;

  function getFormId() {
    return 'story_add';
  }

  function buildForm(array $form, FormStateInterface $form_state) {
    $this->id = \Drupal::request()->get('id');
    $uid = \Drupal::currentUser()->id();
    if ($uid) {
      $form['1'] = array(
        '#type' => 'fieldset',
        '#title' => $this->t('Primary Information')
      );

      $form['1']['type'] = array(
        '#type' => 'select',
        '#title' => t('Type'),
        '#options' => array(
          '' => 'Select Type',
          'homepage' => 'Homepage',
          'menu' => 'Menu',
          'beauty' => 'Beauty',
          'celebrity' => 'Celebrity',
          'fashion' => 'Fashion',
          'life' => 'Life',
          'relationship' => 'Relationship',
          'top_block1' => 'Top Block 1',
          'top_block2' => 'Top Block 2',
          'story' => 'story',
          'gallery' => 'gallery'
        ),
        '#default_value' => 'Select Type',
        '#required' => TRUE
      );

      $form['1']['storyurl'] = array(
        '#type' => 'textarea',
        '#title' => t('Purge Story / Gallery url'),
        '#default_value' => '',
        '#required' => false
      );

      $form['actions'] = array(
        '#type' => 'actions'
      );

      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save Tour'),
        '#ajax' => array(
          'callback' => '::my_node_updatecb',
          'wrapper' => 'author',
          'event' => 'click'
        )
      );
    }

    return $form;
  }
  
  function my_node_updatecb() {
    echo "asdfa";
    exit;
  }

  function clearOsCache($url) {
    global $out, $database, $my, $mainframe;
    $jsonpath = '';

    $path = 'api/purge/node/' . $url;
    $string = file_get_contents("http://cloudapi.itgd.in/awsinfo/json/cosmo_server.json");
    $rs = json_decode($string, true);
    foreach ($rs['COSMO-WEB'] as $k => $v) {
      $ip = $v['private_ip'];
      $url = 'http://' . $ip . '/' . $path;
      $data[$k]['url'] = $url;
      $data[$k]['host'] = 'cosmopolitan.in';
    }

    $result = $this->multiRequest($data);
    $write_data = print_r($result, true);
  }

  function multiRequest($data, $options = array()) {
    // array of curl handles
    $curly = array();
    // data to be returned
    $result = array();
    // multi handle
    $mh = curl_multi_init();
    // loop through $data and create curl handles
    // then add them to the multi-handle
    foreach ($data as $id => $d) {

      $curly[$id] = curl_init();

      $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
      curl_setopt($curly[$id], CURLOPT_URL, $url);
      curl_setopt($curly[$id], CURLOPT_HEADER, 0);
      curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

      // post?
      if (is_array($d)) {
        if (!empty($d['host'])) {
          curl_setopt($curly[$id], CURLOPT_HTTPHEADER, array(
            'Host: ' . $d['host']
          ));
        }
        if (!empty($d['akamaipwd'])) {
          curl_setopt($curly[$id], CURLOPT_USERPWD, $d['akamaipwd']);
        }
        if (!empty($d['post'])) {
          curl_setopt($curly[$id], CURLOPT_POST, 1);
          curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
        }
      }

      // extra options?
      if (!empty($options)) {
        curl_setopt_array($curly[$id], $options);
      }

      curl_multi_add_handle($mh, $curly[$id]);
    }

    // execute the handles
    $running = null;
    do {
      curl_multi_exec($mh, $running);
    } while ($running > 0);


    // get content and remove handles
    foreach ($curly as $id => $c) {
      $result[$id] = curl_multi_getcontent($c);
      curl_multi_remove_handle($mh, $c);
    }

    // all done
    curl_multi_close($mh);
    //echo "<pre>";print_r($result);die;
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    
  }
  
  /**
   * 
   * @param type $storyurl
   */
  function akamaipurse($storyurl) {
    $url = "http://specials.intoday.in/purge-api/new/api.php?site=cosmo&userid=1&user=cosmo&urls=https://www.cosmopolitan.in" . $storyurl;
    file_get_contents($url);
  }

  function akamaipursearray($storyurl) {
    $urls = implode(",", $storyurl);
    $url = "http://specials.intoday.in/purge-api/new/api.php?site=cosmo&userid=1&user=cosmo&urls=" . $urls;
    file_get_contents($url);
  }

  function submitForm(array &$form, FormStateInterface $form_state) {

    $pdata = $form_state->getValues();
    $host = \Drupal::request()->getHost();
    if (isset($_REQUEST['type']) and $_REQUEST['type'] != "") {
      switch ($_REQUEST['type']) {
        case "homepage":
          $this->clearOsCache('COSMO_JSON_API_API_HOMEPAGE');
          $this->clearOsCache('COSMO_HTML_API_HOMEPAGE');
          $this->akamaipurse("/api/homepagelist");
          $this->akamaipurse("/");
          break;
        case "menu":
          $this->clearOsCache('COSMO_JSON_API_API_MENU');
          $this->clearOsCache('COSMO_HTML_API_MENU');
          $this->akamaipurse("/api/menu/");
          break;
        case "beauty":
          $this->clearOsCache('COSMO_JSON_API_api_sectiondata_beauty');
          $this->clearOsCache('COSMO_HTML_api_sectiondata_beauty');
          $this->akamaipurse("/api/category/beauty");
          $this->akamaipurse("/beauty");
          break;
        case "celebrity":
          $this->clearOsCache('COSMO_JSON_API_api_sectiondata_celebrity');
          $this->clearOsCache('COSMO_HTML_api_sectiondata_celebrity');
          $this->akamaipurse("/api/category/celebrity");
          $this->akamaipurse("/celebrity");
          break;
        case "fashion":
          $this->clearOsCache('COSMO_JSON_API_api_sectiondata_fashion');
          $this->clearOsCache('COSMO_HTML_api_sectiondata_fashion');
          $this->akamaipurse("/api/category/fashion");
          $this->akamaipurse("/fashion");
          break;
        case "life":
          $this->clearOsCache('COSMO_JSON_API_api_sectiondata_life');
          $this->clearOsCache('COSMO_HTML__categorylife_0_20');
          $this->clearOsCache('COSMO_HTML__categorylife');
          $this->akamaipurse("/api/category/life");
          $this->akamaipurse("/life");
          break;
        case "relationship":
          $this->clearOsCache('COSMO_JSON_API_api_sectiondata_relationships');
          $this->clearOsCache('COSMO_HTML__categoryrelationships');
          $this->clearOsCache('COSMO_HTML__categoryrelationships_0_20');
          $this->akamaipurse("/api/category/relationship");
          $this->akamaipurse("/relationship");
          break;
        case "top_block1":
          $this->clearOsCache('COSMO_JSON_API_API_TOP_BLOCK_1');
          $this->clearOsCache('COSMO_HTML_API_TOP_BLOCK_1');
          break;
        case "top_block2":
          $this->clearOsCache('COSMO_JSON_API_API_TOP_BLOCK_3');
          $this->clearOsCache('COSMO_HTML_API_TOP_BLOCK_3');
          break;
        case "story":
          $word_array = explode('/', $_REQUEST['storyurl']);
          $oldid = str_replace("a", "", $word_array[5]);
          $allAurls = array();
          $allNurls = array();
          $allHurls = array();
          //"COSMO_JSON_API__api_storydata_nid_8214_story"
          $this->clearOsCache('COSMO_JSON_API__api_storydata_nid_' . $oldid . '_story');
          $this->clearOsCache('COSMO_HTML__api_storydata_nid_' . $oldid . '_story');
          //     COSMO_HTML__api_storydata_nid_8214_story
          $this->clearOsCache('COSMO_JSON_API_API_HOMEPAGE');
          $this->clearOsCache('COSMO_HTML_API_HOMEPAGE');
          $this->clearOsCache('COSMO_JSON_API_api_sectiondata_' . $word_array[3]);
          $this->clearOsCache('COSMO_HTML_api_sectiondata_' . $word_array[3]);

          $allAurls = array(
            "https://www.cosmopolitan.in/api/homepagelist/",
            "https://www.cosmopolitan.in/api/category/" . $word_array[3],
            "https://www.cosmopolitan.in/api/video/detail/" . $oldid . "/story",
            "https://www.cosmopolitan.in/",
            "https://www.cosmopolitan.in/" . $word_array[3],
            $_REQUEST['storyurl']
          );

          $this->akamaipursearray($allurls);
          break;
        case "gallery":
          $word_array = explode('/', $_REQUEST['storyurl']);
          $oldid = str_replace("g", "", $word_array[5]);
          $purkeyJson = "COSMO_JSON_API__api_storydata_nid_" . $oldid . "_gallery";
          $purkeyHtml = "COSMO_HTML__api_storydata_nid_" . $oldid . "_gallery";
          $allAurls = array();
          $allNurls = array();
          $allHurls = array();

          $this->clearOsCache($purkeyJson);
          $this->clearOsCache($purkeyHtml);
          $this->clearOsCache('COSMO_JSON_API_API_HOMEPAGE');
          $this->clearOsCache('COSMO_HTML_API_HOMEPAGE');
          $this->clearOsCache('COSMO_JSON_API_api_sectiondata_' . $word_array[3]);
          $this->clearOsCache('COSMO_HTML_api_sectiondata_' . $word_array[3]);
          $allAurls = array(
            "https://www.cosmopolitan.in/api/homepagelist/",
            "https://www.cosmopolitan.in/api/category/" . $word_array[3],
            "https://www.cosmopolitan.in/api/video/detail/" . $oldid . "/gallery",
            "https://www.cosmopolitan.in/",
            "https://www.cosmopolitan.in/" . $word_array[3],
            $_REQUEST['storyurl']
          );

          $this->akamaipursearray($allurls);
          break;
      }
      drupal_set_message(t($_REQUEST['type'] . ':<br/><br/>:' . $_REQUEST['storyurl'] . '<br/><br/> Cache Successfully Purged. changes reflect you after 10 Seconds'));
    }
    else {
      drupal_set_message(t('Please select type.'));
    }
    return;
  }

}
