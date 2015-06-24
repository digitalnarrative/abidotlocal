<?php
global $post, $wpdb, $bp, $socialArticles;

$directWorkflow = isDirectWorkflow();

$statusLabels = array("publish"=>__('Published', 'social-resources'), 
                        "draft"=>__('Draft', 'social-resources'), 
                      "pending"=>__('Under review', 'social-resources'), 
                     "new-post"=>__('New', 'social-resources'));
?>
<?php if(isset($_GET['article'])):    
       $myArticle = get_post($_GET['article']);
       $post_id = $_GET['article'];
       if(isset($myArticle) && $myArticle->post_author == bp_loggedin_user_id() && ($socialArticles->options['allow_author_adition']=="true" || $myArticle->post_status=="draft")){
           $state = "ok";           
           $title = $myArticle->post_title;
           $content = $myArticle->post_content;             
           $status = $myArticle->post_status;
           $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($_GET['article']), 'large');
			$resource_attachments = get_post_meta( $post_id, '_resource_attachments', true );
           if(isset($large_image_url)){
                $image_name = end(explode("/",$large_image_url[0]));
           }
           
           $resource_group_id = get_post_meta( $post_id, 'group_id', true );
           ?>           
            <input type="hidden" id="mode" value="edit"/>
            <input type="hidden" id="feature-image-url" value="<?php echo $large_image_url[0];?>"/>    
           <?php
       }else{          
           $state = "error";
           $message = __("You cannot perform this action", "social-resources");
       }     
       ?>        
<?php else:
       $post_id = 0;  
       $status = "new-post";
       ?>        
       <input type="hidden" id="mode" value="new"/>    
<?php endif;?>

<input type="hidden" id="image-name" value="<?php echo $image_name;?>"/>
<input type="hidden" id="categories-ids"/>
<input type="hidden" id="tag-ids"/>
<input type="hidden" id="tag-names"/>
<input type="hidden" id="categories-names"/>
<input type="hidden" id="post-id" value="<?php echo $post_id;?>"/>   
<input type="hidden" id="post-status" value="<?php echo $status;?>"/>
<input type="hidden" id="direct-workflow" value="<?php echo $directWorkflow;?>"/>

