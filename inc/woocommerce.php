<?php
/**
 * WooCommerce integration. Uses hooks only (no template overrides),
 * so WooCommerce updates never leave outdated template copies behind.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

/*
 * -------------------------------------------------------------------------
 * General
 * -------------------------------------------------------------------------
 */

// The theme ships its own, lighter WooCommerce stylesheet.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Products per page on shop/category pages.
 *
 * @return int
 */
function verdora_loop_per_page() {
	return max( 4, min( 60, (int) verdora_option( 'shop_per_page' ) ) );
}
add_filter( 'loop_shop_per_page', 'verdora_loop_per_page' );

/**
 * Columns on shop/category pages (layout is CSS grid; this only sets the class).
 *
 * @return int
 */
function verdora_loop_columns() {
	return max( 3, min( 5, (int) verdora_option( 'shop_columns' ) ) );
}
add_filter( 'loop_shop_columns', 'verdora_loop_columns' );

/**
 * Related products: 5 in one row.
 *
 * @param array $args Args.
 * @return array
 */
function verdora_related_products_args( $args ) {
	$count                  = max( 2, min( 10, (int) verdora_option( 'single_related_count' ) ) );
	$args['posts_per_page'] = $count;
	$args['columns']        = min( 5, $count );
	return $args;
}

/**
 * Hooks that depend on Customizer options. Runs on `wp` (after the Customizer
 * preview has applied unsaved values) instead of at file load.
 */
function verdora_wc_conditional_hooks() {
	if ( ! verdora_option( 'card_button' ) ) {
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	}
	if ( 'none' === verdora_option( 'sale_badge' ) ) {
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	}
	if ( ! verdora_option( 'single_meta' ) ) {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	}
	if ( ! verdora_option( 'single_related' ) ) {
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	}
}
add_action( 'wp', 'verdora_wc_conditional_hooks' );
add_filter( 'woocommerce_output_related_products_args', 'verdora_related_products_args' );
add_filter( 'woocommerce_upsell_display_args', 'verdora_related_products_args' );

/**
 * Breadcrumb markup.
 *
 * @param array $defaults Defaults.
 * @return array
 */
function verdora_breadcrumb_defaults( $defaults ) {
	$defaults['delimiter']   = '<span class="vd-breadcrumb__sep" aria-hidden="true">/</span>';
	$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb vd-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'verdora' ) . '">';
	$defaults['wrap_after']  = '</nav>';
	$defaults['home']        = __( 'Home', 'verdora' );
	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'verdora_breadcrumb_defaults' );

/*
 * -------------------------------------------------------------------------
 * Page wrappers + shop sidebar
 * -------------------------------------------------------------------------
 */

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * Whether the current view is a product listing (shop, category, tag, product search).
 *
 * @return bool
 */
function verdora_is_product_listing() {
	return is_shop() || is_product_taxonomy();
}

/**
 * Open main wrapper.
 */
function verdora_wc_wrapper_open() {
	$class = is_product() ? 'vd-main vd-single' : 'vd-main vd-shop';
	echo '<main id="main" class="' . esc_attr( $class ) . '"><div class="vd-container">';
}
add_action( 'woocommerce_before_main_content', 'verdora_wc_wrapper_open', 5 );

/**
 * Close main wrapper.
 */
function verdora_wc_wrapper_close() {
	echo '</div></main>';
}
add_action( 'woocommerce_after_main_content', 'verdora_wc_wrapper_close', 50 );

/**
 * Open the two-column shop layout and print the filter sidebar.
 */
function verdora_shop_layout_open() {
	if ( ! verdora_is_product_listing() ) {
		return;
	}
	if ( ! verdora_option( 'shop_sidebar' ) ) {
		echo '<div class="vd-shop-layout vd-shop-layout--full"><div class="vd-shop-content">';
		return;
	}
	?>
	<div class="vd-shop-layout">
		<aside class="vd-shop-sidebar" id="vd-shop-filters" aria-label="<?php esc_attr_e( 'Product filters', 'verdora' ); ?>">
			<div class="vd-shop-sidebar__head">
				<strong><?php esc_html_e( 'Filter', 'verdora' ); ?></strong>
				<button type="button" class="vd-icon-btn" data-vd-close="vd-shop-filters" aria-label="<?php esc_attr_e( 'Close filters', 'verdora' ); ?>">
					<?php verdora_the_icon( 'close', 20 ); ?>
				</button>
			</div>
			<?php
			if ( is_active_sidebar( 'shop-sidebar' ) ) {
				dynamic_sidebar( 'shop-sidebar' );
			} else {
				verdora_default_shop_filters();
			}
			?>
		</aside>
		<div class="vd-shop-overlay" data-vd-close="vd-shop-filters" hidden></div>
		<div class="vd-shop-content">
	<?php
}
add_action( 'woocommerce_before_main_content', 'verdora_shop_layout_open', 30 );

