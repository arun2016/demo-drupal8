<?php
/**
@file
Contains \Drupal\vod\Controller\AdminController.
 */

namespace Drupal\vod\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\vod\BdContactStorage;


error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

class AdminController extends ControllerBase {
	
	function nomination(){
		
		echo '<pre>'; print_r($_POST); exit;
		
	}
	
	function ankitlist(){
		echo '<pre>';
	   $query = \Drupal::entityTypeManager()
        ->getListBuilder('node')
        ->getStorage()
        ->getQuery();
        $query->condition('type', 'story');
        $query->condition('status', 1);
	//	$query->condition($tagcon,$name);
		$query->sort('field_date', 'DESC');
		$query->range($count,$numofpost);
		print_r($query);
        $nids = $query->execute();
		print_r($nids);die;
	}
	
	
	
	
		function ckimageupload(){
			//if($_FILES['multi_img']['error']) 
			//echo '<pre>';print_r($_FILES);
			$url = array();
			if(!empty($_FILES['multi_img']['name'])){
				foreach($_FILES['multi_img']['name'] as $k => $v){
					$tmpFile = $_FILES['multi_img']['tmp_name'][ $k];
					$contentType = 'story';
					//$this->uploadImage( $tmpPath,$contentType);
					$handle    = fopen($tmpFile, "r");
					$data      = fread($handle, filesize($tmpFile));
					$_POST['file_name']  = $_FILES['multi_img']['name'][ $k];
					$_POST['file'] = base64_encode($data);
					$_POST['site'] = 'cosmo';
					$_POST['type'] = 'story';
					$_POST['is_public'] = !empty($_POST['is_public'])? $_POST['is_public'] :'Y';
					$s3UploadUrl = 'http://feeds.intoday.in/s3_uploader/';
					$response = $this->callApiByCurl($s3UploadUrl,$_POST);
					//echo '<pre>';print_r($response);
					if(!empty($response['error'])) return;
					if(!empty($response['s3_response']['aws_error'])) return;
					$url[] = $response['s3_response']['s3_url'];
				}
			}
			$this->sendJsonResponse($url);
		
		}
		
		function sendJsonResponse($data){
			header("Content-Type: text/plain");
			header('Content-Type: application/json');
			//pr($data);
			echo json_encode($data);die;
		}
		
		public function uploadImage( $url,$contentType){
				//$file = file_get_contents($url);

			//	$file_name = 'sites/default/files/' . $contentType . '/images/'. basename($url);
			//	file_put_contents($file_name, $file);
				file_unmanaged_copy($file_name, 'sites/default/files/' . $contentType . '/images/' . basename($url));
				$image = File::create(['uri' => 'public://' . $contentType . '/images/' . basename($url)]);
				$image->save();
				echo '<pre>';print_r($image);exit;
				return $image->id();
		}
		
		
		
