<?php

class up546E_Custom_Field_Revisions {

	const DISPLAY_SINGLE_REVISION_FIELD = false;

	protected $settings;
	protected $revisioned_fields;
	protected $pre_post_meta;

	static private function log($message){
	//	error_log($message);
	}


	public function __construct( $settings ) {

		$this->settings = $settings;

		add_action( 'init', array($this,'init') );

	}

	/**
	 * Init hook
	 */
	public function init(){

		// Get the meta keys to keep revisions of with method from settings
		$this->setup_revisioned_fields();

		if(!$this->revisioned_fields)
		{
			self::log('No fields to revision. Aborting...');
			return;
		}

		// Setup hooks
		$this->init_hooks();

	}


	/**
	 * Decide which fields to keep revisions of
	 */
	public function setup_revisioned_fields() {
		self::log("setup_revisioned_fields");

		if( $this->settings->method == 'include_keys')
		{
			$this->revisioned_fields = $this->parse_keys_from_settings_include_keys($this->settings->include_keys);
		}
		if( $this->settings->method == 'exclude_keys')
		{
			$this->revisioned_fields = $this->parse_keys_from_settings_exclude_keys($this->settings->exclude_keys);
		}
		if( $this->settings->method == 'regexp')
		{
			$this->revisioned_fields = $this->parse_keys_from_settings_regexp($this->settings->regexp);
		}
	}

	/**
	 * Decide which fields to keep revisions of
	 */
	public function init_hooks() {

		self::log(' - - - REVISION META OPERATING- - -');

		//add_action('pre_post_update' , array($this, 'meta_before_autosave'));

		add_filter('wp_insert_post_data', array($this, 'catch_post_meta'), 99, 2);

		add_action('save_post' , array($this, 'save_post_meta_in_revision'), 10 , 2);

		add_action( 'wp_restore_post_revision', array($this,'restore_meta_revisions'), 10, 2 );

		if(self::DISPLAY_SINGLE_REVISION_FIELD)
		{
			add_filter( '_wp_post_revision_fields', array($this,'revision_fields_single') );
			add_filter( '_wp_post_revision_field_postmeta' . $key, array($this,'revision_field_single'), 10, 2 );
		}
		else
		{
			add_filter( '_wp_post_revision_fields', array($this,'revision_fields') );

			// Adding fields to _wp_post_revision_fields is ok but when they are filtered on revision screen WP throws a notice.
			// If revision.patch is applied to wp-admin/revision.php this filter is not needed...
			if($this->settings->patched_core != 1)
			{
				foreach ( $this->revisioned_fields as $key => $value ) {
					add_filter( '_wp_post_revision_field_' . $key, array(
							$this,
							'revision_field'
						), 10, 2 );
				}
			}
		}

		//add_filter('the_preview', array($this, 'the_preview') );
		

		// Only previews
		
		if ( isset( $_GET['preview_id'] ) ) {
			add_filter( 'get_post_metadata', array($this,'get_post_preview_metadata'), 10, 4 );
		}
		
	}


	/**
	 * Catches post meta from soon to be saved post
	 */
	public function catch_post_meta($data, $postarr) {

		if( isset($postarr['ID']) && (int) $postarr['ID'] > 0 ){
			$post_id = (int) $postarr['ID'];
			if($parent_id = wp_is_post_revision( $post_id ) )
			{
				$post_id = $parent_id;
			};
			self::log( '------------> Catched post meta from post: '.$post_id );
		}
		elseif( (int)$data['post_parent'] > 0 ){
			// Is autosave
			$post_id = $data['post_parent'];
			self::log( '------------> Catched post meta from autosave: '.$post_id );
		}
		else
		{
			self::log( '------------> COULD NOT CATCH POST META' );
			return $data;
		}

		
		// Save post meta for later use
		$this->pre_post_meta = get_post_meta( $post_id );

		// self::log( print_r($this->pre_post_meta, true));

		return $data;
	}

