<?php 

add_action('wp_ajax_nopriv_create_post', 'create_post' );
add_action('wp_ajax_create_post', 'create_post' );  
function create_post(){
    article_manager("create", $_POST['post_id'], $_POST['post_title'], $_POST['post_content'], $_POST['category_id'], $_POST['tag_names'], $_POST['status'], $_POST['post_image'], $_POST['attachmentIds']);
} 

add_action( 'wp_ajax_kasr_add_taxonomy', 'ajax_ka_sr_add_taxonomy');
function ajax_ka_sr_add_taxonomy(){
    $taxonomy = $_POST['taxonomy'];
    $termname = $_POST['newterm'];
    
    $retval = array(
        'status'    => false,
        'term'      => array(),
    );
    if( term_exists( $termname, $taxonomy ) )
        die( json_encode( $retval ) );
    
    $term = wp_insert_term( $termname, $taxonomy );
    if( !empty( $term ) && !is_wp_error( $term ) ){
        $retval['term'] = get_term_by( 'id', $term['term_id'], $taxonomy );
        $retval['status'] = true;
    }
    die(json_encode($retval));
}

add_action('wp_ajax_nopriv_update_post', 'update_post' );
add_action('wp_ajax_update_post', 'update_post' );  
function update_post(){
    article_manager("update", $_POST['post_id'], $_POST['post_title'], $_POST['post_content'], $_POST['category_id'], $_POST['tag_names'], $_POST['status'], $_POST['post_image'], $_POST['attachmentIds']);
}

add_action('wp_ajax_nopriv_delete_article', 'delete_article' );
add_action('wp_ajax_delete_article', 'delete_article' );  
function delete_article(){
    global $socialArticles;
    $postToDelete = get_post($_POST['post_id']);

    if($postToDelete->post_status == 'publish'){
        $status_id = 'resources';
    }else{
        if($postToDelete->post_status == 'pending'){
            $status_id = 'under-review';
        }else{
            $status_id = $postToDelete->post_status;
        }
    }


    if(bp_loggedin_user_id()==$postToDelete->post_author && ($socialArticles->options['allow_author_deletion']=="true" || $postToDelete->post_status=="draft") ){
       wp_delete_post($_POST['post_id']);
       echo json_encode(array("status"=>"ok", 'post_status'=>$status_id));
    }else{
       echo json_encode(array("status"=>"error"));
    }
    die();    
}

add_action('wp_ajax_nopriv_get_more_articles', 'get_more_articles' );
add_action('wp_ajax_get_more_articles', 'get_more_articles' );  
function get_more_articles(){    
    $offset = $_POST['offset'];
    $status = $_POST['status'];
    ob_start();            
    get_articles($offset, $status);        
    $out = ob_get_contents();
    ob_end_clean();      
    echo $out;
    die();    
}

add_action('wp_ajax_nopriv_deleteArticlesNotification', 'deleteArticlesNotification' );
add_action('wp_ajax_deleteArticlesNotification', 'deleteArticlesNotification' );  
function deleteArticlesNotification(){
    global $bp;
        
    $user_id=$bp->loggedin_user->id;
    $item_id=$_POST['item_id'];
    $component_name='social_articles';
    $component_action=$_POST['action_id'];   
      
    bp_core_delete_notifications_by_item_id ($user_id,  $item_id, $component_name, $component_action);     
    die();        
}


