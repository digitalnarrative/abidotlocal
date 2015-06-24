<?php

/**
 * The class_exists() check is recommended, to prevent problems during upgrade
 * or when the Groups component is disabled
 */
if ( class_exists( 'BP_Group_Extension' ) ) :

class Resource_Group_Extension extends BP_Group_Extension {
    public $slug = 'resources';
    public $name = '';
    public $nav_item_position = 105;
    public $show_tab = 'anyone';
	
    /**
     * Your __construct() method will contain configuration options for 
     * your extension, and will pass them to parent::init()
     */
    function __construct() {
        $this->name = __( 'Resources', 'TEXTDOMAIN' );
        $args = array(
            'slug' => $this->slug,
            'name' => $this->name,
            'nav_item_position' => $this->nav_item_position,
            'screens' => array(
                'edit' => false,
                'create' => false,
            ),
        );
        parent::init( $args );
    }
	
    /**
     * display() contains the markup that will be displayed on the main 
     * plugin tab
     */
    function display( $group_id = NULL ) {
        ?>
        <div class="group-<?php echo $this->slug;?>">
            <?php
            $add_resouce_page = get_permalink( ka_misc_settings( 'page_add_resource' ) );
            $add_resouce_page = add_query_arg( 'gid', bp_get_group_id(), $add_resouce_page );
            ?>
            
            <h2><?php echo $this->name;?><a class='button btn-add-resource' href='<?php echo esc_url( $add_resouce_page );?>'><?php _e( 'Add Resource', 'TEXTDOMAIN' );?></a></h2>
            <div class='content'>
                <?php 
                $group_url = trailingslashit( bp_get_group_permalink() ) . 'resources';
                ?>
                <form method='GET' action='<?php echo $group_url;?>' id='frm_group_search_resource'>
                    <input type='search' name='srch' value='<?php echo isset( $_GET['srch'] ) ? esc_attr( $_GET['srch'] ) : '';?>' placeholder='<?php _e( 'Search', 'TEXTDOMAIN' );?>' >
                </form>
                <?php 
                $per_page = 10;
                $current_page = isset( $_GET['list'] ) ? (int)$_GET['list'] : 1;
                $search = isset( $_GET['srch'] ) ? $_GET['srch'] : '';
                $args = array(
                    'post_type' => 'resource',
                    'posts_per_page'   => $per_page,
                    'paged' => $current_page,
                    's' => $search,
                    'meta_key'  => 'group_id',
                    'meta_value'    => bp_get_group_id(),
                );
                
                $rq = new WP_Query( $args );
                if( $rq->have_posts() ){
                    echo "<div class='clearfix resources-list'>";
                    while( $rq->have_posts() ){
                        $rq->the_post();
                        get_template_part( 'loop', 'resource' );
                    }
                    
                    if(function_exists( 'emi_generate_paging_param' ) ){
                        $slug = untrailingslashit( str_replace( home_url(), '', $group_url ) );
                        emi_generate_paging_param($rq->found_posts, $per_page, $current_page, $slug );
                    }
                    echo "</div>";
                } else {
                    echo "<p class='alert alert-info'>" . __( 'Nothing found!', 'TEXTDOMAIN' ) . "<p>";
                }
                wp_reset_postdata();
                ?>
            </div>
        </div>
        <?php 
    }
}

add_action( 'bp_init', 'Resource_Group_Extension_Init' );
function Resource_Group_Extension_Init(){
    if( function_exists('bp_is_group') &&  bp_is_group()) {
        bp_register_group_extension( 'Resource_Group_Extension' );
    }
}

endif; // if ( class_exists( 'BP_Group_Extension' ) )