	/**
	 * Saves post meta in revisions
	 * @uses get_pre_saved_meta()
	 */
	public function save_post_meta_in_revision($post_id, $post) {

		global $metarevkey;

		$LOG_MESSAGES = array();

		if ( $parent_id = wp_is_post_revision( $post_id ) )
		{
			
			foreach ( $this->revisioned_fields as $key => $label ) {

				// Get meta from previous save
				$meta = $this->get_pre_saved_meta($key);

				// If no value then this could be the first revision. Try parent post meta...
				if(!$meta)
				{
					self::log( "No presaved meta for <$post_id>. Getting from parent...");
					self::log( "Presaved is: ".print_r($this->pre_post_meta, true));
					$meta = get_post_meta( $parent_id, $key, true );
				}

				if($meta)
				{
					if( wp_is_post_autosave( $post_id ) )
					{

						// Handle previews (switch the meta in autosave/original) 
						$original_meta 	= $this->get_pre_saved_meta( $key );
						$preview_meta 	= get_post_meta( $parent_id, $key, true );

						//update_post_meta( $parent_id, $key, $original_meta );

						// Put the original back
						update_metadata( 'post', $parent_id, $key, $original_meta);
						// Put the preview meta in the preview/autosave
						update_metadata( 'post', $post_id, $key, $preview_meta);

						$LOG_MESSAGES[] = "Preview meta Original: $original_meta and Preview: $preview_meta";

					}
					else
					{
						// Save it in this revision
						update_metadata( 'post', $post_id, $key, $meta);
						$LOG_MESSAGES[] = 'Revision meta :'.$meta;
					}
				}

			}


			self::log( 'Saved: '.  print_r($LOG_MESSAGES, true) );

		}
	}


	/**
	 * Restore post meta from revision
	 */
	function restore_meta_revisions( $post_id, $revision_id = FALSE ) {
		self::log("restore_meta_revisions");

		$post = get_post( $post_id );
		$revision = get_post( $revision_id );

		// Get post meta from revision and add it to post
		foreach ( $this->revisioned_fields as $key => $value ) {
			$post_meta = get_metadata( 'post', $revision->ID, $key, true );

			if ( false !== $post_meta ) {
				update_post_meta( $post_id, $key, $post_meta );
			}
			else {
				delete_post_meta( $post_id, $key );
			}
		}

	}


	/**
	 * Add the revisioned fields to the revisions screen
	 */
	function revision_fields( $fields ) {
		self::log("revision_fields: Add the revisioned fields to the revisions screen");

		if (!$this->revisioned_fields) return;

		foreach ( $this->revisioned_fields as $key => $value ) {

			$fields[$key] = $this->settings->rewrite_labels ? $this->meta_key_rewrite_label($value) : $value;
		}
		return $fields;
	}

	/**
	 * Add the field post_meta to the revisions screen
	 */
	function revision_fields_single( $fields ) {

		$fields['postmeta'] = 'Post Meta';
		return $fields;
		
	}
	



	/**
	 * Display the field value on revisions screen
	 */
	function revision_field( $value, $field ) {
		self::log("revision_field( $value, $field ): Display the field($field) value($value) on revisions screen");

		global $revision;

		$meta_value = get_metadata( 'post', $revision->ID, $field, true );
		if ( is_array( $meta_value ) ) {
			$display = serialize( $meta_value );
		}
		else {
			$display = (string)$meta_value;
		}
		return $display;

	}

	/**
	 * Display the field value on revisions screen
	 */
	function revision_field_single( $value, $field ) {
		
		global $revision;

		$meta_values = array();

		foreach ( $this->revisioned_fields as $key => $label ) {
			$meta_value = get_metadata( 'post', $revision->ID, $key, true );
			$meta_values[] = $meta_value;
		}

		return serialize( $meta_values );

	}


