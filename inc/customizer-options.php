<?php
/**
 * Theme settings schema. Every Customizer section, control, default and
 * sanitizer is generated from this single list.
 *
 * Field keys:
 *   type        checkbox|text|textarea|url|email|number|range|select|color|media|datetime|heading
 *   label       Control label.
 *   default     Default value.
 *   description Optional help text.
 *   choices     For select.
 *   attrs       input_attrs (min/max/step) for number/range.
 *   show_if     array( other_key, expected ) — expected `true` means "has a value".
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fields for the uploaded banner slides (4 × image, mobile image, link).
 *
 * @return array<string,array>
 */
function verdora_slide_image_fields() {
	$fields = array(
		'h_hero_images' => array(
			'type'        => 'heading',
			'label'       => __( 'Image slides (your own banners)', 'verdora' ),
			'description' => __( 'Upload finished banners that already contain your text and design. Recommended size: desktop 1600 × 600 px, mobile 800 × 400 px. Alternative text comes from the image\'s "Alt Text" field in the Media Library.', 'verdora' ),
		),
	);
	for ( $n = 1; $n <= 4; $n++ ) {
		$fields[ "slide{$n}_image" ]        = array(
			'type'    => 'media',
			/* translators: %d: slide number */
			'label'   => sprintf( __( 'Slide %d: desktop image', 'verdora' ), $n ),
			'default' => 0,
		);
		$fields[ "slide{$n}_image_mobile" ] = array(
			'type'        => 'media',
			/* translators: %d: slide number */
			'label'       => sprintf( __( 'Slide %d: mobile image (optional)', 'verdora' ), $n ),
			'default'     => 0,
			'description' => __( 'If empty, the desktop image is used on mobile too.', 'verdora' ),
			'show_if'     => array( "slide{$n}_image", true ),
		);
		$fields[ "slide{$n}_url" ]          = array(
			'type'    => 'url',
			/* translators: %d: slide number */
			'label'   => sprintf( __( 'Slide %d: link', 'verdora' ), $n ),
			'default' => '',
			'show_if' => array( "slide{$n}_image", true ),
		);
	}
	return $fields;
}

/**
 * Settings schema grouped by Customizer section.
 *
 * @return array<string,array>
 */