		function callApiByCurl($url,$data = NULL){
			//pr($data);die;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
			if(!empty($data )) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			$result=curl_exec($ch);
			if(curl_error($ch)){
				echo 'error:' . curl_error($ch);
			}
			curl_close($ch);
			return json_decode($result, true);
		}
		

	
		function contentDataByType(){
			

			$old_nid = \Drupal::request()->query->get('nid');
			$type = \Drupal::request()->query->get('type');
			$rdata=null;


			$query = \Drupal::entityTypeManager()
			->getListBuilder('node')
			->getStorage()
			->getQuery();
			$query->condition('status', 1);
			$query->condition('field_old_id',$old_nid);
			$query->condition('type', $type);
			$query->sort('field_date', 'DESC');
			$nids = $query->execute();
			$totalcount=count($nids);
//			echo '<pre>'; print_r($totalcount); exit;
			if($totalcount==0){  $status=400; }else{ 

			foreach($nids as $qnid){
			$nid=$qnid;
			break;
			}
			$status=200; 
			$rdata=BdContactStorage::getnodedata($nid);
			}

			header('Content-Type: application/json');
			$finalResult=array("status"=>$status,"response"=>$rdata);

			echo \GuzzleHttp\json_encode($finalResult); exit;


		}
		
		
		function videopagesearch(){
			
			
			$keyword = \Drupal::request()->query->get('keyword');
		
			
			$db = \Drupal::database();
			$query = $db->select('multimedia','sgs');
			$query->fields('sgs',array('id','source_file_name','transcoding_id','s3_domain','file_path','transcoding_source'));

			$query->condition('sgs.bitrate', '16500', '=');
			$query->condition('sgs.source_file_name', "%" .$keyword."%", 'LIKE');


			$result = $query->execute()->fetchAll();
			$li="";
			foreach($result as $key=>$item){
			   
           $vpath='http://'.$item->s3_domain.'/'.$item->file_path;
		     $dailymotionid=str_replace("dailymotion-","",$item->transcoding_source);
			$transcoding_source=split('-',$item->transcoding_source);
		 
		 
		 
          $li .='<li><input id="'.$item->id.'" value="'.$item->id.'" data-item="'.$item->source_file_name.'" data-item-vpath="'.$transcoding_source[1].'"  data-item-vpath-private="'.$transcoding_source[2].'"  type="radio" name="svideo" />'.$item->source_file_name.'</li>';
          
          

        		 }


      echo $li; exit;



		}	
	
	
	
function videopage(){

if($_GET['vid']==1){


	$imgblock = \Drupal\block_content\Entity\BlockContent::load(1);
	$imgblockdata=$imgblock->field_header_top_image_manager->getValue()[0]['target_id'];
	$fimg=NULL;
	if(!empty($imgblockdata))
	{
	$image_file = \Drupal\file\Entity\File::load($imgblockdata);
	$uri = $image_file->uri->value;

	$fimg = file_create_url($uri);
	$fimg =explode("?",$fimg);
	$fimg=$fimg[0];
	
	}
	 header('Content-Type: application/json');
	$finalResult=array("status"=>200,"response"=>$fimg);
	echo \GuzzleHttp\json_encode($finalResult); exit;

	
}



	if($_GET['vid']==12307){	
     $rdata[0]=BdContactStorage::getnodedatalist(12307);
	 $rdata[0]['body']="She recently posted a selfie of Shah Rukh Khan and son Aryan Khan and captioned it, 'Strike a pose ...❤️'.";
	 $rdata[0]['videourl']="http://www.dailymotion.com/embed/video/x6knlyy";
	 $rdata[0]['type']="video";
	 $rdata[0]['path']="/video/v12307/here-are-some-unseen-photos-soon-be-married-couple";
	 
	}elseif($_GET['vid']==15041){
 
     $rdata[0]=BdContactStorage::getnodedatalist(15041);
	 $rdata[0]['body']="She recently posted a selfie of Shah Rukh Khan and son Aryan Khan and captioned it, 'Strike a pose ...❤️'.";
	 $rdata[0]['videourl']="http://www.dailymotion.com/embed/video/x6knks2";
	 $rdata[0]['type']="video";
	 $rdata[0]['path']="/video/v15041/here-are-some-unseen-photos-soon-be-married-couple";

	}else{
		 $rdata[0]=BdContactStorage::getnodedatalist(12307);
	 $rdata[0]['body']="She recently posted a selfie of Shah Rukh Khan and son Aryan Khan and captioned it, 'Strike a pose ...❤️'.";
	 $rdata[0]['videourl']="http://www.dailymotion.com/embed/video/x6knlyy";
	 $rdata[0]['type']="video";
	 $rdata[0]['path']="/video/v12307/here-are-some-unseen-photos-soon-be-married-couple";
    

     $rdata[1]=BdContactStorage::getnodedatalist(15041);
	 $rdata[1]['body']="She recently posted a selfie of Shah Rukh Khan and son Aryan Khan and captioned it, 'Strike a pose ...❤️'.";
	 $rdata[1]['videourl']="http://www.dailymotion.com/embed/video/x6knks2";
	 $rdata[1]['type']="video";
	 $rdata[1]['path']="/video/v15041/here-are-some-unseen-photos-soon-be-married-couple";
	 
	
		
	}
	 
	 
		
	 header('Content-Type: application/json');
	$finalResult=array("status"=>200,"response"=>$rdata);
	echo \GuzzleHttp\json_encode($finalResult); exit;
	
		

   }


	
	
function homepage(){

print "arun"; exit; 
$range = \Drupal::request()->query->get('page');
$numofpost=10;
/*
$block_id=1;	
$block = \Drupal\block_content\Entity\BlockContent::load($block_id);
$field_top_listing_home_block=$block->field_top_listing_home_single->getValue();
$ik=0;

foreach($field_top_listing_home_block as $nid){
		$topfile[$ik]=$nid['target_id'];
		$ik++;
		}
*/
//echo '<pre>'; print_r($topfile); exit;


if($range==NULL || $range==0){
	$count=0;
	
	
	}elseif($range==1){
		$count=$numofpost;
	}else{
	$count=$range * $numofpost;
		
	}
	$ed=time();
		
        $query = \Drupal::entityTypeManager()
        ->getListBuilder('node')
        ->getStorage()
        ->getQuery();
		
        $query->condition('status', 1);
		$query->condition('field_date',$ed,"<=");
		$query->condition('nid', $topfile, 'NOT IN');
	//	$query->condition('type', $type);

		$query->sort('field_date', 'DESC');
		$query->range($count,$numofpost);
        $nids = $query->execute();
	
		
		$i=0;
		foreach($nids as $nid){
		$rdata[$i]=BdContactStorage::getnodedatalist($nid);
			
						
			$i++;
			
		}
		
		 

		header('Content-Type: application/json');
		$finalResult=array("status"=>200,"response"=>$rdata);
		echo '<pre>'; print_r($finalResult); echo '</pre>'; exit;

		echo \GuzzleHttp\json_encode($finalResult); exit;
		
		

   }
	
	
function dashboardapi(){


//echo '<pre>'; print_r($_REQUEST); exit;
//&ed=2018-04-19&sd=2018-04-15&status=1&type=stories&page=1
$ed = \Drupal::request()->query->get('ed');
$sd = \Drupal::request()->query->get('sd');
$range = \Drupal::request()->query->get('page');
$type = \Drupal::request()->query->get('type');
$onlycount = \Drupal::request()->query->get('onlycount');

$numofpost=10;

if($range==NULL || $range==0){
	$count=0;
	
	
	}elseif($range==1){
		$count=$numofpost;
	}else{
	$count=$range * $numofpost;
		
	}

	$numOfCate=array();
		
        $query = \Drupal::entityTypeManager()
        ->getListBuilder('node')
        ->getStorage()
        ->getQuery();
        $query->condition('status', 1);
		$query->condition('field_date',strtotime($ed),"<=");
		$query->condition('field_date',strtotime($sd),">=");
		$query->condition('type', $type);

		$query->sort('field_date', 'DESC');
	//	$query->range($count,$numofpost);
        $nids = $query->execute();
		$totalcount=count($nids);
		
		if($onlycount==1){

			header('Content-Type: application/json');
			$finalResult=array("status"=>200,"response"=>"","totalcount"=>$totalcount);
			//echo '<pre>'; print_r($finalResult); echo '</pre>'; exit;

			echo \GuzzleHttp\json_encode($finalResult); exit;
			
		}
		
		
		$i=0;
		foreach($nids as $nid){

		$rdata[$i]=BdContactStorage::getnodedatalist($nid);
			
		$numOfCate[$i]=BdContactStorage::getnodedatalist($nid)['field_section'];
				
						
			$i++;
			
		}
		
		
		
		$vals = array_count_values($numOfCate);
	//	echo 'No. of NON Duplicate Items: '.count($vals).'<br><br>';
//	echo '<pre>'; print_r($vals); exit;
		
		 $seoData=array("title"=>$type.",".$name,"description"=>$type.",".$name,"keyword"=>$type.",".$name);
		 

		header('Content-Type: application/json');
		$finalResult=array("status"=>200,"response"=>$rdata,"section_data"=>$vals,"totalcount"=>$totalcount);
		//echo '<pre>'; print_r($finalResult); echo '</pre>'; exit;

		echo \GuzzleHttp\json_encode($finalResult); exit;
		
		

   }




function tagSearch(){	
        
$type=@$_GET['type'];
$name=@$_GET['name'];
$range=@$_GET['range'];
$numofpost=10;

if($range==NULL || $range==0){
	$count=0;
	
	
	}elseif($range==1){
		$count=$numofpost;
	}else{
	$count=$range * $numofpost;
		
	}

$name=str_replace("-"," ",$name);


		if($type=="brand"){
			$tagcon='field_brand_tags.entity.name';
		}elseif($type=="content"){
			$tagcon='field_content_tags.entity.name';
		}elseif($type=="people"){
			$tagcon='field_people_tags.entity.name';
		}elseif($type=="product"){
			$tagcon='field_product_tags.entity.name';
		}else{
			$tagcon='field_product_tags.entity.name';
		}
		
		
        $query = \Drupal::entityTypeManager()
        ->getListBuilder('node')
        ->getStorage()
        ->getQuery();
        $query->condition('type', 'story');
        $query->condition('status', 1);
		$query->condition($tagcon,$name);
		$query->sort('field_date', 'DESC');
		$query->range($count,$numofpost);
        $nids = $query->execute();
		$i=0;
		foreach($nids as $nid){
		$rdata[$i]=BdContactStorage::getnodedatalist($nid);
			
						
			$i++;
			
		}
		
		 $seoData=array("title"=>$type.",".$name,"description"=>$type.",".$name,"keyword"=>$type.",".$name);
		 

		header('Content-Type: application/json');
		$finalResult=array("status"=>200,"response"=>$rdata,"seodata"=>$seoData);
		//echo '<pre>'; print_r($finalResult); echo '</pre>'; exit;

		echo \GuzzleHttp\json_encode($finalResult); exit;
		
		

   }

  
  
