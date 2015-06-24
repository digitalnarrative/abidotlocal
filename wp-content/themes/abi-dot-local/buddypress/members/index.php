<?php do_action( 'bp_before_directory_members_page' ); ?>

<div id="buddypress">

	<div class="bp-members-title">
    	<h1><?php _e('ABI.Local Members','Boss'); ?></h1>
        <?php if ( !is_user_logged_in() ) { ?>
        <span>Don't see your face? <a href="<?php echo esc_url(home_url()); ?>/register/">Sign up!</a></span>
        <?php } ?>
    </div>

	<?php do_action( 'bp_before_directory_members' ); ?>

	<?php do_action( 'bp_before_directory_members_content' ); ?>	

	<?php do_action( 'bp_before_directory_members_tabs' ); ?>

	<form action="" method="post" id="members-directory-form" class="dir-form">

		<div class="item-list-tabs" role="navigation">
			<ul>
				<li class="selected" id="members-all"><a href="<?php bp_members_directory_permalink(); ?>"><?php printf( __( 'All Members <span>%s</span>', 'buddyboss' ), bp_get_total_member_count() ); ?></a></li>

				<?php if ( is_user_logged_in() && bp_is_active( 'friends' ) && bp_get_total_friend_count( bp_loggedin_user_id() ) ) : ?>
					<li id="members-personal"><a href="<?php echo bp_loggedin_user_domain() . bp_get_friends_slug() . '/my-friends/'; ?>"><?php printf( __( 'My Friends <span>%s</span>', 'buddyboss' ), bp_get_total_friend_count( bp_loggedin_user_id() ) ); ?></a></li>
				<?php endif; ?>

				<?php do_action( 'bp_members_directory_member_types' ); ?>

				<?php do_action( 'bp_members_directory_member_sub_types' ); ?>

				<li id="members-order-select" class="last filter">                	
					<label for="members-order-by"><?php //_e( 'Order By:', 'buddyboss' ); ?></label>
                    
					<select id="members-order-by" style="display: none">
						<option id="active" value="active"><?php _e( 'Last Active', 'buddyboss' ); ?></option>
						<option id="newest" value="newest"><?php _e( 'Newest Registered', 'buddyboss' ); ?></option>

						<?php if ( bp_is_active( 'xprofile' ) ) : ?>
							<option id="alphabet" value="alphabetical"><?php _e( 'Alphabetical', 'buddyboss' ); ?></option>
						<?php endif; ?>

						<?php do_action( 'bp_members_directory_order_options' ); ?>
					</select>
                                        
                    <button class="select-alphabet">A-Z</button>
                    <button class="select-newest">Newest</button>
				</li>

			</ul>
            
            <div id="members-dir-search" class="dir-search" role="search">
				<?php bp_directory_members_search_form(); ?>
            </div><!-- #members-dir-search -->
		</div><!-- .item-list-tabs -->

		<div id="members-dir-list" class="members dir-list">
			<?php bp_get_template_part( 'members/members-loop' ); ?>
		</div><!-- #members-dir-list -->

		<?php do_action( 'bp_directory_members_content' ); ?>

		<?php wp_nonce_field( 'directory_members', '_wpnonce-member-filter' ); ?>

		<?php do_action( 'bp_after_directory_members_content' ); ?>

	</form><!-- #members-directory-form -->

	<?php do_action( 'bp_after_directory_members' ); ?>

</div><!-- #buddypress -->

<?php do_action( 'bp_after_directory_members_page' ); ?>