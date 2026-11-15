<?php
/**
 * Demo content: Appearance → Verdora Demo Content.
 *
 * Imports the sample products bundled in demo/ (data + images) and removes them again.
 * Everything created here is tagged with the `_verdora_demo` meta key so "Delete"
 * only ever touches demo data, never the store's real products.
 *
 * Loaded only in wp-admin when WooCommerce is active.
 *
 * @package Verdora_Companion
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

const VERDORA_COMPANION_DEMO_META = '_verdora_demo';
const VERDORA_COMPANION_DEMO_PAGE = 'verdora-demo';

/**
 * Bundled demo product definitions.
 *
 * @return array<string,array>
 */
function verdora_companion_demo_products() {
	return require VERDORA_COMPANION_DIR . 'demo/products.php';
}

/**
 * IDs of demo products currently in the store.
 *
 * @return int[]
 */
function verdora_companion_demo_product_ids() {
	return get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => VERDORA_COMPANION_DEMO_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);
}

/**
 * Admin page URL.
 *
 * @param array $args Extra query args.
 * @return string
 */
function verdora_companion_demo_page_url( $args = array() ) {
	return add_query_arg( array_merge( array( 'page' => VERDORA_COMPANION_DEMO_PAGE ), $args ), admin_url( 'themes.php' ) );
}

/*
 * -------------------------------------------------------------------------
 * Admin page
 * -------------------------------------------------------------------------
 */

/**
 * Register the page under Appearance.
 */
function verdora_companion_demo_admin_menu() {
	add_theme_page(
		__( 'Verdora Demo Content', 'verdora-companion' ),
		__( 'Verdora Demo Content', 'verdora-companion' ),
		'manage_woocommerce',
		VERDORA_COMPANION_DEMO_PAGE,
		'verdora_companion_demo_render_page'
	);
}
add_action( 'admin_menu', 'verdora_companion_demo_admin_menu' );

/**
 * Render the page.
 */
