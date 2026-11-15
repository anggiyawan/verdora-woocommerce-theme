<?php
/**
 * Search forms, rendered through get_search_form() so they stay filterable.
 *
 * - get_search_form( array( 'verdora_store' => true ) ): header product search with a
 *   category dropdown. The native <select> is what the form submits; assets/js/main.js
 *   turns it into the styled listbox (progressive enhancement).
 * - get_search_form(): plain site search (widgets, empty results, etc.).
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

if ( empty( $args['verdora_store'] ) ) :
	$verdora_id = wp_unique_id( 'vd-sf-' );
	?>
<form role="search" method="get" class="vd-search vd-search--plain" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $verdora_id ); ?>"><?php esc_html_e( 'Search', 'verdora' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $verdora_id ); ?>" class="vd-search__input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'verdora' ); ?>">
	<button type="submit" class="vd-search__btn" aria-label="<?php esc_attr_e( 'Search', 'verdora' ); ?>"><?php verdora_the_icon( 'search', 18 ); ?></button>
</form>
	<?php
	return;
endif;

$verdora_has_wc = class_exists( 'WooCommerce' );
$verdora_cats   = ( $verdora_has_wc && verdora_option( 'search_categories' ) ) ? verdora_get_product_categories() : array();
$verdora_hint   = $verdora_has_wc ? verdora_option( 'search_placeholder' ) : __( 'Search…', 'verdora' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only search.
$verdora_cat_current = isset( $_GET['product_cat'] ) ? sanitize_title( wp_unslash( $_GET['product_cat'] ) ) : '';
$verdora_input_id    = wp_unique_id( 'vd-search-' );
$verdora_list_id     = $verdora_input_id . '-list';

$verdora_all_label = __( 'All', 'verdora' );
$verdora_current   = $verdora_all_label;
foreach ( $verdora_cats as $verdora_term ) {
	if ( $verdora_term->slug === $verdora_cat_current ) {
		$verdora_current = $verdora_term->name;
	}
}
?>
<form role="search" method="get" class="vd-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<?php if ( $verdora_cats ) : ?>
		<div class="vd-catselect" data-vd-catselect>
			<label class="screen-reader-text" for="<?php echo esc_attr( $verdora_input_id ); ?>-cat"><?php esc_html_e( 'Categories', 'verdora' ); ?></label>
			<select class="vd-search__cat" id="<?php echo esc_attr( $verdora_input_id ); ?>-cat" name="product_cat">
				<option value=""><?php echo esc_html( $verdora_all_label ); ?></option>
				<?php foreach ( $verdora_cats as $verdora_term ) : ?>
					<option value="<?php echo esc_attr( $verdora_term->slug ); ?>" <?php selected( $verdora_cat_current, $verdora_term->slug ); ?>><?php echo esc_html( $verdora_term->name ); ?></option>
				<?php endforeach; ?>
			</select>

			<button type="button" class="vd-catselect__btn" aria-haspopup="listbox" aria-expanded="false" aria-controls="<?php echo esc_attr( $verdora_list_id ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Category:', 'verdora' ); ?></span>
				<span class="vd-catselect__value"><?php echo esc_html( $verdora_current ); ?></span>
				<?php verdora_the_icon( 'chevron-down', 16, 'vd-catselect__chevron' ); ?>
			</button>

			<div class="vd-catselect__panel">
				<p class="vd-catselect__label" aria-hidden="true"><?php esc_html_e( 'Search in category', 'verdora' ); ?></p>
				<ul class="vd-catselect__list" id="<?php echo esc_attr( $verdora_list_id ); ?>" role="listbox" tabindex="-1" aria-label="<?php esc_attr_e( 'Categories', 'verdora' ); ?>">
					<li class="vd-catselect__option" id="<?php echo esc_attr( $verdora_list_id ); ?>-0" role="option" data-value="" data-label="<?php echo esc_attr( $verdora_all_label ); ?>" aria-selected="<?php echo '' === $verdora_cat_current ? 'true' : 'false'; ?>">
						<span class="vd-catselect__icon"><?php verdora_the_icon( 'grid', 16 ); ?></span>
						<span class="vd-catselect__name"><?php esc_html_e( 'All Categories', 'verdora' ); ?></span>
						<?php verdora_the_icon( 'check-circle', 16, 'vd-catselect__check' ); ?>
					</li>
					<?php foreach ( $verdora_cats as $verdora_i => $verdora_term ) : ?>
						<li class="vd-catselect__option" id="<?php echo esc_attr( $verdora_list_id . '-' . ( $verdora_i + 1 ) ); ?>" role="option" data-value="<?php echo esc_attr( $verdora_term->slug ); ?>" data-label="<?php echo esc_attr( $verdora_term->name ); ?>" aria-selected="<?php echo $verdora_term->slug === $verdora_cat_current ? 'true' : 'false'; ?>">
							<span class="vd-catselect__icon"><?php verdora_the_icon( verdora_category_icon_name( $verdora_term ), 16 ); ?></span>
							<span class="vd-catselect__name"><?php echo esc_html( $verdora_term->name ); ?></span>
							<span class="vd-catselect__count"><?php echo esc_html( number_format_i18n( $verdora_term->count ) ); ?></span>
							<?php verdora_the_icon( 'check-circle', 16, 'vd-catselect__check' ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>
	<label class="screen-reader-text" for="<?php echo esc_attr( $verdora_input_id ); ?>"><?php esc_html_e( 'Search', 'verdora' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $verdora_input_id ); ?>" class="vd-search__input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $verdora_hint ); ?>" autocomplete="off">
	<?php if ( $verdora_has_wc ) : ?>
		<input type="hidden" name="post_type" value="product">
	<?php endif; ?>
	<button type="submit" class="vd-search__btn" aria-label="<?php esc_attr_e( 'Search', 'verdora' ); ?>">
		<?php verdora_the_icon( 'search', 20 ); ?>
	</button>
</form>
