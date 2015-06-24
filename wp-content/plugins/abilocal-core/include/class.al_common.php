<?php

class al_common {

    private $main_includes = array(
        'class.al_group_cover',
    );

    function __construct() {
        $this->hooks();
    }
    
    function hooks() {
        add_action('bp_loaded', array($this, 'bp_loaded'));
        add_action( 'groups_custom_group_fields_editable', array( $this, 'edit_group_fields' ), 9 );
        add_action( 'groups_group_details_edited', array( $this, 'edit_group_fields_save' ), 10, 1 );
        add_action( 'bp_actions', array( $this, 'delete_group_cover_photo'), 20 );
    }

    function edit_group_fields(){
        if( !bp_is_active('groups') || !current_user_can('manage_options') ) return;
        global $bp;
        $uploaded_cover = groups_get_groupmeta( $bp->groups->current_group->id, '_group_cover_photo' );
    ?>
        <label class="" for="group_cover_photo"><?php _e('Group cover photo', 'al_text'); ?></label>
        <input type="file" name="group_cover_photo" id="group-cover-photo" />
        <?php if( isset($uploaded_cover['name']) && !empty($uploaded_cover['name']) ): ?>
        <span class="uploaded-cover-file"><?php echo $uploaded_cover['name'];?> &nbsp; <input name="delete_group_cover" id="delete_group_cover" type="submit" value="Delete" /></span>
        <?php endif; ?>
        <input type="hidden" name="action" id="group-cover-upload" value="group_cover_upload" />

    <?php
    }

    function delete_group_cover_photo(){
        if( !bp_is_active('groups') || !current_user_can('manage_options') ) return;
        if ( ! bp_is_groups_component() ) return;

        global $bp;
        $group_id = $bp->groups->current_group->id;

        if ( isset( $_POST['delete_group_cover'] ) && 'Delete' === $_POST['delete_group_cover'] ) {
            $uploaded_cover = groups_get_groupmeta( $group_id, '_group_cover_photo' );
            // delete group meta
            if( isset($uploaded_cover) && !empty($uploaded_cover) ){
                groups_delete_groupmeta( $group_id, '_group_cover_photo' );
            }
            // delete image
            if ( isset( $uploaded_cover['file'] ) && file_exists( $uploaded_cover['file'] ) ) {
                unlink( $uploaded_cover['file'] );
            }
        }

    }

    function edit_group_fields_save($group_id){
        if( !bp_is_active('groups') || !current_user_can('manage_options') ) return;
        global $bp;
        $redirect = trailingslashit( bp_get_group_permalink( $bp->groups->current_group ) .  'admin/edit-details' );

        if ( isset( $_POST['action'] ) && 'group_cover_upload' === $_POST['action'] && ! empty( $_FILES['group_cover_photo']['name'] ) ) {
            // Let's get ready to upload a new custom attachment
            $attachment = new al_group_cover();

            /**
             * Everything is in place to upload the file
             * @see Custom_Attachment->__construct()
             *
             * - custom errors > eg : only upload file containing custom in their name,
             * - max upload file > eg: 512000,
             * - location in /wp-content/uploads > eg: '/wp-content/uploads/custom',
             * - allowed mime types > eg: array( 'png', 'jpg' )
             */
            $file = $attachment->upload( $_FILES );

            // Display the error and do not send the message
            if ( ! empty( $file['error'] ) ) {
                bp_core_add_message( $file['error'], 'error' );
                bp_core_redirect( $redirect );

                // The file was successfully uploaded!!
            } else {
                /**
                 * Globalize the file array
                 *
                 * the file array returned is array(
                 * 	'file' => 'path_to_the_file',
                 * 	'url'  => 'url_to_the_file',
                 * 	'type' => 'file mime type'
                 * )
                 */
                $file['name'] = basename($file['url']);

                // Save a new message meta
                if ( ! empty( $group_id ) && ! empty( $file['file'] ) && file_exists( $file['file'] ) ) {
                    $uploaded_cover = groups_get_groupmeta( $group_id, '_group_cover_photo' );
                    // delete group meta
                    if( isset($uploaded_cover) && !empty($uploaded_cover) ){
                        groups_delete_groupmeta( $group_id, '_group_cover_photo' );
                    }
                    // delete image
                    if ( isset( $uploaded_cover['file'] ) && file_exists( $uploaded_cover['file'] ) ) {
                        unlink( $uploaded_cover['file'] );
                    }
                    groups_add_groupmeta( $group_id, '_group_cover_photo', $file );
                }

            }
        }
    }

    function custom_file_for_messages_delete_file() {
        $bp = buddypress();

        // The message was not sent due to an error, simply remove the file
        if ( isset( $bp->messages->attachment['file'] ) && file_exists( $bp->messages->attachment['file'] ) ) {
            unlink( $bp->messages->attachment['file'] );
        }
    }

    public function bp_loaded(){
        global $bp;
        $this->load_main();
    }

    private function load_main() {
        $this->do_includes($this->main_includes);
    }

    public function do_includes($includes = array()) {
        foreach ((array) $includes as $include) {
            require_once( abilocal()->includes_dir . '/' . $include . '.php' );
        }
    }


}
