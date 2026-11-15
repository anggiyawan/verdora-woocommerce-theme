<?php
/**
 * Site footer, mobile bottom navigation and floating buttons.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

$verdora_has_wc  = class_exists( 'WooCommerce' );
$verdora_phone   = verdora_option( 'contact_phone' );
$verdora_email   = verdora_option( 'contact_email' );
$verdora_address = verdora_option( 'contact_address' );
$verdora_cat_col = (bool) verdora_option( 'footer_categories' );
$verdora_cat_nav = $verdora_cat_col && has_nav_menu( 'footer_categories' );
$verdora_cats    = ( $verdora_cat_col && ! $verdora_cat_nav && $verdora_has_wc ) ? verdora_get_product_categories( 6 ) : array();
$verdora_social  = verdora_social_links();
$verdora_note    = verdora_option( 'footer_note' );
?>
<footer class="vd-footer">
	<div class="vd-container vd-footer__grid">
		<div class="vd-footer__brand">
			<?php verdora_logo(); ?>
			<?php if ( verdora_option( 'footer_about' ) ) : ?>
				<p><?php echo esc_html( verdora_option( 'footer_about' ) ); ?></p>
			<?php endif; ?>
			<?php if ( $verdora_social ) : ?>
				<ul class="vd-social">
					<?php foreach ( $verdora_social as $verdora_network => $verdora_url ) : ?>
						<li>
							<a href="<?php echo esc_url( $verdora_url ); ?>" target="_blank" rel="noopener noreferrer me" aria-label="<?php echo esc_attr( ucfirst( $verdora_network ) ); ?>">
								<?php verdora_the_icon( $verdora_network, 18 ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php if ( $verdora_cat_nav || $verdora_cats ) : ?>
			<nav aria-label="<?php echo esc_attr( verdora_option( 'footer_categories_title' ) ); ?>">
				<h2 class="vd-footer__title"><?php echo esc_html( verdora_option( 'footer_categories_title' ) ); ?></h2>
				<?php if ( $verdora_cat_nav ) : ?>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer_categories',
							'container'      => false,
							'menu_class'     => 'vd-footer__links',
							'depth'          => 1,
						)
					);
					?>
				<?php else : ?>
					<ul class="vd-footer__links">
						<?php
						foreach ( $verdora_cats as $verdora_term ) :
							$verdora_link = get_term_link( $verdora_term );
							if ( is_wp_error( $verdora_link ) ) {
								continue;
							}
							?>
							<li><a href="<?php echo esc_url( $verdora_link ); ?>"><?php echo esc_html( $verdora_term->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</nav>
		<?php endif; ?>

		<div>
			<h2 class="vd-footer__title"><?php echo esc_html( verdora_option( 'footer_help_title' ) ); ?></h2>
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'vd-footer__links',
						'depth'          => 1,
					)
				);
				?>
			<?php elseif ( $verdora_has_wc ) : ?>
				<ul class="vd-footer__links">
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'All Products', 'verdora' ); ?></a></li>
					<li><a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Cart', 'verdora' ); ?></a></li>
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'My Account', 'verdora' ); ?></a></li>
					<?php if ( get_privacy_policy_url() ) : ?>
						<li><a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Privacy Policy', 'verdora' ); ?></a></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php if ( $verdora_phone || $verdora_email || $verdora_address ) : ?>
			<div>
				<h2 class="vd-footer__title"><?php esc_html_e( 'Contact Us', 'verdora' ); ?></h2>
				<ul class="vd-footer__contact">
					<?php if ( $verdora_phone ) : ?>
						<li><?php verdora_the_icon( 'phone', 16 ); ?><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $verdora_phone ) ); ?>"><?php echo esc_html( $verdora_phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $verdora_email ) : ?>
						<li><?php verdora_the_icon( 'mail', 16 ); ?><a href="<?php echo esc_url( 'mailto:' . antispambot( $verdora_email ) ); ?>"><?php echo esc_html( antispambot( $verdora_email ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( $verdora_address ) : ?>
						<li><?php verdora_the_icon( 'pin', 16 ); ?><span><?php echo nl2br( esc_html( $verdora_address ) ); ?></span></li>
					<?php endif; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>

	<div class="vd-footer__bottom">
		<div class="vd-container vd-footer__bottom-inner">
			<p><?php echo esc_html( verdora_copyright_text() ); ?></p>
			<?php if ( $verdora_note ) : ?>
				<p class="vd-footer__note"><?php echo esc_html( $verdora_note ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php if ( $verdora_has_wc && verdora_option( 'bottom_nav' ) ) : ?>
	<nav class="vd-bottomnav" aria-label="<?php esc_attr_e( 'Quick navigation', 'verdora' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="<?php echo is_front_page() ? 'is-active' : ''; ?>">
			<?php verdora_the_icon( 'home', 22 ); ?><span><?php esc_html_e( 'Home', 'verdora' ); ?></span>
		</a>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="<?php echo ( is_shop() || is_product_taxonomy() ) ? 'is-active' : ''; ?>">
			<?php verdora_the_icon( 'grid', 22 ); ?><span><?php esc_html_e( 'Categories', 'verdora' ); ?></span>
		</a>
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="vd-bottomnav__cart <?php echo is_cart() ? 'is-active' : ''; ?>">
			<span class="vd-bottomnav__icon"><?php verdora_the_icon( 'cart', 22 ); ?><?php echo verdora_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span><?php esc_html_e( 'Cart', 'verdora' ); ?></span>
		</a>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="<?php echo is_account_page() ? 'is-active' : ''; ?>">
			<?php verdora_the_icon( 'user', 22 ); ?><span><?php esc_html_e( 'Account', 'verdora' ); ?></span>
		</a>
	</nav>
<?php endif; ?>

<?php if ( verdora_option( 'back_to_top' ) ) : ?>
	<button type="button" class="vd-totop" aria-label="<?php esc_attr_e( 'Back to top', 'verdora' ); ?>" hidden>
		<?php verdora_the_icon( 'arrow-up', 20 ); ?>
	</button>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