	function blocksearch(){
		
		
$block_id=1;	
$block = \Drupal\block_content\Entity\BlockContent::load($block_id);
$j=0;$i=0;
$field_top_listing_home_block=$block->field_top_listing_home_block->getValue();
 		 
		foreach($field_top_listing_home_block as $nid){
		$topfile[$i]=BdContactStorage::getnodedatalist($nid['target_id']);
		$i++;
		}
			


$field_top_listing_home_single=$block->field_top_listing_home_single->getValue();
 		 
		foreach($field_top_listing_home_single as $snid){
		$singletop[$j]=BdContactStorage::getnodedatalist($snid['target_id']);
		$singletop[0]['title']="BLOGGER AWARDS 2019";	
		$singletop[0]['path']="/cosmopolitan-bloggers-award-2019-nomination";
		$singletop[0]['image']="https://www.cosmo-restaurants.co.uk/wp-content/uploads/2017/03/Cosmo-Reading-4372.jpg";
						
			$j++;
			
		}
//echo '<pre>'; print_r($singletop); echo '</pre>'; exit;

		
		 $seoData=array("title"=>$block->field_home_meta_title->value,"description"=>$block->field_home_meta_description->value,"keyword"=>$block->field_home_meta_keyword->value);
		 

		header('Content-Type: application/json');
		$finalResult=array("status"=>200,"topfile"=>$topfile,"singletop"=>$singletop,"seodata"=>$seoData);
		//echo '<pre>'; print_r($finalResult); echo '</pre>'; exit;

		echo \GuzzleHttp\json_encode($finalResult); exit;
		
		
		
		
	}
	
	
	
