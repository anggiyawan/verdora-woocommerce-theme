<?php
/**
 * Off-canvas mobile drawer.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

$verdora_cats = class_exists( 'WooCommerce' ) ? verdora_get_product_categories() : array();
?>
<div class="vd-drawer" id="vd-drawer" aria-hidden="true">
	<div class="vd-drawer__overlay" data-vd-close="vd-drawer"></div>
	<div class="vd-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'verdora' ); ?>">
		<div class="vd-drawer__head">
			<?php verdora_logo(); ?>
			<button type="button" class="vd-icon-btn" data-vd-close="vd-drawer" aria-label="<?php esc_attr_e( 'Close menu', 'verdora' ); ?>">
				<?php verdora_the_icon( 'close', 22 ); ?>
			</button>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Mobile menu', 'verdora' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'vd-drawer__menu',
						'depth'          => 2,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<?php if ( $verdora_cats ) : ?>
			<p class="vd-drawer__label"><?php esc_html_e( 'Categories', 'verdora' ); ?></p>
			<ul class="vd-drawer__cats">
				<?php
				foreach ( $verdora_cats as $verdora_term ) :
					$verdora_link = get_term_link( $verdora_term );
					if ( is_wp_error( $verdora_link ) ) {
						continue;
					}
					?>
					<li>
						<a href="<?php echo esc_url( $verdora_link ); ?>">
							<span class="vd-drawer__cat-icon"><?php verdora_the_icon( verdora_category_icon_name( $verdora_term ), 18 ); ?></span>
							<?php echo esc_html( $verdora_term->name ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( has_nav_menu( 'topbar' ) ) : ?>
			<p class="vd-drawer__label"><?php esc_html_e( 'Information', 'verdora' ); ?></p>
			<nav aria-label="<?php esc_attr_e( 'Information menu', 'verdora' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'topbar',
						'container'      => false,
						'menu_class'     => 'vd-drawer__menu vd-drawer__menu--small',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>
	</div>
</div>
