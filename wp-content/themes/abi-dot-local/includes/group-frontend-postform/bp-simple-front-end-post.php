<?php

/*
 * Main controller class
 * stores various forms and delegates the post saving to appropriate form
 * 
 */

class BPSimpleBlogPostEditor {

	private static $instance;
	var $forms = array(); // array of Post Forms(multiple post forms)
	private $self_url;

	private function __construct() {
		$this->self_url = plugin_dir_url( __FILE__ );

		//hook save action to init
		add_action( 'bp_ready', array( $this, 'save' ) );
	}

	/**
	 * Factory method for singleton object
	 * 
	 * @return BPSimpleBlogPostEditor
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) )
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Register a form
	 * 
	 * @param BPSimpleBlogPostEditForm $form 
	 */
	public function register_form( $form ) {
		$this->forms[ $form->id ] = $form; //save/overwrite
	}

	/**
	 *
	 * @param string $form_name
	 * @return BPSimpleBlogPostEditForm|boolean 
	 */
	public function get_form_by_name( $form_name ) {
		$id = md5( trim( $form_name ) );
		return $this->get_form_by_id( $id );
	}

	/**
	 * Returns the Form Object
	 * 
	 * @param string $form_id
	 * @return BPSimpleBlogPostEditForm|boolean 
	 */
	public function get_form_by_id( $form_id ) {

		if ( isset( $this->forms[ $form_id ] ) )
			return $this->forms[ $form_id ];
		return false;
	}

	/**
	 * Save a post
	 * 
	 * Delegates the task to  BPSimpleBlogPostEditForm::save() of appropriate form(which was submitted)
	 * 
	 * @return type 
	 */
	function save() {
		if ( ! empty( $_POST[ 'bp_simple_post_form_subimitted' ] ) ) {
			//yeh form was submitted
			//get form id
			$form_id = $_POST[ 'bp_simple_post_form_id' ];
			$form = $this->get_form_by_id( $form_id );

			if ( ! $form )
				return; //we don't need to do anything


				
//so if it is a registerd form, let the form handle it

			$form->save(); //save the post and redirect properly
		}
	}

}

/**
 * A Form Instance class
 * 
 * Do not use it directly, instead call bp_new_simple_blog_post_form to create new instances
 * or you can create your own child class for more felxibility
 */
class BPSimpleBlogPostEditForm {

	/**
	 * A unique md5'd id of the post form
	 * Each post form has a unique id
	 * 
	 * @var type 
	 */
	var $id; //an unique md5ed hash of the human readable name
	var $current_user_can_post = false; // It is the responsibility of developer to set it true or false based on whether he wants to allow the current user to post or not
	/**
	 * Which post type we want to edit/create
	 * 
	 * it can be any valid post type, you can specify it while registering the from
	 * 
	 * @var string post_type, defaults to post 
	 */
	var $post_type = 'post';

	/**
	 * post status after the post is submitted via front end, defaults to draft
	 * 
	 * You can set it to 'publish' if you want to directly publish it
	 * It can be set via settings while registering the form
	 * 
	 * @var string
	 */
	var $post_status = 'draft';

	/**
	 * Who wrote this post?, the user_id of post autor, default to current logged in user
	 * If it is not set, the logged in user will be attributed as the author
	 * @var type 
	 */
	var $post_author = false;

	/**
	 * Which categories are allowed for this form
	 * just for backward compatibility
	 * we will rather use taxonomy
	 * @var type 
	 */
	var $allowed_categories = array(); //if there are any
	/**
	 * @todo: remove in next release
	 * 
	 * @var type 
	 */
	var $allowed_tags = array(); //not implemented, 
	/**
	 * Taxonomy settings
	 * 
	 * @var array  Multidimensional array with details of allowed taxonomy 
	 */
	var $tax = array(); //multidimensional array
	/**
	 * Custom Fields settings
	 * 
	 * @var array Mutidimensional array with custom field settings
	 *  
	 */
	var $custom_fields = array(); //multidimensional array
	/**
	 * How many uploads are allowed
	 * 
	 * @todo: we need to finetune it for allowed media types?
	 *
	 *  @var type 
	 */
	var $upload_count = 0;

	/**
	 * Enable/Disable support for post thumbnail
	 * @var type 
	 */
	public $has_post_thumbnail = false;

	/**
	 * Used to store error/success message
	 * @var string 
	 */
	var $message = '';

