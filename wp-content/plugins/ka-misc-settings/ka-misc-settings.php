<?php
/**
 * Plugin Name: Kemarise - Miscellaneous Settings
 * Description: Miscellaneous Settings
 * Author:      Kemarise
 * Author URI:  http://kemarise.com
 * Version:     0.1
 */
// Exit if accessed directly
if ( !defined('ABSPATH') )
	exit;

if ( !class_exists('KA_Misc_Settings_Admin') ):

	class KA_Misc_Settings_Admin {

		public $options = array();
		private $network_activated = false,
			$plugin_slug = 'ka_misc_settings',
			$menu_hook = 'admin_menu',
			$settings_page = 'options-general.php',
			$capability = 'manage_options',
			$form_action = 'options.php',
			$plugin_settings_url;

		public function __construct() {
			// ... leave empty, see Singleton below
		}

		public static function instance() {
			static $instance = null;

			if ( null === $instance ) {
				$instance = new KA_Misc_Settings_Admin();
				$instance->setup();
			}

			return $instance;
		}

		public function option( $key ) {
			if ( !$this->options ) {
				$this->options = get_option('ka_misc_settings');
			}
			$value = isset($this->options[$key]) ? $this->options[$key] : false;
			return apply_filters( 'ka_misc_option_' . $key, $value );
		}

		public function setup() {
			if ( (!is_admin() && !is_network_admin() ) || !current_user_can('manage_options') ) {
				return;
			}

			$this->plugin_settings_url = admin_url('options-general.php?page=' . $this->plugin_slug);

			add_action('admin_init', array( $this, 'admin_init' ));
			add_action($this->menu_hook, array( $this, 'admin_menu' ));
		}

		public function admin_init() {
			register_setting('ka_misc_settings', 'ka_misc_settings', array( $this, 'plugin_options_validate' ));
			add_settings_section('general_section', __('', 'TEXTDOMAIN'), array( $this, 'section_general' ), __FILE__);
			//add_settings_section( 'style_section', 'Style Settings', array( $this, 'section_style' ), __FILE__ );
			//general options
			add_settings_field('page_add_resource', __('Add Resource Page', 'TEXTDOMAIN'), array( $this, 'page_add_resource' ), __FILE__, 'general_section');
		}

		public function admin_menu() {
			add_submenu_page(
				$this->settings_page, 'Misc Settings', 'Misc Settings', $this->capability, $this->plugin_slug, array( $this, 'options_page' )
			);
		}

		public function options_page() {
			?>
			<div class="wrap">
				<h2><?php _e('Miscellaneous Settings', 'TEXTDOMAIN'); ?></h2>

				<div class="content-wrapper clearfix">
					<div class="settings">
						<div class="padder">
							<form method="post" action="<?php echo $this->form_action; ?>">

								<?php settings_fields('ka_misc_settings'); ?>
								<?php do_settings_sections(__FILE__); ?>

								<p class="submit">
									<input name="ka_misc_settings_submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
								</p>
							</form>
						</div>
					</div>
					<div style="clear: both"></div>
				</div>
			</div>
			<?php
		}

		public function section_general() {
			
		}
		

		public function wp_dropdown_pages( $args ){
			$echo = isset( $args['echo'] ) ? $args['echo'] : true;
			$args['echo'] = false;
			
			$html = wp_dropdown_pages($args);
			$html .= "<a class='button button-secondary button-small' href='" . admin_url( 'post-new.php?post_type=page' ) . "'>" . __( 'New Page', 'TEXTDOMAIN' ) . "</a>";
			
			if( $echo )
				echo $html;
			else
				return $html;
		}
		
		public function plugin_options_validate( $input ) {

			return $input; // return validated input
		}

		public function page_add_resource() {
			$page = $this->option('page_add_resource');
			$this->wp_dropdown_pages( array( 'selected'=>$page, 'name'=>'ka_misc_settings[page_add_resource]' ) );
			echo "<p class='description'>WordPress page which has [NEW_SOCIAL_RESOURCE] shortcode</p>";
		}
		
	}

	endif;

add_action('plugins_loaded', 'ka_misc_settings_init');

function ka_misc_settings_init() {
	KA_Misc_Settings_Admin::instance();
}

function ka_misc_settings( $option ){
	$obj = KA_Misc_Settings_Admin::instance();
	return $obj->option( $option );
}