function verdora_companion_demo_render_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	$products = verdora_companion_demo_products();
	$imported = verdora_companion_demo_product_ids();
	$base_url = VERDORA_COMPANION_URL . 'demo/images/';
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display-only result flags set by our own redirects.
	$result = isset( $_GET['verdora_companion_demo'] ) ? sanitize_key( wp_unslash( $_GET['verdora_companion_demo'] ) ) : '';
	$count  = isset( $_GET['n'] ) ? absint( $_GET['n'] ) : 0;
	$skip   = isset( $_GET['skipped'] ) ? absint( $_GET['skipped'] ) : 0;
	// phpcs:enable
	?>
	<div class="wrap verdora-demo">
		<h1><?php esc_html_e( 'Verdora Demo Content', 'verdora-companion' ); ?></h1>

		<?php if ( 'imported' === $result ) : ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php
				/* translators: %d: number of products */
				echo esc_html( sprintf( _n( '%d demo product imported.', '%d demo products imported.', $count, 'verdora-companion' ), $count ) );
				if ( $skip ) {
					/* translators: %d: number of products */
					echo ' ' . esc_html( sprintf( _n( '%d skipped because its SKU already exists.', '%d skipped because their SKU already exists.', $skip, 'verdora-companion' ), $skip ) );
				}
				?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'View store →', 'verdora-companion' ); ?></a>
			</p></div>
		<?php elseif ( 'deleted' === $result ) : ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php
				/* translators: %d: number of products */
				echo esc_html( sprintf( _n( '%d demo product and its images were deleted.', '%d demo products and their images were deleted.', $count, 'verdora-companion' ), $count ) );
				?>
			</p></div>
		<?php endif; ?>

		<p class="verdora-demo__intro">
			<?php esc_html_e( 'Fill the store with 10 complete demo products (description, attributes, sale prices, stock and images) to see the Verdora theme at its best. Demo data is marked, so the Delete button only removes demo data and never touches your own products.', 'verdora-companion' ); ?>
		</p>

		<div class="verdora-demo__status">
			<?php if ( $imported ) : ?>
				<span class="verdora-demo__badge is-on">
					<?php
					/* translators: %d: number of products */
					echo esc_html( sprintf( _n( 'Installed: %d demo product', 'Installed: %d demo products', count( $imported ), 'verdora-companion' ), count( $imported ) ) );
					?>
				</span>
			<?php else : ?>
				<span class="verdora-demo__badge"><?php esc_html_e( 'Not imported yet', 'verdora-companion' ); ?></span>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="verdora_companion_demo_import">
				<?php wp_nonce_field( 'verdora_companion_demo_import' ); ?>
				<button type="submit" class="button button-primary button-hero" <?php disabled( count( $imported ) >= count( $products ) ); ?>>
					<?php echo $imported ? esc_html__( 'Import missing products', 'verdora-companion' ) : esc_html__( 'Import Demo Content', 'verdora-companion' ); ?>
				</button>
			</form>

			<?php if ( $imported ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Delete all demo products and their images? Your own products will not be deleted.', 'verdora-companion' ) ); ?>');">
					<input type="hidden" name="action" value="verdora_companion_demo_delete">
					<?php wp_nonce_field( 'verdora_companion_demo_delete' ); ?>
					<button type="submit" class="button button-hero verdora-demo__delete"><?php esc_html_e( 'Delete Demo Content', 'verdora-companion' ); ?></button>
				</form>
			<?php endif; ?>
		</div>

		<h2><?php esc_html_e( 'What is included', 'verdora-companion' ); ?></h2>
		<div class="verdora-demo__grid">
			<?php foreach ( $products as $item ) : ?>
				<div class="verdora-demo__card">
					<img src="<?php echo esc_url( $base_url . $item['images'][0] ); ?>" alt="" loading="lazy">
					<div>
						<strong><?php echo esc_html( $item['name'] ); ?></strong>
						<span><?php echo esc_html( $item['category'] ); ?></span>
						<span class="verdora-demo__price">
							<?php if ( $item['sale_price'] ) : ?>
								<del><?php echo wp_kses_post( wc_price( $item['regular_price'] ) ); ?></del>
								<?php echo wp_kses_post( wc_price( $item['sale_price'] ) ); ?>
							<?php else : ?>
								<?php echo wp_kses_post( wc_price( $item['regular_price'] ) ); ?>
							<?php endif; ?>
						</span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<p class="description">
			<?php esc_html_e( 'The import uploads about 20 images to the Media Library and may take a few seconds. Missing categories are created and existing ones are reused. Products with the same SKU (VD-DEMO-xxx) are never imported twice.', 'verdora-companion' ); ?>
			<?php esc_html_e( 'Image sources and licenses are listed in demo/credits.txt.', 'verdora-companion' ); ?>
		</p>
	</div>
	<style>
		.verdora-demo__intro { max-width: 760px; font-size: 14px; }
		.verdora-demo__status { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin: 20px 0 28px; padding: 18px 20px; background: #fff; border: 1px solid #dcdcde; border-left: 4px solid #059669; border-radius: 6px; }
		.verdora-demo__status form { margin: 0; }
		.verdora-demo__badge { margin-right: auto; padding: 4px 12px; border-radius: 999px; background: #f0f0f1; font-weight: 600; }
		.verdora-demo__badge.is-on { background: #ecfdf5; color: #065f46; }
		.verdora-demo .button-primary { background: #059669; border-color: #047857; }
		.verdora-demo .button-primary:hover, .verdora-demo .button-primary:focus { background: #047857; border-color: #065f46; }
		.verdora-demo__delete { color: #b32d2e !important; border-color: #b32d2e !important; }
		.verdora-demo__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 12px; max-width: 1200px; }
		.verdora-demo__card { display: flex; gap: 12px; align-items: center; padding: 10px; background: #fff; border: 1px solid #dcdcde; border-radius: 8px; }
		.verdora-demo__card img { width: 64px; height: 64px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
		.verdora-demo__card div { display: grid; gap: 2px; min-width: 0; }
		.verdora-demo__card span { color: #646970; font-size: 12px; }
		.verdora-demo__price { color: #1d2327 !important; font-weight: 600; font-size: 13px !important; }
		.verdora-demo__price del { color: #8c8f94; font-weight: 400; margin-right: 4px; }
	</style>
	<?php
}

/*
 * -------------------------------------------------------------------------
 * Import
 * -------------------------------------------------------------------------
 */

/**
 * Find or create a term, marking newly created ones as demo data.
 *
 * @param string $name     Term name.
 * @param string $taxonomy Taxonomy.
 * @return int Term ID (0 on failure).
 */
function verdora_companion_demo_term( $name, $taxonomy ) {
	$existing = get_term_by( 'name', $name, $taxonomy );
	if ( $existing ) {
		return (int) $existing->term_id;
	}
	$created = wp_insert_term( $name, $taxonomy );
	if ( is_wp_error( $created ) ) {
		return 0;
	}
	update_term_meta( $created['term_id'], VERDORA_COMPANION_DEMO_META, 1 );
	return (int) $created['term_id'];
}

/**
 * Copy a bundled image into the Media Library.
 *
 * @param string $filename File in demo/images/.
 * @param int    $post_id  Parent product.
 * @param string $title    Attachment title / alt text.
 * @return int Attachment ID (0 on failure).
 */
function verdora_companion_demo_sideload( $filename, $post_id, $title ) {
	$source = VERDORA_COMPANION_DIR . 'demo/images/' . basename( $filename );
	if ( ! is_readable( $source ) ) {
		return 0;
	}
	// media_handle_sideload() moves the file, so hand it a temporary copy.
	$tmp = wp_tempnam( $filename );
	if ( ! $tmp || ! copy( $source, $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload(
		array(
			'name'     => basename( $filename ),
			'tmp_name' => $tmp,
		),
		$post_id,
		$title
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, VERDORA_COMPANION_DEMO_META, 1 );
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	return (int) $id;
}

/**
 * Create one demo product.
 *
 * @param string $slug Product slug.
 * @param array  $data Definition from demo/products.php.
 * @return int Product ID (0 on failure).
 */
function verdora_companion_demo_create_product( $slug, $data ) {
	$product = new WC_Product_Simple();
	$product->set_name( $data['name'] );
	$product->set_slug( $slug );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_sku( $data['sku'] );
	$product->set_regular_price( (string) $data['regular_price'] );
	if ( ! empty( $data['sale_price'] ) ) {
		$product->set_sale_price( (string) $data['sale_price'] );
	}
	$product->set_manage_stock( true );
	$product->set_stock_quantity( (int) $data['stock'] );
	$product->set_featured( ! empty( $data['featured'] ) );
	$product->set_short_description( $data['short_description'] );
	$product->set_description( $data['description'] );
	$product->set_weight( (string) $data['weight'] );
	list( $length, $width, $height ) = $data['dimensions'];
	$product->set_length( (string) $length );
	$product->set_width( (string) $width );
	$product->set_height( (string) $height );

	$cat_id = verdora_companion_demo_term( $data['category'], 'product_cat' );
	if ( $cat_id ) {
		$product->set_category_ids( array( $cat_id ) );
	}
	$product->set_tag_ids( array_filter( array_map( 'verdora_companion_demo_tag_id', $data['tags'] ) ) );

	$attributes = array();
	$position   = 0;
	foreach ( $data['attributes'] as $label => $value ) {
		$attribute = new WC_Product_Attribute();
		$attribute->set_name( $label );
		$attribute->set_options( array_map( 'trim', explode( '|', $value ) ) );
		$attribute->set_position( $position++ );
		$attribute->set_visible( true );
		$attribute->set_variation( false );
		$attributes[] = $attribute;
	}
	$product->set_attributes( $attributes );

	$product_id = $product->save();
	if ( ! $product_id ) {
		return 0;
	}
	update_post_meta( $product_id, VERDORA_COMPANION_DEMO_META, 1 );

	$image_ids = array();
	foreach ( $data['images'] as $file ) {
		$image_id = verdora_companion_demo_sideload( $file, $product_id, $data['name'] );
		if ( $image_id ) {
			$image_ids[] = $image_id;
		}
	}
	if ( $image_ids ) {
		$product->set_image_id( array_shift( $image_ids ) );
		$product->set_gallery_image_ids( $image_ids );
		$product->save();
	}

	return $product_id;
}

/**
 * Tag helper for array_map.
 *
 * @param string $name Tag name.
 * @return int
 */
function verdora_companion_demo_tag_id( $name ) {
	return verdora_companion_demo_term( $name, 'product_tag' );
}

/**
 * Handle the import form.
 */
function verdora_companion_demo_handle_import() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'verdora-companion' ), 403 );
	}
	check_admin_referer( 'verdora_companion_demo_import' );

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- may be disabled on some hosts.
	}

	$created = 0;
	$skipped = 0;
	foreach ( verdora_companion_demo_products() as $slug => $data ) {
		if ( wc_get_product_id_by_sku( $data['sku'] ) ) {
			++$skipped;
			continue;
		}
		if ( verdora_companion_demo_create_product( $slug, $data ) ) {
			++$created;
		}
	}

	wp_safe_redirect(
		verdora_companion_demo_page_url(
			array(
				'verdora_companion_demo' => 'imported',
				'n'            => $created,
				'skipped'      => $skipped,
			)
		)
	);
	exit;
}
add_action( 'admin_post_verdora_companion_demo_import', 'verdora_companion_demo_handle_import' );

/*
 * -------------------------------------------------------------------------
 * Delete
 * -------------------------------------------------------------------------
 */

/**
 * Handle the delete form: removes demo products, their images, and demo-created
 * terms that are now empty. Nothing without the demo marker is touched.
 */
function verdora_companion_demo_handle_delete() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'verdora-companion' ), 403 );
	}
	check_admin_referer( 'verdora_companion_demo_delete' );

	$deleted = 0;
	foreach ( verdora_companion_demo_product_ids() as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			continue;
		}
		$image_ids = array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() );
		foreach ( array_filter( $image_ids ) as $image_id ) {
			if ( get_post_meta( $image_id, VERDORA_COMPANION_DEMO_META, true ) ) {
				wp_delete_attachment( $image_id, true );
			}
		}
		$product->delete( true );
		++$deleted;
	}

	// Leftover demo images (e.g. if an import was interrupted).
	$orphans = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => VERDORA_COMPANION_DEMO_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);
	foreach ( $orphans as $attachment_id ) {
		wp_delete_attachment( $attachment_id, true );
	}

	// Terms created by the importer, only once nothing uses them anymore.
	foreach ( array( 'product_cat', 'product_tag' ) as $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'meta_key'   => VERDORA_COMPANION_DEMO_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			)
		);
		if ( is_wp_error( $terms ) ) {
			continue;
		}
		foreach ( $terms as $term ) {
			clean_term_cache( $term->term_id, $taxonomy );
			$fresh = get_term( $term->term_id, $taxonomy );
			if ( $fresh && ! is_wp_error( $fresh ) && 0 === (int) $fresh->count ) {
				wp_delete_term( $term->term_id, $taxonomy );
			}
		}
	}

	wp_safe_redirect(
		verdora_companion_demo_page_url(
			array(
				'verdora_companion_demo' => 'deleted',
				'n'            => $deleted,
			)
		)
	);
	exit;
}
add_action( 'admin_post_verdora_companion_demo_delete', 'verdora_companion_demo_handle_delete' );