	/**
	 * Create a new instance of the Post Editor Form
	 * @param string $name name of the form
	 * @param array $settings, a multidimensional array of form settings 
	 */
	public function __construct( $name, $settings ) {
		
		$this->id = md5( trim( $name ) );

		$default = array(
			'post_type' => 'post',
			'post_status' => 'draft',
			'tax' => false,
			'post_author' => false,
			'can_user_can_post' => false,
			'custom_fields' => false,
			'upload_count' => 0,
			'has_post_thumbnail' => 1,
			'current_user_can_post' => is_user_logged_in()
		);

		$args = wp_parse_args( $settings, $default );
		extract( $args );

		$this->post_type = $post_type;
		$this->post_status = $post_status;



		if ( $post_author )
			$this->post_author = $post_author;
		else
			$this->post_author = get_current_user_id();

		$this->tax = $tax;

		$this->custom_fields = $custom_fields;

		$this->current_user_can_post = $current_user_can_post;

		$this->upload_count = $upload_count;
		$this->has_post_thumbnail = $has_post_thumbnail;
	}

	/**
	 * Show/Render the Post form
	 */
	function show() {
		//needed for category/term walker
		require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/template.php');
		//will be exiting post for editing or 0 for new post
		//load the post form


		$this->load_post_form();
	}

	/**
	 * Locate and load post from
	 * we need to allow theme authors to modify it
	 * so, we will first look into the template directory and if not found, we will load it from the plugin's included file
	 * 
	 */
	function load_post_form() {
		$post_id = $this->get_post_id();

		if ( ! empty( $_POST[ 'bp_simple_post_title' ] ) && ! empty( $_POST[ 'bp_simple_post_text' ] ) ) {
			$default = array(
				'title' => $_POST[ 'bp_simple_post_title' ],
				'content' => $_POST[ 'bp_simple_post_text' ]
			);
		} else
			$default = array();

		if ( ! empty( $post_id ) ) {
			//should we check if current user can edit this post ?
			$post = get_post( $post_id );
			$args = array(
				'title' => $post->post_title,
				'content' => $post->post_content
			);

			$default = wp_parse_args( $args, $default );
		}


		extract( $default );
		include( get_stylesheet_directory() . '/includes/group-frontend-postform/form.php' );
	}

	/**
	 * Get associated term ids for a post/post type
	 * 
	 * @param type $object_ids
	 * @param type $tax
	 * @return array of term_ids 
	 */
	function get_term_ids( $object_ids, $tax ) {

		$terms = wp_get_object_terms( $object_ids, $tax );
		$included = array();
		$included = wp_list_pluck( $terms, 'term_id' );

		return $included;
		/*
		  foreach ((array) $terms as $term)
		  $included[] = $term->term_id;
		  return $included;
		 * 
		 */
	}

	/**
	 * Get the post id
	 * For editing, filter on the hook to return the post_id
	 * @return type 
	 */
	function get_post_id() {
		return apply_filters( 'bpsp_editable_post_id', 0 );
	}

