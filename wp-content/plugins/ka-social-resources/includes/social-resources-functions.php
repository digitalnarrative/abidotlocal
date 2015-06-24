<?php
if ( !defined( 'ABSPATH' ) ) exit;

function ka_sr_can_edit_resource( $user_id=false, $resource_id=false ){
    if( !$user_id )
        $user_id = get_current_user_id();
    
    if( !$user_id )
        return false;
    
    $can = false;
    if( user_can( $user_id, 'ka_sr_create' ) || user_can( $user_id, 'level_10' ) )
        $can = true;
    
    /*
    if( $can && $resource_id ){
        if( )
    }
    */
    return $can;
}

function social_articles_load_template_filter( $found_template, $templates ) {
    global $bp;

    if( !bp_sa_is_bp_default() || !bp_is_current_component( $bp->social_articles->slug )){
        return $found_template;
    }

    foreach ( (array) $templates as $template ) {
        if ( file_exists( STYLESHEETPATH . '/' . $template ) )
            $filtered_templates[] = STYLESHEETPATH . '/' . $template;
        else
            $filtered_templates[] = dirname( __FILE__ ) . '/templates/' . $template;
    }
    $found_template = $filtered_templates[0];
    return apply_filters( 'social_articles_load_template_filter', $found_template );
}
add_filter( 'bp_located_template', 'social_articles_load_template_filter', 10, 2 );


function social_articles_load_sub_template( $template ) {
    if( empty( $template ) )
        return false;

    if( bp_sa_is_bp_default() ) {
        //locate_template( array(  $template . '.php' ), true );
        if ( $located_template = apply_filters( 'bp_located_template', locate_template( $template , false ), $template ) )
            load_template( apply_filters( 'bp_load_template', $located_template ) );

    } else {
        bp_get_template_part( $template );

    }
}

function get_short_text($text, $limitwrd ) {   
    if (str_word_count($text) > $limitwrd) {
      $words = str_word_count($text, 2);
      if ($words > $limitwrd) {
          $pos = array_keys($words);
          $text = substr($text, 0, $pos[$limitwrd]) . ' [...]';
      }
    }
    return $text;
}

function custom_get_user_posts_count($status, $user_id = false ){
    if( !$user_id )
        $user_id = bp_loggedin_user_id ();
    
    $args = array();     
    $args['post_status'] = $status;
    $args['author'] = $user_id;
    $args['fields'] = 'ids';
    $args['posts_per_page'] = "-1";
    $args['post_type'] = 'resource';
    $ps = get_posts($args);
    return count($ps);
}

//add_action('save_post','social_articles_send_notification');
function social_articles_send_notification($id){
    global $bp, $socialArticles;
    $savedPost = get_post($id);
    $notification_already_sent = get_post_meta($id, 'notification_already_sent', true);
    if(empty($notification_already_sent) && function_exists("friends_get_friend_user_ids") && $savedPost->post_status == "publish" && $savedPost->post_type=="post" && !wp_is_post_revision($id) && $socialArticles->options['bp_notifications'] == "true"){
        $friends = friends_get_friend_user_ids($savedPost->post_author);
        foreach($friends as $friend):
            bp_core_add_notification($savedPost->ID,  $friend , $bp->social_articles->id, 'new_article'.$savedPost->ID, $savedPost->post_author);         
        endforeach;
        bp_core_add_notification($savedPost->ID,  $savedPost->post_author , $bp->social_articles->id, 'new_article'.$savedPost->ID, -1);
        update_post_meta($id, 'notification_already_sent', true);
    }
}

function social_articles_remove_screen_notifications() {
    global $bp;
    bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, 'social_articles', 'new_high_five' );
}
//add_action( 'xprofile_screen_display_profile', 'social_articles_remove_screen_notifications' );