/**
 * Close the shop layout.
 */
function verdora_shop_layout_close() {
	if ( verdora_is_product_listing() ) {
		echo '</div></div>';
	}
}
add_action( 'woocommerce_after_main_content', 'verdora_shop_layout_close', 5 );

/**
 * Read a GET parameter as sanitized text.
 *
 * @param string $key Key.
 * @return string
 */
function verdora_query_param( $key ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only catalog filters.
	return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
}

/**
 * Built-in filters shown when the "Sidebar Toko" widget area is empty.
 */
function verdora_default_shop_filters() {
	$current_term = is_product_taxonomy() ? get_queried_object() : null;
	$action       = ( $current_term instanceof WP_Term ) ? get_term_link( $current_term ) : wc_get_page_permalink( 'shop' );
	if ( is_wp_error( $action ) ) {
		$action = wc_get_page_permalink( 'shop' );
	}
	?>
	<section class="vd-widget">
		<h2 class="vd-widget__title"><?php esc_html_e( 'Categories', 'verdora' ); ?></h2>
		<ul class="vd-filter-cats">
			<li class="<?php echo is_shop() ? 'is-current' : ''; ?>">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'All Products', 'verdora' ); ?></a>
			</li>
			<?php
			foreach ( verdora_get_product_categories() as $term ) :
				$link = get_term_link( $term );
				if ( is_wp_error( $link ) ) {
					continue;
				}
				$is_current = $current_term instanceof WP_Term && (int) $current_term->term_id === (int) $term->term_id;
				?>
				<li class="<?php echo $is_current ? 'is-current' : ''; ?>">
					<a href="<?php echo esc_url( $link ); ?>">
						<?php echo esc_html( $term->name ); ?>
						<span class="vd-filter-cats__count"><?php echo esc_html( number_format_i18n( $term->count ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="vd-widget">
		<h2 class="vd-widget__title"><?php esc_html_e( 'Price & Deals', 'verdora' ); ?></h2>
		<form class="vd-filter-form" method="get" action="<?php echo esc_url( $action ); ?>">
			<div class="vd-filter-price">
				<label>
					<span class="screen-reader-text"><?php esc_html_e( 'Minimum price', 'verdora' ); ?></span>
					<input type="number" name="min_price" min="0" step="1" inputmode="numeric" placeholder="<?php esc_attr_e( 'Min', 'verdora' ); ?>" value="<?php echo esc_attr( verdora_query_param( 'min_price' ) ); ?>">
				</label>
				<span aria-hidden="true">–</span>
				<label>
					<span class="screen-reader-text"><?php esc_html_e( 'Maximum price', 'verdora' ); ?></span>
					<input type="number" name="max_price" min="0" step="1" inputmode="numeric" placeholder="<?php esc_attr_e( 'Max', 'verdora' ); ?>" value="<?php echo esc_attr( verdora_query_param( 'max_price' ) ); ?>">
				</label>
			</div>
			<label class="vd-check">
				<input type="checkbox" name="on_sale" value="1" <?php checked( '1', verdora_query_param( 'on_sale' ) ); ?>>
				<span><?php esc_html_e( 'On sale', 'verdora' ); ?></span>
			</label>
			<?php if ( verdora_query_param( 'orderby' ) ) : ?>
				<input type="hidden" name="orderby" value="<?php echo esc_attr( verdora_query_param( 'orderby' ) ); ?>">
			<?php endif; ?>
			<div class="vd-filter-form__actions">
				<button type="submit" class="vd-btn vd-btn--primary vd-btn--sm"><?php esc_html_e( 'Apply', 'verdora' ); ?></button>
				<a class="vd-btn vd-btn--ghost vd-btn--sm" href="<?php echo esc_url( $action ); ?>"><?php esc_html_e( 'Reset', 'verdora' ); ?></a>
			</div>
		</form>
	</section>
	<?php
}

/**
 * Support ?on_sale=1 on the product catalog (used by the filter and "See all" links).
 *
 * @param WP_Query $q Query.
 */
function verdora_on_sale_query( $q ) {
	if ( '1' !== verdora_query_param( 'on_sale' ) ) {
		return;
	}
	$ids = array_map( 'absint', wc_get_product_ids_on_sale() );
	$q->set( 'post__in', $ids ? $ids : array( 0 ) );
}
add_action( 'woocommerce_product_query', 'verdora_on_sale_query' );

/**
 * Shop toolbar: filter toggle + result count + ordering.
 */
function verdora_shop_toolbar_open() {
	echo '<div class="vd-shop-toolbar">';
	if ( ! verdora_option( 'shop_sidebar' ) ) {
		return;
	}
	printf(
		'<button type="button" class="vd-btn vd-btn--ghost vd-btn--sm vd-filter-toggle" data-vd-open="vd-shop-filters" aria-controls="vd-shop-filters" aria-expanded="false">%1$s<span>%2$s</span></button>',
		verdora_icon( 'filter', 16 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Filter', 'verdora' )
	);
}
add_action( 'woocommerce_before_shop_loop', 'verdora_shop_toolbar_open', 15 );

/**
 * Close shop toolbar.
 */
function verdora_shop_toolbar_close() {
	echo '</div>';
}
add_action( 'woocommerce_before_shop_loop', 'verdora_shop_toolbar_close', 35 );

/*
 * -------------------------------------------------------------------------
 * Product card (loop item)
 * -------------------------------------------------------------------------
 */

remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

/**
 * Card open + media link.
 */
function verdora_card_open() {
	global $product;
	printf(
		'<div class="vd-card"><a class="vd-card__media" href="%1$s" tabindex="-1" aria-hidden="true">',
		esc_url( apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ) )
	);
}
add_action( 'woocommerce_before_shop_loop_item', 'verdora_card_open', 5 );

/**
 * Out-of-stock badge on the image.
 */
function verdora_card_stock_badge() {
	global $product;
	if ( $product && ! $product->is_in_stock() ) {
		echo '<span class="vd-badge vd-badge--muted">' . esc_html__( 'Out of stock', 'verdora' ) . '</span>';
	}
}
add_action( 'woocommerce_before_shop_loop_item_title', 'verdora_card_stock_badge', 15 );

/**
 * Close media, open body.
 */
function verdora_card_media_close() {
	echo '</a><div class="vd-card__body">';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'verdora_card_media_close', 30 );

/**
 * Category label.
 */
function verdora_card_category() {
	global $product;
	if ( ! verdora_option( 'card_category' ) ) {
		return;
	}
	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		echo '<span class="vd-card__cat">' . esc_html( $terms[0]->name ) . '</span>';
	}
}
add_action( 'woocommerce_shop_loop_item_title', 'verdora_card_category', 5 );

