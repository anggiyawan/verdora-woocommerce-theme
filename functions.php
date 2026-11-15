<?php
/**
 * Verdora theme bootstrap.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

define( 'VERDORA_VERSION', '1.0.0' );
define( 'VERDORA_DIR', get_template_directory() );
define( 'VERDORA_URI', get_template_directory_uri() );

require VERDORA_DIR . '/inc/setup.php';
require VERDORA_DIR . '/inc/icons.php';
require VERDORA_DIR . '/inc/template-tags.php';
require VERDORA_DIR . '/inc/customizer.php';
require VERDORA_DIR . '/inc/dynamic-css.php';
require VERDORA_DIR . '/inc/enqueue.php';

if ( class_exists( 'WooCommerce' ) ) {
	require VERDORA_DIR . '/inc/woocommerce.php';
	require VERDORA_DIR . '/inc/home.php';
}