function article_manager($task, $postId, $post_title, $post_content, $post_category, $post_tags, $post_status, $post_image, $post_attachments='' ){
    global $bp;
  
    if($post_title == ""){
        $response = array();
        $response['status'] = "error";
        $response['message'] = __("Title is required", "social-resources");
        echo json_encode($response);
    }else{
        /*create resource/post etc*/    
        $my_post = array();        
        $my_post['post_title'] = $post_title; 
        $my_post['post_content'] = $post_content;       
        $my_post['post_author'] = get_current_user_id();    
        $my_post['post_type'] = 'resource'; // post type post,page etc.
        $my_post['post_status'] = $post_status; 
        
        if($task == "create"){
            $postId = wp_insert_post( $my_post );         
        }else{
            $my_post['ID'] = $postId;   
            wp_update_post( $my_post );         
        }     
        
        if($postId != 0){
            /*Set category*/
            if(empty($post_category)){
                $categories[] = get_option('default_category');
            }else{
                $categories = explode(',',$post_category);
            }

            wp_set_post_terms($postId, $categories, 'category');

            /*Set tags*/
            if(!empty($post_tags)){                
                wp_set_post_terms($postId, explode(',',$post_tags), 'post_tag');
            }else{
                wp_set_post_terms($postId, array(), 'post_tag');
            }                
            
            if( isset( $_POST['sr_group_id'] ) ){
                $group_id = $_POST['sr_group_id'];
                update_post_meta( $postId, 'group_id', $group_id );
            }
            
            /*Attach image*/            
           $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($postId), 'large');
           $attachedImage_name="";       
           if(isset($large_image_url)){
                $attachedImage_name = end(explode("/",$large_image_url[0]));
           }              
                 
            $wp_upload_dir = wp_upload_dir();        
            $filenameTemp = SA_TEMP_IMAGE_PATH.$post_image;
            $filename = $wp_upload_dir['path'].'/sa_'.$post_image;
                                   
            if ($post_image != "" && $attachedImage_name != $post_image && copy($filenameTemp,$filename)) {
                unlink($filenameTemp);
                $wp_filetype = wp_check_filetype(basename($filename), null );    
                $attachment = array(        
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_id = wp_insert_attachment( $attachment, $filename, $postId );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                update_post_meta($postId, '_thumbnail_id', $attach_id);      
            }else{
                if($post_image == ""){
                      update_post_meta($postId, '_thumbnail_id', "");    
                }               
            }
			
			if( $post_attachments ){
				$post_attachments = trim( $post_attachments, ',' );
				$post_attachments = explode( ',', $post_attachments );
			}
			update_post_meta( $postId, '_resource_attachments', $post_attachments );
            
            $response = array();
            $response['status'] = "ok";            
            $response['postId'] = $postId;
            $response['viewarticle'] = get_permalink($postId);
            
            $add_article_url = get_permalink( ka_misc_settings( 'page_add_resource' ) );
            $response['newarticle'] = $add_article_url;
            $response['editarticle'] = add_query_arg( 'article', $postId, $add_article_url );
            $response['message'] = get_user_message($post_status);
            echo json_encode($response);
        }else{
            $response = array();
            $response['status'] = "error";
            $response['message'] = __("Error creating the article. Try again please.", "social-resources" );
            echo json_encode($response);
        }        
    }    
    die();    
}


function get_articles($offset, $status, $all = false){
    global $bp, $post, $socialArticles;
    if($all){
       $postPerPage = -1;
    }else{
       $postPerPage = $socialArticles->options['post_per_page']; 
    }        

    $args = array(     'post_status'       => $status,
                       'ignore_sticky_posts'    => 1,                       
                       'posts_per_page'    => $postPerPage,
                       'offset'            => $offset,                      
                       'post_type'         => 'resource',
                       'author'            => bp_displayed_user_id()                                    
                 );                 
    
    $articles_query = new WP_Query( $args );
             
    if ($articles_query->have_posts()):
        $add_resouce_page = get_permalink( ka_misc_settings( 'page_add_resource' ) );
        while ($articles_query->have_posts()):
            $articles_query-> the_post();
            $allCategories = array();
            $categories = get_the_category();
            for($i=0; $i < count($categories); $i++){                                                                
                $allCategories[]='<a href="'.get_category_link( $categories[$i]->cat_ID ).'" >'.
                                    $categories[$i]->cat_name.
                                 '</a>';                
            }      
                 
            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), "thumbnail");                      
            if( $image == null){
                $image = "NO-IMAGE";  
            }else{
                $image = $image[0];
            }
            
            ?>            
             <article id="<?php echo $post->ID; ?>" class="article-container media">
                    <div class="article-image media-left">
                        <?php if($image!="NO-IMAGE"):?>        
                        <a href="<?php the_permalink();?>">
                            <img src="<?php echo $image?>" alt="<?php the_title(); ?>"/>
                        </a>
                        <?php endif;?>                               
                    </div>                    
                    <div class="article-data media-body">
                        <h3 class="media-title"><a href="<?php the_permalink();?>"><?php the_title(); ?></a></h3>                        
                        <span class="date"><?php the_time('j');?>&nbsp;<?php the_time('F');?>&nbsp;<?php the_time('Y');?></span>                         
                        <div class="excerpt">                                                           
                            <?php echo get_short_text(get_the_excerpt(),$socialArticles->options['excerpt_length']);?>                        
                        </div>


						<div class="article-metadata">                     
						<?php if(bp_displayed_user_id()==bp_loggedin_user_id()):?>
							  <div class="author-options">
								<?php if($socialArticles->options['allow_author_adition']=="true" || $post->post_status=="draft"):?>
                                    <?php $edit_article_url = add_query_arg( 'article', $post->ID, $add_resouce_page ); ?>
                                    <a class="edit" title="<?php _e("edit article", "social-resources" );?>" href="<?php echo $edit_article_url;?>"></a>
								<?php endif;?>
								<?php if($socialArticles->options['allow_author_deletion']=="true" || $post->post_status=="draft"):?>
								<a class="delete" title="<?php _e("delete article", "social-resources" );?>" id="delete-<?php echo $post->ID; ?>" href="#" onclick="deleteArticle(<?php echo $post->ID; ?>); return false;"></a>        
								<?php endif;?>
							  </div>
						<?php endif;?>              

							<div class="clear"></div>
						</div>
						<div class="article-footer">
							<div class="article-categories">
								<?php _e("Category", "social-resources" ); echo ": ".implode(" | ",$allCategories);?>
							</div>
							<div class="article-likes">
								<a href="<?php echo get_comments_link( $post->ID ); ?>">
									<span class="likes-count">
										  <?php $comments = wp_count_comments( $post->ID ); echo $comments->approved; ?>
									</span>
									<span class="likes-text"><?php _e("comments", "social-resources" )?></span>
								</a>
							</div>
						</div>


						
                    </div>         
            </article>                  
            <?php endwhile; ?>        
        <?php endif;
    wp_reset_query();
}

