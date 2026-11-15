<?php
/**
 * Site header.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

$verdora_has_wc  = class_exists( 'WooCommerce' );
$verdora_topbar  = verdora_option( 'topbar_text' );
$verdora_account = $verdora_has_wc ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="vd-skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'verdora' ); ?></a>

<?php if ( $verdora_topbar || has_nav_menu( 'topbar' ) ) : ?>
	<div class="vd-topbar">
		<div class="vd-container vd-topbar__inner">
			<span class="vd-topbar__text"><?php echo esc_html( $verdora_topbar ); ?></span>
			<div class="vd-topbar__right">
				<?php if ( has_nav_menu( 'topbar' ) ) : ?>
					<nav aria-label="<?php esc_attr_e( 'Top bar menu', 'verdora' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'topbar',
								'container'      => false,
								'menu_class'     => 'vd-topbar__menu',
								'depth'          => 1,
								'fallback_cb'    => false,
							)
						);
						?>
					</nav>
				<?php endif; ?>
				<?php if ( $verdora_has_wc && verdora_option( 'topbar_account_link' ) ) : ?>
					<a class="vd-topbar__account" href="<?php echo esc_url( $verdora_account ); ?>">
						<?php echo is_user_logged_in() ? esc_html__( 'My Account', 'verdora' ) : esc_html__( 'Log in / Register', 'verdora' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<header class="vd-header" id="masthead">
	<div class="vd-container vd-header__inner">
		<button type="button" class="vd-icon-btn vd-header__menu" data-vd-open="vd-drawer" aria-controls="vd-drawer" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'verdora' ); ?>">
			<?php verdora_the_icon( 'menu', 22 ); ?>
		</button>

		<div class="vd-header__brand">
			<?php verdora_logo(); ?>
		</div>

		<div class="vd-header__search">
			<?php get_search_form( array( 'verdora_store' => true ) ); ?>
		</div>

		<div class="vd-header__actions">
			<?php if ( $verdora_has_wc ) : ?>
				<?php if ( verdora_option( 'header_account' ) ) : ?>
					<a class="vd-icon-btn vd-header__account" href="<?php echo esc_url( $verdora_account ); ?>" aria-label="<?php esc_attr_e( 'Account', 'verdora' ); ?>">
						<?php verdora_the_icon( 'user', 22 ); ?>
					</a>
				<?php endif; ?>
				<a class="vd-icon-btn vd-header__cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Shopping cart', 'verdora' ); ?>">
					<?php verdora_the_icon( 'cart', 22 ); ?>
					<?php echo verdora_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in function. ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( verdora_option( 'header_catnav' ) ) : ?>
		<nav class="vd-catnav" aria-label="<?php esc_attr_e( 'Primary navigation', 'verdora' ); ?>">
			<div class="vd-container">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'vd-catnav__list',
						'depth'          => 2,
						'fallback_cb'    => 'verdora_primary_menu_fallback',
					)
				);
				?>
			</div>
		</nav>
	<?php endif; ?>
</header>

<?php get_template_part( 'template-parts/header/drawer' ); ?>
