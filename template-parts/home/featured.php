<?php
/**
 * Home: featured products row (products marked with a star in the admin).
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

if ( ! verdora_option( 'home_featured_show' ) ) {
	return;
}

$verdora_featured = verdora_get_products(
	array(
		'featured' => true,
		'limit'    => max( 2, min( 20, (int) verdora_option( 'home_featured_count' ) ) ),
	)
);

if ( ! $verdora_featured ) {
	return;
}
?>
<section class="vd-section vd-container vd-reveal" aria-labelledby="vd-featured-title">
	<div class="vd-section__head">
		<h2 class="vd-section__title" id="vd-featured-title"><?php verdora_the_icon( 'sparkles', 22 ); ?><?php echo esc_html( verdora_option( 'home_featured_title' ) ); ?></h2>
	</div>
	<div class="vd-scroller" data-vd-scroller>
		<button type="button" class="vd-scroller__btn vd-scroller__btn--prev" data-dir="-1" aria-label="<?php esc_attr_e( 'Previous', 'verdora' ); ?>"><?php verdora_the_icon( 'chevron-left', 20 ); ?></button>
		<?php verdora_render_products( $verdora_featured, 'verdora_featured', 'row' ); ?>
		<button type="button" class="vd-scroller__btn vd-scroller__btn--next" data-dir="1" aria-label="<?php esc_attr_e( 'Next', 'verdora' ); ?>"><?php verdora_the_icon( 'chevron-right', 20 ); ?></button>
	</div>
</section>