  function elasticsearch(){

			$range=@$_GET['start'];
			$numofpost=@$_GET['numofpost'];


			$query = \Drupal::entityTypeManager()
			->getListBuilder('node')
			->getStorage()
			->getQuery();

			$query->condition('status', 1);
			if(@$_GET['nid']!=""){
			$query->condition('nid', $_GET['nid']);
			}
			$query->sort('field_date', 'DESC');
			$query->range($range,$numofpost);
			$nids = $query->execute();
			$i=0;
			$eladata=array();
			foreach($nids as $nid){



			$rdata=null;	

			$rdata=BdContactStorage::elasticsearchList($nid);
			$eladata[$i]['primary_category']=$rdata['field_section'];

			$eladata[$i]['sectionid']="";

			$eladata[$i]['sectionsefurl']=$rdata['field_section_url'];
			$eladata[$i]['sectionenname']=$rdata['field_section'];
			$eladata[$i]['sectionname']=$rdata['field_section'];
			$eladata[$i]['sectionname']=$rdata['field_section'];
			$eladata[$i]['categoryenname']=$rdata['field_section'];
			$eladata[$i]['categorysefurl']=$rdata['field_section_url'];
			$eladata[$i]['section_id']="";
			$eladata[$i]['type']=$rdata['type'];
			$eladata[$i]['categoryid']="";
			$eladata[$i]['subcategoryid']="";
			$eladata[$i]['id']=$rdata['oldid'];
			$eladata[$i]['metatags']=$rdata['field_all_tags'];
			$eladata[$i]['contenttype']=$rdata['field_content_type'];
			$eladata[$i]['title']=$rdata['title'];
			$eladata[$i]['titlealias']=$rdata['title'];
			$eladata[$i]['strap_headline']=$rdata['field_dek'];
			$eladata[$i]['introtext']=$rdata['field_dek'];
			$eladata[$i]['created']= date("Y-m-d H:i:s",$rdata['created_date_node']);
			$eladata[$i]['publish_up']=date("Y-m-d H:i:s",$rdata['created']);
			$eladata[$i]['story_template']="";
			$eladata[$i]['byline']=$rdata['field_first_name'];
			$eladata[$i]['courtesy']="";
			$eladata[$i]['city']="";
			$eladata[$i]['createddate']=date("Y-m-d H:i:s",$rdata['created_date_node']);
			$eladata[$i]['metakeyword']=$rdata['field_meta_keyword'];
			$eladata[$i]['metadescription']=$rdata['field_meta_description'];
			$eladata[$i]['contenturl']=$rdata['path'];
			$eladata[$i]['sefurl']=$rdata['path'];
			$eladata[$i]['seftitle']=$rdata['title'];
			$eladata[$i]['imagealttext']="";
			$eladata[$i]['imagealtcaption']="";
			$eladata[$i]['img_folder']=0;
			$eladata[$i]['fulltext']=$rdata['body'];
			$eladata[$i]['fileduration']="";
			$eladata[$i]['modified']=date("Y-m-d H:i:s",$rdata['updated_date_node']);
			$eladata[$i]['totalphotos']=1; 
			$eladata[$i]['media']['image']=$rdata['image'];
			$eladata[$i]['media']['thumb']=$rdata['image'];
			$eladata[$i]['website']="https://www.cosmopolitan.in";
			$eladata[$i]['createddatetime']=$rdata['created_date_node'];
			$eladata[$i]['modifieddatetime']=$rdata['updated_date_node'];
			$eladata[$i]['issueid']="";
			$eladata[$i]['issuetitle']="";
			$eladata[$i]['issueyear']="";
			$eladata[$i]['stitlemagazine']="";
			$eladata[$i]['meta_title']=$rdata['field_meta_title'];

			$eladata[$i]['url']="https://www.cosmopolitan.in".$rdata['path'];



			$i++;

			}

			header('Content-Type: application/json');
			$finalResult=array("items"=>$eladata);

			echo \GuzzleHttp\json_encode($finalResult); exit;

   }
   
   
 
