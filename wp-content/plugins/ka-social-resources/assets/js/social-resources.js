var DREAMITIK_CORE = {};
jQuery(document).ready(function($) {
	DREAMITIK_CORE.gtype_completion.init();
    
    $('.lnk_show_toggle').click(function(e){
        e.preventDefault();
        $link = $(this);
        $target = $($link.attr('href'));
        
        var group = $target.data('toggle_group');
        if( group != ''){
            $('[data-toggle_group="'+ group +'"]:not("'+ $link.attr('href') +'")' ).slideUp();
        }
        
        $target.slideToggle();
    });
    
    $('.txt_add_new_taxonomy').on("keypress", function(e) {
        if (e.keyCode == 13) {
            $textbox = $(this);
            $wrapper = $textbox.closest('.taxonomy-ui-wrapper');
            var newterm = $.trim( $textbox.val() );
            if( ''==newterm )
                return;
            
            $textbox.parent().find('.fa-spin').show();
            
            var data = {
                action: 'kasr_add_taxonomy',
                taxonomy: $wrapper.data('taxonomy'),
                newterm: newterm
            };
            
            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: data,
                success: function(response) {
                    response = $.parseJSON(response);
                    if( response.status ){
                        term = response.term;
                        $wrapper.find('select').append("<option value='"+term.term_id+"'>"+term.name+"</option>");
                        $wrapper.find('select').val(term.term_id);
                        
                        $wrapper.find('.choose_taxonomy .term_list').append('<label><input type="checkbox" name="tags" value="' + term.name + '" id="' + term.term_id + '" checked />' + term.name + '</label>' );
                    }
                    
                    $textbox.val('');
                    $textbox.parent().find('.fa-spin').hide();
                }
            });
            
        }
    });
});

