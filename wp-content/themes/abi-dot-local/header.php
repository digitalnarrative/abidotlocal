<?php
/**
 * The Header for your theme.
 *
 * Displays all of the <head> section and everything up until <div id="main">
 *
 * @package WordPress
 * @subpackage BuddyBoss
 * @since BuddyBoss 3.0
 */
?><!DOCTYPE html>
<!--[if lt IE 9 ]>
<html class="ie ie-legacy" <?php language_attributes(); ?>> <![endif]-->
<!--[if gte IE 9 ]><!-->
<html class="ie" <?php language_attributes(); ?>>
<!--<![endif]-->
<!--[if ! IE  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="msapplication-tap-highlight" content="no"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.ico" type="image/x-icon">
<!-- BuddyPress and bbPress Stylesheets are called in wp_head, if plugins are activated -->
<?php wp_head(); ?>
</head>

<body <?php if ( current_user_can('manage_options') ) : ?>id="role-admin"<?php endif; ?> <?php body_class(); ?>>

<?php do_action( 'buddyboss_before_header' ); ?>
<div id="wpadminbar">
    <a class="x" href="#"><i class="fa fa-close"></i></a>
    <a class="user-link" href="<?php echo bp_core_get_user_domain( get_current_user_id() ); ?>">
        <span>
            <?php echo bp_core_fetch_avatar ( array( 'item_id' => get_current_user_id(), 'type' => 'full', 'width' => '50', 'height' => '50' ) );  ?>                        </span>
        <span class="name"><?php echo bp_core_get_user_displayname( get_current_user_id() ); ?></span>
    </a>
    <hr class="blue-hr">
    <ul class="dashboard">
        <li class="menupop">
            <a class="ab-item" href="<?php echo admin_url(); ?>"><?php _e('Dashboard','boss'); ?></a>
            <div class="ab-sub-wrapper">
                <ul class="ab-submenu">
                    <li>
                        <a href="<?php echo admin_url(); ?>"><?php _e('Dashboard','boss'); ?></a>
                        <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e( 'News Feed', 'boss' );?></a>
                        <a href="<?php echo admin_url('themes.php'); ?>"><?php _e( 'My Likes', 'boss' );?></a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
    <?php buddyboss_adminbar_myaccount();?>
    <div class="clearfix"></div>
    <hr class="blue-hr">
    
    <span class="logout">
        <a href="<?php echo wp_logout_url(); ?>"><?php _e('Logout','boss'); ?></a>
    </span>
</div>
<header id="masthead" class="site-header" role="banner" data-infinite="<?php echo (esc_attr(get_option('buddyboss_activity_infinite')) !== 'off')?'on':'off'; ?>">

	<div class="header-inner">

        <!-- Look for uploaded logo -->
        <?php if ( get_theme_mod( 'buddyboss_logo' ) ) : ?>
            <div id="logo">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php echo esc_url( get_theme_mod( 'buddyboss_logo' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"></a>
            </div>

        <!-- If no logo, display site title and description -->
        <?php else: ?>
            <div class="site-name">
                <h1 class="site-title">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
                        <?php bloginfo( 'name' ); ?>
                    </a>
                </h1>
                <p class="site-description"><?php bloginfo( 'description' ); ?></p>
            </div>
        <?php endif; ?>

       
        <div class="right-column">
            <?php if ( is_user_logged_in() ) : ?>
                           
                                
                            <?php if(buddyboss_is_bp_active()): ?> 

                                <!--Account details -->
                                <div class="header-account-login">

                                    <?php do_action("buddyboss_before_header_account_login_block"); ?>

                                    <a class="user-link" href="<?php echo bp_core_get_user_domain( get_current_user_id() ); ?>">
                                        <span class="name"><?php echo bp_core_get_user_displayname( get_current_user_id() ); ?></span>
                                        <span>
                                            <?php echo bp_core_fetch_avatar ( array( 'item_id' => get_current_user_id(), 'type' => 'full', 'width' => '100', 'height' => '100' ) );  ?>                        </span>
                                    </a>

                                    <div class="pop">
                                       
                                        <!-- Dashboard links -->
                                        <?php 
                                        if( get_option( 'buddyboss_dashboard' ) !== '0' && current_user_can( 'level_10' ) ): 
                                        ?>
                                        <div id="dashboard-links" class="bp_components">
                                            <ul>
                                                <?php if( is_multisite() ):?>
                                                    <?php if( is_super_admin() ):?>
                                                        <li class="menupop">
                                                            <a class="ab-item" href="<?php echo admin_url( 'my-sites.php' ); ?>"><?php _e('My Sites','boss'); ?></a>
                                                            <div class="ab-sub-wrapper">
                                                                <ul class="ab-submenu">
                                                                    <li class="menupop network-menu">
                                                                        <a class="ab-item" href="<?php echo network_admin_url(); ?>"><?php _e('Network Admin','boss'); ?></a>
                                                                        <div class="ab-sub-wrapper">
                                                                            <ul class="ab-submenu">
                                                                                <li>
                                                                                    <a href="<?php echo network_admin_url(); ?>"><?php _e( 'Dashboard', 'boss' );?></a>
                                                                                    <a href="<?php echo network_admin_url('sites.php'); ?>"><?php _e( 'Sites', 'boss' );?></a>
                                                                                    <a href="<?php echo network_admin_url('users.php'); ?>"><?php _e( 'Users', 'boss' );?></a>
                                                                                    <a href="<?php echo network_admin_url('themes.php'); ?>"><?php _e( 'Themes', 'boss' );?></a>
                                                                                    <a href="<?php echo network_admin_url('plugins.php'); ?>"><?php _e( 'Plugins', 'boss' );?></a>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </li>
                                                                    <?php 
                                                                    $current_blog_id = get_current_blog_id();
                                                                    
                                                                    global $wp_admin_bar;
                                                                    foreach ( (array) $wp_admin_bar->user->blogs as $blog ) {
                                                                        switch_to_blog( $blog->userblog_id );
                                                                        $blogname = empty( $blog->blogname ) ? $blog->domain : $blog->blogname;
                                                                        ?>
                                                                        <li class="menupop">
                                                                            <a class="ab-item" href="<?php echo home_url(); ?>"><?php echo $blogname; ?></a>
                                                                            <div class="ab-sub-wrapper">
                                                                                <ul class="ab-submenu">
                                                                                    <li>
                                                                                        <a href="<?php echo admin_url(); ?>"><?php _e( 'Dashboard', 'boss' );?></a>
                                                                                        <a href="<?php echo admin_url('users.php'); ?>"><?php _e( 'Users', 'boss' );?></a>
                                                                                        <a href="<?php echo admin_url('themes.php'); ?>"><?php _e( 'Themes', 'boss' );?></a>
                                                                                        <a href="<?php echo admin_url('plugins.php'); ?>"><?php _e( 'Plugins', 'boss' );?></a>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </li>
                                                                        <?php 
                                                                    }
                                                                    
                                                                    //switch back to current blog
                                                                    switch_to_blog( $current_blog_id );
                                                                    ?>
                                                                </ul>
                                                            </div>
                                                        </li>
                                                    <?php endif;?>
                                                        <li class="menupop">
                                                            <a class="ab-item" href="<?php echo admin_url(); ?>"><?php _e('Dashboard','boss'); ?></a>
                                                            <div class="ab-sub-wrapper">
                                                                <ul class="ab-submenu">
                                                                    <li>
                                                                        <a href="<?php echo admin_url('customize.php'); ?>"><?php _e( 'Customize', 'boss' );?></a>
                                                                        <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e( 'Widgets', 'boss' );?></a>
                                                                        <a href="<?php echo admin_url('nav-menus.php'); ?>"><?php _e( 'Menus', 'boss' );?></a>
                                                                        <a href="<?php echo admin_url('plugins.php'); ?>"><?php _e( 'Plugins', 'boss' );?></a>
                                                                        <a href="<?php echo admin_url('themes.php'); ?>"><?php _e( 'Themes', 'boss' );?></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </li>
                                                <?php else: ?>
                                                    <li class="menupop">
                                                        <a class="ab-item" href="<?php echo admin_url(); ?>"><?php _e('Dashboard','boss'); ?></a>
                                                        <div class="ab-sub-wrapper">
                                                            <ul class="ab-submenu">
                                                                <li>
                                                                    <a href="<?php echo admin_url('customize.php'); ?>"><?php _e( 'Customize', 'boss' );?></a>
                                                                    <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e( 'Widgets', 'boss' );?></a>
                                                                    <a href="<?php echo admin_url('nav-menus.php'); ?>"><?php _e( 'Menus', 'boss' );?></a>
                                                                    <a href="<?php echo admin_url('plugins.php'); ?>"><?php _e( 'Plugins', 'boss' );?></a>
                                                                    <a href="<?php echo admin_url('themes.php'); ?>"><?php _e( 'Themes', 'boss' );?></a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                           
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                       
                                        <!-- Adminbar -->
                                        <div id="adminbar-links" class="bp_components">
                                        <?php 
                                            buddyboss_adminbar_myaccount();
                                        ?>
                                        </div>
                                        
                                        <?php
                                        wp_nav_menu( array( 'theme_location' => 'header-my-account', 'fallback_cb'=>'','menu_class' => 'links' ) );     
                                        ?>

                                        <span class="logout">
                                            <a href="<?php echo wp_logout_url(); ?>"><?php _e('Logout','boss'); ?></a>
                                        </span>
                                    </div> 

                                    <?php do_action("buddyboss_after_header_account_login_block"); ?>

                                </div><!--.header-account-login-->
                                

                     <?php 
                            $update_data = wp_get_update_data();

                            if ($update_data['counts']['total'] && current_user_can( 'update_core' ) && current_user_can( 'update_plugins' ) && current_user_can( 'update_themes' )) { ?>
                                <!-- Notification -->
                                <div class="header-notifications">
                                    <a class="notification-link fa fa-refresh" href="<?php echo network_admin_url( 'update-core.php' ); ?>">
                                       <span class="ab-label"><?php echo number_format_i18n( $update_data['counts']['total'] ); ?></span>
                                    </a>
                                </div>
                                
                            <?php } ?>
                    
                            <?php if(buddyboss_is_bp_active()):  

                                if(function_exists('buddyboss_notification_bp_members_shortcode_bar_notifications_menu')) {
                                    echo do_shortcode('[buddyboss_notification_bar]');
                                } else {
                                    
                                $notifications = buddyboss_adminbar_notification();
                                $link = $notifications[0];
                                unset($notifications[0]);
                                ?>

                                <!-- Notification -->
                                <div class="header-notifications">
                                    <a class="notification-link fa fa-bell" href="<?php if($link) { echo $link->href; } ?>">
                                    <?php if($link) { echo $link->title; } ?>
                                    </a>

                                    <div class="pop">
                                    <?php
                                    if($link) {
                                        foreach($notifications as $notification) {
                                            echo '<a href="'.$notification->href.'">'.$notification->title.'</a>';
                                        }
                                    }
                                    ?>
                                    </div>
                                </div>
            
                                <?php 
                                } 
                                ?>
                                
                            <?php endif; ?>
                               



                        <?php endif; ?>
                                
                    <?php else: ?>
                         
                        <!-- Register/Login links for logged out users -->
                        <?php if ( !is_user_logged_in() && buddyboss_is_bp_active()) : ?>
                 
                            <div class="header-account">
                 
                                <?php if ( buddyboss_is_bp_active() && bp_get_signup_allowed() ) : ?>
                                    <?php _e('Connect with women in tech ', 'buddyboss');?><a class="button register" href="<?php echo bp_get_signup_page(); ?>"><?php _e( 'Join Today', 'buddyboss' ); ?></a>
                                <?php endif; ?>
                 
                                <?php _e( 'or ', 'buddyboss' ); ?><a href="<?php echo wp_login_url(); ?>" class="login"><?php _e( 'Login', 'buddyboss' ); ?></a>
                 
                            </div>
                 
                        <?php endif; ?>

                         

                <?php endif; ?> <!-- if ( is_user_logged_in() ) -->
        </div>

	</div>

	<nav id="site-navigation" class="main-navigation" role="navigation">
		<div class="nav-inner">

            <div class="mobile"><?php get_search_form( );?></div>
			<a class="assistive-text" href="#content" title="<?php esc_attr_e( 'Skip to content', 'buddyboss' ); ?>"><?php _e( 'Skip to content', 'buddyboss' ); ?></a>
			<?php wp_nav_menu( array( 'theme_location' => 'primary-menu', 'menu_class' => 'nav-menu clearfix' ) ); ?>
    
            <?php get_search_form( );?>
            <div class="clearfix"></div>
		</div>

	</nav><!-- #site-navigation -->
</header><!-- #masthead -->

<?php do_action( 'buddyboss_after_header' ); ?>

<div id="mobile-header"> <!-- Toolbar for Mobile -->
    <div class="mobile-header-inner">
        <!-- Left button -->
        <?php if ( is_user_logged_in() || ( !is_user_logged_in() && buddyboss_is_bp_active() && !bp_hide_loggedout_adminbar( false ) ) ) : ?>
            <div id="user-nav" class="left-btn"></div>
        <?php endif; ?>
        <!-- Right button -->
            <div id="main-nav" class="right-btn"></div>
    </div>
    <h1><a class="mobile-site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
</div><!-- #mobile-header -->
<div id="breadcrumbs" class="container">
    <?php if ( is_home() || is_singular('post' )) {
        if ( function_exists('yoast_breadcrumb') ) {
            yoast_breadcrumb('<p id="breadcrumbs">','</p>');
        }
    } ?>
</div>
<div id="main-wrap"> <!-- Wrap for Mobile content -->
    <div id="inner-wrap"> <!-- Inner Wrap for Mobile content -->
    	<?php do_action( 'buddyboss_inside_wrapper' ); ?>
        <div id="page" class="hfeed site">
            <div id="main" class="wrapper">