   function fulltextSearch(){

			$keys=@$_GET['keys'];
			$keys=str_replace(" ","+",$keys);


			$range=@$_GET['range'];

			$numofpost=10;


			if($range==NULL || $range==0){
			$count=0;


			}elseif($range==1){
			$count=$numofpost;
			}else{
			$count=$range * $numofpost;

			}

			
			$url="http://aajtakpro.simpleapi.itgd.in/elasticsearch/api/v1/search/?q=".$keys."&site=cosmo&ctype=all&print=1&from=".$count."&size=".$numofpost;


			$curl = curl_init();
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_URL, $url);  
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
			$result = curl_exec($curl);
			$res=json_decode($result);


			$seoData=array("title"=>$keys,"description"=>$keys,"keyword"=>$keys,"urls"=>$url);

			header('Content-Type: application/json');
			$finalResult=array("status"=>200,"response"=>$res,"seodata"=>$seoData);

			echo \GuzzleHttp\json_encode($finalResult); exit;



   } 

   
   
   
  
  public function content1() {
    return array(
      '#type' => 'markup',
      '#markup' => t('Hello World'),
    );
  }

  public function getSearchvod()
  {


if(!empty($_REQUEST['submit'])){

if(!empty($_REQUEST['social_url'])){

$socialMediaType="";
$socialMediaShortCode="";	
//$validUrl = '<iframe width="560" height="315" src="https://www.youtube.com/embed/MkdOarSl1t8" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
$validUrl=$_REQUEST['social_url'];


    if($this->fbUrlCheck($validUrl)=='facebook'){   
	  //$socialMediaShortCode .='[facebook]'.$validUrl.'[/facebook]';  
	  $socialMediaShortCode .=$this->parseFacebook($validUrl);
    
	
	}   	
	if ($this->pinUrlCheck($validUrl)=='pinterest'){   
		$socialMediaShortCode .=$this->parsePin($validUrl); 
	  // $socialMediaShortCode='[pinterest]'.$validUrl.'[/pinterest]';      
    }
	if ($this->gifyUrlCheck($validUrl)=='gipfy'){    
	  //$socialMediaShortCode='[gipfy]'.$validUrl.'[/gipfy]';  
	  $socialMediaShortCode .=$this->parseGiphy($validUrl);  
	      
    }
	if ($this->instaUrlCheck($validUrl)=='instagram'){
	$socialMediaShortCode .=$this->parseInstagram($validUrl);     
	 // $socialMediaShortCode='[instagram]'.$validUrl.'[/instagram]';      
    }
	if ($this->twitterUrlCheck($validUrl)=='twitter'){ 
	  		$socialMediaShortCode .=$this->parseTwitter($validUrl);   
	 // $socialMediaShortCode='[twitter]'.$validUrl.'[/twitter]';      
    }
	if ($this->youtubeUrlCheck($validUrl)=='youtube'){   
		$socialMediaShortCode .=$this->parseYoutube($validUrl); 
	 // $socialMediaShortCode='[youtube]'.$validUrl.'[/youtube]';      
    }
	else{
	$error="This Format is not supported";
	}
	//echo "k".$socialMediaShortCode;die;
	  echo "<script>
	 CKEDITOR=window.parent.CKEDITOR;
	 //ed=window.parent.getElementById('edit-body-0-value');
	 ed=window.parent.document.getElementById('edit-body-0-value');

var editor = CKEDITOR.instances['edit-body-0-value'];
	var value ='hello';

		editor.insertHtml( '".$socialMediaShortCode."' );
			window.parent.jQuery('.close').click();
								</script>";

 }else{
	$error="Please Provide Input";
	
	}
}


  ?>
 <h2>Social Embed</h2>
<h2><?php if(!empty($error)){echo $error;}?></h2>
<form action="" method="post" enctype="multipart/form-data" name="social">
<textarea required name="social_url" cols="50" rows="10"></textarea>
<input name="submit" type="submit" value="Insert">

</form>


  <?php
exit;


}