DREAMITIK_CORE.gtype_completion = {};
(function(context, window, $) {
	var config = {
				},
		uploader = false,
		_l = {},
		filesAdded = 0
		pics_uploaded = [],
		state = {
			uploader_max_files : 3,
			uploader_temp_img : 'http://placehold.it/150&text=image'
		};

	context.init = function() {
		if ( ! context.get_elements() ) {
			return false;
		}

		/*$('.btn_gtype_completion').fancybox({
			href: '#content_gtype_completion'
		});*/
		
		//context.setup_modal();
      
		setTimeout( function() {
			context.start_uploader();
		}, 10 );
		
	};
	
	context.get_elements = function() {
		_l.$form = $('#frm_gtype_completion');//CHANGE THIS

		if ( _l.$form.length === 0 ) {
			return false;
		}

		_l.$open_uploder_button = $('.btn_gtype_completion');
		_l.$add_photo = $('#gtype-media-bulk-uploader');
		_l.$add_photo_button = $('#gtype-file-browser-button');
		_l.$uploader = $('#gtype-media-bulk-uploader');
		_l.$uploaded = $('#gtype-media-bulk-uploader-uploaded .images');
		_l.$hidden_field = $('input[name="hdn_resource_attachments"]');

		return true;
    };
	
	context.setup_modal= function() {
		/*_l.$open_uploder_button.fancybox({
			href : '#content_gtype_completion',
			minWidth : 500,
			beforeLoad : function(){
				_l.$open_uploder_button.find('.fa-spin').show();
			},
			beforeClose: function(){
				_l.$open_uploder_button.find('.fa-spin').hide();
			}
		});*/
    };
	
	
	context.start_uploader = function() {
		var $progressBar, progressPercent = 0;
  
		//var uploader_state = 'closed';
		var ieMobile = navigator.userAgent.indexOf('IEMobile') !== -1;
    
		uploader = new plupload.Uploader({
			runtimes: 'html5,flash,silverlight,html4',
			browse_button: _l.$add_photo_button[0],
			dragdrop: true,
			container: 'gtype-media-bulk-uploader-reception',
			drop_element: 'gtype-media-bulk-uploader-reception',
			max_file_size: '10mb',
			multi_selection: true,
			url: ajaxurl,
			multipart: true,
			multipart_params: {
				action: 'gtype_completion_post_photo',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce_post_update': $("input[name='gtype_completion']").val()//CHANGE THIS
			},
			filters: [
				{ title: 'Upload files', extensions: 'jpg,jpeg,gif,png,bmp,doc,docx,pdf,txt', prevent_duplicates:true }//change this
			],
			init: {
				Init: function () {
					if (_l.$add_photo.find('.moxie-shim').find("input").length == '0') {
						_l.$add_photo.find('.moxie-shim').first().css("z-index",10);
						_l.$add_photo.find('.moxie-shim').css("cursor",'pointer');
					} else {
						clone = $(_l.$add_photo_button[0]).clone();
						$(_l.$add_photo_button[0]).after(clone).remove();
						_l.$add_photo_button[0] = clone;
						$(_l.$add_photo_button[0]).on("click",function() {
							_l.$add_photo.find('.moxie-shim').find("input").click();
						});
					}
				},
				FilesAdded: function(up, files) {
					if(up.files.length > state.uploader_max_files || files.length > state.uploader_max_files ) {
						uploader.splice( filesAdded, uploader.files.length );
      
						alert( 'Exceeded maximum number of files' );
						return false;
					}
        
					for( var i=0; i < files.length; i++ ){
						if( $( 'div[data-fileid="'+files[i].id+'"]' ).length === 0 ){
							var newdoc = "<div data-fileid='"+ files[i].id +"' class='file'><strong><span class='docname'>"+ files[i].name +"</span></strong>" + 
									"<progress class='buddyboss-media-progress-bar' value='0' max='100'></progress></div>";
							_l.$uploaded.append( newdoc );
							filesAdded++;
						}
					}
					
					//$.fancybox.update();
					up.start();
				},

				UploadProgress: function(up, file) {
					if ( file && file.hasOwnProperty( 'percent' ) ) {
						$progressBar = $('div[data-fileid="'+file.id+'"]').find('progress');
						progressPercent = file.percent;
						$progressBar.val(progressPercent);
					}
				},

				FileUploaded: function(up, file, info) {
					var responseJSON = $.parseJSON( info.response );
					//console.log('// ----- upload response ----- //');
					//console.log(responseJSON);
					$file = $('div[data-fileid="'+file.id+'"]');
					$file.removeClass('uploading');
					$file.data('attachment_id',responseJSON.attachment_id);
					//$file.find('>img').attr('src',responseJSON.url);
		  
					$file.find('progress').replaceWith(
						"<a href='#' onclick='return window.DREAMITIK_CORE.gtype_completion.removeUploaded(\""+ file.id +"\");' class='delete'>X</a>"
					);
		
					pics_uploaded.push( responseJSON );
					
					//$.fancybox.update();
					
					//PUSH file.id into hidden field( name="attch_ids" ) value
					//loop through all the files in pics_uploaded variable and add their ids to hidden field
					context.updateHiddenField();
				},

				Error: function(up, args) {
					alert( 'Error uploading photo' );

					$progressWrap.removeClass('uploading');
					$postButton.prop("disabled", false).removeClass('loading');

					//uploader_state = 'closed';
				}
			}
		});

		uploader.init();
    }, // start_uploader();

    context.removeUploaded = function( fileid ){
		/* remove from upload files list */
		var $file = $('div[data-fileid="'+fileid+'"]');
		if( pics_uploaded.length > 0 ){
			var pics_uploaded_temp = [];
			for( var i=0; i<pics_uploaded.length; i++ ){
				if( pics_uploaded[i].attachment_id !== $file.data('attachment_id') ){
					pics_uploaded_temp.push( pics_uploaded[i] );
				}
			}
      
			pics_uploaded = pics_uploaded_temp;
		}
  
		var file_to_remove = false;
		/* remove from plupload queue */
		$.each(uploader.files, function(i, ufile) {
			if( ufile.hasOwnProperty( 'id' ) && ufile.id==fileid ){
				file_to_remove = ufile;
			}
		});
  
		if( file_to_remove ){
			uploader.removeFile(file_to_remove);
			filesAdded--;
		}
  
		/* delete html */
		$file.remove();
		
		//REMOVE file.id FROM hidden field
		//loop through all the files in pics_uploaded variable and add their ids to hidden field
		context.updateHiddenField();
		return false;
    };
	
	context.updateHiddenField = function(){
		_l.$hidden_field.val();
		var attachment_ids = '';
		if( pics_uploaded.length > 0 ){
			for( var i=0; i<pics_uploaded.length; i++ ){
				var file = pics_uploaded[i];
				if( file.hasOwnProperty( 'attachment_id' ) ){
					attachment_ids += file.attachment_id + ',';
				}
			}
			
			_l.$hidden_field.val( attachment_ids );
		} else {
			_l.$hidden_field.val( '' );
		}
	};
  
})(DREAMITIK_CORE.gtype_completion, window, window.jQuery);