	/**
	 * Get correct post meta (from autosave) when post is previewed
	 */
	function get_post_preview_metadata( $null, $object_id, $meta_key, $single ) {
		self::log("get_post_preview_metadata( $null, $object_id, $meta_key, $single ): Get post meta from autosave when previewed");
		// Abort if meta_key is not revisioned
		if ( !isset($this->revisioned_fields[$meta_key]) ) return null;

		global $wpdb;

		$q = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s", "$object_id-autosave" );
		$autosave = $wpdb->get_row( $q );

		$q = $wpdb->prepare( "SELECT meta_id, meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC", $autosave->ID, $meta_key );
		$meta = $wpdb->get_row( $q );

		return array( maybe_unserialize( $meta->meta_value ) );

	}













	/**
	 --------------------------------------------------------------------
	Private
	 --------------------------------------------------------------------
	 */



	/**
	 * Returns values from pre saved post meta
	 */
	private function get_pre_saved_meta($key)
	{
		// TODO: Need to support multiple values per key?
		$meta = isset($this->pre_post_meta[$key][0]) ? $this->pre_post_meta[$key][0] : false;

		return $meta;
	}

	/**
	 * Make field labels human readable when autogenerated
	 */
	private function meta_key_rewrite_label($label) {
		return ucfirst( trim(str_replace('_', ' ', $label)) );
	}


	/**
	 * Get all available meta_keys
	 */
	private function get_current_meta_keys() {
		if(isset($_REQUEST['post_ID']))
		{
			$post_id = $_REQUEST['post_ID'];
		}
		elseif(isset($_REQUEST['post']))
		{
			$post_id = $_REQUEST['post'];
		}
		elseif(isset($_REQUEST['revision']))
		{
			$post_id = $_REQUEST['revision'];
		}
		elseif(isset($_REQUEST['preview_id']))
		{
			$post_id = $_REQUEST['preview_id'];
		}
		elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == 'diff' && isset($_REQUEST['right']))
		{
			$post_id = $_REQUEST['right'];
		}
		else
		{
		//	die('Can not find post ID for meta keys');
			return array();
		}

		// Get fields from original/published post
		if($parent_id = wp_is_post_revision( $post_id ))
		{
			$post_id = $parent_id;
		}

		self::log('POST ID: '.$post_id);
		$post_meta_keys = get_post_custom_keys( $post_id );

		return $post_meta_keys;
	}

	/**
	 * Get revisioned fields from explicit included keys in settings
	 */
	private function parse_keys_from_settings_include_keys($include_keys) {
		$revisioned_fields = $include_keys;

		return $revisioned_fields;
	}


	/**
	 * Get revisioned fields from explicit excluded keys in settings
	 */
	private function parse_keys_from_settings_exclude_keys($exclude_keys_setting) {

		// Turn settings string into array and remove keys holding empty values
		$exclude_keys = array_filter(explode("\n", $exclude_keys_setting));

		if(empty($exclude_keys)) return false;

		// Get list of meta_keys for current post
		$post_meta_keys = $this->get_current_meta_keys();

		if(!$post_meta_keys) return false;

		// Populate our array with fields to revision
		$revisioned_fields = array();
		foreach($post_meta_keys as $key)
		{
			if( !in_array($key, $exclude_keys) )
			{
				$revisioned_fields[$key] = $key;
			}
		}
		return $revisioned_fields;
	}

	/**
	 * Get revisioned fields from post filtered by reg exp in settings
	 */
	private function parse_keys_from_settings_regexp($regexp) {
		if(empty($regexp)) return false;

		$post_meta_keys = $this->get_current_meta_keys();

		if(!$post_meta_keys) return false;

		$revisioned_fields = array();
		foreach($post_meta_keys as $key)
		{
			if( preg_match($regexp, $key) )
			{
				$revisioned_fields[$key] = $key;
			}
		}
		return $revisioned_fields;
	}


	/**
	 * Get meta value from post before saved by preview
	 */
	private function get_meta_before_preview( $post_id, $key ) {
		$revisions = wp_get_post_revisions( $post_id, array( 'order' => 'ASC' ) );

		// Remove autosave
		array_pop( $revisions );

		// Use the latest
		$latest_revision = array_pop( $revisions );

		$post_meta = get_metadata( 'post', $latest_revision->ID, $key, true );
		return $post_meta;
	}

}
