<?php
function social_articles_page() {?>
    <div class="wrap options-social-resources"><?php
        global $socialArticles;
        $options = get_option('social_articles_options');

        if (isset($_POST['form_submit'])) {
            $options['post_per_page']      = isset($_POST['post_per_page']) ? $_POST['post_per_page'] : '';
            $options['excerpt_length']     = isset($_POST['excerpt_length']) ? $_POST['excerpt_length'] : '';
            $options['category_type']     = isset($_POST['category_type']) ? $_POST['category_type'] : '';
            $options['workflow']         = isset($_POST['workflow']) ? $_POST['workflow'] : '';
            $options['bp_notifications']   = isset($_POST['bp_notifications']) ? $_POST['bp_notifications'] : '';
            $options['allow_author_adition']   = isset($_POST['allow_author_adition']) ? $_POST['allow_author_adition'] : '';
            $options['allow_author_deletion']   = isset($_POST['allow_author_deletion']) ? $_POST['allow_author_deletion'] : '';

            echo '<div class="updated fade"><p>' . __('Settings Saved', 'social-resources') . '</p></div>';

            update_option('social_articles_options', $options);
        }?>


        <div id="icon-options-general" class="icon32"></div>
        <h2><?php _e( "Social Resource Settings", 'social-resources' ) ?></h2>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div class="postbox-container" id="postbox-container-2">
                    <div class="meta-box-sortables ui-sortable">
                        <form id="form_data" name="form" method="post">
                        <?php
                            $rows = array();

                            $rows[]       = array(
                                'id'      => 'post_per_page',
                                'label'   => __('Post per page#','social-resources'),
                                'content' => $socialArticles->textinput( 'post_per_page' ),
                            );

                            $rows[]       = array(
                                'id'      => 'excerpt_length',
                                'label'   => __('Excerpt length#','social-resources'),
                                'content' => $socialArticles->textinput( 'excerpt_length' ),
                            );

                            $rows[] = array(
                                'id'      => 'category_type',
                                'label'   => __('Select category type','social-resources'),
                                'content' => $socialArticles->select( 'category_type', array(
                                        'single' => __('Single', 'social-resources'),
                                        'multiple'  => __('Multiple', 'social-resources'),
                                    ), false, "", __('Number of categories people can select', 'social-resources')
                                ),
                            );

                            $rows[] = array(
                                'id'      => 'workflow',
                                'label'   => __('Select workflow type','social-resources'),
                                'content' => $socialArticles->select( 'workflow', array(
                                        'direct' => __('Direct Publish', 'social-resources'),
                                        'approval'  => __('With Approval ', 'social-resources'),
                                    ), false, "", ""
                                ),
                            );

                            $status = "";
                            $msg = "";
                            if(!function_exists("friends_get_friend_user_ids")){
                                $status="disabled";
                                $msg=__("To use this feature, you need to activate bb Friend Connections module.", "social-resources");
                            }

                            $rows[] = array(
                                'id'      => 'bp_notifications',
                                'label'   => __('Send buddyPress notifications?','social-resources'),
                                'content' => $socialArticles->select( 'bp_notifications', array(
                                        'false' => __('False', 'social-resources'),
                                        'true'  => __('True', 'social-resources'),
                                    ), false, $status, $msg
                                ),
                            );

                            $rows[] = array(
                                'id'      => 'allow_author_adition',
                                'label'   => __('Can users edit their articles?','social-resources'),
                                'content' => $socialArticles->select( 'allow_author_adition', array(
                                        'false' => __('False', 'social-resources'),
                                        'true'  => __('True', 'social-resources'),
                                    ), false
                                ),
                            );

                            $rows[] = array(
                                'id'      => 'allow_author_deletion',
                                'label'   => __('Can users delete their articles?','social-resources'),
                                'content' => $socialArticles->select( 'allow_author_deletion', array(
                                        'false' => __('False', 'social-resources'),
                                        'true'  => __('True', 'social-resources'),
                                    ), false
                                ),
                            );

                            $save_button = '<div class="submitbutton"><input type="submit" class="button-primary" name="submit" value="'.__('Update Social Resource Settings','social-resources'). '" /></div><br class="clear"/>';
                            $socialArticles->postbox( 'social_articles_general_options', __( 'General', 'social-resources' ), $socialArticles->form_table( $rows ) . $save_button);
                            ?>
                            <input type="hidden" name="form_submit" value="true" />
                        </form>
                    </div>                 
                </div>
            </div>

        </div>
    </div><?php
} 
?>
