<?php
/**
 * Home page data: banner slides, product collages and quick shortcuts.
 * Loaded only when WooCommerce is active.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

/**
 * Products with a photo for the banner art, cached per request.
 *
 * @param string $type  featured|sale|new.
 * @param int    $limit How many.
 * @return WC_Product[]
 */
function verdora_banner_products( $type, $limit = 3 ) {
	static $cache = array();
	$key = $type . ':' . $limit;
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$args = array(
		'limit'   => $limit * 3,
		'orderby' => 'date',
		'order'   => 'DESC',
	);
	if ( 'sale' === $type ) {
		$ids = array_map( 'absint', wc_get_product_ids_on_sale() );
		if ( ! $ids ) {
			$cache[ $key ] = array();
			return array();
		}
		$args['include'] = $ids;
	} elseif ( 'featured' === $type ) {
		$args['featured'] = true;
	}

	$products = array_values(
		array_filter(
			verdora_get_products( $args ),
			function ( $product ) {
				return (bool) $product->get_image_id();
			}
		)
	);

	// Not enough featured products: top up with the newest ones.
	if ( 'featured' === $type && count( $products ) < $limit ) {
		$have = array_map(
			function ( $product ) {
				return $product->get_id();
			},
			$products
		);
		foreach ( verdora_banner_products( 'new', $limit + count( $have ) ) as $product ) {
			if ( count( $products ) >= $limit ) {
				break;
			}
			if ( ! in_array( $product->get_id(), $have, true ) ) {
				$products[] = $product;
			}
		}
	}

	if ( 'sale' === $type ) {
		// Biggest discounts first: the strongest message for a promo banner.
		usort(
			$products,
			function ( $a, $b ) {
				return verdora_discount_percent( $b ) - verdora_discount_percent( $a );
			}
		);
	}

	$cache[ $key ] = array_slice( $products, 0, $limit );
	return $cache[ $key ];
}

/**
 * Highest discount percentage among products on sale (0 when none).
 *
 * @return int
 */
function verdora_max_discount() {
	static $max = null;
	if ( null !== $max ) {
		return $max;
	}
	$max = 0;
	$ids = array_slice( array_map( 'absint', wc_get_product_ids_on_sale() ), 0, 60 );
	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );
		if ( $product && 'publish' === $product->get_status() ) {
			$max = max( $max, verdora_discount_percent( $product ) );
		}
	}
	return $max;
}

/**
 * Decorative stack of product photos for a designed banner slide.
 *
 * @param WC_Product[] $products Products (max 3 used).
 * @param string       $badge    price|discount|new.
 * @param bool         $eager    Load images eagerly (first slide).
 */
function verdora_banner_collage( $products, $badge = 'price', $eager = false ) {
	if ( ! $products ) {
		echo '<span class="vd-collage__empty">' . verdora_icon( 'bag', 72 ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
		return;
	}

	echo '<div class="vd-collage vd-collage--' . count( $products ) . '">';
	foreach ( array_slice( $products, 0, 3 ) as $i => $product ) {
		$attrs = array(
			'alt'     => '',
			'loading' => $eager ? 'eager' : 'lazy',
			'sizes'   => '(max-width: 640px) 120px, 220px',
		);
		if ( $eager && 0 === $i ) {
			$attrs['fetchpriority'] = 'high';
		}

		if ( 'discount' === $badge ) {
			$percent = verdora_discount_percent( $product );
			/* translators: %d: discount percentage */
			$chip = $percent ? sprintf( __( '-%d%%', 'verdora' ), $percent ) : '';
		} elseif ( 'new' === $badge ) {
			$chip = __( 'New', 'verdora' );
		} else {
			$chip = wp_strip_all_tags( wc_price( wc_get_price_to_display( $product ) ) );
		}

		printf(
			'<span class="vd-collage__card vd-collage__card--%1$d">%2$s%3$s</span>',
			(int) $i + 1,
			wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, $attrs ),
			$chip ? '<span class="vd-collage__chip vd-collage__chip--' . esc_attr( $badge ) . '">' . esc_html( $chip ) . '</span>' : ''
		);
	}
	echo '</div>';
}

/**
 * Banner slides in display order.
 *
 * Each slide: array( 'type' => main|sale|new|image, ... ).
 *
 * @return array[]
 */
