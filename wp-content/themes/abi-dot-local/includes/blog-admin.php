<?php

//handle everything except the front end display

class ABI_Group_Extension extends BP_Group_Extension {

	var $visibility = 'public'; // 'public' will show your extension to non-group members, 'private' means you have to be a member of the group to view your extension.
	var $enable_create_step = true; // enable create step
	var $enable_nav_item = false; //do not show in front end
	var $enable_edit_item = true; // If your extensi

	function __construct() {


		$this->name = __( 'Blog Options', 'buddyboss' );
		$this->slug = 'blog-options';

		$this->create_step_position = 21;
		$this->nav_item_position = 31;
	}

//on group crate step
	function create_screen($group_id = NULL) {
		if ( ! bp_is_group_creation_step( $this->slug ) )
			return false;
		abi_user_role_html();
		wp_nonce_field( 'groups_create_save_' . $this->slug );
	}

//on group create save
	function create_screen_save($group_id = NULL) {
		global $bp;

		check_admin_referer( 'groups_create_save_' . $this->slug );

		$group_id = $bp->groups->new_group_id;
		$groupblog_default_admin_role = esc_attr($_POST['default-administrator']);
		
		if ( empty( $groupblog_default_admin_role ) ) { 
			
			$groupblog_default_admin_role = 'subscriber';
			
		}
		
		groups_update_groupmeta ( $group_id, 'groupblog_default_admin_role', $groupblog_default_admin_role );
		
		
	}

	function edit_screen($group_id = NULL) {
		if ( ! bp_is_group_admin_screen( $this->slug ) )
			return false;
		?>

		<h2><?php echo esc_attr( $this->name ) ?></h2>

		<?php abi_user_role_html(); ?>

		<?php wp_nonce_field( 'groups_edit_save_' . $this->slug ); ?>
		<p><input type="submit" value="<?php _e( 'Save Changes', 'buddyboss' ) ?> &rarr;" id="save" name="save" /></p>
		<?php
	}

	function edit_screen_save($group_id = NULL) {

		if ( ! isset( $_POST[ 'save' ] ) )
			return false;

		check_admin_referer( 'groups_edit_save_' . $this->slug );

		abi_groupblog_edit_settings();
	}

	function display($group_id = NULL) {
		/* Use this function to display the actual content of your group extension when the nav item is selected */
	}

	function widget_display() {
		
	}

}

bp_register_group_extension( 'ABI_Group_Extension' );

function abi_user_role_html() {
	global $bp;
	?>

	<div id="groupblog-member-options">

		<h3><?php _e( 'Member Options', 'groupblog' ) ?></h3>

		<p><?php _e( 'Enable blog posting to allow adding of group members to the blog with the roles set below.', 'buddyboss' ); ?><br /><?php _e( 'When disabled, all members will temporarily be set to subscribers, disabling posting.', 'buddyboss' ); ?></p>

		<?php
		// Assign our default roles to variables.
		// If nothing has been saved in the groupmeta yet, then we assign our own defalt values.
		if ( ! ( $groupblog_default_admin_role = groups_get_groupmeta( $bp->groups->current_group->id, 'groupblog_default_admin_role' ) ) ) {
			$groupblog_default_admin_role = $bp->groupblog->default_admin_role;
		}
		?>

		<label><strong><?php _e( 'Default Administrator Role:', 'groupblog' ); ?></strong></label>
		<input type="radio" <?php checked( $groupblog_default_admin_role, 'author' ) ?> value="author" name="default-administrator" /><span>&nbsp;<?php _e( 'Author', 'buddyboss' ); ?>&nbsp;&nbsp;</span>
		<input type="radio" <?php checked( $groupblog_default_admin_role, 'contributor' ) ?> value="contributor" name="default-administrator" /><span>&nbsp;<?php _e( 'Contributor', 'buddyboss' ); ?>&nbsp;&nbsp;</span>
		<input type="radio" <?php checked( $groupblog_default_admin_role, 'subscriber' ) ?> value="subscriber" name="default-administrator" /><span>&nbsp;<?php _e( 'Subscriber', 'buddyboss' ); ?>&nbsp;&nbsp;</span>

		<div id="groupblog-member-roles">
			<label><strong><?php _e( 'A bit about WordPress member roles:', 'groupblog' ); ?></strong></label>
			<ul id="groupblog-members">
				<li><?php _e( 'Author', 'buddyboss' ); ?> - <?php _e( "Somebody who can publish and manage their own posts.", 'groupblog' ); ?></li>
				<li><?php _e( 'Contributor', 'buddyboss' ); ?> - <?php _e( "Somebody who can write and manage their posts but not publish posts.", 'groupblog' ); ?></li>
				<li><?php _e( 'Subscriber', 'buddyboss' ); ?> - <?php _e( "Somebody who can read comments/comment/receive news letters, etc.", 'groupblog' ); ?></li>
			</ul>
		</div>

	</div><?php
}

/**
 * abi_groupblog_edit_settings()
 *
 * Save the blog-settings accessible only by the group admin or mod.
 *
 */
function abi_groupblog_edit_settings() {
	global $bp;

	$group_id = bp_get_current_group_id();
	
	// Get the necessary settings out of the $_POST global so that we can use them to set up
	// the blog
	$settings = array(
		'default-administrator' => ''
	);

	foreach ( $settings as $setting => $val ) {
		if ( isset( $_POST[ $setting ] ) ) {
			$settings[ $setting ] = $_POST[ $setting ];
		}
	}

	if ( ! abi_groupblog_edit_base_settings( $settings[ 'default-administrator' ], $group_id ) ) {
		bp_core_add_message( __( 'There was an error creating your group blog, please try again.', 'buddyboss' ), 'error' );
	} else {
		bp_core_add_message( __( 'Group details were successfully updated.', 'buddyboss' ) );
	}
}

/**
 * groupblog_edit_base_settings()
 *
 * Updates the groupmeta with the blog_id, default roles and if it is enabled or not.
 * Initiating member permissions loop on save 
 */
function abi_groupblog_edit_base_settings( $groupblog_default_admin_role, $group_id ) {
	global $bp;

	$group_id = (int)$group_id;

	if ( empty( $group_id ) )
		return false;

	$default_role_array = array( 'groupblog_default_admin_role' => $groupblog_default_admin_role );
  	groups_update_groupmeta ( $group_id, 'groupblog_default_admin_role', $groupblog_default_admin_role );

	return true;
}
