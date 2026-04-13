<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Newscrunch
 */
?>
<article data-wow-delay=".8s" <?php post_class('wow-callback zoomIn spnc-post'); ?> >
	<div class="spnc-post-content">

		<?php
		if('NewsBlogger' == wp_get_theme()) { 

			$newscrunch_spage_sort=get_theme_mod( 'single_page_sort', array('reorder_spage_title', 'reorder_spage_img' ) );
			if ( ! empty( $newscrunch_spage_sort ) && is_array( $newscrunch_spage_sort ) ) :
				foreach ( $newscrunch_spage_sort as $newscrunch_spage_sort_key => $newscrunch_spage_sort_val ) :

				if(get_theme_mod('newscrunch_enable_reorder_spage_title',true)==true):
					if ( 'reorder_spage_title' === $newscrunch_spage_sort_val ) :?>        
				        <header class="entry-header">
				        	<?php
				        	
				        	$newscrunch_page_title_markup= esc_html(get_theme_mod('single_page_title_markup', 'h1')); 
				        	
				        	$newscrunch_page_title_markup_before='<' . $newscrunch_page_title_markup . ' itemprop="name" class="spnc-entry-title">';
		  					$newscrunch_page_title_markup_after='</' . $newscrunch_page_title_markup . '>';

							echo wp_kses_post($newscrunch_page_title_markup_before);
								 the_title();
						    echo wp_kses_post($newscrunch_page_title_markup_after);
						    ?>                                               
						</header>
						<?php endif;
				endif;

				if(get_theme_mod('newscrunch_enable_reorder_spage_img',true)==true):
					if ( 'reorder_spage_img' === $newscrunch_spage_sort_val ) :
					 if(has_post_thumbnail()) {
						if ( is_single() ) { ?>
							<figure class="spnc-post-thumbnail">
								<?php the_post_thumbnail('full', array('class'=>'img-fluid', 'loading' => false )); ?>					
							</figure>
						<?php }
						else { ?>
							<figure class="spnc-post-thumbnail">
								<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
									<?php the_post_thumbnail('full', array('class'=>'img-fluid', 'loading' => false )); ?>
								</a>				
							</figure>
						<?php }
					}
					endif;
				endif;
				endforeach;
			endif;
		} else { 
			if(has_post_thumbnail()) {
				if ( is_single() ) { ?>
					<figure class="spnc-post-thumbnail">
						<?php the_post_thumbnail('full', array('class'=>'img-fluid', 'loading' => false )); ?>					
					</figure>
				<?php }
				else { ?>
					<figure class="spnc-post-thumbnail">
						<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
							<?php the_post_thumbnail('full', array('class'=>'img-fluid', 'loading' => false )); ?>
						</a>				
					</figure>
				<?php }
			}
	    } ?>					
		<div class="spnc-entry-content">
			<?php the_content();
			newscrunch_edit_link_button();
			?>
		</div>
	</div>
</article>