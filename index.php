<?php
/**
 * Main template: blog index and generic fallback.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

get_header();
?>
<main id="main" class="vd-main">
	<div class="vd-container vd-content-layout">
		<div class="vd-content">
			<?php if ( is_home() && ! is_front_page() ) : ?>
				<header class="vd-page-header">
					<h1 class="vd-page-title"><?php single_post_title(); ?></h1>
				</header>
			<?php elseif ( is_archive() ) : ?>
				<header class="vd-page-header">
					<?php the_archive_title( '<h1 class="vd-page-title">', '</h1>' ); ?>
					<?php the_archive_description( '<div class="vd-page-desc">', '</div>' ); ?>
				</header>
			<?php elseif ( is_search() ) : ?>
				<header class="vd-page-header">
					<h1 class="vd-page-title">
						<?php
						/* translators: %s: search query */
						printf( esc_html__( 'Search results: %s', 'verdora' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
						?>
					</h1>
				</header>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="vd-posts">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
					?>
				</div>
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => verdora_icon( 'chevron-left', 18 ) . '<span class="screen-reader-text">' . esc_html__( 'Previous', 'verdora' ) . '</span>',
						'next_text' => '<span class="screen-reader-text">' . esc_html__( 'Next', 'verdora' ) . '</span>' . verdora_icon( 'chevron-right', 18 ),
					)
				);
				?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</main>
<?php
get_footer();
