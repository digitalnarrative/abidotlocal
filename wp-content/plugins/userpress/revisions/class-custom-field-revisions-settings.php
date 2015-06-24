<?php


/*
This code is based on an separate plugin. 
The settings page functionality is no longer needed and should be deleted (making sure not to break needed functionality).


*/

class up546E_UserPress_Revision_Settings {

	const PLUGIN_NAME = 'Custom Field Revisions';
	const PLUGIN_DISPLAY_NAME = 'Custom Field Revisions';
	const PLUGIN_SLUG = 'custom-field-revisions';
	const PLUGIN_OPTION_GROUP = 'Custom_Field_Revisions_options';
	const PLUGIN_OPTIONS_PAGE = 'settings-custom-field-revisions';

	public $settings;

	public function __construct() {

		$this->method = get_option( 'Custom_Field_Revisions_method' );
		$this->include_keys = self::parse_fields_from_option( get_option( 'Custom_Field_Revisions_include_keys' ) );
		$this->exclude_keys = get_option( 'Custom_Field_Revisions_exclude_keys' );
		$this->regexp = get_option( 'Custom_Field_Revisions_regexp' );

		$this->rewrite_labels = get_option( 'Custom_Field_Revisions_rewrite_labels' );

		$this->patched_core = get_option( 'Custom_Field_Revisions_patched_core' );


		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'settings_api_init' ) );
