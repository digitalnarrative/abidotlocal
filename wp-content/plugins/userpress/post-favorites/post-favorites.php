<?php 

/*
--------------------------------------------------------------------------------
Favorites button applied to individual posts
Based on: http://www.dnxpert.com/2010/06/11/mark-blog-post-as-favorite-in-buddypress/
--------------------------------------------------------------------------------
*/
 
function up546E_my_bp_activity_is_favorite($activity_id) {
global $bp, $activities_template;
return apply_filters( 'bp_get_activity_is_favorite', in_array( $activity_id, (array)$activities_template->my_favs ) );
}
 
function up546E_my_bp_activity_favorite_link($activity_id) {
global $activities_template;
echo apply_filters( 'bp_get_activity_favorite_link', wp_nonce_url( site_url( BP_ACTIVITY_SLUG . '/favorite/' . $activity_id . '/' ), 'mark_favorite' ) );
}
 
function up546E_my_bp_activity_unfavorite_link($activity_id) {
global $activities_template;
echo apply_filters( 'bp_get_activity_unfavorite_link', wp_nonce_url( site_url( BP_ACTIVITY_SLUG . '/unfavorite/' . $activity_id . '/' ), 'unmark_favorite' ) );
}


?>