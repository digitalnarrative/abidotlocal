<?php

include_once 'licence-manager.php';

/**
 * Settings screen for Licence activation/deactivation
 *
 * @since 3.0
 */
if ( ! class_exists( 'Codepress_Licence_Manager_Settings' ) ) {

	/**
	 * @since 3.0
	 */
	class Codepress_Licence_Manager_Settings extends Codepress_Licence_Manager {

		public $cpac;

		/**
		 * @since 1.0
		 * @param array $args Arguments; This must contain: api_url, option_key, version, file, secret_key, product_name
		 */
		function __construct( $file_path, $cpac ) {

			parent::__construct( $file_path );

			$this->cpac = $cpac;

			// Add UI
			add_filter( 'cac/settings/groups', array( $this, 'settings_group' ) );
			add_action( 'cac/settings/groups/row=addons', array( $this, 'display' ) );

			// licence Requests
			add_action( 'admin_init', array( $this, 'handle_request' ) );

			// Hook into the plugin install process, inject addon download url
			add_action( 'plugins_api', array( $this, 'inject_addon_install_resource' ), 10, 3 );

			// Do check before installing add-on
			add_filter( 'cac/addons/install_request/maybe_error', array( $this, 'maybe_install_error' ), 10, 2 );

			// Add notifications to the plugin screen
			add_action( 'after_plugin_row_' . $this->basename, array( $this, 'display_plugin_row_notices' ), 11 );

			// Adds notice to update message that a licence is needed
			add_action( 'in_plugin_update_message-' . $this->basename, array( $this, 'need_license_message' ), 10, 2 );

			// add scripts, after settings page is set.
			add_action( 'admin_menu', array( $this, 'scripts' ), 20 ); // low prio, after $settings_page has been set by CPAC_Settings.

			// check for a secure connection
			add_action( 'wp_ajax_cpac_check_connection', array( $this, 'ajax_check_connection' ) );
		}

		/**
		 * @since 3.1.2
		 */
		public function ajax_check_connection() {
			echo $this->api->test_request( $this->basename ) ? '1' : '0';
			exit;
		}

		/**
		 * @since 3.1.2
		 */
		public function scripts() {
			$settings_page = $this->cpac->settings()->get_settings_page();
			add_action( "admin_print_scripts-" . $settings_page, array( $this, 'admin_scripts' ) );
		}

		/**
		 * @since 3.1.2
		 */
		public function admin_scripts() {
			wp_enqueue_script( 'cac-addon-pro', CAC_PRO_URL . "assets/js/cac-addon-pro.js", array( 'jquery' ), CAC_PRO_VERSION );

		}

		/**
		 * @since 1.0
		 */
		public function maybe_install_error( $error, $plugin_name ) {

			if ( ! $this->get_licence_status() ) {
				$error = sprintf( __( "Licence not active. Enter your licence key under the <a href='%s'>Settings tab</a>.", 'cpac' ), $this->cpac->settings()->get_settings_url('settings') );
			}

			$install_data = $this->get_plugin_install_data( $plugin_name, $clear_cache = true ); // get remote add-on info

			if ( is_wp_error( $install_data ) ) {
				$error = $install_data->get_error_message();
			}

			return $error;
		}

		/**
		 * @since 1.2
		 */
		public function get_available_addons() {
			return $this->cpac->addons()->get_available_addons();
		}

		/**
		 * Get addons data for the update process
		 *
		 * @since 1.0.0
		 */
		public function get_addons_update_data() {
			$addons_update_data = array();

			$addons = $this->cpac->addons()->get_available_addons();
			foreach ( $addons as $plugin => $data ) {
				$basename = $this->cpac->addons()->get_installed_addon_plugin_basename( $plugin );
				$version = $this->cpac->addons()->get_installed_addon_plugin_version( $plugin );

				if ( ! $basename ) {
					continue;
				}

				$addons_update_data[] = array(
					'plugin' => $basename,
					'version' => $version
				);
			}

			return $addons_update_data;
		}

		/**
		 * Add addons to install process, not the update process.
		 *
		 * @since 1.0
		 */
		public function inject_addon_install_resource( $result, $action, $args ) {

			if ( 'plugin_information' != $action || empty( $args->slug ) ) {
				return $result;
			}

			$addons = $this->cpac->addons()->get_available_addons();

			if ( ! isset( $addons[ $args->slug ] ) ) {
				return $result;
			}

			$install_data = $this->get_plugin_install_data( $args->slug, true );

			if ( ! $install_data ) {
				return $result;
			}

			return $install_data;
		}

	    /**
		 * Handle requests for license activation and deactivation
		 *
		 * @since 1.0
		 */
		public function handle_request() {

			// Activation
			if ( isset( $_POST['_wpnonce_addon_activate'] ) && wp_verify_nonce( $_POST['_wpnonce_addon_activate'], $this->option_key ) ) {

				$licence_key = isset( $_POST[ $this->option_key ] ) ? sanitize_text_field( $_POST[ $this->option_key ] ) : '';

				if ( empty( $licence_key ) ) {
					cpac_admin_message( __( 'Empty licence.', 'cpac' ), 'error' );
					return;
				}

				$response = $this->activate_licence( $licence_key );

				if ( is_wp_error( $response ) ) {
					cpac_admin_message( __( 'Wrong response from API.', 'cpac' ) . ' ' . $response->get_error_message(), 'error' );
				}
				elseif ( isset( $response->activated ) ) {
					cpac_admin_message( $response->message, 'updated' );
				}
				else {
					cpac_admin_message( __( 'Wrong response from API.', 'cpac' ), 'error' );
				}
			}

			// Deactivation
			if ( isset( $_POST['_wpnonce_addon_deactivate'] ) && wp_verify_nonce( $_POST['_wpnonce_addon_deactivate'], $this->option_key ) ) {

				$response = $this->deactivate_licence();

				if ( is_wp_error( $response ) ) {
					cpac_admin_message( __( 'Wrong response from API.', 'cpac' ) . ' ' . $response->get_error_message(), 'error' );
				}
				elseif ( isset( $response->deactivated ) ) {
					cpac_admin_message( $response->message, 'updated' );
				}
				else {
					cpac_admin_message( __( 'Wrong response from API.', 'cpac' ), 'error' );
				}
			}

			// Toggle SSL
			if ( isset( $_POST['_wpnonce_addon_toggle_ssl'] ) && wp_verify_nonce( $_POST['_wpnonce_addon_toggle_ssl'], $this->option_key ) ) {

				// disable ssl
				if ( '0' == $_POST['ssl'] ) {
					$this->disable_ssl();
				}
				else {
					$this->enable_ssl();
				}
			}

		}

		/**
		 * Add settings group to Admin Columns settings page
		 *
		 * @since 1.0
		 * @param array $groups Add group to ACP settings screen
		 * @return array Settings group for ACP
		 */
		public function settings_group( $groups ) {

			if ( isset( $groups['addons'] ) ) {
				return $groups;
			}

			$groups['addons'] =  array(
				'title'			=> __( 'Updates', 'cpac' ),
				'description'	=> __( 'Enter your licence code to receive automatic updates.', 'cpac' )
			);

			return $groups;
		}

		/**
		 * Display licence field
		 *
		 * @since 1.0
		 * @return void
		 */
		public function display() {

			// Display message on multisite
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( ACP_FILE ) ) && ! is_main_site() ) {
				$settings_url = get_admin_url( get_current_site()->blog_id, 'options-general.php?page=codepress-admin-columns&tab=settings' )
				?>
				<p>
					<?php _e( 'This plugin has been network activated.', 'cpac' ); ?>
				<?php if ( current_user_can( 'manage_network_options' ) ) : ?>
					<?php printf( __( 'Go to <a href="%s">network settings</a>.', 'cpac' ), $settings_url ); ?>
				<?php endif; ?>
				</p>
				<?php
				return;
			}

			// Use this hook when you want to hide to licence form
			if ( ! apply_filters( 'cac/display_licence/addon=' . $this->option_key , true ) ) {
				return;
			}

			$licence = $this->get_licence_key();
			$status  = $this->get_licence_status();

			// on submit
			if ( ! empty( $_POST[ $this->option_key ] ) ) {
				$licence = $_POST[ $this->option_key ];
			}

			?>

			<form id="licence_activation" action="" method="post">
				<label for="<?php echo $this->option_key; ?>">
					<strong><?php echo $this->get_name(); ?></strong>
				</label>
				<br/>

			<?php if ( $status ) : ?>

				<?php wp_nonce_field( $this->option_key, '_wpnonce_addon_deactivate' ); ?>
				<p>
					<span class="icon-yes"></span>
					<?php _e( 'Automatic updates are enabled.', 'cpac' ); ?> <?php //echo $this->get_masked_licence_key(); ?>
					<input type="submit" class="button" value="<?php _e( 'Deactivate licence', 'cpac' ); ?>" >
				</p>

			<?php else : ?>

				<?php wp_nonce_field( $this->option_key, '_wpnonce_addon_activate' ); ?>

				<input type="password" value="<?php echo $licence; ?>" id="<?php echo $this->option_key; ?>" name="<?php echo $this->option_key; ?>" size="30" placeholder="<?php _e( 'Enter your licence code', 'cpac' ) ?>" >
				<input type="submit" class="button" value="<?php _e( 'Update licence', 'cpac' ); ?>" >
				<p class="description">
					<?php _e( 'Enter your licence code to receive automatic updates.', 'cpac' ); ?><br/>
					<?php printf( __( 'You can find your license key on your %s.', 'cpac' ), '<a href="https://admincolumns.com/my-account" target="_blank">' . __( 'account page', 'cpac' ) . '</a>' ); ?>
				</p>

			<?php endif; ?>

			</form>

			<form id="toggle_ssl" action="" method="post" style="display:none; background: white;">
				<?php wp_nonce_field( $this->option_key, '_wpnonce_addon_toggle_ssl' ); ?>

				<p style="padding: 20px;">
					<?php _e( 'Could not connect to admincolumns.com — You will not receive update notifications or be able to activate your license until this is fixed. This issue is often caused by an improperly configured SSL server (https). We recommend fixing the SSL configuration on your server, but if you need a quick fix you can:', 'cpac' ); ?>
					<br/><br/>

			<?php if ( $this->is_ssl_enabled() ) : ?>
				<input type="hidden" name="ssl" value="0" >
				<input type="submit" class="button" value="<?php _e( 'Disable SSL', 'cpac' ); ?>" >
			<?php else : ?>
				<input type="hidden" name="ssl" value="1" >
				<input type="submit" class="button" value="<?php _e( 'Enable SSL', 'cpac' ); ?>" >
			<?php endif; ?>
				</p>
			</form>
			<?php
		}

		/**
		 * Shows a message below the plugin on the plugins page
		 *
		 * @since 1.0.3
		 */
		public function display_plugin_row_notices() {

			if ( $this->get_licence_status() ) {
				return;
			}

			$plugin_details = $this->get_plugin_details();

			$message = __( 'To finish activating Admin Columns Pro, please ', 'cpac' );
			if ( isset( $plugin_details->version ) && version_compare( $this->get_version(), $plugin_details->version, '<' ) ) {
				$message = __( 'To update, ', 'cpac' );
			}

			// multisite
			if ( is_network_admin() ) {
				$message .= sprintf( __( 'go to %s and enter your licence key. If you don\'t have a licence key, you may <a href="%s" target="_blank">purchase one</a>.', 'cpac' ), sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=codepress-admin-columns&tab=settings' ), __( 'each subsite', 'cpac' ) ), 'https://www.admincolumns.com/' );
			}

			// single or subsite
			else {
				$message .= sprintf( __( 'go to %s and enter your licence key. If you don\'t have a licence key, you may <a href="%s" target="_blank">purchase one</a>.', 'cpac' ), sprintf( '<a href="%s">%s</a>', network_admin_url( 'options-general.php?page=codepress-admin-columns&tab=settings' ), __( 'Settings', 'cpac' ) ), 'https://www.admincolumns.com/' );
			}
			?>
			<tr class="plugin-update-tr">
				<td colspan="3" class="plugin-update cac-plugin-update">
					<div class="update-message">

						<?php echo $message; ?>

						<style type="text/css">
						.plugin-update-tr .cac-plugin-update {
							border-left: 4px solid #2EA2CC;
						}
						.plugin-update-tr .cac-plugin-update .update-message {
							margin-top: 6px;
						}
						.plugin-update-tr .cac-plugin-update .update-message:before {
							content: "\f348";
						}
						</style>
					</div>
				</td>
			</tr>
			<?php
		}

		/**
		 * Message displayed on plugin page if license not activated
		 *
		 * @param  array $plugin_data
		 * @param  object $r
		 * @return void
		 */
		public function need_license_message ( $plugin_data, $r ) {
			if ( empty( $r->package ) ) {
				printf( ' ' . __( "To enable updates for this product, please <a href='%s'>activate your license</a>.", 'cpac' ), $this->cpac->settings()->get_settings_url('settings') );
			}
		}
	}
}