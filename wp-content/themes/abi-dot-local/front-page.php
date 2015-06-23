<?php
/**
 * Template Name: Front Page Template
 *
 * Description: A page template that provides a key component of WordPress as a CMS
 * by meeting the need for a carefully crafted introductory page. The front page template
 * in BuddyBoss consists of a page text area for adding text, images, video --
 * anything you'd like -- followed by front-page-only widgets in one or two columns.
 *
 * @package WordPress
 * @subpackage BuddyBoss
 * @since BuddyBoss 3.0
 */

get_header(); ?>

	<div class="page-full-width">

		<!-- Frontpage Slider -->
	
		<div class="home_slider">
			<?php putRevSlider("home_slider") ?>
		</div><!-- .home_slider -->
		
		<div class="home_right">
			<div class="home_right_box cities">
				<h3>Cities</h3>
				<a class="absolute_link" href="<?php echo bloginfo('url');?>/groups"></a>
				<a class="home_right_link" href="<?php echo bloginfo('url');?>/groups">Find an ABI.Local near you</a>
				<div class="home_right_arrow"><img src="<?php echo get_stylesheet_directory_uri();?>/images/right-arrow.png"></div>
			</div>

			<div class="home_right_box forums">
				<h3>Forums</h3>
				<a class="absolute_link" href="<?php echo bloginfo('url');?>/forums"></a>
				<a class="home_right_link" href="<?php echo bloginfo('url');?>/forums">Discuss the hottest topics in tech</a>
				<div class="home_right_arrow"><img src="<?php echo get_stylesheet_directory_uri();?>/images/right-arrow.png"></div>
			</div>

			<div class="home_right_box events">
				<h3>Events</h3>
				<a class="absolute_link" href="<?php echo bloginfo('url');?>/events"></a>
				<a class="home_right_link" href="<?php echo bloginfo('url');?>/events">Attend an event in you city</a>
				<div class="home_right_arrow"><img src="<?php echo get_stylesheet_directory_uri();?>/images/right-arrow.png"></div>
			</div>
		</div>

		<div class="clearfix"></div>

		<div id="primary" class="site-text">
		
			<div id="text" role="main">
				
				<?php while ( have_posts() ) : the_post(); ?>
					<div class="content_after_slider">
						<?php if (get_field('content_after_slider')){
							echo get_field('content_after_slider');
						}?>
					</div>
					<hr class="blue-hr"/>

					<h2 class="callout_headline">
						<?php if(get_field('callout_headline')) {
							echo get_field('callout_headline');
						}?>
					</h2>

					<div class="one-third callout">
						<div class="callout_image">
							<img src="<?php if(get_field('callout_1_image')) {
								echo get_field('callout_1_image');
							}?>">
						</div>
						<h4 class="callout_title">
							<?php if(get_field('callout_1_title')) {
								echo get_field('callout_1_title');
							}?>
						</h4>
						<div class="callout_text">
							<?php if(get_field('callout_1_text')) {
								echo get_field('callout_1_text');
							}?>
						</div>
					</div>


					<div class="one-third callout">
						<div class="callout_image">
							<img src="<?php if(get_field('callout_2_image')) {
								echo get_field('callout_2_image');
							}?>">
						</div>
						<h4 class="callout_title">
							<?php if(get_field('callout_2_title')) {
								echo get_field('callout_2_title');
							}?>
						</h4>
						<div class="callout_text">
							<?php if(get_field('callout_2_text')) {
								echo get_field('callout_2_text');
							}?>
						</div>
					</div>


					<div class="one-third callout last">
						<div class="callout_image">
							<img src="<?php if(get_field('callout_3_image')) {
								echo get_field('callout_3_image');
							}?>">
						</div>
						<h4 class="callout_title">
							<?php if(get_field('callout_3_title')) {
								echo get_field('callout_3_title');
							}?>
						</h4>
						<div class="callout_text">
							<?php if(get_field('callout_3_text')) {
								echo get_field('callout_3_text');
							}?>
						</div>
					</div>

				
					<div class="made-possible">
						<div class="container">
							<div class="textwidget">
								<h4 class="widgettitle">ABI.LOCAL IS MADE POSSIBLE BY </h4><img src="<?php echo get_stylesheet_directory_uri();?>/images/google-light.jpg">
							</div>
						</div>
					</div>

				<?php endwhile; // end of the loop. ?>


			</div><!-- #text -->
		</div><!-- #primary -->
	</div>
<?php get_footer(); ?>