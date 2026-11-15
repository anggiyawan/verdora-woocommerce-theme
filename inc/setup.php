<?php
/**
 * Theme setup: supports, menus, sidebars.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports and menus.
 */
function verdora_setup() {
	load_theme_textdomain( 'verdora', VERDORA_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor-style.css' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 48,
			'width'       => 180,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'editor-color-palette',
		array(
			array(
				'name'  => __( 'Emerald', 'verdora' ),
				'slug'  => 'emerald',
				'color' => '#059669',
			),
			array(
				'name'  => __( 'Dark Emerald', 'verdora' ),
				'slug'  => 'emerald-dark',
				'color' => '#065f46',
			),
			array(
				'name'  => __( 'Mint', 'verdora' ),
				'slug'  => 'mint',
				'color' => '#ecfdf5',
			),
			array(
				'name'  => __( 'Amber', 'verdora' ),
				'slug'  => 'amber',
				'color' => '#f59e0b',
			),
			array(
				'name'  => __( 'Text', 'verdora' ),
				'slug'  => 'ink',
				'color' => '#0f172a',
			),
			array(
				'name'  => __( 'White', 'verdora' ),
				'slug'  => 'white',
				'color' => '#ffffff',
			),
		)
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width'         => 420,
			'single_image_width'            => 800,
			'gallery_thumbnail_image_width' => 160,
			'product_grid'                  => array(
				'default_rows'    => 5,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 6,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary'           => __( 'Primary Navigation (below search, supports dropdowns)', 'verdora' ),
			'topbar'            => __( 'Top Bar (very top row)', 'verdora' ),
			'footer_categories' => __( 'Footer: Categories Column', 'verdora' ),
			'footer'            => __( 'Footer: Help Column', 'verdora' ),
		)
	);
}
add_action( 'after_setup_theme', 'verdora_setup' );

/**
 * Accessible dropdown attributes for top-level navbar items that have a submenu.
 *
 * @param array    $atts  Link attributes.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  Menu arguments.
 * @param int      $depth Depth.
 * @return array
 */
function verdora_nav_link_attributes( $atts, $item, $args, $depth ) {
	if ( 0 === (int) $depth && isset( $args->menu_class ) && 'vd-catnav__list' === $args->menu_class && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		$atts['aria-haspopup'] = 'true';
		$atts['aria-expanded'] = 'false';
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'verdora_nav_link_attributes', 10, 4 );

/**
 * Content width for embeds.
 */
function verdora_content_width() {
	$GLOBALS['content_width'] = 1240;
}
add_action( 'after_setup_theme', 'verdora_content_width', 0 );

/**
 * Widget areas.
 */
function verdora_widgets_init() {
	$shared = array(
		'before_widget' => '<section id="%1$s" class="vd-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="vd-widget__title">',
		'after_title'   => '</h2>',
	);

	register_sidebar(
		array_merge(
			$shared,
			array(
				'name'        => __( 'Shop Sidebar', 'verdora' ),
				'id'          => 'shop-sidebar',
				'description' => __( 'Shown on the Shop and category pages. If empty, the theme shows built-in category and price filters.', 'verdora' ),
			)
		)
	);

	register_sidebar(
		array_merge(
			$shared,
			array(
				'name'        => __( 'Blog Sidebar', 'verdora' ),
				'id'          => 'blog-sidebar',
				'description' => __( 'Shown on the blog and single posts.', 'verdora' ),
			)
		)
	);
}
add_action( 'widgets_init', 'verdora_widgets_init' );

/**
 * Extra body classes.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function verdora_body_classes( $classes ) {
	$classes[] = class_exists( 'WooCommerce' ) ? 'vd-has-wc' : 'vd-no-wc';
	if ( is_active_sidebar( 'blog-sidebar' ) && ( is_home() || is_singular( 'post' ) || is_archive() ) && ! verdora_is_wc_page() ) {
		$classes[] = 'vd-has-sidebar';
	}

	// Classes driven by Customizer options.
	if ( ! verdora_option( 'header_sticky' ) ) {
		$classes[] = 'vd-header-static';
	}
	if ( ! verdora_option( 'animations' ) ) {
		$classes[] = 'vd-no-motion';
	}
	if ( ! verdora_option( 'single_sticky_gallery' ) ) {
		$classes[] = 'vd-gallery-static';
	}
	if ( class_exists( 'WooCommerce' ) && verdora_option( 'bottom_nav' ) ) {
		$classes[] = 'vd-has-bottomnav';
	}
	return $classes;
}
add_filter( 'body_class', 'verdora_body_classes' );

/**
 * Shorter excerpts for post cards.
 *
 * @return int
 */
function verdora_excerpt_length() {
	return 22;
}
add_filter( 'excerpt_length', 'verdora_excerpt_length' );
