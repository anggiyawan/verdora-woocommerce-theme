<?php
/**
 * Turns Customizer options into CSS custom properties.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hex to RGB.
 *
 * @param string $hex Hex color.
 * @return int[] r, g, b
 */
function verdora_hex_to_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return array( 0, 0, 0 );
	}
	return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
}

/**
 * Mix a color with another.
 *
 * @param string $hex    Base color.
 * @param string $with   Color to mix in.
 * @param float  $weight 0..1 amount of $with.
 * @return string Hex color.
 */
function verdora_mix( $hex, $with, $weight ) {
	$a = verdora_hex_to_rgb( $hex );
	$b = verdora_hex_to_rgb( $with );
	$c = array();
	for ( $i = 0; $i < 3; $i++ ) {
		$c[ $i ] = (int) round( $a[ $i ] + ( $b[ $i ] - $a[ $i ] ) * $weight );
	}
	return sprintf( '#%02x%02x%02x', $c[0], $c[1], $c[2] );
}

/**
 * Color option with fallback to its default if empty/invalid.
 *
 * @param string $key Option key.
 * @return string
 */
function verdora_color_option( $key ) {
	$value    = sanitize_hex_color( (string) verdora_option( $key ) );
	$defaults = verdora_defaults();
	return $value ? strtolower( $value ) : $defaults[ $key ];
}

/**
 * Google Fonts family map.
 *
 * @return array<string,array{0:string,1:string}> key => (CSS family, Google query)
 */
function verdora_font_choices() {
	return array(
		'plus-jakarta' => array( 'Plus Jakarta Sans', 'Plus+Jakarta+Sans:wght@400;500;600;700;800' ),
		'poppins'      => array( 'Poppins', 'Poppins:wght@400;500;600;700;800' ),
		'inter'        => array( 'Inter', 'Inter:wght@400;500;600;700;800' ),
		'nunito'       => array( 'Nunito', 'Nunito:wght@400;500;600;700;800' ),
		'dm-sans'      => array( 'DM Sans', 'DM+Sans:wght@400;500;600;700;800' ),
	);
}

/**
 * Build the dynamic CSS.
 *
 * @return string
 */
