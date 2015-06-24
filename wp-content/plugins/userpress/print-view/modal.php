<?php 
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

<style>

::-webkit-scrollbar
{
  width: 10px;  /* for vertical scrollbars */
}

::-webkit-scrollbar-track
{
  background: rgba(0, 0, 0, 0.1);
}

::-webkit-scrollbar-thumb
{
  background: rgba(0, 0, 0, 0.5);
}


#PrintViewModal {
	position:fixed;
	top:10% !important;
	max-height:85%;
	overflow:hidden;
	display:block;
	padding:1em;
	
	}
#iframe-body {
	overflow-y:scroll;
	overflow-x:hidden;
	max-height:420px;
}

#modal-wrapper {
	position:relative;
	overflow:hidden;
	padding:0;
	}

.iframe-modal-header {
display:fixed;
height:30px;
padding-bottom:1em;
margin-bottom:1em;
margin-top:0;
font-weight:bold;
border-bottom:1px solid #eee;
}


#modal-content {
	position:relative;

}

#fixed-bottom {
position:absolute;
bottom:0%;
right:0%;
height:3em;
text-align:right;
background: #fff;
}

#fixed-bottom a {
margin-right:30px;
color:#111;
font-size:0.8em;
}

article {
border:0;
}

</style>



<div id="modal-wrapper">
	<div class="iframe-modal-header">
					<a class="close-reveal-modal right">X</a>
					<p><?php bloginfo( 'name' ); ?> &rarr; <?php the_title(); ?> 		<small><a href="<?php the_permalink(); ?>">(View Full Page)</a></small>
</p>
	</div>					
					<div id="modal-content" class="clearfix">
	
<div id="iframe-body">
	

<article id="post-<?php the_ID(); ?>" >

	
		<?php if ( have_posts() ) : ?>

			<?php while ( have_posts() ) : the_post(); ?>





	
	<?php the_content(); ?>

	<footer>

		<p><?php wp_link_pages(); ?></p>





	</footer>




			<?php endwhile; ?>
			
		<?php endif; ?>
		
<div class="last-modified"><strong><i class="fi-clock"></i> Last modified</strong>: <?php the_modified_time('F j, Y'); ?> at <?php the_modified_time('g:i a'); ?></div>

<?php if (function_exists('next_page_activation')) { ?>

<br>
<br>
<div class="next-previous-navigation">
<?php previous_link(); ?><?php next_link(); ?>
</div>
<?php } ?>

</article>



		

</div>
		
	</div>


</div>
</body>
</html>