function social_articles_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

    do_action( 'social_articles_format_notifications', $action, $item_id, $secondary_item_id, $total_items, $format );
    
    $createdPost = get_post($item_id);

    if($secondary_item_id == "-1"){
         $text = '</a> <div id="'.$action.'"class="sa-notification">'.
                    __("One of your articles was approved","social-resources").'<a class="ab-item" title="'.$createdPost->post_title.'"href="'.get_permalink( $item_id ).'">,'.__("check it out!", "social-resources").'
                 </a> 
                 <a href="#" class="social-delete" onclick="deleteArticlesNotification(\''.$action.'\',\''.$item_id.'\', \''.admin_url( 'admin-ajax.php' ).'\'); return false;">x</a><span class="social-loader"></span></div>';
    
    }else{
        $creator = get_userdata($secondary_item_id); 
        $text = '</a> <div id="'.$action.'"class="sa-notification">'.
                    __("There is a new article by ", "social-resources").'<a class="ab-item" href="'.get_bloginfo('blog').'/members/'.$creator->user_login.'">'.$creator->user_nicename.', </a>
                 <a class="ab-item" title="'.$createdPost->post_title.'"href="'.get_permalink( $item_id ).'"> '.__("check it out!", "social-resources").'
                 </a> 
                 <a href="#" class="social-delete" onclick="deleteArticlesNotification(\''.$action.'\',\''.$item_id.'\', \''.admin_url( 'admin-ajax.php' ).'\'); return false;">x</a><span class="social-loader"></span></div>';
    }
    return $text;
}


function bp_sa_is_bp_default() {

    if(current_theme_supports('buddypress') || in_array( 'bp-default', array( get_stylesheet(), get_template() ) )  || ( defined( 'BP_VERSION' ) && version_compare( BP_VERSION, '1.7', '<' ) ))
        return true;
    else {
        $theme = wp_get_theme();
        $theme_tags = ! empty( $theme->tags ) ? $theme->tags : array();
        $backpat = in_array( 'buddypress', $theme_tags );
        if($backpat)
            return true;
        else
            return false; //wordpress theme
    }

}

function isDirectWorkflow(){
    global $socialArticles;
    return $socialArticles->options['workflow'] == 'direct' ;
}

function sa_notifications_stuff(){

    echo "
    <style>
        .sa-notification {
            height: 20px !important;
            width: 100% !important;
            margin-top: -25px !important;
            padding-bottom: 8px !important;
            padding-left: 10px !important;
            text-shadow: none !important;
            min-width: 320px !important;
        }

        .sa-notification a {
            display: inline !important;
            height: 15px !important;
            min-width: 0 !important;
            padding: 0 !important;
        }
    </style>";

    echo '<script>
        function deleteArticlesNotification(action_id, item_id, adminUrl){
            jQuery("#"+action_id).children(".social-delete").html("");
            jQuery("#"+action_id ).children(".social-loader").show();

            jQuery.ajax({
                type: "post",
                url: adminUrl,
                data: { action: "deleteArticlesNotification", action_id:action_id, item_id:item_id },
                success:
                function(data) {
                    jQuery("#"+action_id).parent().hide();
                    jQuery("#ab-pending-notifications").html(jQuery("#ab-pending-notifications").html() - 1);
                }
             });
        }
    </script>';
}
add_action( 'wp_head', 'sa_notifications_stuff' );

add_filter( 'the_content', 'kasa_add_downloadable_files' );
function kasa_add_downloadable_files( $content='' ){
	if( is_singular( 'resource' ) && in_the_loop() ){
		ob_start();
		get_template_part( 'resource/downloadables' );
		$extra_content = ob_get_clean();
		$content .= $extra_content;
	}
	return $content;
}

function kasa_the_attachment( $file_info ){
	global $kasa_current_file;
	$kasa_current_file = $file_info;
}

function kasa_file_cssclass(){
	echo kasa_get_file_cssclass();
}
	function kasa_get_file_cssclass(){
		global $kasa_current_file;
		$classes = array( 'attachment' );
		
		$classes[] = $kasa_current_file['file_type_group'];
		$classes[] = $kasa_current_file['subtype'];
		$classes[] = 'file-' . $kasa_current_file['id'];
		$classes[] = 'media';
		
		return apply_filters( 'kasa_get_file_cssclass', implode( ' ', $classes ) );
	}
	
function kasa_file_thumbnail_url(){
	echo kasa_get_file_thumbnail_url();
}
	function kasa_get_file_thumbnail_url(){
		global $kasa_current_file;
		
		$url = $kasa_current_file['icon'];
		if( $kasa_current_file['type']=='image' ){
			if( isset( $kasa_current_file['sizes'] ) && isset( $kasa_current_file['sizes']['thumbnail'] ) ){
				$url = $kasa_current_file['sizes']['thumbnail']['url'];
			}
		}
		
		return apply_filters( 'kasa_get_file_thumbnail_url', $url );
	}
	