function get_category_list( $post_id ){
    global $socialArticles;
    $currentCategories = array();
    if(isset($post_id)){
        $currentCategories =  wp_get_post_categories($post_id);
    }
    
    $category_ids = get_all_category_ids();
    $categories_backList = array();
    
    $options = "";

    foreach($category_ids as $cat_id) {
        $cat_name = get_cat_name($cat_id);
        if(!in_array($cat_name, $categories_backList)){

            if(in_array($cat_id, $currentCategories)){
                $selected = "selected";
            }else{
                $selected="";
            }

            $options .= "<option {$selected} value='{$cat_id}'>{$cat_name}</option>";
        }
    }
    
    $categoryList  = "<div class='taxonomy-ui-wrapper' data-taxonomy='category'>"
            . "<div class='clearfix'>"
            .   "<select name='categories' id='categories-ids' class='taxonomy-list'><option value=''>" . __( 'Select..', 'TEXTDOMAIN' ) . "</option>"
            .       $options 
            .   "</select>"
            .   "<a class='lnk_add_taxonomy lnk_show_toggle' data-taxonomy='category' href='#input_new_category'><i class='fa fa-plus-square'></i></a>"
            . "</div>"
            . "<div class='clearfix add_new_taxonomy' id='input_new_category' style='display: none'>"
            .   "<input type='text' class='txt_add_new_taxonomy' placeholder='" . __( 'Add new', 'TEXTDOMAIN' ) . "'><i class='fa fa-spin fa-spinner' style='display: none'></i>"
            . "</div>"
            . "</div>";
    return $categoryList;
}

function get_tags_list($post_id){            
    $currentTags = array();
    if(isset($post_id)){
       $currentTags =  wp_get_post_tags($post_id, array( 'fields' => 'ids' ));        
    }        
    $tagsList = "";

    $tags = get_terms('post_tag', 'hide_empty=0');

    foreach ( $tags as $key => $tag ) { 
      $tag_id =  $tag->term_id;
      $tag_name = $tag->name;              
              
      if(in_array($tag_id, $currentTags)){
        $checked = "checked";         
      }else{
        $checked="";
      }           
      $tagsList .= '<label><input type="checkbox" name="tags" value="'.$tag_name.'" id="'.$tag_id.'" '.$checked.'/>' . $tag_name.'</label>';
    }
    
    $categoryList  = "<div class='taxonomy-ui-wrapper' data-taxonomy='post_tag'>"
            . "<div class='clearfix'>"
            .   "<a href='#input_tags_list' class='lnk_show_toggle' id='lnk_show_tags'>" . __( 'Select', 'TEXTDOMAIN' ) . " <i class='fa fa-chevron-right'></i></a>"
            . "</div>"
            . "<div id='input_tags_list' class='clearfix choose_taxonomy toggled_content' data-toggle_group='post_tag' style='display:none'>"
            .   "<div class='term_list'>" . $tagsList . "</div>"
            . "<hr>"
            . "<input type='text' class='txt_add_new_taxonomy' placeholder='" . __( 'Add new', 'TEXTDOMAIN' ) . "'><i class='fa fa-spin fa-spinner' style='display: none'></i>"
            . "</div>"
            . "</div>";
    
    return $categoryList;   
}