function verdora_banner_slides() {
	$shop   = wc_get_page_permalink( 'shop' );
	$slides = array();

	if ( verdora_option( 'hero_slide_main' ) ) {
		$slides[] = array( 'type' => 'main' );
	}

	for ( $n = 1; $n <= 4; $n++ ) {
		$image = absint( verdora_option( "slide{$n}_image" ) );
		if ( $image && wp_attachment_is_image( $image ) ) {
			$mobile   = absint( verdora_option( "slide{$n}_image_mobile" ) );
			$slides[] = array(
				'type'   => 'image',
				'image'  => $image,
				'mobile' => ( $mobile && wp_attachment_is_image( $mobile ) ) ? $mobile : 0,
				'url'    => verdora_option( "slide{$n}_url" ),
			);
		}
	}

	if ( verdora_option( 'hero_auto_slides' ) ) {
		$discount = verdora_max_discount();
		$sale     = verdora_banner_products( 'sale', 3 );
		if ( $discount > 0 && $sale ) {
			$count    = count( wc_get_product_ids_on_sale() );
			$slides[] = array(
				'type'     => 'sale',
				'eyebrow'  => __( 'Great Deals', 'verdora' ),
				/* translators: %d: highest discount percentage */
				'title'    => sprintf( __( 'Up to %d%% Off', 'verdora' ), $discount ),
				/* translators: %s: number of discounted products */
				'text'     => sprintf( _n( '%s product is on sale. Shop before the deal ends!', '%s products are on sale. Shop before the deal ends!', $count, 'verdora' ), number_format_i18n( $count ) ),
				'button'   => __( 'View Deals', 'verdora' ),
				'url'      => add_query_arg( 'on_sale', '1', $shop ),
				'products' => $sale,
				'badge'    => 'discount',
			);
		}

		$new = verdora_banner_products( 'new', 3 );
		if ( $new ) {
			$slides[] = array(
				'type'     => 'new',
				'eyebrow'  => __( 'Just Arrived', 'verdora' ),
				'title'    => __( 'New Collection', 'verdora' ),
				'text'     => __( 'Brand-new products, ready for you to check out today.', 'verdora' ),
				'button'   => __( 'View New Arrivals', 'verdora' ),
				'url'      => add_query_arg( 'orderby', 'date', $shop ),
				'products' => $new,
				'badge'    => 'new',
			);
		}
	}

	// Never render an empty banner.
	if ( ! $slides ) {
		$slides[] = array( 'type' => 'main' );
	}

	return $slides;
}

/**
 * Quick shortcut icons under the banner. Items that don't apply to the store
 * (no discounts, reviews off…) are left out.
 *
 * @return array[] Each: label, url, icon, color.
 */
function verdora_quick_links() {
	$shop    = wc_get_page_permalink( 'shop' );
	$on_sale = (bool) wc_get_product_ids_on_sale();
	$links   = array();

	if ( $on_sale && verdora_option( 'flash_enabled' ) && verdora_flash_end_timestamp() > time() ) {
		$links['flash'] = array(
			'label' => __( 'Flash Sale', 'verdora' ),
			'url'   => '#vd-flash',
			'icon'  => 'zap',
			'color' => 'orange',
		);
	}
	if ( $on_sale ) {
		$links['sale'] = array(
			'label' => __( 'Discounts', 'verdora' ),
			'url'   => add_query_arg( 'on_sale', '1', $shop ),
			'icon'  => 'badge-percent',
			'color' => 'rose',
		);
	}
	$links['best'] = array(
		'label' => __( 'Best Sellers', 'verdora' ),
		'url'   => add_query_arg( 'orderby', 'popularity', $shop ),
		'icon'  => 'trophy',
		'color' => 'violet',
	);
	$links['new'] = array(
		'label' => __( 'New Arrivals', 'verdora' ),
		'url'   => add_query_arg( 'orderby', 'date', $shop ),
		'icon'  => 'sparkles',
		'color' => 'blue',
	);
	if ( wc_review_ratings_enabled() ) {
		$links['rating'] = array(
			'label' => __( 'Top Rated', 'verdora' ),
			'url'   => add_query_arg( 'orderby', 'rating', $shop ),
			'icon'  => 'star',
			'color' => 'amber',
		);
	}
	$links['all'] = array(
		'label' => __( 'All Products', 'verdora' ),
		'url'   => $shop,
		'icon'  => 'grid',
		'color' => 'brand',
	);
	$links['orders'] = array(
		'label' => __( 'My Orders', 'verdora' ),
		'url'   => wc_get_account_endpoint_url( 'orders' ),
		'icon'  => 'receipt',
		'color' => 'cyan',
	);
	/**
	 * Filter the home quick shortcuts.
	 *
	 * @param array[] $links Keyed items: label, url, icon (see inc/icons.php), color (palette key or "brand"), external (bool).
	 */
	return apply_filters( 'verdora_quick_links', $links );
}