	/**
	 * Does the saving thing
	 */
	function save() {
		$post_id = false;
		//verify nonce
		if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'bp_simple_post_new_post_' . $this->id ) ) {
			bp_core_add_message( __( 'The Security check failed!', 'bpsfep' ), 'error' );
			return; //do not proceed
		}


		$post_type_details = get_post_type_object( $this->post_type );

		$title = $_POST[ 'bp_simple_post_title' ];
		$content = $_POST[ 'bp_simple_post_text' ];
		$message = '';
		$error = '';
		if ( isset( $_POST[ 'post_id' ] ) )
			$post_id = $_POST[ 'post_id' ];

		if ( ! empty( $post_id ) ) {
			$post = get_post( $post_id );
			//in future, we may relax this check
			if ( ! ( $post->post_author == get_current_user_id() || is_super_admin() ) ) {
				$error = true;
				$message = __( 'You are not authorized for the action!', 'buddyboss' );
			}
		}
		if ( empty( $title ) || empty( $content ) ) {
			$error = true;
			$message = __( 'Please make sure to fill the required fields', 'buddyboss' );
		}

		$error = apply_filters( 'bsfep_validate_post', $error, $_POST );

		if ( ! $error ) {

			$post_data = array(
				'post_author' => $this->post_author,
				'post_content' => $content,
				'post_type' => $this->post_type,
				'post_status' => $this->post_status,
				'post_title' => $title
			);

			if ( ! empty( $post_id ) )
				$post_data[ 'ID' ] = $post_id;
			//EDIT

			$post_id = wp_insert_post( $post_data );
			//if everything worked fine, the post was saved
			if ( ! is_wp_error( $post_id ) ) {

				//update the taxonomy
				$group_id = $_POST['post_group_id'];
				$group_info = groups_get_group( array( 'group_id' => $group_id ) );
				$city_term = strtolower(str_replace( ' ', '-', $group_info->name ));
				
				wp_set_object_terms( $post_id, $city_term , 'cities-taxonomy' );
				
				//check for upload 
				//upload and save

				$action = 'bp_simple_post_new_post_' . $this->id;
				for ( $i = 0; $i < $this->upload_count; $i ++ ) {
					$input_field_name = 'bp_simple_post_upload_' . $i;
					$attachment = $this->handle_upload( $post_id, $input_field_name, 'bpsfep_new_post' );
				}
				//set post thumbnail
				if ( $this->has_post_thumbnail ) {

					$input_field_name = 'bp_simple_post_upload_thumbnail';
					$attachment = $this->handle_upload( $post_id, $input_field_name, 'bpsfep_new_post' );

					if ( $post_id && $attachment && wp_attachment_is_image( $attachment ) )
						set_post_thumbnail( $post_id, $attachment );
				}
				do_action( 'abi_group_post_created', $post_id );
				
				$message = sprintf( __( '%s Saved as %s successfully.', 'buddyboss' ), $post_type_details->labels->singular_name, $this->post_status );
				$message = apply_filters( 'bsfep_post_success_message', $message, $post_id, $post_type_details, $this );
			} else {
				$error = true;
				$message = sprintf( __( 'There was a problem saving your %s. Please try again later.', 'buddyboss' ), $post_type_details->labels->singular_name );
			}
		}

		//need to refactor the message/error infor data in next release when I will be modularizing the plugin a little bit more
		if ( ! $message )
			$message = $this->message;

		if ( $error )
			$error = 'error'; //buddypress core_add_message does not understand boolean properly

		bp_core_add_message( $message, $error );
	}

	/**
	 * Renders html for individual custom field
	 * @param type $field_data array of array(type=>checkbox/dd/input/textbox
	 * @param type $current_value
	 * @return string 
	 */
	function render_field( $field_data, $current_value = false ) {
		extract( $field_data );
		$current_value = esc_attr( $current_value );

		$name = "custom_fields[$key]";
		if ( $type == 'checkbox' )
			$name = $name . "[]";

		switch ( $type ) {
			case 'textbox':
				$input = "<label>{$label}<input type='text' name='{$name}' id='custom-field-{$key}' value='{$current_value}' /></label>";
				break;

			case 'textarea':
				$input = "<label>{$label}</label><textarea  name='{$name}' id='custom-field-{$key}' >{$current_value}</textarea>";
				break;


			case 'radio':
				$input = "<label>{$label}</label>";
				foreach ( $options as $option )
					$input.="<label>{$option[ 'label' ]}<input type='radio' name='{$name}' " . checked( $option[ 'value' ], $current_value, false ) . "  value='" . $option[ 'value' ] . "' /></label>";

				break;

			case 'select':
				$input = "<label>{$label}<select name='{$name}' id='custom-field-{$key}'>";
				foreach ( $options as $option )
					$input.="<option  " . selected( $option[ 'value' ], $current_value, false ) . "  value='" . $option[ 'value' ] . "' >{$option[ 'label' ]}</option>";

				$input.="</select></label>";
				break;

			case 'checkbox':
				$input = "<label>{$label}</label>";
				foreach ( $options as $option )
					$input.="<label>{$option[ 'label' ]}<input type='checkbox' name='{$name}' " . checked( $option[ 'value' ], $current_value, false ) . "  value='" . $option[ 'value' ] . "' /></label>";

				break;

			case 'date':
				$input = "<label>{$label}<input type='text' class='bp-simple-front-end-post-date'  id='custom-field-{$key}' name='{$name}' value='{$current_value}' /></label>";
				break;
			case 'hidden':
				$input = "<input type='hidden' class='bp-simple-front-end-post-hidden'  id='custom-field-{$key}' name='{$name}' value='{$current_value}' />";
				break;

			default:
				$input = '';
		}
		return $input; //return html
	}

	/**
	 * Get a validated value for the custom field data
	 * 
	 * @param type $key
	 * @param type $value
	 * @param type $data
	 * @return string 
	 */
	function get_validated( $key, $value, $data ) {

		extract( $data, EXTR_SKIP );
		$sanitized = '';

		switch ( $type ) {
			case 'textbox':
			case 'date':
			case 'textarea':
			case 'hidden':
				$sanitized = esc_attr( $value ); //should we escape?   
				break;


			case 'radio':
			case 'select':

				foreach ( $options as $option )
					if ( $option[ 'value' ] == $value )
						$sanitized = $value;

				break;



			//for checkbox     
			case 'checkbox':
				$vals = array();
				foreach ( $options as $option )//how to validate
					$vals[] = $option[ 'value' ];

				$sanitized = array_diff( $vals, ( array ) $vals );

				break;


			default:
				$sanitized = '';
				break;
		}
		return $sanitized;
	}

	/**
	 * Handles Upload
	 * @param type $post_id
	 * @param type $input_field_name
	 * @param type $action
	 * @return type 
	 */
	function handle_upload( $post_id, $input_field_name, $action ) {
		require_once( ABSPATH . 'wp-admin/includes/admin.php' );
		$post_data = array();
		$override = array( 'test_form' => false, 'action' => $action );
		$attachment = media_handle_upload( $input_field_name, $post_id, $post_data, $override );

		return $attachment;
	}

	/**
	 * Used to generate terms dropdown
	 * 
	 * @param type $args
	 * @return string 
	 */
	function list_terms_dd( $args ) {
		$defaults = array(
			'show_option_all' => 1,
			'selected' => 0,
			'hide_empty' => false,
			'echo' => false,
			'include' => false,
			'hierarchical' => true,
			'select_label' => false,
			'show_label' => true
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		$excluded = false;
		if ( is_array( $selected ) )
			$selected = array_pop( $selected ); //in dd, we don't allow multipl evaues at themoment

		if ( ! empty( $include ) )
			$excluded = array_diff( ( array ) get_terms( $taxonomy, array( 'fields' => 'ids', 'get' => 'all' ) ), $include );
		$tax = get_taxonomy( $taxonomy );
		if ( $show_option_all ) {

			if ( ! $select_label )
				$show_option_all = sprintf( __( 'Select %s', 'bpsep' ), $tax->labels->singular_name );
			else
				$show_option_all = $select_label;
		}
		$always_echo = false;
		if ( empty( $name ) )
			$name = 'tax_input[' . $taxonomy . ']';


		$info = wp_dropdown_categories( array( 'taxonomy' => $taxonomy, 'hide_empty' => $hide_empty, 'name' => $name, 'id' => 'bp-simple-post-' . $taxonomy, 'selected' => $selected, 'show_option_all' => $show_option_all, 'echo' => false, 'excluded' => $excluded, 'hierarchical' => $hierarchical ) );
		$html = "<div class='simple-post-tax-wrap simple-post-tax-{$taxonomy}-wrap'>";
		if ( $show_label )
			$info = "<div class='simple-post-tax simple-post-tax-{$taxonomy}'><h3>{$tax->labels->singular_name}</h3>" . $info . "</div>";
		$html = $html . $info . "</div>";
		if ( $echo )
			echo $html;
		else
			return $html;
	}

	/*	 * *
	 * Some utility functions for template
	 */

	function has_custom_fields() {
		if ( ! empty( $this->custom_fields ) )
			return true;
		return false;
	}
}