function get_category_list00($post_id){
    global $socialArticles;
    $currentCategories = array();
    if(isset($post_id)){
        $currentCategories =  wp_get_post_categories($post_id);
    }

    if( $socialArticles->options['category_type'] == 'single'){
        $optionType="radio";
    }else{
        $optionType="checkbox";
    }

    $categoryList = "<div class='category-list-container'>
                        <div class='categories-ready'>
                            <div class='generic-button'>
                                <a href='#' title='".__("done", "social-resources" )."' onclick='closeCategoriesList(); return false;'>".__("done", "social-resources" )."</a>
                            </div>
                        </div>
                        <div class='categories-content'>";

    $category_ids = get_all_category_ids();
    $categories_backList = array();

    foreach($category_ids as $cat_id) {
        $cat_name = get_cat_name($cat_id);
        if(!in_array($cat_name, $categories_backList)){

            if(in_array($cat_id, $currentCategories)){
                $checked="checked";
            }else{
                $checked="";
            }

            $categoryList .= '<p><input type="'.$optionType.'" name="categories" value="'.$cat_name.'" id="'.$cat_id.'" '.$checked.'/>
                            <label for="'.$cat_id.'"><span></span>'.$cat_name.'</label>';
        }
    }
    $categoryList .= "</div></div>";
    return $categoryList;
}

function get_tags_list00($post_id){            
    $currentTags = array();
    if(isset($post_id)){
       $currentTags =  wp_get_post_tags($post_id, array( 'fields' => 'ids' ));        
    }        
    $tagsList = "<div class='tags-list-container'>
                    <div class='tags-ready'>
                        <div class='generic-button'>
                            <a href='#' title='".__("done", "social-resources" )."' onclick='closeTagsList(); return false;'>".__("done", "social-resources" )."</a>
                        </div>
                        </div> <div class='tags-content'>";

    $tags = get_terms('post_tag', 'hide_empty=0');

    foreach ( $tags as $key => $tag ) { 
      $tag_id =  $tag->term_id;
      $tag_name = $tag->name;              
              
      if(in_array($tag_id, $currentTags)){
        $checked="checked";            
      }else{
        $checked="";
      }           
      $tagsList .= '<p><input type="checkbox" name="tags" value="'.$tag_name.'" id="'.$tag_id.'" '.$checked.'/>
                            <label for="'.$tag_id.'"><span></span>'.$tag_name.'</label>';                        
    }        
    $tagsList .= "</div></div>";  
    return $tagsList;   
}


function show_taxonomy_select($article_id, $taxonomy_name){

    $current_terms =array();

    if(isset($article_id)){
        $current_terms = wp_get_post_terms( $article_id, $taxonomy_name, array("fields" => "ids"));
    }
    $args = array(
        'hide_empty'=>0
    );
    $all_terms = get_terms($taxonomy_name, $args);
    $combo = '';
    if ( !empty( $all_terms ) && !is_wp_error( $all_terms ) ){
        $combo = '<select id="sa-'.$taxonomy_name.'">';
        foreach($all_terms as $term){
            $selected = '';
            if(in_array($term->term_id,$current_terms))
                $selected = 'selected';
            $combo .= '<option value="'.$term->term_id.'" '.$selected.'>'.$term->name.'</option>';
        }
        $combo.='</select>';
    }
    echo $combo;
}




function get_user_message($status){
    switch ($status) {
        case 'pending':
            return __("Your resource is under review. When the editors approve it you will get a notification.", "social-resources");
            break;
        
        case 'draft':
            return __("Your resource is in draft form.", "social-resources" );
            break;

        case 'publish':
            return __("Your resource has been published.", "social-resources" );
            break;
    }    
}

?>