function fbUrlCheck($validUrl){
$fbUrlCheck = '/(?=.*www\.facebook\.com.*)\/[a-zA-Z0-9(\.\?)?]/';
	$secondCheck = '/(?=.*www\.facebook\.com.*)<iframe .*<\/iframe>/';
	
	//$validUrl = '<iframe src="https://www.facebook.com/plugins/post.php?href=https%3A%2F%2Fwww.facebook.com%2FCosmopolitan%2Fposts%2F10156365982357708&width=500" width="500" height="617" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
	
	//$validUrl = 'www.facebook.com/Cosmopolitan/videos/535236143529417/';
	
	if(preg_match($fbUrlCheck, $validUrl) == 1  || preg_match($secondCheck, $validUrl) == 1 ) { //
		//echo 'Facebook URL is valid!';
		return 'facebook';
	} else {
		//echo 'Facebook URL is not valid!';
		return false;
	}

}



function pinUrlCheck($validUrl){
	//$validUrl = "https://www.pinterest.com/pin/154037249734402709/";
	if(preg_match("/http(s)?:\/\/(www.|)pin(\.(.*?)\/|terest\.(.*?)\/)/mi", $validUrl)){
		//echo 'Pinterest URL is valid!';
		return 'pinterest';
	}
	else{
		//echo 'Pinterest URL is not valid!';
		return false;
	}

}

function gifyUrlCheck($validUrl){
$re = '~https?://(?|media\.giphy\.com/media/([^ /\n]+)/giphy\.gif|gph\.is/([^ /\n]+)|i\.giphy\.com/([^ /\n]+)\.gif|giphy\.com/gifs/(?:.*-)?([^ /\n]+))~i';
$secondCheck='/<(.*)iframe src="(.*):\/\/giphy\.com\/embed\/.*"(.*)><\/iframe>/i';

	if(preg_match($re, $validUrl) == 1  || preg_match($secondCheck, $validUrl) == 1 ) { //
			//echo 'Gipfy URL is valid!';
			return 'gipfy';
		} else {
			//echo 'Gipfy URL is not valid!';
			return false;
		}
}
function instaUrlCheck($validUrl){
$re = '/(https?:\/\/(www\.)?)?instagram\.com(\/p\/\w+\/?)/i';
	if(preg_match($re, $validUrl) == 1 ) { //
			//echo 'Instagram URL is valid!';
			return 'instagram';
		} else {
			//echo 'Instagram URL is not valid!';
			return false;
		}
}