function savePost(){
    var swith_back_to_tmce = false;
	if( ! jQuery('#post_content').is(':visible') ) {
		jQuery('#post_content-html').trigger('click');
		swith_back_to_tmce = true;
	}
	
    jQuery('#error-box').hide();
        
    if(validateFields()){ 	
        jQuery('#post-maker-container :input, #post-maker-container textarea').attr('disabled', true);                         
        jQuery('#save-waiting').show();
        
        if(jQuery("#mode").val() == "new"){
            actionName="create_post";             
        } else {                    
            actionName="update_post";            
        }

        currentPostStatus =  jQuery("#post-status").val();
        
        if( jQuery('#publish-draft').is(":checked") ){
            article_status="draft";
        }else{
            article_status="publish";
        }

                       
        postTitle = jQuery("#post_title").val();   
        postContent = jQuery("#post_content").val();
        postImage = jQuery("#image-name").val();
        categoryId = jQuery("#categories-ids").val();
        tagnames = "";
        jQuery('[name="tags"]').each(function(){
           if( jQuery(this).is(':checked') ) {
               tagnames += jQuery(this).val() + ',';
           }
        });
        postId = jQuery("#post-id").val();         
        attachmentIds = jQuery("input[name='hdn_resource_attachments']").val();
        sr_group_id = jQuery("select[name='sr_group_id']").val();
        
        jQuery.ajax({
                type: 'post',
                url: MyAjax.ajaxurl,
                data: { action: actionName, post_title:postTitle, post_content:postContent, post_image:postImage, category_id:categoryId, tag_names:tagnames, status:article_status, post_id:postId, attachmentIds: attachmentIds, sr_group_id: sr_group_id},
                success:
                function(response) {   
                                                                                       
                       data = jQuery.parseJSON(response);
                       if(data.status == "ok"){                                
                            jQuery("#post-maker-container").hide();
                            
                            jQuery('#view-article').click(
                             function () {       
                                 window.open(data.viewarticle, "_self");        
                             }
                            );
                            jQuery('#new-article').click(
                             function () {       
                                 window.open(data.newarticle, "_self");        
                             }
                            );
                            jQuery('#edit-article').click(
                             function () {       
                                 window.open(data.editarticle, "_self");        
                             }
                            );
                            jQuery("#save-message").html(data.message);

                            if(jQuery("#post-status").val() == "new-post" && article_status== 'publish'){
                                jQuery("#articles span").html(parseInt(jQuery("#articles span").html())+1);
                            }

                            if(jQuery("#post-status").val() == "new-post" && article_status== 'draft'){
                               jQuery("#draft span").html(parseInt(jQuery("#draft span").html())+1);
                            }

                            if(jQuery("#post-status").val() == "new-post" && article_status== 'pending'){
                               jQuery("#under-review span").html(parseInt(jQuery("#under-review span").html())+1);
                            }

                            if(jQuery("#post-status").val() == "draft" && article_status== 'publish'){
                                jQuery("#articles span").html(parseInt(jQuery("#articles span").html())+1);
                                jQuery("#draft span").html(parseInt(jQuery("#draft span").html())-1);
                            }

                           if(jQuery("#post-status").val() == "draft" && article_status== 'pending'){
                               jQuery("#under-review span").html(parseInt(jQuery("#under-review span").html())+1);
                               jQuery("#draft span").html(parseInt(jQuery("#draft span").html())-1);
                           }


                            jQuery(".post-save-options").show();
                            jQuery('html, body').animate({scrollTop:0}, 'slow');                                                                      
                       }else{
                           jQuery('#save-waiting').hide();
                           jQuery('#post-maker-container :input, #post-maker-container textarea').removeAttr('disabled');
                           showError(data.message);                          
                       }                                      
                    }                
         });             
     }else{
         jQuery('html, body').animate({scrollTop:0}, 'slow');
     }       

	if( swith_back_to_tmce ){
		jQuery('#post_content-tmce').trigger('click');
	}
}

function getImageObject(urlImage){            
    return "<img id='image-container' src='"+urlImage+"'/>";            
}

function cancelImage(){      
    jQuery("#image-name").val("");          
    jQuery("#image-preview-container").html("");        
    jQuery(".edit-controls").hide();
    jQuery(".upload-controls").show();            
}

/*category stuff*/
function showCategoryList(){
    jQuery(".picker").hide();
    jQuery(".white-picker").show();
    jQuery(".category-list-container").fadeIn();
}

function showTagsList(){
    jQuery(".picker-t").hide();
    jQuery(".white-picker-t").show();
    jQuery(".tags-list-container").fadeIn();            
}

function closeTagsList(){           
    setListsElements("tags-content","tag-names", "tag-ids","tags-selector");
    jQuery(".picker-t").show();
    jQuery(".white-picker-t").hide();
    jQuery(".tags-list-container").fadeOut();                 
}

function closeCategoriesList(){
    setListsElements("categories-content","categories-names", "categories-ids","categories-selector");
    jQuery(".picker").show();
    jQuery(".white-picker").hide();
    jQuery(".category-list-container").fadeOut();
}
        
