<?php
/**
 * Home: recommended product grid.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

if ( ! verdora_option( 'home_reco_show' ) ) {
	return;
}

$verdora_orderby = array(
	'date'       => array(
		'orderby' => 'date',
		'order'   => 'DESC',
	),
	'popularity' => array(
		'orderby'  => 'meta_value_num',
		'meta_key' => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'order'    => 'DESC',
	),
	'rating'     => array(
		'orderby'  => 'meta_value_num',
		'meta_key' => '_wc_average_rating', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'order'    => 'DESC',
	),
	'price'      => array(
		'orderby'  => 'meta_value_num',
		'meta_key' => '_price', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'order'    => 'ASC',
	),
	'rand'       => array(
		'orderby' => 'rand',
	),
);
$verdora_sort    = verdora_option( 'home_reco_orderby' );
$verdora_sort    = isset( $verdora_orderby[ $verdora_sort ] ) ? $verdora_orderby[ $verdora_sort ] : $verdora_orderby['date'];

$verdora_latest = verdora_get_products(
	array_merge(
		array( 'limit' => max( 4, min( 40, (int) verdora_option( 'home_products' ) ) ) ),
		$verdora_sort
	)
);

if ( ! $verdora_latest ) {
	return;
}

// Link the "see all" button to the matching shop sort order.
$verdora_shop_sort = array(
	'date'       => 'date',
	'popularity' => 'popularity',
	'rating'     => 'rating',
	'price'      => 'price',
);
$verdora_shop_url  = wc_get_page_permalink( 'shop' );
if ( isset( $verdora_shop_sort[ verdora_option( 'home_reco_orderby' ) ] ) ) {
	$verdora_shop_url = add_query_arg( 'orderby', $verdora_shop_sort[ verdora_option( 'home_reco_orderby' ) ], $verdora_shop_url );
}
?>
<section class="vd-section vd-container vd-reveal" aria-labelledby="vd-reco-title">
	<div class="vd-section__head vd-section__head--center">
		<h2 class="vd-section__title" id="vd-reco-title"><?php echo esc_html( verdora_option( 'home_reco_title' ) ); ?></h2>
	</div>
	<?php verdora_render_products( $verdora_latest, 'verdora_recommended', 'grid' ); ?>
	<?php if ( verdora_option( 'home_reco_button' ) ) : ?>
		<div class="vd-section__more">
			<a class="vd-btn vd-btn--outline vd-btn--lg" href="<?php echo esc_url( $verdora_shop_url ); ?>">
				<?php echo esc_html( verdora_option( 'home_reco_button' ) ); ?>
				<?php verdora_the_icon( 'arrow-right', 18 ); ?>
			</a>
		</div>
	<?php endif; ?>
</section>
