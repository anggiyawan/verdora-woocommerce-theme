<?php
/**
 * Plugin Name:       Verdora Companion
 * Plugin URI:        https://github.com/anggiyawan/verdora-woocommerce-theme
 * Description:       Optional extras for the Verdora theme: WhatsApp chat buttons, basic SEO meta tags and a one-click demo product importer.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Priyana Anggiyawan
 * Author URI:        https://github.com/anggiyawan
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       verdora-companion
 * Domain Path:       /languages
 *
 * @package Verdora_Companion
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

define( 'VERDORA_COMPANION_VERSION', '1.0.0' );
define( 'VERDORA_COMPANION_DIR', plugin_dir_path( __FILE__ ) );
define( 'VERDORA_COMPANION_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load translations bundled with the plugin.
 */
function verdora_companion_load_textdomain() {
	load_plugin_textdomain( 'verdora-companion', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'verdora_companion_load_textdomain' );

/**
 * Default values for the settings this plugin adds.
 *
 * @return array<string,mixed>
 */
function verdora_companion_defaults() {
	return array(
		'wa_number'         => '',
		'wa_float'          => true,
		'wa_position'       => 'right',
		'wa_label'          => __( 'Chat with us', 'verdora-companion' ),
		'wa_product_button' => true,
		'wa_message'        => __( 'Hi {site}, I have a question.', 'verdora-companion' ),
		'seo_meta'          => true,
	);
}

/**
 * Read a setting. Stored as theme mods with the "verdora_" prefix so values saved
 * by earlier Verdora versions (where these lived in the theme) keep working.
 *
 * @param string $key Setting key without prefix.
 * @return mixed
 */
function verdora_companion_option( $key ) {
	$defaults = verdora_companion_defaults();
	return get_theme_mod( 'verdora_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/**
 * Customizer panel to place our sections in: the Verdora theme panel when present.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 * @return string Panel ID or '' for top level.
 */
function verdora_companion_panel( $wp_customize ) {
	return $wp_customize->get_panel( 'verdora' ) ? 'verdora' : '';
}

/**
 * Sanitize a checkbox value.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function verdora_companion_sanitize_checkbox( $value ) {
	return (bool) $value && 'false' !== $value;
}

require VERDORA_COMPANION_DIR . 'includes/whatsapp.php';
require VERDORA_COMPANION_DIR . 'includes/seo.php';

/**
 * Demo importer: admin only, and only with WooCommerce (it loads after this plugin).
 */
function verdora_companion_load_admin() {
	if ( is_admin() && class_exists( 'WooCommerce' ) ) {
		require VERDORA_COMPANION_DIR . 'includes/demo-import.php';
	}
}
add_action( 'plugins_loaded', 'verdora_companion_load_admin' );
