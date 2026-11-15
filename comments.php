<?php
/**
 * Comments.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="vd-panel vd-comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="vd-comments__title">
			<?php
			/* translators: %s: number of comments */
			printf( esc_html( _n( '%s comment', '%s comments', get_comments_number(), 'verdora' ) ), esc_html( number_format_i18n( get_comments_number() ) ) );
			?>
		</h2>
		<ol class="vd-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 40,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="vd-meta"><?php esc_html_e( 'Comments are closed.', 'verdora' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_submit' => 'vd-btn vd-btn--primary',
		)
	);
	?>
</section>