function verdora_customizer_schema() {
	static $schema = null;
	if ( null !== $schema ) {
		return $schema;
	}

	$order_attrs = array(
		'min'  => 1,
		'max'  => 9,
		'step' => 1,
	);

	$schema = array(

		'verdora_general'  => array(
			'title'       => __( 'General', 'verdora' ),
			'description' => __( 'Colors, fonts and basic shapes used across the whole site.', 'verdora' ),
			'fields'      => array(
				'h_colors'      => array(
					'type'  => 'heading',
					'label' => __( 'Colors', 'verdora' ),
				),
				'color_primary' => array(
					'type'        => 'color',
					'label'       => __( 'Primary color', 'verdora' ),
					'default'     => '#059669',
					'description' => __( 'Buttons, links, top bar, footer and banner. Lighter and darker shades are calculated automatically.', 'verdora' ),
				),
				'color_accent'  => array(
					'type'        => 'color',
					'label'       => __( 'Accent color', 'verdora' ),
					'default'     => '#f59e0b',
					'description' => __( 'Rating stars, the yellow promo box and decorations.', 'verdora' ),
				),
				'color_sale'    => array(
					'type'        => 'color',
					'label'       => __( 'Sale & notification color', 'verdora' ),
					'default'     => '#e11d48',
					'description' => __( 'Sale badges and the number on the cart icon.', 'verdora' ),
				),
				'color_bg'      => array(
					'type'    => 'color',
					'label'   => __( 'Page background color', 'verdora' ),
					'default' => '#f3f6f4',
				),
				'h_shape'       => array(
					'type'  => 'heading',
					'label' => __( 'Fonts & shapes', 'verdora' ),
				),
				'font'          => array(
					'type'        => 'select',
					'label'       => __( 'Font', 'verdora' ),
					'default'     => 'plus-jakarta',
					'choices'     => array(
						'plus-jakarta' => 'Plus Jakarta Sans',
						'poppins'      => 'Poppins',
						'inter'        => 'Inter',
						'nunito'       => 'Nunito',
						'dm-sans'      => 'DM Sans',
						'system'       => __( 'Device default font (lightest)', 'verdora' ),
					),
					'description' => __( 'The "Device default font" option does not load Google Fonts at all.', 'verdora' ),
				),
				'radius'        => array(
					'type'    => 'select',
					'label'   => __( 'Corner style for cards & buttons', 'verdora' ),
					'default' => 'medium',
					'choices' => array(
						'sharp'  => __( 'Sharp (slightly rounded)', 'verdora' ),
						'medium' => __( 'Medium', 'verdora' ),
						'round'  => __( 'Very round', 'verdora' ),
					),
				),
				'container'     => array(
					'type'    => 'select',
					'label'   => __( 'Maximum content width', 'verdora' ),
					'default' => '1240',
					'choices' => array(
						'1140' => __( 'Narrow (1140 px)', 'verdora' ),
						'1240' => __( 'Normal (1240 px)', 'verdora' ),
						'1400' => __( 'Wide (1400 px)', 'verdora' ),
					),
				),
				'animations'    => array(
					'type'        => 'checkbox',
					'label'       => __( 'Enable animations', 'verdora' ),
					'default'     => true,
					'description' => __( 'Reveal-on-scroll effects and floating decorations in the banner. Hover effects stay on.', 'verdora' ),
				),
			),
		),

		'verdora_header'   => array(
			'title'  => __( 'Header', 'verdora' ),
			'fields' => array(
				'topbar_text'         => array(
					'type'        => 'text',
					'label'       => __( 'Top bar text', 'verdora' ),
					'default'     => __( 'Welcome! Find your favorite products at the best prices.', 'verdora' ),
					'description' => __( 'Leave empty to hide the top bar.', 'verdora' ),
				),
				'topbar_account_link' => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show the "Log in / Register" link in the top bar', 'verdora' ),
					'default' => true,
				),
				'header_sticky'       => array(
					'type'    => 'checkbox',
					'label'   => __( 'Keep the header visible while scrolling', 'verdora' ),
					'default' => true,
				),
				'header_account'      => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show the account icon', 'verdora' ),
					'default' => true,
				),
				'h_search'            => array(
					'type'  => 'heading',
					'label' => __( 'Search bar', 'verdora' ),
				),
				'search_placeholder'  => array(
					'type'    => 'text',
					'label'   => __( 'Search field placeholder', 'verdora' ),
					'default' => __( 'Search products in the store…', 'verdora' ),
				),
				'search_categories'   => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show the category picker in the search field (desktop)', 'verdora' ),
					'default' => true,
				),
				'h_nav'               => array(
					'type'  => 'heading',
					'label' => __( 'Navigation', 'verdora' ),
				),
				'header_catnav'       => array(
					'type'        => 'checkbox',
					'label'       => __( 'Show the navigation row below the header', 'verdora' ),
					'default'     => true,
					'description' => __( 'Its items come from the "Primary Navigation" menu (Appearance → Menus). Items with sub-items appear as dropdowns. Without a menu, product categories (with their subcategories) are shown automatically. Links in the very top bar are set with the "Top Bar" menu location.', 'verdora' ),
				),
			),
		),

		'verdora_hero'     => array(
			'title'  => __( 'Home: Banner', 'verdora' ),
			'fields' => array(
				'hero_show'        => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show the banner', 'verdora' ),
					'default' => true,
				),
				'h_hero_slider'    => array(
					'type'        => 'heading',
					'label'       => __( 'Slider', 'verdora' ),
					'description' => __( 'The banner can hold several slides: the main (text) slide, image slides you upload, and automatic slides built from your products.', 'verdora' ),
				),
				'hero_autoplay'    => array(
					'type'        => 'select',
					'label'       => __( 'Autoplay', 'verdora' ),
					'default'     => '6',
					'choices'     => array(
						'0'  => __( 'Off', 'verdora' ),
						'5'  => __( 'Every 5 seconds', 'verdora' ),
						'6'  => __( 'Every 6 seconds', 'verdora' ),
						'8'  => __( 'Every 8 seconds', 'verdora' ),
						'10' => __( 'Every 10 seconds', 'verdora' ),
					),
					'description' => __( 'Pauses while the pointer is over the banner and stops for good once a visitor swipes it. Never runs when the visitor\'s device is set to reduce motion.', 'verdora' ),
				),
				'hero_auto_slides' => array(
					'type'        => 'checkbox',
					'label'       => __( 'Add the automatic "Sale" & "New Collection" slides', 'verdora' ),
					'default'     => true,
					'description' => __( 'Built from your store data: the biggest discount percentage and the newest product photos. The Sale slide only appears when products are on sale.', 'verdora' ),
				),
				'h_hero_content'   => array(
					'type'  => 'heading',
					'label' => __( 'Main slide (text)', 'verdora' ),
				),
				'hero_slide_main'  => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show the main slide', 'verdora' ),
					'default' => true,
				),
				'hero_eyebrow'     => array(
					'type'    => 'text',
					'label'   => __( 'Small label above the title', 'verdora' ),
					'default' => __( 'Special Offer', 'verdora' ),
				),
				'hero_title'       => array(
					'type'    => 'text',
					'label'   => __( 'Title', 'verdora' ),
					'default' => __( 'Shop Smart, Live Greener', 'verdora' ),
				),
				'hero_subtitle'    => array(
					'type'    => 'textarea',
					'label'   => __( 'Subtitle', 'verdora' ),
					'default' => __( 'Discover handpicked products at the best prices every day, straight from our store.', 'verdora' ),
				),
				'hero_button_text' => array(
					'type'        => 'text',
					'label'       => __( 'Button text', 'verdora' ),
					'default'     => __( 'Shop Now', 'verdora' ),
					'description' => __( 'Leave empty to hide the button.', 'verdora' ),
				),
				'hero_button_url'  => array(
					'type'        => 'url',
					'label'       => __( 'Button link', 'verdora' ),
					'default'     => '',
					'description' => __( 'Leave empty to link to the Shop page.', 'verdora' ),
				),
				'hero_image'       => array(
					'type'        => 'media',
					'label'       => __( 'Image on the right side of the banner', 'verdora' ),
					'default'     => 0,
					'description' => __( 'Optional. A transparent PNG works best, such as a product photo without a background. If empty, a collage of Featured products (or the newest products) is shown.', 'verdora' ),
				),
				'hero_art'         => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show decorative shapes on slides', 'verdora' ),
					'default' => true,
				),
				'h_hero_bg'        => array(
					'type'  => 'heading',
					'label' => __( 'Background & size', 'verdora' ),
				),
				'hero_bg_start'    => array(
					'type'        => 'color',
					'label'       => __( 'Gradient start color', 'verdora' ),
					'default'     => '',
					'description' => __( 'Leave empty to follow the primary color.', 'verdora' ),
				),
				'hero_bg_end'      => array(
					'type'        => 'color',
					'label'       => __( 'Gradient end color', 'verdora' ),
					'default'     => '',
					'description' => __( 'Leave empty to follow the primary color (dark shade).', 'verdora' ),
				),
				'hero_bg_image'    => array(
					'type'        => 'media',
					'label'       => __( 'Background image (optional)', 'verdora' ),
					'default'     => 0,
					'description' => __( 'Covers the whole banner, on top of the gradient. At least 1600 × 600 px recommended.', 'verdora' ),
				),
				'hero_overlay'     => array(
					'type'        => 'range',
					'label'       => __( 'Overlay strength over the background image', 'verdora' ),
					'default'     => 45,
					'attrs'       => array(
						'min'  => 0,
						'max'  => 90,
						'step' => 5,
					),
					'description' => __( 'Slide to the right to make the text easier to read. Dark overlay for white text, light overlay for dark text.', 'verdora' ),
					'show_if'     => array( 'hero_bg_image', true ),
				),
				'hero_text'        => array(
					'type'    => 'select',
					'label'   => __( 'Banner text color', 'verdora' ),
					'default' => 'light',
					'choices' => array(
						'light' => __( 'White (for dark backgrounds)', 'verdora' ),
						'dark'  => __( 'Dark (for light backgrounds)', 'verdora' ),
					),
				),
				'hero_height'      => array(
					'type'    => 'select',
					'label'   => __( 'Banner height (desktop)', 'verdora' ),
					'default' => 'normal',
					'choices' => array(
						'compact' => __( 'Compact', 'verdora' ),
						'normal'  => __( 'Normal', 'verdora' ),
						'tall'    => __( 'Tall', 'verdora' ),
					),
				),
			) + verdora_slide_image_fields() + array(
				'h_hero_promos'    => array(
					'type'  => 'heading',
					'label' => __( 'Promo boxes next to the banner', 'verdora' ),
				),
				'hero_promos'      => array(
					'type'        => 'checkbox',
					'label'       => __( 'Show 2 promo boxes', 'verdora' ),
					'default'     => true,
					'description' => __( 'When turned off, the banner spans the full width.', 'verdora' ),
				),
				'promo1_title'     => array(
					'type'    => 'text',
					'label'   => __( 'Promo 1: title', 'verdora' ),
					'default' => __( 'On Sale Now', 'verdora' ),
					'show_if' => array( 'hero_promos', true ),
				),
				'promo1_subtitle'  => array(
					'type'    => 'text',
					'label'   => __( 'Promo 1: subtitle', 'verdora' ),
					'default' => __( 'See every product that is on sale', 'verdora' ),
					'show_if' => array( 'hero_promos', true ),
				),
				'promo1_url'       => array(
					'type'        => 'url',
					'label'       => __( 'Promo 1: link', 'verdora' ),
					'default'     => '',
					'description' => __( 'Leave empty to link to the list of discounted products.', 'verdora' ),
					'show_if'     => array( 'hero_promos', true ),
				),
				'promo1_image'     => array(
					'type'        => 'media',
					'label'       => __( 'Promo 1: banner image (optional)', 'verdora' ),
					'default'     => 0,
					'description' => __( 'Replaces the text layout with a finished banner. 800 × 360 px recommended.', 'verdora' ),
					'show_if'     => array( 'hero_promos', true ),
				),
				'promo2_title'     => array(
					'type'    => 'text',
					'label'   => __( 'Promo 2: title', 'verdora' ),
					'default' => __( 'Latest Products', 'verdora' ),
					'show_if' => array( 'hero_promos', true ),
				),
				'promo2_subtitle'  => array(
					'type'    => 'text',
					'label'   => __( 'Promo 2: subtitle', 'verdora' ),
					'default' => __( 'Check out the latest arrivals', 'verdora' ),
					'show_if' => array( 'hero_promos', true ),
				),
				'promo2_url'       => array(
					'type'        => 'url',
					'label'       => __( 'Promo 2: link', 'verdora' ),
					'default'     => '',
					'description' => __( 'Leave empty to link to the newest products.', 'verdora' ),
					'show_if'     => array( 'hero_promos', true ),
				),
				'promo2_image'     => array(
					'type'        => 'media',
					'label'       => __( 'Promo 2: banner image (optional)', 'verdora' ),
					'default'     => 0,
					'description' => __( 'Replaces the text layout with a finished banner. 800 × 360 px recommended.', 'verdora' ),
					'show_if'     => array( 'hero_promos', true ),
				),
			),
		),

		'verdora_home'     => array(
			'title'       => __( 'Home: Product Sections', 'verdora' ),
			'description' => __( 'Choose which sections appear on the home page. "Order" sets their position: lower numbers come first.', 'verdora' ),
			'fields'      => array(
				'h_home_quick'        => array(
					'type'  => 'heading',
					'label' => __( 'Quick Menu', 'verdora' ),
				),
				'home_quick_show'     => array(
					'type'        => 'checkbox',
					'label'       => __( 'Show the quick menu icons below the banner', 'verdora' ),
					'default'     => true,
					'description' => __( 'Colorful shortcuts like in marketplace apps: Flash Sale, Discounts, Best Sellers, New Arrivals, Top Rated, All Products and My Orders. Shortcuts that don\'t apply are hidden automatically (for example Flash Sale when nothing is on sale).', 'verdora' ),
				),
				'h_home_cats'         => array(
					'type'  => 'heading',
					'label' => __( 'Categories', 'verdora' ),
				),
				'home_cats_show'      => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show the Categories section', 'verdora' ),
					'default' => true,
				),
				'home_cats_title'     => array(
					'type'    => 'text',
					'label'   => __( 'Title', 'verdora' ),
					'default' => __( 'Shop by Category', 'verdora' ),
					'show_if' => array( 'home_cats_show', true ),
				),
				'home_cats_count'     => array(
					'type'    => 'number',
					'label'   => __( 'Maximum number of categories', 'verdora' ),
					'default' => 12,
					'attrs'   => array(
						'min' => 1,
						'max' => 24,
					),
					'show_if' => array( 'home_cats_show', true ),
				),
				'home_cats_style'     => array(
					'type'        => 'select',
					'label'       => __( 'Category image style', 'verdora' ),
					'default'     => 'photo',
					'choices'     => array(
						'photo' => __( 'Product photo in a circle', 'verdora' ),
						'icon'  => __( 'Colored icon', 'verdora' ),
					),
					'description' => __( 'A category image set in Products → Categories always comes first. Otherwise, Photo mode uses the newest product photo in that category and Icon mode uses a colored icon.', 'verdora' ),
					'show_if'     => array( 'home_cats_show', true ),
				),
				'home_cats_order'     => array(
					'type'    => 'number',
					'label'   => __( 'Order', 'verdora' ),
					'default' => 1,
					'attrs'   => $order_attrs,
					'show_if' => array( 'home_cats_show', true ),
				),
				'h_home_flash'        => array(
					'type'  => 'heading',
					'label' => __( 'Flash Sale', 'verdora' ),
				),
				'flash_enabled'       => array(
					'type'        => 'checkbox',
					'label'       => __( 'Show Flash Sale', 'verdora' ),
					'default'     => true,
					'description' => __( 'Lists products that currently have a sale price.', 'verdora' ),
				),
				'flash_title'         => array(
					'type'    => 'text',
					'label'   => __( 'Title', 'verdora' ),
					'default' => __( 'Flash Sale', 'verdora' ),
					'show_if' => array( 'flash_enabled', true ),
				),
				'flash_end'           => array(
					'type'        => 'datetime',
					'label'       => __( 'Ends at', 'verdora' ),
					'default'     => '',
					'description' => __( 'Leave empty to always count down to the end of today. Once it has passed, the section is hidden automatically.', 'verdora' ),
					'show_if'     => array( 'flash_enabled', true ),
				),
				'flash_count'         => array(
					'type'    => 'number',
					'label'   => __( 'Maximum number of products', 'verdora' ),
					'default' => 10,
					'attrs'   => array(
						'min' => 2,
						'max' => 20,
					),
					'show_if' => array( 'flash_enabled', true ),
				),
				'flash_order'         => array(
					'type'    => 'number',
					'label'   => __( 'Order', 'verdora' ),
					'default' => 2,
					'attrs'   => $order_attrs,
					'show_if' => array( 'flash_enabled', true ),
				),
				'h_home_featured'     => array(
					'type'  => 'heading',
					'label' => __( 'Featured Products', 'verdora' ),
				),
				'home_featured_show'  => array(
					'type'        => 'checkbox',
					'label'       => __( 'Show Featured Products', 'verdora' ),
					'default'     => true,
					'description' => __( 'Lists products marked with a star (Featured) in Products → All Products. Hidden while there are none.', 'verdora' ),
				),
				'home_featured_title' => array(
					'type'    => 'text',
					'label'   => __( 'Title', 'verdora' ),
					'default' => __( 'Featured Products', 'verdora' ),
					'show_if' => array( 'home_featured_show', true ),
				),
				'home_featured_count' => array(
					'type'    => 'number',
					'label'   => __( 'Maximum number of products', 'verdora' ),
					'default' => 10,
					'attrs'   => array(
						'min' => 2,
						'max' => 20,
					),
					'show_if' => array( 'home_featured_show', true ),
				),
				'home_featured_order' => array(
					'type'    => 'number',
					'label'   => __( 'Order', 'verdora' ),
					'default' => 3,
					'attrs'   => $order_attrs,
					'show_if' => array( 'home_featured_show', true ),
				),
				'h_home_reco'         => array(
					'type'  => 'heading',
					'label' => __( 'Recommendations (product grid)', 'verdora' ),
				),
				'home_reco_show'      => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show the Recommendations grid', 'verdora' ),
					'default' => true,
				),
				'home_reco_title'     => array(
					'type'    => 'text',
					'label'   => __( 'Title', 'verdora' ),
					'default' => __( 'Recommended for You', 'verdora' ),
					'show_if' => array( 'home_reco_show', true ),
				),
				'home_products'       => array(
					'type'    => 'number',
					'label'   => __( 'Number of products', 'verdora' ),
					'default' => 15,
					'attrs'   => array(
						'min' => 4,
						'max' => 40,
					),
					'show_if' => array( 'home_reco_show', true ),
				),
				'home_reco_orderby'   => array(
					'type'    => 'select',
					'label'   => __( 'Sort products by', 'verdora' ),
					'default' => 'date',
					'choices' => array(
						'date'       => __( 'Newest', 'verdora' ),
						'popularity' => __( 'Best Sellers', 'verdora' ),
						'rating'     => __( 'Highest rated', 'verdora' ),
						'price'      => __( 'Lowest price', 'verdora' ),
						'rand'       => __( 'Random', 'verdora' ),
					),
					'show_if' => array( 'home_reco_show', true ),
				),
				'home_reco_button'    => array(
					'type'        => 'text',
					'label'       => __( 'Button text below the grid', 'verdora' ),
					'default'     => __( 'View All Products', 'verdora' ),
					'description' => __( 'Leave empty to hide the button.', 'verdora' ),
					'show_if'     => array( 'home_reco_show', true ),
				),
				'home_reco_order'     => array(
					'type'    => 'number',
					'label'   => __( 'Order', 'verdora' ),
					'default' => 4,
					'attrs'   => $order_attrs,
					'show_if' => array( 'home_reco_show', true ),
				),
			),
		),

		'verdora_shop'     => array(
			'title'  => __( 'Shop & Product Cards', 'verdora' ),
			'fields' => array(
				'h_shop_layout'   => array(
					'type'  => 'heading',
					'label' => __( 'Layout', 'verdora' ),
				),
				'shop_per_page'   => array(
					'type'    => 'number',
					'label'   => __( 'Products per page (Shop & categories)', 'verdora' ),
					'default' => 20,
					'attrs'   => array(
						'min' => 4,
						'max' => 60,
					),
				),
				'shop_columns'    => array(
					'type'    => 'select',
					'label'   => __( 'Product columns on the Shop page (desktop)', 'verdora' ),
					'default' => '4',
					'choices' => array(
						'3' => '3',
						'4' => '4',
						'5' => '5',
					),
				),
				'home_columns'    => array(
					'type'    => 'select',
					'label'   => __( 'Product columns on the home page (desktop)', 'verdora' ),
					'default' => '5',
					'choices' => array(
						'4' => '4',
						'5' => '5',
						'6' => '6',
					),
				),
				'mobile_columns'  => array(
					'type'    => 'select',
					'label'   => __( 'Product columns on mobile', 'verdora' ),
					'default' => '2',
					'choices' => array(
						'1' => '1',
						'2' => '2',
					),
				),
				'shop_sidebar'    => array(
					'type'        => 'checkbox',
					'label'       => __( 'Show the filter sidebar on the Shop page', 'verdora' ),
					'default'     => true,
					'description' => __( 'The sidebar content can be replaced through the "Shop Sidebar" widget area.', 'verdora' ),
				),
				'h_shop_card'     => array(
					'type'  => 'heading',
					'label' => __( 'Product cards', 'verdora' ),
				),
				'card_ratio'      => array(
					'type'    => 'select',
					'label'   => __( 'Product photo shape', 'verdora' ),
					'default' => '1 / 1',
					'choices' => array(
						'1 / 1' => __( 'Square (1:1)', 'verdora' ),
						'3 / 4' => __( 'Portrait (3:4), great for fashion', 'verdora' ),
						'4 / 3' => __( 'Landscape (4:3)', 'verdora' ),
					),
				),
				'card_fit'        => array(
					'type'    => 'select',
					'label'   => __( 'How the photo fills the box', 'verdora' ),
					'default' => 'cover',
					'choices' => array(
						'cover'   => __( 'Fill the box (crops part of the photo)', 'verdora' ),
						'contain' => __( 'Show the whole photo (great for white backgrounds)', 'verdora' ),
					),
				),
				'card_category'   => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show the category label', 'verdora' ),
					'default' => true,
				),
				'card_meta'       => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show rating & units sold', 'verdora' ),
					'default' => true,
				),
				'card_button'     => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show the add to cart button', 'verdora' ),
					'default' => true,
				),
				'sale_badge'      => array(
					'type'    => 'select',
					'label'   => __( 'Sale badge', 'verdora' ),
					'default' => 'percent',
					'choices' => array(
						'percent' => __( 'Percentage (e.g. -20%)', 'verdora' ),
						'text'    => __( 'Text', 'verdora' ),
						'none'    => __( 'Hide', 'verdora' ),
					),
				),
				'sale_badge_text' => array(
					'type'    => 'text',
					'label'   => __( 'Sale badge text', 'verdora' ),
					'default' => __( 'Sale', 'verdora' ),
					'show_if' => array( 'sale_badge', 'text' ),
				),
			),
		),

		'verdora_product'  => array(
			'title'  => __( 'Single Product Page', 'verdora' ),
			'fields' => array(
				'single_sold'           => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show units sold', 'verdora' ),
					'default' => true,
				),
				'single_meta'           => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show SKU, categories and tags', 'verdora' ),
					'default' => true,
				),
				'single_sticky_gallery' => array(
					'type'    => 'checkbox',
					'label'   => __( 'Keep product photos in view while scrolling (desktop)', 'verdora' ),
					'default' => true,
				),
				'single_info'           => array(
					'type'        => 'textarea',
					'label'       => __( 'Info points below the buy buttons', 'verdora' ),
					'default'     => '',
					'description' => __( 'One line per point, for example "Free shipping on orders over $50". Leave empty to hide.', 'verdora' ),
				),
				'single_related'        => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show related products', 'verdora' ),
					'default' => true,
				),
				'single_related_count'  => array(
					'type'    => 'number',
					'label'   => __( 'Number of related products', 'verdora' ),
					'default' => 5,
					'attrs'   => array(
						'min' => 2,
						'max' => 10,
					),
					'show_if' => array( 'single_related', true ),
				),
			),
		),

		'verdora_social'   => array(
			'title'  => __( 'Social Media', 'verdora' ),
			'fields' => array(
				'h_social'          => array(
					'type'        => 'heading',
					'label'       => __( 'Social profiles', 'verdora' ),
					'description' => __( 'Icons appear in the footer for every link you fill in.', 'verdora' ),
				),
				'social_instagram'  => array(
					'type'    => 'url',
					'label'   => 'Instagram',
					'default' => '',
				),
				'social_facebook'   => array(
					'type'    => 'url',
					'label'   => 'Facebook',
					'default' => '',
				),
				'social_tiktok'     => array(
					'type'    => 'url',
					'label'   => 'TikTok',
					'default' => '',
				),
				'social_youtube'    => array(
					'type'    => 'url',
					'label'   => 'YouTube',
					'default' => '',
				),
				'social_x'          => array(
					'type'    => 'url',
					'label'   => 'X (Twitter)',
					'default' => '',
				),
			),
		),

		'verdora_footer'   => array(
			'title'  => __( 'Footer & Contact', 'verdora' ),
			'fields' => array(
				'footer_about'      => array(
					'type'    => 'textarea',
					'label'   => __( 'Short store description', 'verdora' ),
					'default' => __( 'A modern online store with handpicked products and an easy shopping experience.', 'verdora' ),
				),
				'h_footer_menus'    => array(
					'type'        => 'heading',
					'label'       => __( 'Menu columns', 'verdora' ),
					'description' => __( 'Column contents are managed in Appearance → Menus ("Footer: Categories Column" and "Footer: Help Column" locations). Without a menu, the columns are filled automatically.', 'verdora' ),
				),
				'footer_categories' => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show the Categories column', 'verdora' ),
					'default' => true,
				),
				'footer_categories_title' => array(
					'type'    => 'text',
					'label'   => __( 'Categories column title', 'verdora' ),
					'default' => __( 'Categories', 'verdora' ),
					'show_if' => array( 'footer_categories', true ),
				),
				'footer_help_title' => array(
					'type'    => 'text',
					'label'   => __( 'Help column title', 'verdora' ),
					'default' => __( 'Help', 'verdora' ),
				),
				'h_contact'         => array(
					'type'        => 'heading',
					'label'       => __( 'Contact', 'verdora' ),
					'description' => __( 'The "Contact Us" column only appears when at least one field is filled in.', 'verdora' ),
				),
				'contact_phone'     => array(
					'type'    => 'text',
					'label'   => __( 'Phone', 'verdora' ),
					'default' => '',
				),
				'contact_email'     => array(
					'type'    => 'email',
					'label'   => __( 'Email', 'verdora' ),
					'default' => '',
				),
				'contact_address'   => array(
					'type'    => 'textarea',
					'label'   => __( 'Address', 'verdora' ),
					'default' => '',
				),
				'h_footer_bottom'   => array(
					'type'  => 'heading',
					'label' => __( 'Bottom bar', 'verdora' ),
				),
				'footer_copyright'  => array(
					'type'        => 'text',
					'label'       => __( 'Copyright text', 'verdora' ),
					'default'     => __( '© {year} {site}. All rights reserved.', 'verdora' ),
					'description' => __( '{year} and {site} are replaced automatically.', 'verdora' ),
				),
				'footer_note'       => array(
					'type'        => 'text',
					'label'       => __( 'Note on the bottom right', 'verdora' ),
					'default'     => '',
					'description' => __( 'Optional, for example "We accept bank transfers & e-wallets".', 'verdora' ),
				),
			),
		),

		'verdora_mobile'   => array(
			'title'  => __( 'Mobile', 'verdora' ),
			'fields' => array(
				'bottom_nav'  => array(
					'type'        => 'checkbox',
					'label'       => __( 'Show the app-style bottom navigation', 'verdora' ),
					'default'     => true,
					'description' => __( 'Home, Categories, Cart and Account at the bottom of phone screens.', 'verdora' ),
				),
				'back_to_top' => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show the back to top button', 'verdora' ),
					'default' => true,
				),
			),
		),
	);

	return $schema;
}
