<?php
/**
 * Template helpers.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current request is a WooCommerce page.
 *
 * @return bool
 */
function verdora_is_wc_page() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}
	return is_woocommerce() || is_cart() || is_checkout() || is_account_page();
}

/**
 * Site logo: custom logo or text logo with leaf mark.
 */
function verdora_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	?>
	<a class="vd-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<span class="vd-logo__mark"><?php verdora_the_icon( 'leaf', 20 ); ?></span>
		<span class="vd-logo__text"><?php bloginfo( 'name' ); ?></span>
	</a>
	<?php
}

/**
 * Top-level product categories, excluding the default "Uncategorized" one.
 *
 * @param int $limit Max terms (0 = all).
 * @return WP_Term[]
 */
function verdora_get_product_categories( $limit = 0 ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_product_cat', 0 ) ),
			'number'     => $limit,
			'orderby'    => 'menu_order',
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Pick an icon for a product category from its name/slug.
 *
 * @param WP_Term $term Category.
 * @return string Icon name.
 */
function verdora_category_icon_name( $term ) {
	$haystack = strtolower( $term->slug . ' ' . $term->name );
	$map      = array(
		'smartphone' => array( 'handphone', 'ponsel', 'smartphone', 'tablet' ),
		'laptop'     => array( 'laptop', 'komputer', 'computer', 'notebook' ),
		'camera'     => array( 'kamera', 'camera', 'foto' ),
		'gamepad'    => array( 'gaming', 'game', 'mainan', 'toy', 'hobi', 'hobby' ),
		'shirt'      => array( 'fashion', 'baju', 'kaos', 'pakaian', 'shirt', 'cloth', 'apparel' ),
		'footprints' => array( 'sepatu', 'shoe', 'sneaker', 'sandal', 'footwear' ),
		'headphones' => array( 'elektronik', 'electronic', 'gadget', 'audio', 'headphone' ),
		'bag'        => array( 'tas', 'bag', 'ransel', 'dompet' ),
		'watch'      => array( 'aksesoris', 'accessor', 'jam', 'watch' ),
		'utensils'   => array( 'makanan', 'minuman', 'food', 'kuliner', 'snack', 'dapur', 'kitchen' ),
		'lamp'       => array( 'rumah', 'home', 'household', 'furnitur', 'furniture' ),
		'heart'      => array( 'kecantikan', 'beauty', 'perawatan', 'skincare', 'kosmetik' ),
		'leaf'       => array( 'organik', 'organic', 'herbal', 'sayur', 'kesehatan', 'health', 'vitamin' ),
		'book'       => array( 'buku', 'book', 'alat-tulis', 'stationery', 'kantor', 'office' ),
		'gift'       => array( 'hadiah', 'gift', 'souvenir', 'kado' ),
	);
	foreach ( $map as $icon => $needles ) {
		foreach ( $needles as $needle ) {
			if ( false !== strpos( $haystack, $needle ) ) {
				return $icon;
			}
		}
	}
	return 'tag';
}

/**
 * Image of a recent product in a category (used as the category "photo").
 * Cached in the object cache (no database option), flushed when products or categories change.
 *
 * @param WP_Term $term Category.
 * @return int Attachment ID or 0.
 */
function verdora_category_product_image_id( $term ) {
	$cached = wp_cache_get( 'cat_image_' . $term->term_id, 'verdora' );
	if ( false !== $cached ) {
		return (int) $cached;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- cached below.
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			),
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- cached below.
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				),
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => array( 'exclude-from-catalog' ),
					'operator' => 'NOT IN',
				),
			),
		)
	);

	$image_id = $query->posts ? (int) get_post_thumbnail_id( $query->posts[0] ) : 0;
	wp_cache_set( 'cat_image_' . $term->term_id, $image_id, 'verdora', DAY_IN_SECONDS );
	return $image_id;
}

/**
 * Forget cached category photos.
 */
function verdora_flush_category_images() {
	if ( wp_cache_supports( 'flush_group' ) ) {
		wp_cache_flush_group( 'verdora' );
		return;
	}
	$term_ids = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'fields'     => 'ids',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $term_ids ) ) {
		return;
	}
	foreach ( $term_ids as $term_id ) {
		wp_cache_delete( 'cat_image_' . $term_id, 'verdora' );
	}
}
add_action( 'save_post_product', 'verdora_flush_category_images' );
add_action( 'deleted_post', 'verdora_flush_category_images' );
add_action( 'edited_product_cat', 'verdora_flush_category_images' );
add_action( 'created_product_cat', 'verdora_flush_category_images' );
add_action( 'delete_product_cat', 'verdora_flush_category_images' );

/**
 * Category visual.
 *
 * Order: the category's own thumbnail, then (in "photo" style) a product photo
 * from the category, otherwise a colored icon tile.
 *
 * @param WP_Term $term  Category.
 * @param string  $style photo|icon.
 * @param int     $index Position, used to vary tile colors.
 */
