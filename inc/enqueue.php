<?php
/**
 * Styles and scripts.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache-busting version for a theme asset.
 *
 * @param string $path Path relative to the theme root.
 * @return string
 */
function verdora_asset_version( $path ) {
	$file = VERDORA_DIR . '/' . ltrim( $path, '/' );
	return file_exists( $file ) ? VERDORA_VERSION . '.' . filemtime( $file ) : VERDORA_VERSION;
}

/**
 * Google Fonts URL.
 *
 * @return string
 */
function verdora_fonts_url() {
	$fonts = verdora_font_choices();
	$font  = verdora_option( 'font' );
	if ( 'system' === $font ) {
		return '';
	}
	$query = isset( $fonts[ $font ] ) ? $fonts[ $font ][1] : $fonts['plus-jakarta'][1];
	return 'https://fonts.googleapis.com/css2?family=' . $query . '&display=swap';
}

/**
 * Enqueue front-end assets.
 */
function verdora_enqueue_assets() {
	if ( verdora_fonts_url() ) {
		wp_enqueue_style( 'verdora-fonts', verdora_fonts_url(), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}
	wp_enqueue_style( 'verdora-main', VERDORA_URI . '/assets/css/main.css', array(), verdora_asset_version( 'assets/css/main.css' ) );

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'verdora-woocommerce', VERDORA_URI . '/assets/css/woocommerce.css', array( 'verdora-main' ), verdora_asset_version( 'assets/css/woocommerce.css' ) );
		// Keeps the header cart count in sync after AJAX add-to-cart.
		wp_enqueue_script( 'wc-cart-fragments' );
	}

	wp_enqueue_script(
		'verdora-main',
		VERDORA_URI . '/assets/js/main.js',
		array(),
		verdora_asset_version( 'assets/js/main.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
	wp_localize_script(
		'verdora-main',
		'verdoraConfig',
		array(
			'i18n' => array(
				'ended'    => __( 'Ended', 'verdora' ),
				'decrease' => __( 'Decrease quantity', 'verdora' ),
				'increase' => __( 'Increase quantity', 'verdora' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'verdora_enqueue_assets' );

/**
 * Flag JS support before paint so reveal animations never flash.
 * A timeout fallback reveals content if the main script fails to load.
 */
function verdora_js_flag() {
	wp_print_inline_script_tag(
		"document.documentElement.classList.add('vd-js');setTimeout(function(){document.querySelectorAll('.vd-reveal').forEach(function(e){e.classList.add('is-visible')})},3000);"
	);
}
add_action( 'wp_head', 'verdora_js_flag', 1 );

/**
 * Preconnect to Google Fonts.
 *
 * @param array  $urls          URLs.
 * @param string $relation_type Relation type.
 * @return array
 */
function verdora_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type && verdora_fonts_url() ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'verdora_resource_hints', 10, 2 );