//		add_action( 'admin_menu', array( $this, 'add_options_page' ) );

	}

	static function admin_enqueue_scripts( ) {
		$screen = get_current_screen();

		// Abort if not on our settings screen
		if($screen->base != 'settings_page_options-'.self::PLUGIN_SLUG) return;

		wp_enqueue_script( 'custom-field-revisions-settings', plugins_url( '/assets/js/settings.js', __FILE__ ), 'jquery', '1.0.0' );
		wp_enqueue_style('custom-field-revisions-settings', plugins_url('/assets/css/settings.css', __FILE__ ), FALSE, '1.0.0');
	}

	static function parse_fields_from_option( $option ) {
		if ( trim( $option ) == '' ) return array();
		// defaults
		$option = isset( $option ) ? $option : '';

		// vars
		$fields = array();

		// explode choices from each line
		if ( strpos( $option, "\n" ) !== false ) {
			// found multiple lines, explode it
			$option = explode( "\n", $option );
		}
		else {
			// no multiple lines!
			$option = array( $option );
		}

		// Make key => value
		foreach ( $option as $choice ) {
			if ( strpos( $choice, ':' ) !== false ) {
				$choice = explode( ':', $choice );
				$fields[trim( $choice[0] )] = trim( $choice[1] );
			}
			else {
				$fields[trim( $choice )] = trim( $choice );
			}
		}


		// return fields array
		return $fields;

	}

	function add_options_page() {
		add_options_page( self::PLUGIN_DISPLAY_NAME, self::PLUGIN_DISPLAY_NAME, 'manage_options', 'options-' . self::PLUGIN_SLUG, array(
				$this,
				'options_page'
			) );
	}


	function options_page() {
		require 'custom-field-revisions-settings-page.php';
	}


	public function settings_api_init() {

		add_settings_section( 'Custom_Field_Revisions_common_setting_section', 'Common', array(
				$this,
				'common_setting_section_callback_function'
			), self::PLUGIN_OPTIONS_PAGE );

		// Fields
		add_settings_field( 'Custom_Field_Revisions_method', 'Method of selecting fields to revision', array(
				$this,
				'method_callback'
			), self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_common_setting_section' );

		add_settings_field( 'Custom_Field_Revisions_include_keys', 'Keep revisions for these fields', array(
				$this,
				'include_keys_callback'
			), self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_common_setting_section' );

		add_settings_field( 'Custom_Field_Revisions_exclude_keys', 'Keep revisions for all fields but these', array(
				$this,
				'exclude_keys_callback'
			), self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_common_setting_section' );


		add_settings_field( 'Custom_Field_Revisions_regexp', 'Keep revisions for fields matching', array(
				$this,
				'regexp_callback'
			), self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_common_setting_section' );



		add_settings_section( 'Custom_Field_Revisions_edit_post_section', 'Edit post', array(
				$this,
				'edit_post_section_callback_function'
			), self::PLUGIN_OPTIONS_PAGE );

		add_settings_field( 'Custom_Field_Revisions_rewrite_labels', 'Field labels', array(
				$this,
				'rewrite_labels_callback'
			), self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_edit_post_section' );



		add_settings_section( 'Custom_Field_Revisions_advanced_section', 'Advanced', array(
				$this,
				'advanced_section_callback_function'
			), self::PLUGIN_OPTIONS_PAGE );

		add_settings_field( 'Custom_Field_Revisions_patched_core', 'Using core hack', array(
				$this,
				'patched_core_callback'
			), self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_advanced_section' );

		// Register our setting so that $_POST handling is done for us and
		// our callback function just has to echo the <input>
		register_setting( self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_method' );

		register_setting( self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_include_keys', array( $this, 'include_keys_sanitize_callback' ) );
		register_setting( self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_exclude_keys', array( $this, 'exclude_keys_sanitize_callback' ) );
		register_setting( self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_regexp', array( $this, 'regexp_sanitize_callback' ) );

		register_setting( self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_rewrite_labels' );
		register_setting( self::PLUGIN_OPTIONS_PAGE, 'Custom_Field_Revisions_patched_core' );


	}



	function advanced_section_callback_function() {
		echo '<p class="intro">These are totally optional and ONLY for advanced users</p>';
	}
	function common_setting_section_callback_function() {
		echo '<p class="intro">Define which fields the plugin should process</p>';
	}
	function edit_post_section_callback_function() {
		echo '<p class="intro">Set up how you want the plugin to behave for the user</p>';
	}

	function method_callback() {

		$option = get_option( 'Custom_Field_Revisions_method' );

		$html = '<input type="radio" id="method_include_keys" name="Custom_Field_Revisions_method" value="include_keys"' . checked( 'include_keys', $option, false ) . '/> ';
		$html .= '<label for="method_include_keys">Enter keys to include</label>';
		$html .= '<br />';
		$html .= '<input type="radio" id="method_exclude_keys" name="Custom_Field_Revisions_method" value="exclude_keys"' . checked( 'exclude_keys', $option, false ) . '/> ';
		$html .= '<label for="method_exclude_keys">Enter keys to exclude</label>';
		$html .= '<br />';
		$html .= '<input type="radio" id="method_regexp" name="Custom_Field_Revisions_method" value="regexp"' . checked( 'regexp', $option, false ) . '/> ';
		$html .= '<label for="method_regexp">Regular expression</label>';

		echo $html;

	} // end sandbox_radio_element_callback




	function patched_core_callback() {
		echo '<label for="Custom_Field_Revisions_patched_core"><input name="Custom_Field_Revisions_patched_core" id="Custom_Field_Revisions_patched_core" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'Custom_Field_Revisions_patched_core' ), false ) . ' /> I have applied the patch in the plugin folder <strong>assets</strong> to the WP core file</label><br />';
		echo '<p class="description">If you check this the plugin will not work correctly unless the patch has been applied. Read more about this <a href="http://wordpress.org/extend/plugins/custom-field-revisions/installation/">on the plugin installation page</a></p>';
	}

	function rewrite_labels_callback() {
		echo '<label for="Custom_Field_Revisions_rewrite_labels"><input name="Custom_Field_Revisions_rewrite_labels" id="Custom_Field_Revisions_rewrite_labels" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'Custom_Field_Revisions_rewrite_labels' ), false ) . ' /> Rewrite labels on revision screen if not present</label><br />';
		echo '<p class="description">Rewrites field names on revisions screen to a more readable format. <strong>_field_key</strong> becomes <strong>Field key</strong></p>';
	}

	function active_callback() {
		echo '<label for="Custom_Field_Revisions_method"><input name="Custom_Field_Revisions_method" id="Custom_Field_Revisions_method" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'Custom_Field_Revisions_method' ), false ) . ' /> Keep revisions of these fields</label><br />';
	}

	function include_keys_sanitize_callback( $value ) {
		
		if(get_option( 'Custom_Field_Revisions_method' ) != 'include_keys') return $value;

		if ( count( self::parse_fields_from_option( $value ) ) <= 0 ) {
			add_settings_error(
				'Custom_Field_Revisions_include_keys', // setting title
				self::PLUGIN_OPTIONS_PAGE . '_txt_include_keys', // error ID
				__( 'You need to specify fields.', 'wptuts_textdomain' ), // error message
				'error' // type of message
			);
		}
		return $value;
	}


	function exclude_keys_sanitize_callback( $value ) {
		
		if(get_option( 'Custom_Field_Revisions_method' ) != 'exclude_keys') return $value;

		$keys = array_filter(explode("\n", $value));
		
		if ( count( $keys ) <= 0 ) {
			add_settings_error(
				'Custom_Field_Revisions_exclude_keys', // setting title
				self::PLUGIN_OPTIONS_PAGE . '_txt_exclude_keys', // error ID
				__( 'You need to specify fields to exclude.', 'wptuts_textdomain' ), // error message
				'error' // type of message
			);
		}
		return $value;
	}

	function regexp_sanitize_callback( $value ) {
		if(get_option( 'Custom_Field_Revisions_method' ) != 'regexp') return $value;

		if ( @preg_match($value, "") === false ) {
			add_settings_error(
				'Custom_Field_Revisions_regexp', // setting title
				self::PLUGIN_OPTIONS_PAGE . '_txt_regexp', // error ID
				__( 'The regular expression you entered is not valid.', 'wptuts_textdomain' ), // error message
				'error' // type of message
			);
		}
		return $value;
	}

	function include_keys_callback() {
		echo '<label for="Custom_Field_Revisions_include_keys">Enter one key per line.</label><br />';
		echo '<textarea rows="12" type="textarea" style="width: 100%" id="Custom_Field_Revisions_include_keys" name="Custom_Field_Revisions_include_keys">' . get_option( 'Custom_Field_Revisions_include_keys' ) . '</textarea>';
		echo '<p class="description">Optionally format every line with: <strong>meta_key : Field name</strong>. Field name is then used on the revision screen and the option "Rewrite labels..." below should not be active.</p>';
	}

	function exclude_keys_callback() {
		echo '<label for="Custom_Field_Revisions_exclude_keys">Enter one key per line.</label><br />';
		echo '<textarea rows="12" type="textarea" style="width: 100%" id="Custom_Field_Revisions_exclude_keys" name="Custom_Field_Revisions_exclude_keys">' . get_option( 'Custom_Field_Revisions_exclude_keys' ) . '</textarea>';
	}

	function regexp_callback() {
		echo '<label for="Custom_Field_Revisions_regexp">Enter regular expression.</label><br />';
		echo '<input type="text" style="width: 100%" id="Custom_Field_Revisions_regexp" name="Custom_Field_Revisions_regexp" value="'.get_option( 'Custom_Field_Revisions_regexp' ).'">';
		echo '<p class="description">Uses <a href="http://php.net/manual/en/function.preg-match.php">preg_match()</a>. The string <strong>/^_rev_meta/</strong> will match all meta_keys starting with <strong>_rev_meta</strong>.</p>';
	}


}
