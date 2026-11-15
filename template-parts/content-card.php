<?php
/**
 * Post card used in blog lists.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'vd-post-card vd-reveal' ); ?>>
	<a class="vd-post-card__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) );
		} else {
			echo '<span class="vd-post-card__placeholder">' . verdora_icon( 'leaf', 40 ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</a>
	<div class="vd-post-card__body">
		<?php verdora_posted_on(); ?>
		<?php the_title( '<h2 class="vd-post-card__title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
		<div class="vd-post-card__excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