/**
 * Product title (h2 on listing pages, h3 inside home sections).
 */
function verdora_card_title() {
	global $product;
	$tag = verdora_is_product_listing() && ! wc_get_loop_prop( 'is_shortcode' ) ? 'h2' : 'h3';
	printf(
		'<%1$s class="woocommerce-loop-product__title vd-card__title"><a href="%2$s">%3$s</a></%1$s>',
		tag_escape( $tag ),
		esc_url( apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ) ),
		esc_html( get_the_title() )
	);
}
add_action( 'woocommerce_shop_loop_item_title', 'verdora_card_title', 10 );

/**
 * Rating + sold count.
 */
function verdora_card_meta() {
	global $product;
	if ( ! verdora_option( 'card_meta' ) ) {
		return;
	}
	$rating = (float) $product->get_average_rating();
	$sold   = (int) $product->get_total_sales();
	if ( ! $rating && ! $sold ) {
		return;
	}
	echo '<div class="vd-card__meta">';
	if ( $rating ) {
		echo '<span class="vd-card__rating">' . verdora_icon( 'star', 14 ) . esc_html( number_format_i18n( $rating, 1 ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( $rating && $sold ) {
		echo '<span class="vd-card__dot" aria-hidden="true">·</span>';
	}
	if ( $sold ) {
		/* translators: %s: number of items sold */
		echo '<span>' . esc_html( sprintf( __( '%s sold', 'verdora' ), verdora_short_number( $sold ) ) ) . '</span>';
	}
	echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item_title', 'verdora_card_meta', 20 );

/**
 * Stock progress bar, only inside the Flash Sale row.
 */
function verdora_card_stock_progress() {
	global $product;
	if ( 'verdora_flash' !== wc_get_loop_prop( 'name' ) || ! $product->managing_stock() ) {
		return;
	}
	$stock = max( 0, (int) $product->get_stock_quantity() );
	$sold  = (int) $product->get_total_sales();
	$total = $stock + $sold;
	$pct   = $total > 0 ? (int) round( ( $sold / $total ) * 100 ) : 0;
	?>
	<div class="vd-stockbar" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: stock */ __( '%d left in stock', 'verdora' ), $stock ) ); ?>">
		<span class="vd-stockbar__fill" style="width: <?php echo esc_attr( max( 6, $pct ) ); ?>%"></span>
		<span class="vd-stockbar__text">
			<?php
			echo esc_html(
				$stock <= 5 && $stock > 0
					/* translators: %d: stock */
					? sprintf( __( 'Almost gone! %d left', 'verdora' ), $stock )
					/* translators: %d: stock */
					: sprintf( __( '%d left', 'verdora' ), $stock )
			);
			?>
		</span>
	</div>
	<?php
}
add_action( 'woocommerce_after_shop_loop_item_title', 'verdora_card_stock_progress', 25 );

/**
 * Close card body + card.
 */
function verdora_card_close() {
	echo '</div></div>';
}
add_action( 'woocommerce_after_shop_loop_item', 'verdora_card_close', 50 );

/**
 * Add-to-cart button class.
 *
 * @param array $args Args.
 * @return array
 */
function verdora_loop_add_to_cart_args( $args ) {
	$args['class'] = trim( $args['class'] . ' vd-card__cta' );
	return $args;
}
add_filter( 'woocommerce_loop_add_to_cart_args', 'verdora_loop_add_to_cart_args' );

/**
 * Sale badge as a percentage.
 *
 * @param string     $html    Default HTML.
 * @param WP_Post    $post    Post.
 * @param WC_Product $product Product.
 * @return string
 */
function verdora_sale_flash( $html, $post, $product ) {
	$style   = verdora_option( 'sale_badge' );
	$text    = verdora_option( 'sale_badge_text' ) ? verdora_option( 'sale_badge_text' ) : __( 'Sale', 'verdora' );
	$percent = verdora_discount_percent( $product );
	if ( 'none' === $style ) {
		return '';
	}
	if ( 'text' === $style || $percent <= 0 ) {
		return '<span class="onsale">' . esc_html( $text ) . '</span>';
	}
	return '<span class="onsale">-' . esc_html( $percent ) . '%</span>';
}
add_filter( 'woocommerce_sale_flash', 'verdora_sale_flash', 10, 3 );

/**
 * Largest discount percentage of a product (handles variable products).
 *
 * @param WC_Product $product Product.
 * @return int
 */
function verdora_discount_percent( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return 0;
	}
	$max = 0;
	if ( $product->is_type( 'variable' ) ) {
		$prices = $product->get_variation_prices();
		foreach ( $prices['regular_price'] as $id => $regular ) {
			$sale = isset( $prices['sale_price'][ $id ] ) ? (float) $prices['sale_price'][ $id ] : 0;
			if ( (float) $regular > 0 && $sale < (float) $regular ) {
				$max = max( $max, ( (float) $regular - $sale ) / (float) $regular * 100 );
			}
		}
	} else {
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
		if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
			$max = ( $regular - $sale ) / $regular * 100;
		}
	}
	return (int) round( $max );
}

