<?php 
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
// Creating the widget 
class wpcpt_widget extends WP_Widget {

function __construct() {
parent::__construct(
// Base ID of your widget
'wpcpt_widget', 

// Widget name will appear in UI
__('Page Tree', 'wpcpt_widget_domain'), 

// Widget description
array( 'description' => __( 'Display (and sort) hierachical posts in tree format', 'wpcpt_widget_domain' ), ) );
}
// Creating widget front-end
// This is where the action happens
public function widget( $args, $instance ) {
$title = apply_filters( 'widget_title', $instance['title'] );
// before and after widget arguments are defined by themes
echo $args['before_widget'];
if ( ! empty( $title ) )
echo $args['before_title'] . $title . $args['after_title'];

// This is where you run the code and display the output

 //$cpt_posttype=$instance['cpt_name'];
global $post;
$thispageid = 0;
	if ($post && is_single()) $thispageid = $post->ID;
$userrole_exp=explode(',',$instance['cpt_role']);
$current_user_cpt = wp_get_current_user();
$user_id_cpt=$current_user_cpt->ID;
$user_roles_cpt = $current_user_cpt->roles;
$user_role_cpt = array_shift($user_roles_cpt);
if(in_array($user_role_cpt,$userrole_exp) || is_super_admin())
$checkuserrole=true;
else 
$checkuserrole=false;
if (!is_user_logged_in()) $checkuserrole=false;
## Show the top parent elements from DB ###
		function menu_showNested($parentID,$checkuserrole) {
			global $post;
			$thispageid = 0;
			if ($post) $thispageid = $post->ID;
		
				
			$args_child = array(
	          'sort_order' => 'ASC',
			  'sort_column' => 'menu_order',
	          'parent' => $parentID,
	          'offset' => 0,
	          'post_type' => 'userpress_wiki',
	          'post_status' => 'publish'
			); 
		$pages_chiled = get_pages($args_child); 
						
			if (count($pages_chiled) || (isset($_GET['action']) && isset($_GET['eaction']) && $_GET['action'] == 'edit' && $_GET['eaction'] == 'create_sub' && $parentID == $thispageid ) ) {
				echo "\n";
				echo "<ol class='dd-list'>\n";
				
					if (isset($_GET['action']) && isset($_GET['eaction']) && $_GET['action'] == 'edit' && $_GET['eaction'] == 'create_sub' && $parentID == $thispageid) {
							echo '<li class="dd-item newitem" id="newitem" data-id="X">';
							if($checkuserrole)
							echo '<span class="dd-handle" style="width:100%;margin-right: 1px;" >New Wiki</span>';
							else 
							echo '<span style="width:100%">THIS NEW ITEM</span>';
							echo "</li>\n";
						}
				if (count($pages_chiled)){
					   foreach ( $pages_chiled as $page_chiled ) {
							echo "\n";
							
						echo '<li class="dd-item';
						echo $page_chiled->ID == $thispageid ? ' checked' : '';
						echo '" data-id='.$page_chiled->ID.'>';
							
					if($checkuserrole)
						echo '<span class="dd-handle" style="width:100%;margin-right: 1px;" ><a href="'.post_permalink( $page_chiled->ID ).'">'.$page_chiled->post_title.'</a></span>';
					else 		
						echo '<span  style="width:100%"><a href="'.post_permalink( $page_chiled->ID ).'">'.$page_chiled->post_title.'</a></span>';	
						// Run this function again (it would stop running when the mysql_num_result is 0
								menu_showNested($page_chiled->ID,$checkuserrole);
							
					echo "</li>\n";
				}
				
				}
			echo "</ol>\n";
			}
		}			

		//userpress_wiki
	$args_main = array(
	          'sort_order' => 'ASC',
			  'sort_column' => 'menu_order',
	          'parent' => 0,
	          'offset' => 0,
	          'post_type' => 'userpress_wiki',
	          'post_status' => 'publish'
			); 
		$pages_main = get_pages($args_main); 
  
		if(count($pages_main)){
		echo "<div class='cf nestable-lists'>\n";
			echo "<div class='dd' id='nestableMenu'>\n\n";
				echo "<ol class='dd-list'>\n";
				
						if (isset($_GET['action']) && $_GET['action'] == 'create') {
							echo '<li class="dd-item newitem" id="newitem" data-id="X">';
							if($checkuserrole)
							echo '<span class="dd-handle" style="width:100%;margin-right: 1px;" >New Wiki</span>';
							else 
							echo '<span style="width:100%">THIS NEW ITEM</span>';
							echo "</li>\n";
						}
				
                       foreach ( $pages_main as $page_main ) {
							echo "\n";
	
						echo '<li class="dd-item';
						echo $page_main->ID == $thispageid ? ' checked' : '';
						echo '" data-id='.$page_main->ID.'>';
						if($checkuserrole)
						echo '<span class="dd-handle" style="width:100%;margin-right: 1px;" ><a href="'.post_permalink( $page_main->ID ).'">'.$page_main->post_title.'</a></span>';
						else 
						echo '<span  style="width:100%"><a href="'.post_permalink( $page_main->ID ).'">'.$page_main->post_title.'</a></span>';
						
						menu_showNested($page_main->ID,$checkuserrole);
						
						echo "</li>\n";	
                    }
  
				echo "</ol>\n\n";
			echo "</div>\n";
		echo "</div>\n\n";
		}

		//Feedback div for update hierarchy to DB
		// IMPORTANT: This needs to be here! But you can remove the style
		echo '<div id="sortDBfeedback" style="display:none;"></div>';		
		
echo $args['after_widget'];
}

// Widget Backend 
public function form( $instance ) {
		
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'New title', 'wpcpt_widget_domain' );
}