function kasa_file_download_url(){
	echo kasa_get_file_download_url();
}
	function kasa_get_file_download_url(){
		global $kasa_current_file;
		$url = add_query_arg( 'kasa_download', 'file', $kasa_current_file['link'] );
		$url = add_query_arg( 'nonce', wp_create_nonce( 'kasa_download_' . $kasa_current_file['id'] ), $url );
		return apply_filters( 'kasa_get_file_download_url', $url );
	}
	
function kasa_file_url(){
	echo kasa_get_file_url();
}
	function kasa_get_file_url(){
		global $kasa_current_file;
		$url = $kasa_current_file['url'];
		return apply_filters( 'kasa_get_file_url', $url );
	}
	
function kasa_file_name(){
	echo kasa_get_file_name();
}
	function kasa_get_file_name(){
		global $kasa_current_file;
		$name = $kasa_current_file['filename'];
		return apply_filters( 'kasa_get_file_name', $name );
	}
	
function kasa_file_download_count( $attachment_id=false ){
	global $kasa_current_file;
	
	if( !$attachment_id )
		$attachment_id = $kasa_current_file['id'];
		
	return (int)get_post_meta( $attachment_id, '_download_count', true );
}


add_action( 'bp_actions',	'kasa_file_downloader' );
function kasa_file_downloader(){
	global $wpdb;
	if ( isset( $_GET['attachment_id'] ) && isset( $_GET['kasa_download'] ) &&  $_GET['kasa_download']=='file' ) {
		if( !wp_verify_nonce( $_GET['nonce'], 'kasa_download_' . $_GET['attachment_id'] ) )
			die();
		
		$attachment_id = $_GET['attachment_id'];
		$attachment = get_post( $attachment_id );
		
		$file_file = basename( $attachment->guid );
		$file_path = get_attached_file( $attachment_id );
		
		$file_mime_type = $attachment->post_mime_type;
		$kasa_file_download_counts = 0;
		
		// we have a file! let's force download.
		if ( file_exists( $file_path ) ){
			//update download count
			$prev_count = (int)get_post_meta( $attachment_id, '_download_count', true );
			$prev_count++;
			update_post_meta( $attachment_id, '_download_count', $prev_count );
			
			//also update the download count of its parent post
			$prev_count = (int)get_post_meta( $attachment->post_parent, '_download_count', true );
			$prev_count++;
			update_post_meta( $attachment->post_parent, '_download_count', $prev_count );
			
			$attachment_id_string = '%:"'.$attachment_id.'";%';
			$sql = 'SELECT post_id FROM ' .$wpdb->prefix . 'postmeta WHERE meta_key="_resource_attachments" and meta_value LIKE '. "'" .$attachment_id_string ."'";
			//print_r($sql);die();
			$post_attachment_id = $wpdb->get_results($sql, ARRAY_A); 
			$kasa_file_download_counts = (int)get_post_meta( $post_attachment_id[0]['post_id'], '_resource_attachments_download_count', true );
			$kasa_file_download_counts++;
			update_post_meta( $post_attachment_id[0]['post_id'], '_resource_attachments_download_count', $kasa_file_download_counts  );
			
			status_header( 200 );
			header( 'Cache-Control: cache, must-revalidate' );
			header( 'Pragma: public' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Length: ' . filesize( $file_path ) );
			header( 'Content-Disposition: attachment; filename='.$file_file );
			header( 'Content-Type: ' .$file_mime_type );
			readfile( $file_path );
			die();
		}
	}
}

if( !function_exists('emi_generate_paging') ):
/**
 * Prints pagination links for given parameters.
 * 
 * If your theme uses twitter bootstrap styles, define a constant :
 * define('BOOTSTRAP_ACTIVE', true)
 * and this function will generate the bootstrap-pagination-compliant html. 
 * 
 * @author @ckchaudhary
 * @param int $total_items total number of items(grand total)
 * @param int $items_per_page number of items displayed per page
 * @param int $curr_paged current page number, 1 based index
 * @param string $slug part of url to be appended after the home_url and before the '/page/2/' part. withour any starting or trailing '/'
 * @param string $hashlink Optional, the '#' link to be appended ot url, optional
 * @param int $links_on_each_side Optional, how many links to be displayed on each side of current active page. Default 2.
 *
 * @return void
 */
function emi_generate_paging($total_items, $items_per_page, $curr_paged, $slug, $links_on_each_side=2, $hashlink="" ){
	$use_bootstrap = false;
	if( defined( 'BOOTSTRAP_ACTIVE' ) ){
		$use_bootstrap = true;
	}
	
	$s = $links_on_each_side; //no of tabs to show for previos/next paged links
	if($curr_paged == 0){$curr_paged=1;}
	
	/*$elements : an array of arrays; each child array will have following structure
	$child[0] = text of the link
	$child[1] = page no of target page
	$child[2] = link type :: link|current|nolink
	*/
	$elements = array(); 
	
	$no_of_pages = ceil($total_items/$items_per_page);
	
	//prev lik
	if($curr_paged > 1){$elements[] = array('&laquo; Prev', $curr_paged-1, 'link');}
	
	//generating $s(2) links before the current one
	if($curr_paged > 1){
		$rev_array = array();//paged in reverse order
		$i = $curr_paged-1;
		$counter = 0;
		while($counter<$s && $i>0){
			$rev_array[] = $i;
			$i--;
			$counter++;
		}
		$arr = array_reverse ($rev_array);
		if($counter==$s){$elements[] = array(' ... ', '', 'nolink');}
		foreach($arr as $el){
			$elements[] = array($el, $el, 'link');
		}
		unset($rev_array); unset($arr); unset($i); unset($counter);
	}
	
	//generating $s+1(3) links after the current one (includes current)
	if($curr_paged <= $no_of_pages){
		$i = $curr_paged;
		$counter = 0;
		while($counter<$s+1 && $i<=$no_of_pages){
			if($i==$curr_paged){
				$elements[] = array($i, $i, 'current');
			}
			else{
				$elements[] = array($i, $i, 'link');
			}
			$counter++; $i++;
		}
		if($counter==$s+1){$elements[] = array(' ... ', '', 'nolink');}
		unset($i); unset($counter);
	}
	//next link
	if($curr_paged < $no_of_pages){$elements[] = array('Next &raquo;', $curr_paged+1, 'link');}
	
	/*enough php, lets echo some html*/
	if(isset($elements) && count($elements) > 1){?>
		
		<div class="navigation">
			<?php if( $use_bootstrap ):?>
				<ul class='pagination'>
			<?php else: ?>
				<ol class="wp-paginate">
			<?php endif;?>
				<?php
				foreach($elements as $e){
					$link_html = "";
					$class = "";
					switch($e[2]){
						case 'link':
							$base_link = get_bloginfo('url')."/$slug/page/$e[1]/?";
							foreach($_GET as $k=>$v){
								$base_link .= "$k=$v&";
							}
							$base_link = trim($base_link, "&");
							if(isset($hashlink) && $hashlink!=""){
								$base_link .="#$hashlink";
							}
							$link_html = "<a href='$base_link' title='$e[0]' class='page-numbers'>$e[0]</a>";
							break;
						case 'current':
							$class = "active";
							if( $use_bootstrap ){
								$link_html = "<span>$e[0] <span class='sr-only'>(current)</span></span>";
							} else {
								$link_html = "<span class='page-numbers current'>$e[0]</span>";
							}
							break;
						default:
							if( $use_bootstrap ){
								$link_html = "<span>$e[0]</span>";
							} else {
								$link_html = "<span class='page-numbers'>$e[0]</span>";
							}
							break;
					}
					$link_html = "<li class='". esc_attr($class) ."'>" . $link_html . "</li>";
					echo $link_html;
				}
				?>
			<?php if( $use_bootstrap ):?>
				</ul>
			<?php else:?>
				</ol>
			<?php endif;?>
		</div>
		
	<?php
	}
}
endif;

if( !function_exists('emi_generate_paging_param') ):
/**
 * Prints pagination links for given parameters with pagination value as a querystring parameter.
 * Instead of printing http://yourdomain.com/../../page/2/, it prints http://yourdomain.com/../../?list=2
 * 
 * If your theme uses twitter bootstrap styles, define a constant :
 * define('BOOTSTRAP_ACTIVE', true)
 * and this function will generate the bootstrap-pagination-compliant html. 
 * 
 * @author @ckchaudhary
 * @param int $total_items total number of items(grand total)
 * @param int $items_per_page number of items displayed per page
 * @param int $curr_paged current page number, 1 based index
 * @param string $slug part of url to be appended after the home_url and before the '/?ke1=value1&...' part. withour any starting or trailing '/'
 * @param string $hashlink Optional, the '#' link to be appended ot url, optional
 * @param int $links_on_each_side Optional, how many links to be displayed on each side of current active page. Default 2.
 * @param string $param_key the name of the queystring paramter for page value. Default 'list'
 *
 * @return void
 */
function emi_generate_paging_param($total_items, $items_per_page, $curr_paged, $slug, $links_on_each_side=2, $hashlink="", $param_key="list"){
	$use_bootstrap = false;
	if( defined( 'BOOTSTRAP_ACTIVE' ) ){
		$use_bootstrap = true;
	}
	
    $s = $links_on_each_side; //no of tabs to show for previos/next paged links
    if($curr_paged == 0){$curr_paged=1;}
    /*$elements : an array of arrays; each child array will have following structure
    $child[0] = text of the link
    $child[1] = page no of target page
    $child[2] = link type :: link|current|nolink
    */
    $elements = array(); 
    $no_of_pages = ceil($total_items/$items_per_page);
    //prev lik
    if($curr_paged > 1){$elements[] = array('&laquo; Previous', $curr_paged-1, 'link');}
    //generating $s(2) links before the current one
    if($curr_paged > 1){
        $rev_array = array();//paged in reverse order
        $i = $curr_paged-1;
        $counter = 0;
        while($counter<$s && $i>0){
            $rev_array[] = $i;
            $i--;
            $counter++;
        }
        $arr = array_reverse ($rev_array);
        if($counter==$s){$elements[] = array(' ... ', '', 'nolink');}
        foreach($arr as $el){
            $elements[] = array($el, $el, 'link');
        }
        unset($rev_array); unset($arr); unset($i); unset($counter);
    }
    
    //generating $s+1(3) links after the current one (includes current)
    if($curr_paged <= $no_of_pages){
        $i = $curr_paged;
        $counter = 0;
        while($counter<$s+1 && $i<=$no_of_pages){
            if($i==$curr_paged){
                $elements[] = array($i, $i, 'current');
            }
            else{
                $elements[] = array($i, $i, 'link');
            }
            $counter++; $i++;
        }
        if($counter==$s+1){$elements[] = array(' ... ', '', 'nolink');}
        unset($i); unset($counter);
    }
    //next link
    if($curr_paged < $no_of_pages){$elements[] = array('Next &raquo;', $curr_paged+1, 'link');}
    /*enough php, lets echo some html*/
    if(isset($elements) && count($elements) > 1){?>
        <div class="navigation">
			<?php if( $use_bootstrap ):?>
				<ul class='pagination'>
			<?php else: ?>
				<ol class="wp-paginate">
			<?php endif;?>
                <?php
                foreach($elements as $e){
                    $link_html = "";
					$class = "";
                    switch($e[2]){
                        case 'link':
                            unset($_GET[$param_key]);
                            $base_link = get_bloginfo('url')."/$slug?";
                            foreach($_GET as $k=>$v){
                                $base_link .= "$k=$v&";
                            }
                            $base_link .= "$param_key=$e[1]";
                            if(isset($hashlink) && $hashlink!=""){
                                $base_link .="#$hashlink";
                            }
                            $link_html = "<a href='$base_link' title='$e[0]' class='page-numbers'>$e[0]</a>";
                            break;
                        case 'current':
							$class = "active";
							if( $use_bootstrap ){
								$link_html = "<span>$e[0] <span class='sr-only'>(current)</span></span>";
							} else {
								$link_html = "<span class='page-numbers current'>$e[0]</span>";
							}
                            break;
                        default: 
							if( $use_bootstrap ){
								$link_html = "<span>$e[0]</span>";
							} else {
								$link_html = "<span class='page-numbers'>$e[0]</span>";
							}
                            break;
                    }
					
					$link_html = "<li class='". esc_attr($class) ."'>" . $link_html . "</li>";
					echo $link_html;
                }
                ?>
            <?php if( $use_bootstrap ):?>
				</ul>
			<?php else:?>
				</ol>
			<?php endif;?>
        </div>
    <?php
    }
}
endif;
?>