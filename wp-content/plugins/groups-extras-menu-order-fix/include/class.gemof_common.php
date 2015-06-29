<?php

if ( ! class_exists ( 'gemof_common' ) ):

    class gemof_common {

        public function gemof_common() {
            $this->hooks();
        }

        function hooks() {
            add_action( 'bpge_get_nav_order', array($this, 'bpge_get_nav_order'), 101 );
        }

        public function bpge_get_nav_diff(){
            global $bp, $bpge;

            if ( empty( $bpge ) ) {
                $bpge = bpge_get_options();
            }

            if ( bp_is_group() && bp_is_single_item() ) {
                $order = groups_get_groupmeta( $bp->groups->current_group->id, 'bpge_nav_order' );
                $all_menu = array();
                $ordered_menu = array();

                if ( ! empty( $order ) && is_array( $order ) ) {
                    $ordered_menu = array_keys($order);
                    $ordered_menu = str_replace('/', '-', $ordered_menu);
                    foreach($bp->bp_options_nav[ $bp->groups->current_group->slug ] as $key => $val){
                        $all_menu[] = $val['slug'];
                    }
                }
                return array_diff( $all_menu, $ordered_menu );
            }

            return false;
        }


        public function bpge_get_nav_order(){
            global $bp, $bpge;

            if ( empty( $bpge ) ) {
                $bpge = bpge_get_options();
            }

            if ( bp_is_group() && bp_is_single_item() ) {
                $order = groups_get_groupmeta( $bp->groups->current_group->id, 'bpge_nav_order' );
                $prepare_menu = array();

                if ( ! empty( $order ) && is_array( $order ) ) {
                    foreach($bp->bp_options_nav[ $bp->groups->current_group->slug ] as $key => $val){
                        foreach ( $order as $slug => $position ) {
                            $slug = str_replace('/', '-', $slug);
                            if ( $bp->bp_options_nav[ $bp->groups->current_group->slug ][$key]['slug'] == $slug ) {
                                $prepare_menu[$position] = $val;
                                $prepare_menu[$position]['position'] = $position;
                            }
                        }
                    }

                    // add extra new menus
                    $get_new_item = $this->bpge_get_nav_diff();
                    if( isset( $get_new_item ) && !empty( $get_new_item ) ){
                        $pos_gap = count($order) - 1;
                        $position = array_pop($order);
                        foreach($bp->bp_options_nav[ $bp->groups->current_group->slug ] as $key => $val){
                            if ( in_array($bp->bp_options_nav[ $bp->groups->current_group->slug ][$key]['slug'], $get_new_item) ) {
                                $position = $position + $pos_gap;
                                $prepare_menu[$position] = $val;
                                $prepare_menu[$position]['position'] = $position;
                            }
                        }
                    }

                    ksort($prepare_menu);
                    $bp->bp_options_nav[ $bp->groups->current_group->slug ] = $prepare_menu;

                }


                return $bp->bp_options_nav[ $bp->groups->current_group->slug ];
            }

            return false;
        }

        public function gemof_bpge_load() {
            global $bp, $bpge;

            if ( bp_is_group() && ! defined( 'DOING_AJAX' ) ) {
                if (
                    ( is_string( $bpge['groups'] ) && $bpge['groups'] == 'all' ) ||
                    ( is_array( $bpge['groups'] ) && in_array( $bp->groups->current_group->id, $bpge['groups'] ) )
                ) {
                    require( GEMOF_PLUGIN_DIR . '/include/class.group_extras_loader.php' );
                }

                do_action( 'bpge_group_load' );
            }
        }

        public function gemof_bpge_view($view, $params = false){
            global $bp, $bpge;

            do_action('bpge_view_pre', $view, $params);

            $params = apply_filters('bpge_view_params', $params);

            if(!empty($params))
                extract($params);

            $theme_parent_file =   get_template_directory() . DS . BPGE . DS . $view .'.php';
            $theme_child_file  = get_stylesheet_directory() . DS . BPGE . DS . $view .'.php';

            // admin area templates should not be overridable via theme files
            // check that file exists in theme folder
            if(!is_admin() && file_exists($theme_child_file)){
                // from child theme
                include $theme_child_file;
            }elseif(!is_admin() && file_exists($theme_parent_file)){
                // from parent theme if no in child
                include $theme_parent_file;
            }else{
                // from plugin folder
                $plugin_file = GEMOF_PLUGIN_DIR . 'views'. DS . $view . '.php';
                if(file_exists($plugin_file)){
                    include $plugin_file;
                }
            }

            do_action('bpge_view_post', $view, $params);
        }

    }
endif;