function setListsElements(content, namesContainer, idsContainer, selector){
    jQuery(function() {                
        names = "";
        ids = "";
        jQuery('.'+content +' input[type="radio"]:checked').each(function() {
            names += jQuery(this).val() + ',';                    
            ids += jQuery(this).attr('id') + ',';                   
        });

        jQuery('.'+content +' input[type="checkbox"]:checked').each(function() {
            names += jQuery(this).val() + ',';
            ids += jQuery(this).attr('id') + ',';
        });
        
        jQuery("#"+namesContainer).val(names.slice(0, -1));
        jQuery("#"+idsContainer).val(ids.slice(0, -1));
        names = names.slice(0, -1).substring(0, 25);
        if( names.length >= 25 ){
            names += "...";                
        }           
        jQuery("."+selector).html(names);
    });
}

function setCategoriesElements(){
    jQuery(function() { 
        name = "";
        id = "";
        jQuery('.category-list-container input[type="radio"]:checked').each(function() {                    
            name = jQuery(this).val();
            id = jQuery(this).attr('id');                 
        });
        
        jQuery("#categories-ids").val(id);
        jQuery(".categories-selector").html(name);
    });      
}

function hideCategoryList(){        
    jQuery(".picker").show();
    jQuery(".category-list-container").hide();
    jQuery(".white-picker").hide();         
}

jQuery('.category-list-container').hover(
     function () {
        showCategoryList()
     },
     function () {
        hideCategoryList();
     }
);

function validateFields(){
    send=true;            
    if(jQuery("#post_title").val()==""){
        send=false;
        jQuery("#post_title").addClass("error-field");                
    }     
    return send;            
}

jQuery('#post_title').focus(
	 function () {       
	     jQuery("#post_title").removeClass("error-field");           
	 }
);
 
jQuery('.categories-selector').hover(
	 function () {       
    	 jQuery(".categories-selector").removeClass("error-field");           
 	}
);

function showError(message){            
   jQuery("#error-box").html("<label>"+message+'</label>');
   jQuery("#error-box").css('display', "block");
   jQuery("#error-box").effect("highlight", {}, 3000);
}


function setImagePreview(){            
    if(jQuery("#feature-image-url").val() != ""){
        jQuery(".edit-controls").show();  
        jQuery(".upload-controls").hide();                
        jQuery("#image-preview-container").html(getImageObject(jQuery("#feature-image-url").val()));                
    }     
}

jQuery(document).ready(function() {
    if(jQuery("#mode").val()=="edit"){
        //setCategoriesElements();
        //setListsElements("tags-content","tag-names","tag-ids","tags-selector");
        //setListsElements("categories-content","categories-names", "categories-ids", "categories-selector");
        setImagePreview();
    }    
});


function getMoreArticles(){
     jQuery("#more-articles-loader").show();   
     jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: { action: "get_more_articles", offset:jQuery("#offset").val(), status:"publish"},                                                
            success:
            function(response) {    
               jQuery("#more-articles-loader").hide();
               newOffset =parseInt(jQuery("#offset").val()) + jQuery("#inicialcount").val();
               if(newOffset >= parseInt(jQuery("#postcount").val())){
                   jQuery(".more-articles-button-container").hide();                               
               }else{
                   jQuery("#offset").val(newOffset);  
               }                    
               jQuery("#more-container-publish").append(response);                       
            }
     });         
}

function showContent(current){
    
    switch(current)
    {
    case "publish":          
      jQuery("#publish-tab").addClass("current");
      jQuery("#pending-tab, #draft-tab").removeClass("current");
      jQuery(".publish-container").show();
      jQuery(".pending-container, .draft-container").hide();         
      break;
    case "pending":          
      jQuery("#pending-tab").addClass("current");
      jQuery("#publish-tab, #draft-tab").removeClass("current");
      jQuery(".pending-container").show();
      jQuery(".publish-container, .draft-container").hide();     
      break;
    case "draft":          
      jQuery("#draft-tab").addClass("current");
      jQuery("#pending-tab, #publish-tab").removeClass("current");  
      jQuery(".draft-container").show();
      jQuery(".pending-container, .publish-container").hide();     
      break;
    }       
    
    jQuery("#current-state").val(current); 
}


function deleteArticle(postId){
    jQuery("#delete-"+postId).addClass("deleting");          
	jQuery.ajax({
	            type: 'post',
	            url: MyAjax.ajaxurl,
	            data: { action: "delete_article", post_id:postId},                                                
	            success:
	            function(response) {
                   response = JSON.parse(response);
                   if(response.status == 'ok'){

                       jQuery("#"+response.post_status+" span").html(parseInt(jQuery("#"+response.post_status+" span").html())-1);
                       counterId = '#'+jQuery("#current-state").val() + '-count';
                       jQuery(counterId).html(parseInt(jQuery(counterId).html())-1);
                       jQuery("#"+postId).hide();
                   }
	            }
	      });
} 
