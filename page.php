<?php
/**
 * Pages, including WooCommerce Cart, Checkout and My Account.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

get_header();

$verdora_is_store_page = class_exists( 'WooCommerce' ) && ( is_cart() || is_checkout() || is_account_page() );
?>
<main id="main" class="vd-main<?php echo $verdora_is_store_page ? ' vd-store-page' : ''; ?>">
	<div class="vd-container">
		<?php
		if ( $verdora_is_store_page ) {
			woocommerce_breadcrumb();
		}

		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( $verdora_is_store_page ? '' : 'vd-panel vd-article' ); ?>>
				<header class="vd-page-header">
					<?php the_title( '<h1 class="vd-page-title">', '</h1>' ); ?>
				</header>
				<div class="vd-entry">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<nav class="vd-page-links">' . esc_html__( 'Pages:', 'verdora' ),
							'after'  => '</nav>',
						)
					);
					?>
				</div>
			</article>
			<?php
			if ( ! $verdora_is_store_page && ( comments_open() || get_comments_number() ) ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
