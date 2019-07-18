(function($) {
  $(document).ready(function() {
	 	 
			CKEDITOR=window.parent.CKEDITOR;
			var plainEditorContent = CKEDITOR.instances['edit-body-0-value'].getData();
			plainEditorContent.replace(/(\r\n|\n|\r)/gm, "").replace(/(&nbsp;)/g, " ");
			wordCount = strip(plainEditorContent).trim().split(/\s+/).length;


			$("#edit-body-0-value").after('<h5 style="float: right; margin: 15px; position: relative;font-weight: bold;}" id="count" >Total Words : '+wordCount+'</h5>');	

			var editor=CKEDITOR.instances['edit-body-0-value'];
			editor.on('key', function() {
			var plainEditorContent = CKEDITOR.instances['edit-body-0-value'].getData();
           plainEditorContent.replace(/(\r\n|\n|\r)/gm, "").replace(/(&nbsp;)/g, " ");
			wordCount = strip(plainEditorContent).trim().split(/\s+/).length;

        console.log("wordCount: "+wordCount);
		
		 $("#count").html('<h5 id="count" style="float: right; margin: 15px; position: relative;font-weight: bold;}" >Total Words : '+wordCount+'</h5>');
			});
	  
	  $("#edit-body-0-value").after('<a class="popembed" href="javascript:void(0)">Social Embed</a>  <div id="showbc" style="background:#000; opacity:0.7; width:100%; height:100%; position:fixed; top:0px; left:0px; z-index:9999; display:none"></div><div id="showcont" class="comment-box" style="background: none repeat scroll 0 0 #fff;box-shadow: 5px 5px 5px #000;padding:10px;position: fixed;top: 20%;width: 520px;z-index: 99999;left: 50%; display:none; border-right:6px; margin-left: -250px; border-radius: 10px;"> <b class=\'close\'>X</b> <!-- <div class="comment-head"><h1>Embed Picture</h1></div>--> <!--comment slider--> <div class="devider"></div> <div id="commentbox"> <div class="user-c"> <iframe src="" width="520" height="300" id="frmi" frameborder="0" scrolling="no"></iframe> </div> </div></div> ');
	  
		htmlImg = '<form name="photo" id="imageUploadForm" enctype="multipart/form-data" action="/api/ckimage/ckimageupload" method="post">';
		htmlImg += '<input type="file"  id="ImageBrowse" name="multi_img[]" hidden="hidden" name="image" size="30"/ multiple>';
		htmlImg +='</form>';
	  
	  $("#edit-body-0-value").after('<a class="showimagebox" href="javascript:void(0)">Upload Image</a>  <div id="showimagelightbox" style="background:#000; opacity:0.7; width:100%; height:100%; position:fixed; top:0px; left:0px; z-index:9999; display:none"></div><div id="showimagecontainer" class="comment-box" style="background: none repeat scroll 0 0 #fff;box-shadow: 5px 5px 5px #000;padding:10px;position: fixed;top: 20%;width: 520px;z-index: 99999;left: 50%; display:none; border-right:6px; margin-left: -250px; border-radius: 10px;"> <b class=\'close\'>X</b>  <div class="comment-head"><h1>Multiple image Upload</h1> <div class="row displayError"> </div></div>  <br><br><br><br><br><div class="devider"></div> <div id="commentbox"> '+htmlImg+'<div class="user-c"> </div> </div></div> ');
	 	  
	  
	   $('#imageUploadForm').on('submit',(function(e) {
			e.preventDefault();
			if(jQuery("#ImageBrowse").get(0).files.length > 5){
				message = showError('You can not upload more than 5 images');
				$('.displayError').html(message);
				jQuery('#ImageBrowse').val(null);
				return;
			}
			
			var formData = new FormData(this);
			$('.displayError').html('');
				$.ajax({
					type:'POST',
					url: $(this).attr('action'),
					data:formData,
					cache:false,
					dataType: "json",
					contentType: false,
					processData: false,
					beforeSend : function(){
						showLoading();
					},
					complete : function(){
						hideLoading()
					},
					success:function(data){
						jQuery('#ImageBrowse').val(null);
						console.log(data);
						createImgTag(data); 
						
					},
					error: function(data){
						
					}
			});
			
    }));
	
	
	
    $("#ImageBrowse").on("change", function() {
        $("#imageUploadForm").submit();
    });
	  
	  $('.close').click(function(){
			jQuery("#showimagelightbox").hide();
			jQuery("#showimagecontainer").hide();
	  });
	  
	   $('.popembed').click(function () {
		$('#showbc').show();
        $('#showcont').show();
        $('#frmi').attr('src', '/api/rest/search/');
        });
		
		
		$('.showimagebox').click(function () {
			
			jQuery("#showimagelightbox").show();
			jQuery("#showimagecontainer").show();
		});
		
	
        $('.close').click(function () {
            $('#showbc').hide();
            $('#showcont').hide();
        });
	  
	  
	  
 $("#searchvideo").click(function() { 
 
   var sk=$("#searchkeyword").val();
   
   if(sk=="")
   {
    $("#searchkeyword").css("border","1px solid red");

  return false;
     }else{
    $("#searchkeyword").css("border","1px solid #40b6ff");
		
	}
   
    url="/api/rest/videopagesearch";
     $.get(url,{"action":"vodsearch","keyword":sk},function (data) { 
     
    $(".vodlists").html(data); 
    
    
      
$("input[name='svideo']").click(function(){
    
    if($('input:radio[name=svideo]:checked').val()){
        svid=$('input:radio[name=svideo]:checked').val();
	    vt=$('input:radio[name=svideo]:checked').attr('data-item');
	    vpath=$('input:radio[name=svideo]:checked').attr('data-item-vpath');
	    pvpath=$('input:radio[name=svideo]:checked').attr('data-item-vpath-private');
	
  	$(".tbvideolistR").html('<p class="tbvideolistR">'+vt+'</p>');
  	$("#edit-field-video-0-value").val(svid);
	$("#edit-field-video-trans-url-0-value").val(vpath);

	
	
	var vpathhtml='<div id="vplayvideo"><iframe frameborder="0" width="480" height="270" src="'+pvpath+'" allowfullscreen allow="autoplay"></iframe></div>';
	
	$("#vplayvideo").html(vpathhtml);
		

    }
});  
     });
   
   
 
 });  
  $("#searchvideoReset").click(function() { 
 
   var sk=$("#searchkeyword").val("");
   
    url="/admin/content/getSearchvod";
     $.post(url,{"action":"vodsearch","keyword":""},function (data) { 
     
    $(".vodlists").html(data); 
    
        
$("input[name='svideo']").click(function(){
    
    if($('input:radio[name=svideo]:checked').val()){
        svid=$('input:radio[name=svideo]:checked').val();
	    vt=$('input:radio[name=svideo]:checked').attr('data-item');
	    vpath=$('input:radio[name=svideo]:checked').attr('data-item-vpath');
	
  	$(".tbvideolistR").html('<p class="tbvideolistR">'+vt+'</p>');
  	$("#edit-field-video-0-value").val(svid);
	
	
	var vpathhtml='<div id="vplayvideo"><iframe frameborder="0" width="480" height="270" src="'+vpath+'" allowfullscreen allow="autoplay"></iframe></div>';
	
	$("#vplayvideo").html(vpathhtml); 
		

    }
}); 
        
     });
   
   
 
 });  
 
$("input[name='svideo']").click(function(){
    
    if($('input:radio[name=svideo]:checked').val()){
			svid=$('input:radio[name=svideo]:checked').val();
			vt=$('input:radio[name=svideo]:checked').attr('data-item');
			vpath=$('input:radio[name=svideo]:checked').attr('data-item-vpath');
			pvpath=$('input:radio[name=svideo]:checked').attr('data-item-vpath-private');

		
		
	
  	$(".tbvideolistR").html('<p class="tbvideolistR">'+vt+'</p>');
  	$("#edit-field-video-trans-0-value").val(svid);
 	$("#edit-field-video-trans-url-0-value").val(vpath);
	
	console.log(vpath);
	
	var vpathhtml='<div id="vplayvideo"><iframe frameborder="0" width="480" height="270" src="'+vpath+'" allowfullscreen allow="autoplay"></iframe></div>';
	console.log("vpath: ",vpath);
	console.log("vpathhtml",vpathhtml);
	
	$("#vplayvideo").html(vpathhtml);
		

    }
}); 
 
   
  
	  
  });
})(jQuery);