function twitterUrlCheck($validUrl){
$re = '/(?:http:\/\/)?(?:www\.)?twitter\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-]*)/i';
	if(preg_match($re, $validUrl) == 1 ) { //
			//echo 'Twitter URL is valid!';
			return 'twitter';
		} else {
			//echo 'Twitter URL is not valid!';
			return false;
		}
}
function youtubeUrlCheck($validUrl){
$re = '/(?:https?:\/\/)?(?:www\.)?youtu\.?be(?:\.com)?\/?.*(?:watch|embed)?(?:.*v=|v\/|\/)([\w-_]+)/i';
	if(preg_match($re, $validUrl) == 1 ) { //
	
	
			//echo 'Youtube URL is valid!';
			return 'youtube';
		} else {
			//echo 'Youtube URL is not valid!';
			return false;
		}
}

function parseTwitter($validUrl){ 
 $finalString="";
 $re = '/https:\/\/twitter\.com\/\w+\/status\/\d+(\?\w+\=[^\"]*|)/mi';
 preg_match_all($re, $validUrl, $matches);
// echo "<pre>";print_r($matches);die;
	foreach ($matches[0] as $result){
		$finalString .="[twitter]".$result."[/twitter]<br>";  
	}
	return $finalString;
}

function parseInstagram($validUrl){ 
 $finalString="";
$re = '/([^data-instgrm-permalink="]|href=")((https|http):\/\/(www.|)instagr(\.am|am\.com)\/\w\/\w+\/(\?\w+\=[^\"]*|))/im';
preg_match_all($re, $validUrl, $matches);
//echo "<pre>";print_r($matches);die;
	foreach ($matches[2] as $result){
		$finalString .="[instagram]".$result."[/instagram]<br>";  
	}
	return $finalString;
}

function parsePin($validUrl){
$finalString="";
$re = '/http(s)?:\/\/(www.|)pin(\.(.*?)\/|terest\.(.*?)\/)(\w+|pin)(\/\w+\/|)/mi';
preg_match_all($re, $validUrl, $matches);
//echo "<pre>";print_r($matches);die;
	foreach ($matches[0] as $result){
		$finalString .="[pinterest]".$result."[/pinterest]<br>";  
	}
	return $finalString;

} 

function parseFacebook($validUrl){
$finalString="";
$re = '/(http(s)?:\/\/(www.|)facebook\.com.*|src="(http(s)?:\/\/(www.|)facebook.com[^"]+)")/';
preg_match_all($re, $validUrl, $matches);
//echo "<pre>";print_r($matches);die;
	foreach ($matches[0] as $result){
		$finalString .="[facebook]".str_replace(array('src="','"'),'',$result)."[/facebook]<br>";  
	}
	
	$finalString=str_replace(array("%3A","%2F"),array(':','/'),$finalString);
	
	return $finalString;

} 