/*
 * -------------------------------------------------------------------------
 * Single product
 * -------------------------------------------------------------------------
 */

/**
 * Sold count + stock info under the title.
 */
function verdora_single_sold() {
	global $product;
	if ( ! verdora_option( 'single_sold' ) ) {
		return;
	}
	$sold = (int) $product->get_total_sales();
	if ( $sold > 0 ) {
		/* translators: %s: number sold */
		echo '<p class="vd-single__sold">' . esc_html( sprintf( __( '%s sold', 'verdora' ), verdora_short_number( $sold ) ) ) . '</p>';
	}
}
add_action( 'woocommerce_single_product_summary', 'verdora_single_sold', 11 );

/**
 * Store-defined info points (one per line) under the buy buttons.
 */
function verdora_single_info() {
	$lines = array_filter( array_map( 'trim', explode( "\n", (string) verdora_option( 'single_info' ) ) ) );
	if ( ! $lines ) {
		return;
	}
	echo '<ul class="vd-single-info">';
	foreach ( $lines as $line ) {
		echo '<li>' . verdora_icon( 'check-circle', 18 ) . '<span>' . esc_html( $line ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</ul>';
}
add_action( 'woocommerce_single_product_summary', 'verdora_single_info', 35 );

/**
 * Show discount percent next to the single price.
 *
 * @param string     $price_html Price HTML.
 * @param WC_Product $product    Product.
 * @return string
 */
function verdora_single_price_badge( $price_html, $product ) {
	if ( 'percent' === verdora_option( 'sale_badge' ) && is_product() && did_action( 'woocommerce_before_single_product_summary' ) && ! did_action( 'woocommerce_after_single_product_summary' ) && $product->is_on_sale() ) {
		$percent = verdora_discount_percent( $product );
		if ( $percent > 0 && get_queried_object_id() === $product->get_id() ) {
			$price_html .= ' <span class="vd-price-badge">-' . esc_html( $percent ) . '%</span>';
		}
	}
	return $price_html;
}
add_filter( 'woocommerce_get_price_html', 'verdora_single_price_badge', 20, 2 );

/*
 * -------------------------------------------------------------------------
 * Header cart count (AJAX fragments)
 * -------------------------------------------------------------------------
 */

/**
 * Cart count badge.
 *
 * @return string
 */
function verdora_cart_count_html() {
	$count = ( WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
	return sprintf(
		'<span class="vd-cart-count%1$s" aria-hidden="true">%2$s</span>',
		$count ? '' : ' is-empty',
		esc_html( $count > 99 ? '99+' : (string) $count )
	);
}

/**
 * Refresh the count after AJAX add to cart.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function verdora_cart_fragments( $fragments ) {
	$fragments['span.vd-cart-count'] = verdora_cart_count_html();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'verdora_cart_fragments' );

/*
 * -------------------------------------------------------------------------
 * Home page product helpers
 * -------------------------------------------------------------------------
 */

/**
 * Base query args that respect catalog visibility and the "hide out of stock" setting.
 *
 * @param array $args Extra args.
 * @return WC_Product[]
 */
function verdora_get_products( $args ) {
	$defaults = array(
		'status'     => 'publish',
		'visibility' => 'catalog',
		'limit'      => 10,
	);
	if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
		$defaults['stock_status'] = 'instock';
	}
	return wc_get_products( array_merge( $defaults, $args ) );
}

/**
 * Render products through the standard loop so every WooCommerce hook,
 * including AJAX add to cart, behaves exactly like on the shop page.
 *
 * @param WC_Product[] $products Products.
 * @param string       $name     Loop name.
 * @param string       $variant  Layout variant: grid|row.
 */
function verdora_render_products( $products, $name, $variant = 'grid' ) {
	if ( empty( $products ) ) {
		return;
	}
	global $post;
	$original_post = $post;

	wc_setup_loop(
		array(
			'name'         => $name,
			'is_shortcode' => true,
			'columns'      => 5,
			'total'        => count( $products ),
		)
	);

	echo '<div class="vd-products vd-products--' . esc_attr( $variant ) . '">';
	woocommerce_product_loop_start();
	foreach ( $products as $item ) {
		$post = get_post( $item->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		wc_get_template_part( 'content', 'product' );
	}
	woocommerce_product_loop_end();
	echo '</div>';

	wc_reset_loop();
	$post = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	wp_reset_postdata();
}
