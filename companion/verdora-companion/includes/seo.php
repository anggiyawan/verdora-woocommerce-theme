<?php
/**
 * Lightweight SEO output: meta description and Open Graph tags.
 * Skipped automatically when a dedicated SEO plugin is active.
 *
 * @package Verdora_Companion
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a dedicated SEO plugin handles meta tags.
 *
 * @return bool
 */
function verdora_companion_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || class_exists( 'The_SEO_Framework\Load' );
}

/**
 * Register the setting.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function verdora_companion_seo_customize( $wp_customize ) {
	$wp_customize->add_section(
		'verdora_companion_seo',
		array(
			'title'       => __( 'SEO Meta Tags', 'verdora-companion' ),
			'description' => __( 'Added by the Verdora Companion plugin.', 'verdora-companion' ),
			'panel'       => verdora_companion_panel( $wp_customize ),
			'priority'    => 95,
		)
	);
	$wp_customize->add_setting(
		'verdora_seo_meta',
		array(
			'default'           => true,
			'sanitize_callback' => 'verdora_companion_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'verdora_seo_meta',
		array(
			'type'        => 'checkbox',
			'label'       => __( 'Add a meta description and Open Graph tags', 'verdora-companion' ),
			'description' => __( 'Skipped automatically when an SEO plugin (Yoast, Rank Math, AIOSEO, SEOPress, The SEO Framework) is active.', 'verdora-companion' ),
			'section'     => 'verdora_companion_seo',
		)
	);
}
add_action( 'customize_register', 'verdora_companion_seo_customize', 20 );

/**
 * Plain-text description for the current view.
 *
 * @return string
 */
function verdora_companion_meta_description() {
	$text = '';

	if ( is_front_page() || is_home() ) {
		$text = get_bloginfo( 'description' );
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		if ( function_exists( 'wc_get_product' ) && is_singular( 'product' ) ) {
			$product = wc_get_product( $post->ID );
			$text    = $product ? ( $product->get_short_description() ? $product->get_short_description() : $product->get_description() ) : '';
		} else {
			$text = has_excerpt( $post ) ? $post->post_excerpt : $post->post_content;
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$text = term_description();
	}

	// Space before each tag so list items and paragraphs don't run together once tags are stripped.
	$text = wp_strip_all_tags( str_replace( '<', ' <', strip_shortcodes( (string) $text ) ), true );
	$text = trim( preg_replace( '/\s+/', ' ', $text ) );

	return $text ? wp_html_excerpt( $text, 158, '…' ) : '';
}

/**
 * Print the meta description and Open Graph tags.
 */
function verdora_companion_head_meta() {
	if ( verdora_companion_seo_plugin_active() || ! verdora_companion_option( 'seo_meta' ) ) {
		return;
	}

	$description = verdora_companion_meta_description();
	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}

	$og = array(
		'og:site_name' => get_bloginfo( 'name' ),
		'og:locale'    => get_locale(),
		'og:type'      => is_singular( 'product' ) ? 'product' : ( is_singular() ? 'article' : 'website' ),
		'og:title'     => wp_get_document_title(),
		'og:url'       => is_singular() ? get_permalink() : home_url( '/' . ( isset( $GLOBALS['wp']->request ) ? $GLOBALS['wp']->request : '' ) ),
	);
	if ( $description ) {
		$og['og:description'] = $description;
	}
	if ( is_singular() && has_post_thumbnail() ) {
		$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
		if ( $image ) {
			$og['og:image'] = $image[0];
		}
	}

	foreach ( $og as $property => $content ) {
		printf( '<meta property="%1$s" content="%2$s">' . "\n", esc_attr( $property ), esc_attr( $content ) );
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action( 'wp_head', 'verdora_companion_head_meta', 2 );