////post type name
/*if(isset($instance[ 'cpt_name' ]))
{
	$cpt_name=$instance[ 'cpt_name' ];
}
else 
{
	$cpt_name='';
}
*/
/////user role

if(isset($instance[ 'cpt_role' ]))
{
	$cpt_role=@explode(',',$instance[ 'cpt_role' ]);
}
else 
{
	$cpt_role= array('administrator');
}

// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<!---
<p>
<label for="<?php //echo $this->get_field_id( 'cpt_name' ); ?>"><?php //_e( 'Post Type:' ); ?></label> 
<select name="<?php //echo $this->get_field_name( 'cpt_name' ); ?>">
<?php
/* $post_types = get_post_types( '', 'names' ); 
foreach ( $post_types as $post_type ) {
	if($post_type==$cpt_name)
	{
		$selected_pt='selected="selected"';
	}
	else { $selected_pt=''; }
   echo '<option '.$selected_pt.'>' . $post_type . '</option>';
} */
?>
</select>
</p>
<p>--->


<label for="<?php echo $this->get_field_id( 'cpt_role' ); ?>"><?php _e( 'User Role:' ); ?></label> </p>
<p>	
<?php
global $wp_roles;
     $roles = $wp_roles->get_names();
 
foreach ( $roles as $rolekey => $role ) {
	if(is_array($cpt_role) && in_array($rolekey,$cpt_role))
	{
	
		$selected_ptr='checked="checked"';
	}
	else { 
	
		$selected_ptr=''; 
	}
	?>
	<input <?php echo $selected_ptr; ?> id="<?php echo $this->get_field_id('cpt_role'); ?>" name="<?php echo $this->get_field_name('cpt_role'); ?>[]" type="checkbox" value="<?php echo $rolekey; ?>"  />
	
	<?php 
	echo $role .'<br/>';

}
?>

<?php 
}
	
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
		$checkauthcount=count($new_instance['cpt_role']);
	if($checkauthcount)
	{
	 $cpt_nameimp=@implode(',',$new_instance['cpt_role']);
	}
	else {
		$cpt_nameimp='';
	}

$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
//$instance['cpt_name'] = ( ! empty( $new_instance['cpt_name'] ) ) ? strip_tags( $new_instance['cpt_name'] ) : '';
$instance['cpt_role'] = $cpt_nameimp;

return $instance;
}
} // Class wpb_widget ends here

// Register and load the widget
function wpcpt_load_widget() {
	register_widget( 'wpcpt_widget' );
}
add_action( 'widgets_init', 'wpcpt_load_widget' );

/**
 * enqueue scripts and styles
 */
function cpt_name_scripts() {	
	wp_enqueue_style( 'cptstyle', plugins_url( '/css/cptstyle.css' , __FILE__ ) );
	wp_enqueue_script( 'cptfunctions', plugins_url( '/js/cptfunctions.js' , __FILE__ ), array(), '1.0.0', true );	
    //wp_enqueue_script( 'jquery.min', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', array(), '1.0.0', true );			
	wp_enqueue_script( 'jquery.nestable', plugins_url( '/js/jquery.nestable.js' , __FILE__ ), array(), '1.0.0', true );
	wp_enqueue_script( 'cptinnerfun', plugins_url( '/js/cptinnerfun.js' , __FILE__ ), array(), '1.0.0', true );	
	$treeupdate_path = array('treeupdate_path_url' => plugins_url( 'menuSortableSave.php' , __FILE__ ));
    wp_localize_script('cptfunctions', 'php_data', $treeupdate_path);
    $options = get_option('wiki_default');
    $subwikiexist = '0';
	if (!isset($options['subpages_enabled']) || $options['subpages_enabled'] == 1)
		$subwikiexist = '1';
    
    wp_localize_script('jquery.nestable', 'subwikisexist', $subwikiexist);	
}

add_action( 'wp_enqueue_scripts', 'cpt_name_scripts' );


?>