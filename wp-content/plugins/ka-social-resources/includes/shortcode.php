<?php 
function sc_new_social_resource_handler( $atts ) {
    if( !ka_sr_can_edit_resource() ){
        return false;
    }
    
    ob_start();
    echo "<div id='sc_new_resource_container'>";
    get_template_part( 'buddypress/members/single/resources/new' );
    echo "</div>";
    return ob_get_clean();
}
add_shortcode( 'NEW_SOCIAL_RESOURCE', 'sc_new_social_resource_handler' );