//end of class

//API for general use
/**
 * Create and Register a New Form Instance, Please make sure to call it before bp_init action to make the form available to the controller logic
 * @param type $form_name:string, a unique name, It can contain letters or what ever eg. my_form or my form or My Form 123
 * @param type $settings:array,It governs what is shown in the form and how the form will be handled, possible values are
 *  array('post_type'=>'post'|'page'|valid_post_type,'post_status'=>'draft'|'publish'|'valid_post_status','show_categories'=>true|false,'current_user_can_post'=>true|false
 * @return BPSimpleBlogPostEditForm 
 */
function bp_new_simple_blog_post_form( $form_name, $settings ) {

	$form = new BPSimpleBlogPostEditForm( $form_name, $settings );
	$editor = BPSimpleBlogPostEditor::get_instance();
	$editor->register_form( $form );

	return $form;
}

//get a referenace to a particular form instance
function bp_get_simple_blog_post_form( $name ) {
	$editor = BPSimpleBlogPostEditor::get_instance();
	return $editor->get_form_by_name( $name );
}

//get a referenace to a particular form instance
function bp_get_simple_blog_post_form_by_id( $form_id ) {
	$editor = BPSimpleBlogPostEditor::get_instance();
	return $editor->get_form_by_id( $form_id );
}