function verdora_category_visual( $term, $style = 'icon', $index = 0 ) {
	$image_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	if ( ! $image_id && 'photo' === $style ) {
		$image_id = verdora_category_product_image_id( $term );
	}
	if ( $image_id && wp_attachment_is_image( $image_id ) ) {
		echo wp_get_attachment_image(
			$image_id,
			'woocommerce_gallery_thumbnail',
			false,
			array(
				'loading' => 'lazy',
				'alt'     => '',
				'class'   => 'vd-cats__img',
			)
		);
		return;
	}
	$colors = array_keys( verdora_tile_palette() );
	echo verdora_tile( verdora_category_icon_name( $term ), $colors[ $index % 8 ], 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
}

/**
 * Compact number: 1200 -> 1,2rb.
 *
 * @param int $number Number.
 * @return string
 */
function verdora_short_number( $number ) {
	$number = (int) $number;
	if ( $number >= 1000000 ) {
		/* translators: %s: number in millions, e.g. 1.2 */
		return sprintf( _x( '%sM', 'compact number, millions', 'verdora' ), number_format_i18n( $number / 1000000, 1 ) );
	}
	if ( $number >= 1000 ) {
		/* translators: %s: number in thousands, e.g. 1.2 */
		return sprintf( _x( '%sK', 'compact number, thousands', 'verdora' ), number_format_i18n( $number / 1000, 1 ) );
	}
	return number_format_i18n( $number );
}

/**
 * Post meta line (date + author).
 */
function verdora_posted_on() {
	printf(
		'<span class="vd-meta"><time datetime="%1$s">%2$s</time> &middot; %3$s</span>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() ),
		esc_html( get_the_author() )
	);
}

/**
 * Section heading with optional "see all" link.
 *
 * @param string $title Title.
 * @param string $link  URL.
 * @param string $icon  Icon name.
 * @param string $extra Extra trusted HTML (already escaped) placed after the title.
 */
function verdora_section_heading( $title, $link = '', $icon = '', $extra = '' ) {
	?>
	<div class="vd-section__head">
		<h2 class="vd-section__title">
			<?php
			if ( $icon ) {
				verdora_the_icon( $icon, 22 );
			}
			echo esc_html( $title );
			?>
		</h2>
		<?php echo $extra; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by caller. ?>
		<?php if ( $link ) : ?>
			<a class="vd-section__link" href="<?php echo esc_url( $link ); ?>">
				<?php esc_html_e( 'See all', 'verdora' ); ?>
				<?php verdora_the_icon( 'chevron-right', 16 ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Configured social links (network => url).
 *
 * @return array<string,string>
 */
function verdora_social_links() {
	$links = array();
	foreach ( array( 'instagram', 'facebook', 'tiktok', 'youtube', 'x' ) as $network ) {
		$url = verdora_option( 'social_' . $network );
		if ( $url ) {
			$links[ $network ] = $url;
		}
	}
	return $links;
}

/**
 * Copyright line with {year} and {site} placeholders replaced
 * ({tahun} and {situs} from earlier versions still work).
 *
 * @return string Plain text.
 */
function verdora_copyright_text() {
	return strtr(
		(string) verdora_option( 'footer_copyright' ),
		array(
			'{year}'  => wp_date( 'Y' ),
			'{site}'  => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'{tahun}' => wp_date( 'Y' ),
			'{situs}' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		)
	);
}

/**
 * Primary navigation fallback (no menu assigned): top-level product categories,
 * with their subcategories as a dropdown, in the same markup as a nav menu.
 */
function verdora_primary_menu_fallback() {
	$terms   = verdora_get_product_categories( 10 );
	$current = is_tax( 'product_cat' ) ? (int) get_queried_object_id() : 0;

	echo '<ul class="vd-catnav__list">';
	foreach ( $terms as $term ) {
		$link = get_term_link( $term );
		if ( is_wp_error( $link ) ) {
			continue;
		}
		$children = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => $term->term_id,
				'hide_empty' => true,
				'number'     => 12,
			)
		);
		$children = is_wp_error( $children ) ? array() : $children;

		$classes = array( 'menu-item' );
		if ( $children ) {
			$classes[] = 'menu-item-has-children';
		}
		if ( $current === (int) $term->term_id ) {
			$classes[] = 'current-menu-item';
		}

		printf(
			'<li class="%1$s"><a href="%2$s"%3$s>%4$s</a>',
			esc_attr( implode( ' ', $classes ) ),
			esc_url( $link ),
			$children ? ' aria-haspopup="true" aria-expanded="false"' : '',
			esc_html( $term->name )
		);
		if ( $children ) {
			echo '<ul class="sub-menu">';
			foreach ( $children as $child ) {
				$child_link = get_term_link( $child );
				if ( ! is_wp_error( $child_link ) ) {
					printf( '<li class="menu-item"><a href="%1$s">%2$s</a></li>', esc_url( $child_link ), esc_html( $child->name ) );
				}
			}
			echo '</ul>';
		}
		echo '</li>';
	}
	echo '</ul>';
}