<?php if(!isset($_GET['article']) || $state == "ok"):?>
    <div class="post-save-options messages-container"> 
        <label id="save-message"></label>
        <input type="submit" id="edit-article" class="button" value="<?php _e("Edit resource", "social-resources"); ?>" />
        <input type="submit" id="view-article" class="button" value="<?php _e("View resource", "social-resources"); ?>" />
        <input type="submit" id="new-article" class="button" value="<?php _e("New resource", "social-resources"); ?>" />
    </div>
    <div id="post-maker-container">
        <div class="options">
            <div class="options-content">
                <span class="titlelabel"><?php _e("Categories", "social-resources"); ?></span>
                <div class="categories-selector"><?php _e("Select your category", "social-resources"); ?></div>
                <span class="picker" onmouseover="showCategoryList()"></span>
                <span class="white-picker"></span>
                <?php echo get_category_list($_GET['article']);?>
            </div>
            <div class="options-content options-content-second">
                <label class="titlelabel"><?php _e("Tags", "social-resources"); ?></label>
                <div class="tags-selector"><?php _e("Select your tags", "social-resources"); ?></div>
                <span class="picker-t" onmouseover="showTagsList()"></span>
                <span class="white-picker-t"></span>
                <?php echo get_tags_list($_GET['article']);?>
            </div>

            <div class="post-status-container options-content">
                <label class="titlelabel"><?php _e("Status", "social-resources"); ?></label>
                <span class="article-status <?php echo $status;?>"><?php echo $statusLabels[$status];?></span>
            </div>
        </div>

        <input type="text" id="post_title" class="title-input" autofocus placeholder="<?php _e("Resource title...", "TEXTDOMAIN"); ?>" value="<?php echo $title; ?>"/>
        
        <div class="editor-container">
            
			<div class="textarea-container">
				<?php wp_editor( $content, 'post_content' );?>             
			</div>
            
        </div>        
        
        <?php 
        $include = array();
        
        $user_groups = groups_get_user_groups( get_current_user_id() );
        if( !empty( $user_groups ) && isset( $user_groups['groups'] ) && !empty( $user_groups['groups'] ) ){
            foreach( $user_groups['groups'] as $g_id ){
                //only those companies where i am an admin
                //if( groups_is_user_admin(get_current_user_id(), $g_id))
                    $include[] = $g_id;
            }
        }

        if( empty( $include ) )
            $include = array( 9999999 );

        $args = array( 'include' => $include );
        if( bp_has_groups( $args ) ):
            ?>
            <div class='editfield'>
                <label><?php _e( 'Post in group', 'TEXTDOMAIN' );?></label>
                <select name='sr_group_id'>
                    <?php 
                    $selected_group = isset( $_GET['gid'] ) ? (int)$_GET['gid'] : false;
                    if( $resource_group_id ){
                        $selected_group = $resource_group_id;
                    }
                    echo "<option value=''>" . __( 'Select..', 'TEXTDOMAIN' ) . "</option>";
                
                    while( bp_groups() ){
                        $selected = bp_get_group_id()==$selected_group ? ' selected' : '';
                        bp_the_group();
                        printf( "<option value='%s' %s >%s</option>", bp_get_group_id(), $selected, bp_get_group_name() );
                    }
                    ?>
                </select>
            </div>
        <?php endif; ?>
        
        <div id="post_image" class="post-image-container">
            <div class="image-preview-container" id="image-preview-container">
            </div>    
            <div class="upload-controls">
                <input id="uploader" type="submit" class="button" value="<?php _e("Upload Image", "social-resources"); ?>" />     
                <label><?php _e("Max size allowed is 2 MB", "social-resources"); ?></label>
            </div>    
            <div class="uploading" id="uploading">
               <img src ="<?php echo SA_BASE_URL;?>/assets/images/load.gif"/>
               <label><?php _e("Uploading your image. Please wait.", "social-resources"); ?></label>
            </div>  
            
            <div class="edit-controls">
                <input type="submit" class="button" value="<?php _e("Delete", "social-resources"); ?>" onclick="cancelImage()" /> 
            </div>    
        </div>
		
		<label><?php _e("Resource Files", "social-resources"); ?></label>
        <div class='post-image-container upload-controls-container' id="frm_gtype_completion">
        	<?php wp_nonce_field( 'gtype_completion', 'nonce_gtype_completion' );?>
			
			<div id="gtype-media-bulk-uploader" class="">
				
				<div id="gtype-media-bulk-uploader-uploaded">
					<div class="images clearfix">
						
					</div>
				</div>
				<div id="gtype-media-bulk-uploader-reception" class="image-drop-box">
					<p class="buddyboss-media-drop-instructions"><?php _e( 'Drop files here to upload', 'TEXTDOMAIN' );?></p>
                    <p class="buddyboss-media-drop-separator"><?php _e( 'or', 'TEXTDOMAIN' );?></p>
					<a id="gtype-file-browser-button" title="Select image" class="browse-file-button"><?php _e( 'Select Files', 'buddyboss-media' );?></a>
				</div>
				<?php 
				if( $resource_attachments ){
					$resource_attachments_csv = implode( ',', $resource_attachments );
				} else {
					$resource_attachments_csv = '';
				}
				?>
				<input type="hidden" name="hdn_resource_attachments" value="<?php echo esc_attr( $resource_attachments_csv );?>" >
			</div>
		</div>

        <div id="save-waiting" class="messages-container">
             <img id="save-gif"src ="<?php echo SA_BASE_URL;?>/assets/images/load.gif"/>
             <label><?php _e("Saving your resource. Please wait.", "TEXTDOMAIN"); ?></label>        
        </div>        
        <div id="error-box" class="messages-container">       
        </div>
        <div class="buttons-container" id="create-controls">
            <?php if(($status=="draft" || $status == "new-post") && !$directWorkflow):?>
                <input type="checkbox" id="publish-save" /><label for="publish-save"><span></span><?php _e("Save and move it under review", "TEXTDOMAIN"); ?></label>
            <?php endif?>
            <?php if(($status=="draft" || $status == "new-post") && $directWorkflow):?>
                <input type="checkbox" id="publish-save" /><label for="publish-save"><span></span><?php _e("Save and publish", "TEXTDOMAIN"); ?></label>
            <?php endif?>

            <input type="submit" class="button save" value="<?php _e("Save", "social-resources"); ?>" onclick="savePost(); return false;" />
            <input type="submit" class="button cancel" value="<?php _e("Cancel", "social-resources"); ?>" onclick="window.open('<?php echo $bp->loggedin_user->domain.'resources';?>', '_self')" />
        </div>  
    </div>    
<?php else:?>
    <div id="message" class="messageBox note icon">
        <span><?php echo $message; ?></span>
    </div>    
<?php endif;?>

<script>
jQuery(function(){                    
    new AjaxUpload('uploader', {
        action: MyAjax.baseUrl+'/upload-handler.php',                
                onComplete: function(file, response){                                       
                    response = jQuery.parseJSON(response);
                    jQuery("#uploading").hide();
                    if(response.status == "ok"){                                                           
                        jQuery("#image-name").val(response.value);
                        jQuery("#image-preview-container").html(getImageObject(MyAjax.tmpImageUrl+ response.value));
                        jQuery(".edit-controls").show();                                                    
                    }else{
                        jQuery(".upload-controls").show();   
                        showError(response.value);                                
                    }
                },
                onSubmit: function(file, extension){
                   jQuery('#error-box').hide();
                   jQuery(".upload-controls").hide();
                   jQuery("#uploading").show();                              
                }   
            });         
        
        });             

</script>
