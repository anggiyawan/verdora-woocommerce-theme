<?php
/**
 * Single blog post.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

get_header();
?>
<main id="main" class="vd-main">
	<div class="vd-container vd-content-layout">
		<div class="vd-content">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'vd-panel vd-article' ); ?>>
					<header class="vd-page-header">
						<?php the_title( '<h1 class="vd-page-title">', '</h1>' ); ?>
						<?php verdora_posted_on(); ?>
					</header>
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="vd-article__thumb"><?php the_post_thumbnail( 'large', array( 'fetchpriority' => 'high' ) ); ?></figure>
					<?php endif; ?>
					<div class="vd-entry">
						<?php
						the_content();
						wp_link_pages(
							array(
								'before' => '<nav class="vd-page-links">' . esc_html__( 'Pages:', 'verdora' ),
								'after'  => '</nav>',
							)
						);
						?>
					</div>
					<?php if ( has_tag() ) : ?>
						<footer class="vd-article__tags"><?php the_tags( '', ' ' ); ?></footer>
					<?php endif; ?>
				</article>

				<?php
				the_post_navigation(
					array(
						'prev_text' => '<span class="vd-meta">' . esc_html__( 'Previous', 'verdora' ) . '</span><span>%title</span>',
						'next_text' => '<span class="vd-meta">' . esc_html__( 'Next', 'verdora' ) . '</span><span>%title</span>',
					)
				);

				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
			endwhile;
			?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</main>
<?php
get_footer();
