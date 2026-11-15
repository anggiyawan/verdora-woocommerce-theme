<?php
/**
 * WhatsApp chat: floating button, "Ask via WhatsApp" on product pages and a
 * shortcut in the Verdora home quick menu.
 *
 * @package Verdora_Companion
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

/**
 * WhatsApp number in international format (62xxx), or '' if not set.
 * Local numbers starting with 0 or 8 are treated as Indonesian (+62).
 *
 * @return string
 */
function verdora_companion_wa_number() {
	$number = preg_replace( '/\D+/', '', (string) verdora_companion_option( 'wa_number' ) );
	if ( '' === $number ) {
		return '';
	}
	if ( 0 === strpos( $number, '0' ) ) {
		$number = '62' . substr( $number, 1 );
	} elseif ( 0 === strpos( $number, '8' ) ) {
		$number = '62' . $number;
	}
	return strlen( $number ) >= 9 ? $number : '';
}

/**
 * The wa.me link with the configured greeting (plus product info when given).
 *
 * @param WC_Product|null $product Product to ask about.
 * @return string
 */
function verdora_companion_wa_url( $product = null ) {
	$number = verdora_companion_wa_number();
	if ( ! $number ) {
		return '';
	}
	$site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$message = strtr(
		(string) verdora_companion_option( 'wa_message' ),
		array(
			'{site}'  => $site,
			'{situs}' => $site,
		)
	);
	if ( $product instanceof WC_Product ) {
		/* translators: %s: product name */
		$message .= "\n\n" . sprintf( __( 'Product: %s', 'verdora-companion' ), wp_specialchars_decode( $product->get_name(), ENT_QUOTES ) ) . "\n" . get_permalink( $product->get_id() );
	}
	$message = trim( $message );
	return 'https://wa.me/' . $number . ( $message ? '?text=' . rawurlencode( $message ) : '' );
}

/**
 * Escape a wa.me link for an href attribute.
 *
 * esc_url() strips encoded line breaks (%0A), which would glue the greeting and the
 * product line together. The link is built only from a fixed host, digits and a
 * rawurlencode()d message, so attribute escaping is enough; anything else is passed
 * through esc_url().
 *
 * @param string $url Link from verdora_companion_wa_url().
 * @return string
 */
function verdora_companion_esc_wa_url( $url ) {
	if ( preg_match( '#^https://wa\.me/\d+(\?text=[A-Za-z0-9%._~-]*)?$#', $url ) ) {
		return esc_attr( $url );
	}
	return esc_url( $url );
}

/**
 * Chat icon: the theme's icon set when available, otherwise the same inline SVG.
 *
 * @param int $size Pixel size.
 * @return string
 */
function verdora_companion_chat_icon( $size = 22 ) {
	if ( function_exists( 'verdora_icon' ) ) {
		return verdora_icon( 'chat', $size );
	}
	return sprintf(
		'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M9.5 9.5c0 2.5 2.5 5 5 5l1-1.5-2-1-1 .8a3.5 3.5 0 0 1-2.3-2.3l.8-1-1-2z"/></svg>',
		(int) $size
	);
}

/*
 * -------------------------------------------------------------------------
 * Customizer
 * -------------------------------------------------------------------------
 */

/**
 * Register the WhatsApp section.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function verdora_companion_wa_customize( $wp_customize ) {
	$defaults = verdora_companion_defaults();
	$wp_customize->add_section(
		'verdora_companion_whatsapp',
		array(
			'title'       => __( 'WhatsApp', 'verdora-companion' ),
			'description' => __( 'Added by the Verdora Companion plugin.', 'verdora-companion' ),
			'panel'       => verdora_companion_panel( $wp_customize ),
			'priority'    => 75,
		)
	);

	$has_number = function () {
		return '' !== verdora_companion_wa_number();
	};

	$fields = array(
		'wa_number'         => array(
			'type'        => 'text',
			'label'       => __( 'WhatsApp number', 'verdora-companion' ),
			'description' => __( 'International format such as 6281234567890. Local Indonesian numbers (08…) are converted automatically. Leave empty to turn off all WhatsApp features.', 'verdora-companion' ),
			'sanitize'    => 'sanitize_text_field',
		),
		'wa_float'          => array(
			'type'     => 'checkbox',
			'label'    => __( 'Show the floating chat button', 'verdora-companion' ),
			'sanitize' => 'verdora_companion_sanitize_checkbox',
		),
		'wa_position'       => array(
			'type'     => 'select',
			'label'    => __( 'Floating button position', 'verdora-companion' ),
			'choices'  => array(
				'right' => __( 'Bottom right', 'verdora-companion' ),
				'left'  => __( 'Bottom left', 'verdora-companion' ),
			),
			'sanitize' => 'verdora_companion_sanitize_position',
		),
		'wa_label'          => array(
			'type'     => 'text',
			'label'    => __( 'Floating button text (desktop)', 'verdora-companion' ),
			'sanitize' => 'sanitize_text_field',
		),
		'wa_product_button' => array(
			'type'     => 'checkbox',
			'label'    => __( 'Show an "Ask via WhatsApp" button on product pages', 'verdora-companion' ),
			'sanitize' => 'verdora_companion_sanitize_checkbox',
		),
		'wa_message'        => array(
			'type'        => 'textarea',
			'label'       => __( 'Greeting message', 'verdora-companion' ),
			'description' => __( '{site} is replaced with the store name. On product pages the product name and link are added automatically.', 'verdora-companion' ),
			'sanitize'    => 'sanitize_textarea_field',
		),
	);

	foreach ( $fields as $key => $field ) {
		$id = 'verdora_' . $key;
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => $field['sanitize'],
			)
		);
		$control = array(
			'type'        => $field['type'],
			'label'       => $field['label'],
			'description' => isset( $field['description'] ) ? $field['description'] : '',
			'section'     => 'verdora_companion_whatsapp',
		);
		if ( isset( $field['choices'] ) ) {
			$control['choices'] = $field['choices'];
		}
		if ( 'wa_number' !== $key ) {
			$control['active_callback'] = $has_number;
		}
		$wp_customize->add_control( $id, $control );
	}
}
add_action( 'customize_register', 'verdora_companion_wa_customize', 20 );

/**
 * Sanitize the floating button position.
 *
 * @param string $value Raw value.
 * @return string
 */