/*
 * -------------------------------------------------------------------------
 * Notice: suggest importing when the store is still empty
 * -------------------------------------------------------------------------
 */

/**
 * Show a notice on the Dashboard / Themes screens while the store has no products.
 */
function verdora_companion_demo_admin_notice() {
	if ( ! current_user_can( 'manage_woocommerce' ) || get_user_meta( get_current_user_id(), 'verdora_companion_demo_notice_dismissed', true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->base, array( 'dashboard', 'themes' ), true ) ) {
		return;
	}
	$counts = wp_count_posts( 'product' );
	if ( ! empty( $counts->publish ) ) {
		return;
	}
	$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=verdora_companion_demo_dismiss' ), 'verdora_companion_demo_dismiss' );
	?>
	<div class="notice notice-info">
		<p>
			<strong><?php esc_html_e( 'Your store is still empty.', 'verdora-companion' ); ?></strong>
			<?php esc_html_e( 'Want to see the Verdora theme with products? Import 10 demo products with images. You can delete them again at any time.', 'verdora-companion' ); ?>
		</p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( verdora_companion_demo_page_url() ); ?>"><?php esc_html_e( 'View Demo Content', 'verdora-companion' ); ?></a>
			<a class="button-link" href="<?php echo esc_url( $dismiss ); ?>" style="margin-left:8px"><?php esc_html_e( 'Don’t show again', 'verdora-companion' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'verdora_companion_demo_admin_notice' );

/**
 * Remember that the current user dismissed the notice.
 */
function verdora_companion_demo_handle_dismiss() {
	check_admin_referer( 'verdora_companion_demo_dismiss' );
	update_user_meta( get_current_user_id(), 'verdora_companion_demo_notice_dismissed', 1 );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
add_action( 'admin_post_verdora_companion_demo_dismiss', 'verdora_companion_demo_handle_dismiss' );