function parseYoutube($validUrl){
$finalString="";
//$re = '/(?:https?:\/\/)?(?:www\.)?youtu\.?be(?:\.com)?\/?.*(?:watch|embed)?(?:.*v=|v\/|\/)([\w-_]+)/';
$re = '/(?:https?:\/\/)?(?:www\.)?youtu\.?be(?:\.com)?(\/?.*(?:watch|embed)(\?v=.*|\/)|\/.*).[\w-_]+/mi';
preg_match_all($re, $validUrl, $matches);
//echo "<pre>";print_r($matches);die;
	foreach ($matches[0] as $result){
		$finalString .="[youtube]".str_replace(array('src="','"'),'',$result)."[/youtube]<br>";  
	}
	return $finalString;

} 
function parseGiphy($validUrl){
$finalString="";
$re = '/(?<!href=")(?:https?:\/\/)(?:www.)?(?:media.)?(gph|giphy)?\.\w+\/\w+(:?\/)?[\w _-]+(:?\/[\w-_]+)?(:?.\w{3,4})/mi';
preg_match_all($re, $validUrl, $matches);
//echo "<pre>";print_r($matches);die;
	foreach ($matches[0] as $result){
		$finalString .="[giphy]".str_replace(array('src="','"'),'',$result)."[/giphy]<br>";  
	}
	return $finalString;

} 


  public function getSearchvod111()
  {
      $nsearch1=@$_POST['notsearch'];
      $keyword=@$_POST['keyword'];

      $data=BdContactStorage::searchvod($keyword,$nsearch1);

	  if($_POST['action']=="vodsearch")
	  {
		  $data_v=BdContactStorage::searchMultiVideo($keyword);

		  //echo '<pre>'; print_r($data_v); exit;

		   $li='<ul class="vodlists" style="text-align: left; padding: 10px; overflow: scroll; height: 300px; width: 400px;">';

		   if($data_v)
		   {

  		   foreach($data_v as $key=>$item){

           $vpath='http://'.$item->s3_domain.'/'.$item->file_path;

           $li .='<li><input id="'.$item->id.'" value="'.$item->id.'" data-item="'.$item->source_file_name.'" data-item-vpath="'.$vpath.'"  type="radio" name="svideo" />'.$item->source_file_name.'</li>';


        		 }
		   }else{
		 $li .='<li>Video Not available !</li>';



		   }

		 $li .='</ul>';


        echo $li;
				 exit;
	  }



   if($_POST['action']=="search")
      {
       $attb="related_video";
            $attbtype="checkbox";
            $listclass="related_video";



    $html='<table data-drupal-selector="edit-related-video" id="edit-related-video" class="responsive-enabled" data-striping="1"> <thead> <tr> <th class="select-all"></th> <th>Add Related Video</th> <th></th></tr> </thead>';
      }else{

                 $attb="related_video_list";
                $attbtype="hidden";
            $listclass="related_video_hlist";


      }
    foreach($data as $item)
    {

    if($_POST['action']=="search")
      {
        $delete="";
         $idfev='searchvvVideoList---'.$item->id;
      }else{
          $delete=' <a class="dark_red btn_col ajaxDeleteRelatedVideoList" id="VideoList---'.$item->id.'" href="#">Delete</a>';
           $idfev='ajaxrelatedVideoList---'.$item->id;
      }

    $html .= '<tr id="'.$idfev.'" class="odd"> <td><div class="js-form-item form-item js-form-type-checkbox form-type-checkbox js-form-item-related-video-'.$item->id.' form-item-related-video-'.$item->id.' form-no-label"> <input data-drupal-selector="edit-related-video-'.$item->id.'" id="edit-related-video-'.$item->id.'" name="'.$attb.'['.$item->id.']" value="'.$item->id.'" class="form-checkbox  '.$listclass.'" type="'.$attbtype.'"> </div></td> <td>'.$item->title.'</td><td>'.$delete.'</td> </tr> ';


    }
     if($_POST['action']=="search")
      {
    $html .=' </tbody> </table>';
      }
 echo $html;
   exit;
  }
  
  
  function content() {


$csv = $this->readCSV('/opt/httpd/vhosts/cosmodrupal/Cosmodataonlybody-0-1000.csv');
unset($csv[0]);
$csv=array_values($csv);
//echo '<pre>'; print_r($csv); echo '</pre>';exit;
$i=0;
foreach($csv as $item){
//echo $item[3];die;
    $nodes = \Drupal::entityTypeManager()   ->getStorage('node')
    ->loadByProperties(['field_old_id' =>$item[3]],['type' => 'story']);
                
//echo '<pre>'; print_r($nodes); echo '</pre>';
foreach ($nodes as $n) {
//echo '<pre>'; print_r($n); echo '</pre>';

    
        $nid= $n->nid->value;
     
      $node = \Drupal\node\Entity\Node::load($nid);
      $node->set("body", $item[2]);
  //    $node->set("field_name", 'New value');
      $node->save();
                
//echo "ishant";exit;

                }
                                
                echo "<br/>".$i.'-->>'.$item[1]."--->".$item[3]."--->successfully Updated";
                $i++;
                
                } //csv loop end

    return array(
      $text,
      $table,
         $text1,
    );
  }

  
       function readCSV($csvFile){
      $file_handle = fopen($csvFile, 'r');
      while (!feof($file_handle) ) {
          $line_of_text[] = fgetcsv($file_handle, 10000000);
      }
      fclose($file_handle);
      return $line_of_text;
    }

}