function verdora_companion_sanitize_position( $value ) {
	return 'left' === $value ? 'left' : 'right';
}

/*
 * -------------------------------------------------------------------------
 * Front end
 * -------------------------------------------------------------------------
 */

/**
 * Whether the floating button is shown.
 *
 * @return bool
 */
function verdora_companion_wa_float_enabled() {
	return verdora_companion_wa_number() && verdora_companion_option( 'wa_float' );
}

/**
 * Styles, only when WhatsApp is configured.
 */
function verdora_companion_wa_assets() {
	if ( ! verdora_companion_wa_number() ) {
		return;
	}
	wp_enqueue_style( 'verdora-companion', VERDORA_COMPANION_URL . 'assets/companion.css', array(), VERDORA_COMPANION_VERSION );
}
add_action( 'wp_enqueue_scripts', 'verdora_companion_wa_assets', 20 );

/**
 * Body classes so the theme can make room for the floating button.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function verdora_companion_wa_body_class( $classes ) {
	if ( verdora_companion_wa_float_enabled() ) {
		$classes[] = 'vd-has-wa';
		$classes[] = 'left' === verdora_companion_option( 'wa_position' ) ? 'vd-wa-left' : 'vd-wa-right';
	}
	return $classes;
}
add_filter( 'body_class', 'verdora_companion_wa_body_class' );

/**
 * Floating chat button.
 */
function verdora_companion_wa_float() {
	if ( ! verdora_companion_wa_float_enabled() ) {
		return;
	}
	$product = ( function_exists( 'is_product' ) && is_product() ) ? wc_get_product( get_queried_object_id() ) : null;
	$url     = verdora_companion_wa_url( $product );
	if ( ! $url ) {
		return;
	}
	$label = (string) verdora_companion_option( 'wa_label' );
	printf(
		'<a class="vd-wa" href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s">%3$s%4$s</a>',
		verdora_companion_esc_wa_url( $url ),
		esc_attr__( 'Chat via WhatsApp', 'verdora-companion' ),
		verdora_companion_chat_icon( 22 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
		'' !== $label ? '<span>' . esc_html( $label ) . '</span>' : ''
	);
}
add_action( 'wp_footer', 'verdora_companion_wa_float', 5 );

/**
 * "Ask via WhatsApp" button under the add-to-cart form.
 */
function verdora_companion_wa_product_button() {
	global $product;
	if ( ! verdora_companion_option( 'wa_product_button' ) ) {
		return;
	}
	$url = verdora_companion_wa_url( $product );
	if ( ! $url ) {
		return;
	}
	printf(
		'<a class="vd-btn vd-btn--wa" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s%3$s</a>',
		verdora_companion_esc_wa_url( $url ),
		verdora_companion_chat_icon( 18 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
		esc_html__( 'Ask via WhatsApp', 'verdora-companion' )
	);
}
add_action( 'woocommerce_single_product_summary', 'verdora_companion_wa_product_button', 32 );

/**
 * "Chat seller" shortcut in the Verdora home quick menu.
 *
 * @param array[] $links Quick links.
 * @return array[]
 */
function verdora_companion_wa_quick_link( $links ) {
	$url = verdora_companion_wa_url();
	if ( $url ) {
		$links['chat'] = array(
			'label'    => __( 'Chat Seller', 'verdora-companion' ),
			'url'      => $url,
			'icon'     => 'chat',
			'color'    => 'wa',
			'external' => true,
		);
	}
	return $links;
}
add_filter( 'verdora_quick_links', 'verdora_companion_wa_quick_link' );
