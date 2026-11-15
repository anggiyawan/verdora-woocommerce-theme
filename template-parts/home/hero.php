<?php
/**
 * Home: banner slider + two side promo banners (marketplace layout).
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

$verdora_shop     = wc_get_page_permalink( 'shop' );
$verdora_slides   = verdora_banner_slides();
$verdora_total    = count( $verdora_slides );
$verdora_autoplay = $verdora_total > 1 ? absint( verdora_option( 'hero_autoplay' ) ) : 0;
$verdora_promos   = (bool) verdora_option( 'hero_promos' );

$verdora_side = array();
if ( $verdora_promos ) {
	$verdora_sale_items = verdora_banner_products( 'sale', 1 );
	$verdora_new_items  = verdora_banner_products( 'new', 1 );

	$verdora_side = array(
		array(
			'title'    => verdora_option( 'promo1_title' ),
			'subtitle' => verdora_option( 'promo1_subtitle' ),
			'url'      => verdora_option( 'promo1_url' ) ? verdora_option( 'promo1_url' ) : add_query_arg( 'on_sale', '1', $verdora_shop ),
			'image'    => absint( verdora_option( 'promo1_image' ) ),
			'product'  => $verdora_sale_items ? $verdora_sale_items[0] : null,
			'icon'     => 'badge-percent',
			'color'    => 'orange',
			'variant'  => 'warm',
		),
		array(
			'title'    => verdora_option( 'promo2_title' ),
			'subtitle' => verdora_option( 'promo2_subtitle' ),
			'url'      => verdora_option( 'promo2_url' ) ? verdora_option( 'promo2_url' ) : add_query_arg( 'orderby', 'date', $verdora_shop ),
			'image'    => absint( verdora_option( 'promo2_image' ) ),
			'product'  => $verdora_new_items ? $verdora_new_items[0] : null,
			'icon'     => 'sparkles',
			'color'    => 'brand',
			'variant'  => 'fresh',
		),
	);
}
?>
<section class="vd-hero vd-container<?php echo $verdora_promos ? '' : ' vd-hero--solo'; ?>" aria-label="<?php esc_attr_e( 'Promotions', 'verdora' ); ?>">
	<?php if ( ! in_array( 'main', wp_list_pluck( $verdora_slides, 'type' ), true ) ) : ?>
		<h1 class="screen-reader-text"><?php bloginfo( 'name' ); ?></h1>
	<?php endif; ?>
	<div class="vd-slider" data-vd-slider data-autoplay="<?php echo esc_attr( $verdora_autoplay ); ?>" aria-roledescription="<?php esc_attr_e( 'carousel', 'verdora' ); ?>" aria-label="<?php esc_attr_e( 'Promotional banners', 'verdora' ); ?>">
		<div class="vd-slider__track" id="vd-slider-track">
			<?php
			foreach ( $verdora_slides as $verdora_i => $verdora_slide ) {
				get_template_part(
					'template-parts/home/slide',
					null,
					array(
						'slide' => $verdora_slide,
						'index' => $verdora_i,
						'total' => $verdora_total,
					)
				);
			}
			?>
		</div>

		<?php if ( $verdora_total > 1 ) : ?>
			<button type="button" class="vd-slider__arrow vd-slider__arrow--prev" data-dir="-1" aria-controls="vd-slider-track" aria-label="<?php esc_attr_e( 'Previous banner', 'verdora' ); ?>"><?php verdora_the_icon( 'chevron-left', 22 ); ?></button>
			<button type="button" class="vd-slider__arrow vd-slider__arrow--next" data-dir="1" aria-controls="vd-slider-track" aria-label="<?php esc_attr_e( 'Next banner', 'verdora' ); ?>"><?php verdora_the_icon( 'chevron-right', 22 ); ?></button>
			<div class="vd-slider__bar">
				<div class="vd-slider__dots">
					<?php
					for ( $verdora_d = 0; $verdora_d < $verdora_total; $verdora_d++ ) {
						printf(
							'<button type="button" class="vd-slider__dot" aria-controls="vd-slider-track"%1$s aria-label="%2$s"></button>',
							0 === $verdora_d ? ' aria-current="true"' : '',
							/* translators: %d: banner number */
							esc_attr( sprintf( __( 'Show banner %d', 'verdora' ), $verdora_d + 1 ) )
						);
					}
					?>
				</div>
				<?php if ( $verdora_autoplay ) : ?>
					<button type="button" class="vd-slider__toggle" aria-label="<?php esc_attr_e( 'Pause banners', 'verdora' ); ?>" data-label-pause="<?php esc_attr_e( 'Pause banners', 'verdora' ); ?>" data-label-play="<?php esc_attr_e( 'Play banners', 'verdora' ); ?>">
						<?php verdora_the_icon( 'pause', 12, 'vd-slider__ico-pause' ); ?>
						<?php verdora_the_icon( 'play', 12, 'vd-slider__ico-play' ); ?>
					</button>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $verdora_side ) : ?>
	<div class="vd-hero__side">
		<?php foreach ( $verdora_side as $verdora_promo ) : ?>
			<?php if ( $verdora_promo['image'] && wp_attachment_is_image( $verdora_promo['image'] ) ) : ?>
				<a class="vd-promo vd-promo--image" href="<?php echo esc_url( $verdora_promo['url'] ); ?>">
					<?php
					$verdora_alt = get_post_meta( $verdora_promo['image'], '_wp_attachment_image_alt', true );
					echo wp_get_attachment_image(
						$verdora_promo['image'],
						'large',
						false,
						array(
							'alt'   => $verdora_alt ? $verdora_alt : $verdora_promo['title'],
							'sizes' => '(max-width: 1024px) 50vw, 400px',
						)
					);
					?>
				</a>
			<?php else : ?>
				<a class="vd-promo vd-promo--<?php echo esc_attr( $verdora_promo['variant'] ); ?>" href="<?php echo esc_url( $verdora_promo['url'] ); ?>">
					<span class="vd-promo__body">
						<strong class="vd-promo__title"><?php echo esc_html( $verdora_promo['title'] ); ?></strong>
						<?php if ( $verdora_promo['subtitle'] ) : ?>
							<span class="vd-promo__text"><?php echo esc_html( $verdora_promo['subtitle'] ); ?></span>
						<?php endif; ?>
						<span class="vd-promo__cta"><?php esc_html_e( 'View', 'verdora' ); ?><?php verdora_the_icon( 'arrow-right', 14 ); ?></span>
					</span>
					<span class="vd-promo__media" aria-hidden="true">
						<?php if ( $verdora_promo['product'] ) : ?>
							<span class="vd-promo__photo">
								<?php
								echo wp_get_attachment_image(
									$verdora_promo['product']->get_image_id(),
									'woocommerce_thumbnail',
									false,
									array(
										'alt'   => '',
										'sizes' => '140px',
									)
								);
								?>
							</span>
							<?php echo verdora_tile( $verdora_promo['icon'], $verdora_promo['color'], 16, 'vd-promo__badge' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
						<?php else : ?>
							<?php echo verdora_tile( $verdora_promo['icon'], $verdora_promo['color'], 26, 'vd-promo__tile' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
						<?php endif; ?>
					</span>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</section>