function verdora_dynamic_css() {
	$defaults = verdora_defaults();
	$vars     = array();

	// Primary + derived shades. Defaults keep the hand-tuned palette in main.css.
	$primary = verdora_color_option( 'color_primary' );
	if ( $primary !== $defaults['color_primary'] ) {
		$vars['--vd-primary']     = $primary;
		$vars['--vd-primary-600'] = verdora_mix( $primary, '#000000', .15 );
		$vars['--vd-primary-700'] = verdora_mix( $primary, '#000000', .32 );
		$vars['--vd-primary-800'] = verdora_mix( $primary, '#000000', .48 );
		$vars['--vd-primary-50']  = verdora_mix( $primary, '#ffffff', .92 );
		$vars['--vd-primary-100'] = verdora_mix( $primary, '#ffffff', .80 );
		$vars['--vd-primary-200'] = verdora_mix( $primary, '#ffffff', .62 );
		$vars['--vd-primary-rgb'] = implode( ', ', verdora_hex_to_rgb( $primary ) );
	}

	$accent = verdora_color_option( 'color_accent' );
	if ( $accent !== $defaults['color_accent'] ) {
		$vars['--vd-accent']     = $accent;
		$vars['--vd-accent-50']  = verdora_mix( $accent, '#ffffff', .92 );
		$vars['--vd-accent-100'] = verdora_mix( $accent, '#ffffff', .78 );
		$vars['--vd-star']       = $accent;
	}

	$sale = verdora_color_option( 'color_sale' );
	if ( $sale !== $defaults['color_sale'] ) {
		$vars['--vd-danger']    = $sale;
		$vars['--vd-danger-50'] = verdora_mix( $sale, '#ffffff', .93 );
	}

	$bg = verdora_color_option( 'color_bg' );
	if ( $bg !== $defaults['color_bg'] ) {
		$vars['--vd-bg'] = $bg;
	}

	// Typography.
	$fonts = verdora_font_choices();
	$font  = verdora_option( 'font' );
	if ( 'system' === $font ) {
		$vars['--vd-font'] = 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
	} elseif ( isset( $fonts[ $font ] ) && 'plus-jakarta' !== $font ) {
		$vars['--vd-font'] = '"' . $fonts[ $font ][0] . '", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif';
	}

	// Shape & width.
	$radii = array(
		'sharp' => array( '6px', '10px', '14px' ),
		'round' => array( '14px', '22px', '30px' ),
	);
	$radius = verdora_option( 'radius' );
	if ( isset( $radii[ $radius ] ) ) {
		list( $vars['--vd-radius-sm'], $vars['--vd-radius'], $vars['--vd-radius-lg'] ) = $radii[ $radius ];
	}
	$container = absint( verdora_option( 'container' ) );
	if ( $container && 1240 !== $container ) {
		$vars['--vd-container'] = $container . 'px';
	}

	// Product grid.
	$vars['--vd-cols-home']   = (string) absint( verdora_option( 'home_columns' ) );
	$vars['--vd-cols-shop']   = (string) absint( verdora_option( 'shop_columns' ) );
	$vars['--vd-cols-mobile'] = (string) absint( verdora_option( 'mobile_columns' ) );
	$ratio                    = (string) verdora_option( 'card_ratio' );
	if ( preg_match( '#^\d+ / \d+$#', $ratio ) ) {
		$vars['--vd-card-ratio'] = $ratio;
	}
	if ( 'contain' === verdora_option( 'card_fit' ) ) {
		$vars['--vd-card-fit'] = 'contain';
		$vars['--vd-card-pad'] = '8%';
		$vars['--vd-card-bg']  = '#ffffff';
	}

	// Hero banner.
	$heights = array(
		'compact' => '240px',
		'tall'    => '380px',
	);
	$height  = verdora_option( 'hero_height' );
	if ( isset( $heights[ $height ] ) ) {
		$vars['--vd-hero-min-h'] = $heights[ $height ];
	}

	$start    = verdora_sanitize_color( verdora_option( 'hero_bg_start' ) );
	$end      = verdora_sanitize_color( verdora_option( 'hero_bg_end' ) );
	$image_id = absint( verdora_option( 'hero_bg_image' ) );
	$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
	if ( $start || $end || $image ) {
		$layers = array();
		if ( $image ) {
			$alpha    = min( 90, max( 0, (int) verdora_option( 'hero_overlay' ) ) ) / 100;
			$tint     = 'dark' === verdora_option( 'hero_text' ) ? '255, 255, 255' : '0, 0, 0';
			$layers[] = sprintf( 'linear-gradient(rgba(%1$s, %2$s), rgba(%1$s, %2$s))', $tint, $alpha );
			$layers[] = 'url("' . esc_url_raw( $image ) . '") center / cover no-repeat';
		}
		$layers[]             = sprintf(
			'linear-gradient(135deg, %1$s 0%%, %2$s 100%%)',
			$start ? $start : 'var(--vd-primary)',
			$end ? $end : 'var(--vd-primary-800)'
		);
		$vars['--vd-hero-bg'] = implode( ', ', $layers );
	}

	$css = '';
	foreach ( $vars as $name => $value ) {
		$css .= $name . ':' . $value . ';';
	}
	return ':root{' . wp_strip_all_tags( $css ) . '}';
}

/**
 * Attach the dynamic CSS after the main stylesheet.
 */
function verdora_enqueue_dynamic_css() {
	wp_add_inline_style( 'verdora-main', verdora_dynamic_css() );
}
add_action( 'wp_enqueue_scripts', 'verdora_enqueue_dynamic_css', 20 );

/**
 * Browser UI color (mobile address bar) follows the primary color.
 */
function verdora_theme_color_meta() {
	printf( '<meta name="theme-color" content="%s">' . "\n", esc_attr( verdora_color_option( 'color_primary' ) ) );
}
add_action( 'wp_head', 'verdora_theme_color_meta', 2 );