function strip(html) {
        var tmp = document.createElement("div");
        tmp.innerHTML = html;

        if (tmp.textContent == '' && typeof tmp.innerText == 'undefined') {
           return '0';
        }
        return tmp.textContent || tmp.innerText;
    }
function InsertHTML() {
	// Get the editor instance that we want to interact with.
	var editor = CKEDITOR.instances['edit-body-0-value'];
	var value ="hello";

	// Check the active editing mode.
	if ( editor.mode == 'wysiwyg' )
	{
		// Insert HTML code.
		// http://docs.ckeditor.com/#!/api/CKEDITOR.editor-method-insertHtml
		editor.insertHtml( value );
	}
	else
		console.log( 'You must be in WYSIWYG mode!' );
}
function showLoading(){
    jQuery('#showimagecontainer').prepend('<div class="loading-div" style="background: rgba(0,0,0,0.4);width: 100%;height: 100%;min-height: 100%;position: absolute;top: 0;left: 0;z-index: 10000;"><img style="left:49%;position: absolute;top: 50%;"src="http://aajtak.intoday.in/includes/bigLoader.gif"></div>'); 
}

function hideLoading(){
    jQuery('.loading-div').remove();
}
	
function createImgTag(data){
	if(data.length==0) return ;
	var editor = CKEDITOR.instances['edit-body-0-value'];
	var d = new Date();
	uuid = Date.now();
	imgTag = '';
	for(i=0;i<data.length;i++){
		imgTag += '<p><img data-entity-type="file" data-entity-uuid="'+uuid+'--'+i+'" height="330"  src="'+data[i]+'" /></p>';
	}
	editor.insertHtml( imgTag );
	jQuery("#showimagelightbox").hide();
	jQuery("#showimagecontainer").hide();
	
}

function showError(messageInfo,type = 'error'){ //status
	message = '<div role="contentinfo" aria-label="Error message" class="messages messages--'+type+'"> <div role="alert">';
    message +=  messageInfo;  
    message += '</div></div>' ;        
    return  message;           
      
}
