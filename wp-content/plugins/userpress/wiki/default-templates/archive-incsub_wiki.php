<?php get_header(); ?>

	<div id="content">
		<div class="padder">
<h2 class="pagetitle">
			    <?php  if ($_GET["action"] == 'create') { 
				echo "Create New Wiki"; }
				else { printf( __( '%1$s', 'buddypress' ), wp_title( false, false ) ); echo " "; } ?> </h2>



		<div class="wiki-index" role="main">

	    <?php  
	    
	    if ($_GET["action"] == 'create') { 

	    echo $wiki->get_new_wiki_form(); 	    
	    
	    
	    } if ($_GET["view"] == 'created') { 
		
		// PLACEHOLDER //
		
		
	    } elseif ($_GET["view"] == 'updated') { 

$today = current_time('mysql', 1);
$howMany = 20;
if ( $recentposts = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'userpress_wiki' AND post_modified_gmt < '$today' ORDER BY post_modified_gmt DESC LIMIT $howMany")):
?>
<h2><?php _e("Recently Updated"); ?></h2>
<ul>
<?php
foreach ($recentposts as $post) {
if ($post->post_title == '') $post->post_title = sprintf(__('Post #%s'), $post->ID); ?>

<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <span style="font-size:0.7em; font-weight:normal;">(<a href="<?php the_permalink(); ?>?action=history">history</a> | <a href="<?php the_permalink(); ?>?action=discussion">discussion</a>)</span> <?php edit_post_link( __( '#', 'buddypress' ), '<p class="edit-link">', '</p>'); ?></h3>
<h5 style="font-weight:normal;">Revision Note: Larry O'Connor (born April 1, 1950) is a Canadian former professional ice hockey defenceman. O'Connor played junior hockey in the Quebec Major Jun...  Updated by [avatar][Username] / November 22, 2013</h5>
<hr>
<?php }
?>
</ul>
<?php endif;
		
	    } elseif ($_GET["view"] == 'recently_discussed') { 
query_posts(array('orderby_last_comment' => true));

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1; // for pagination
$args = array(

  'posts_per_page' => '20',
  'paged' => $paged,
  'post_type' => 'userpress_wiki' 
  );
  
$wikis = new WP_Query($args);
  
  ?>
			<?php if ($wikis->have_posts()) : ?>

<h2><?php _e("Recently Discussed"); ?></h2>

				<?php while ($wikis->have_posts()) : $wikis->the_post(); ?>

<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <span style="font-size:0.7em; font-weight:normal;">(<a href="<?php the_permalink(); ?>?action=history">history</a> | <a href="<?php the_permalink(); ?>?action=discussion">discussion</a>)</span> <?php edit_post_link( __( '#', 'buddypress' ), '<p class="edit-link">', '</p>'); ?>
</h3>
<h5 style="font-weight:normal;">Revision Note: Larry O'Connor (born April 1, 1950) is a Canadian former professional ice hockey defenceman. O'Connor played junior hockey in the Quebec Major Jun...  Updated by [avatar][Username] / November 22, 2013</h5>
<hr>
  <?php endwhile; endif;
  
	    } elseif ($_GET["view"] == 'most_discussed') { 

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1; // for pagination
$args = array(

  'orderby' => 'comment_count',  
  'order' => 'DESC',
  'posts_per_page' => '20',
  'paged' => $paged,
  'post_type' => 'userpress_wiki' 
  );
  
$wikis = new WP_Query($args);
  
  ?>
			<?php if ($wikis->have_posts()) : ?>

<h2><?php _e("Most Discussed"); ?></h2>

				<?php while ($wikis->have_posts()) : $wikis->the_post(); ?>

<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <span style="font-size:0.7em; font-weight:normal;">(<a href="<?php the_permalink(); ?>?action=history">history</a> | <a href="<?php the_permalink(); ?>?action=discussion">discussion</a>)</span> <?php edit_post_link( __( '#', 'buddypress' ), '<p class="edit-link">', '</p>'); ?>
</h3>
<h5 style="font-weight:normal;">Revision Note: Larry O'Connor (born April 1, 1950) is a Canadian former professional ice hockey defenceman. O'Connor played junior hockey in the Quebec Major Jun...  Updated by [avatar][Username] / November 22, 2013</h5>
<hr>
  <?php endwhile; endif;

		} else {		

	    ?>
	    
	    


<style>
.col { width:24%; float:left; margin-right:5px; }
</style>

<?php
$num_cols = 4; // set the number of columns here
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1; // for pagination
$args = array(
  'orderby' => 'title',
  'order' => 'ASC',
  'posts_per_page' => '199',
  'paged' => $paged,
  'post_type' => 'userpress_wiki' 
  );
  
$wikis = new WP_Query($args);
//end of query section

if ($wikis->have_posts()) :
  // figure out where we need to break the columns
  // ceil() rounds up to a whole number
  $break_at = ceil( $wikis->post_count / $num_cols );
  // start with the first column
  $col_counter = 1;
  $post_counter = 1;
  // Set the title letter empty so that it's always output at the beginning of the cols
  $initial = '';
  ?>
  <div id="col-<?php echo $col_counter ?>" class="col">

  <?php while ($wikis->have_posts()) : $wikis->the_post();

    // Start a new column (but not the first one)
    if( $post_counter % $break_at == 0 && $post_counter > 1 ) :
      $col_counter++;
      ?>
      </ul></div>
      <div id="col-<?php echo $col_counter ?>" class="col"><ul>     

    <?php endif;

    $title = get_the_title();
    $wiki_letter = strtoupper(substr($title,0,1));

    if( $initial != $wiki_letter) : ?>
      <?php if ( $post_counter > 1 ) : // close the previous ul ?>
        </ul>
      <?php endif; ?>
      <h3><?php echo $wiki_letter ?></h3>
      <ul>
      <?php $initial = $wiki_letter;
    endif; ?>

    <li>
      <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'buddypress' ); ?> <?php the_title_attribute(); ?>"><?php echo $title ?></a>
    </li>
    <?php $post_counter++; ?>
  <?php endwhile; ?>
  </ul>
</div>

<?php wp_reset_postdata();
endif; ?> 




</div><!-- END wiki-index -->
<div class="pagination">
<div class="alignleft"><?php previous_posts_link('&larr; Previous Entries') ?></div>
<div class="alignright"><?php next_posts_link('Next Entries &rarr;','') ?></div>
</div>

		<?php } ?>


		<?php do_action( 'bp_after_blog_search' ); ?>
		</div><!-- .padder -->
	</div><!-- #content -->

	<?php get_sidebar(); ?>

<?php get_footer(); ?>