<?php
/**
 * Home: Flash Sale row (products that currently have a sale price).
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

if ( ! verdora_option( 'flash_enabled' ) ) {
	return;
}

$verdora_end = verdora_flash_end_timestamp();
if ( $verdora_end <= time() ) {
	return;
}

$verdora_ids = array_map( 'absint', wc_get_product_ids_on_sale() );
if ( ! $verdora_ids ) {
	return;
}

$verdora_products = verdora_get_products(
	array(
		'include' => $verdora_ids,
		'limit'   => max( 2, min( 20, (int) verdora_option( 'flash_count' ) ) ),
		'orderby' => 'date',
		'order'   => 'DESC',
	)
);
if ( ! $verdora_products ) {
	return;
}
?>
<section class="vd-section vd-container vd-reveal" id="vd-flash" aria-labelledby="vd-flash-title">
	<div class="vd-panel vd-flash">
		<div class="vd-section__head">
			<h2 class="vd-section__title vd-flash__title" id="vd-flash-title">
				<span class="vd-flash__bolt"><?php verdora_the_icon( 'zap', 18 ); ?></span>
				<?php echo esc_html( verdora_option( 'flash_title' ) ); ?>
			</h2>
			<div class="vd-countdown" data-vd-countdown="<?php echo esc_attr( $verdora_end ); ?>" role="timer" aria-label="<?php esc_attr_e( 'Flash sale time left', 'verdora' ); ?>">
				<span class="vd-countdown__label"><?php esc_html_e( 'Ends in', 'verdora' ); ?></span>
				<span class="vd-countdown__box" data-unit="h">00</span><span class="vd-countdown__sep">:</span>
				<span class="vd-countdown__box" data-unit="m">00</span><span class="vd-countdown__sep">:</span>
				<span class="vd-countdown__box" data-unit="s">00</span>
			</div>
			<a class="vd-section__link" href="<?php echo esc_url( add_query_arg( 'on_sale', '1', wc_get_page_permalink( 'shop' ) ) ); ?>">
				<?php esc_html_e( 'See all', 'verdora' ); ?><?php verdora_the_icon( 'chevron-right', 16 ); ?>
			</a>
		</div>
		<div class="vd-scroller" data-vd-scroller>
			<button type="button" class="vd-scroller__btn vd-scroller__btn--prev" data-dir="-1" aria-label="<?php esc_attr_e( 'Previous', 'verdora' ); ?>"><?php verdora_the_icon( 'chevron-left', 20 ); ?></button>
			<?php verdora_render_products( $verdora_products, 'verdora_flash', 'row' ); ?>
			<button type="button" class="vd-scroller__btn vd-scroller__btn--next" data-dir="1" aria-label="<?php esc_attr_e( 'Next', 'verdora' ); ?>"><?php verdora_the_icon( 'chevron-right', 20 ); ?></button>
		</div>
	</div>
</section>
