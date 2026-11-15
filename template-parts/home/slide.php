<?php
/**
 * One banner slide.
 *
 * Args: slide (array from verdora_banner_slides()), index, total.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

$verdora_slide = isset( $args['slide'] ) ? $args['slide'] : array( 'type' => 'main' );
$verdora_index = isset( $args['index'] ) ? (int) $args['index'] : 0;
$verdora_total = isset( $args['total'] ) ? (int) $args['total'] : 1;
$verdora_first = 0 === $verdora_index;
$verdora_label = sprintf(
	/* translators: 1: slide number, 2: total slides */
	__( '%1$d of %2$d', 'verdora' ),
	$verdora_index + 1,
	$verdora_total
);
$verdora_group = sprintf(
	'role="group" aria-roledescription="%1$s" aria-label="%2$s"',
	esc_attr__( 'slide', 'verdora' ),
	esc_attr( $verdora_label )
);

/*
 * Uploaded banner image (artwork already contains its own text).
 */
if ( 'image' === $verdora_slide['type'] ) :
	$verdora_alt = get_post_meta( $verdora_slide['image'], '_wp_attachment_image_alt', true );
	if ( ! $verdora_alt ) {
		/* translators: %d: banner number */
		$verdora_alt = sprintf( __( 'Promotional banner %d', 'verdora' ), $verdora_index + 1 );
	}
	$verdora_img = wp_get_attachment_image(
		$verdora_slide['image'],
		'full',
		false,
		array_filter(
			array(
				'class'         => 'vd-slide__img',
				'alt'           => $verdora_alt,
				'sizes'         => '(max-width: 1024px) 100vw, 860px',
				'loading'       => $verdora_first ? 'eager' : 'lazy',
				'fetchpriority' => $verdora_first ? 'high' : '',
			)
		)
	);
	if ( $verdora_slide['mobile'] ) {
		$verdora_srcset = wp_get_attachment_image_srcset( $verdora_slide['mobile'], 'full' );
		$verdora_img    = sprintf(
			'<picture><source media="(max-width: 640px)" srcset="%1$s" sizes="100vw">%2$s</picture>',
			esc_attr( $verdora_srcset ? $verdora_srcset : wp_get_attachment_image_url( $verdora_slide['mobile'], 'full' ) ),
			$verdora_img
		);
	}
	?>
	<div class="vd-slide vd-slide--image" <?php echo $verdora_group; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>>
		<?php if ( $verdora_slide['url'] ) : ?>
			<a class="vd-slide__link" href="<?php echo esc_url( $verdora_slide['url'] ); ?>"><?php echo $verdora_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core image markup. ?></a>
		<?php else : ?>
			<?php echo $verdora_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core image markup. ?>
		<?php endif; ?>
	</div>
	<?php
	return;
endif;

/*
 * Designed slides: the main (Customizer) slide, or an automatic promo slide.
 */
$verdora_classes = array( 'vd-slide', 'vd-slide--designed', 'vd-slide--' . $verdora_slide['type'] );

if ( 'main' === $verdora_slide['type'] ) {
	$verdora_content = array(
		'eyebrow' => verdora_option( 'hero_eyebrow' ),
		'title'   => verdora_option( 'hero_title' ),
		'text'    => verdora_option( 'hero_subtitle' ),
		'button'  => verdora_option( 'hero_button_text' ),
		'url'     => verdora_option( 'hero_button_url' ) ? verdora_option( 'hero_button_url' ) : wc_get_page_permalink( 'shop' ),
	);
	$verdora_image   = absint( verdora_option( 'hero_image' ) );
	if ( 'dark' === verdora_option( 'hero_text' ) ) {
		$verdora_classes[] = 'is-dark';
	}
	if ( absint( verdora_option( 'hero_bg_image' ) ) ) {
		$verdora_classes[] = 'has-bg';
	}
} else {
	$verdora_content = $verdora_slide;
	$verdora_image   = 0;
}

$verdora_heading = ( 'main' === $verdora_slide['type'] ) ? 'h1' : 'h2';
?>
<div class="<?php echo esc_attr( implode( ' ', $verdora_classes ) ); ?>" <?php echo $verdora_group; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>>
	<div class="vd-slide__content">
		<?php if ( $verdora_content['eyebrow'] ) : ?>
			<span class="vd-slide__eyebrow"><?php verdora_the_icon( 'sale' === $verdora_slide['type'] ? 'zap' : 'sparkles', 14 ); ?><?php echo esc_html( $verdora_content['eyebrow'] ); ?></span>
		<?php endif; ?>
		<<?php echo esc_html( $verdora_heading ); ?> class="vd-slide__title"><?php echo esc_html( $verdora_content['title'] ); ?></<?php echo esc_html( $verdora_heading ); ?>>
		<?php if ( $verdora_content['text'] ) : ?>
			<p class="vd-slide__text"><?php echo esc_html( $verdora_content['text'] ); ?></p>
		<?php endif; ?>
		<?php if ( $verdora_content['button'] ) : ?>
			<a class="vd-btn vd-btn--light vd-slide__btn" href="<?php echo esc_url( $verdora_content['url'] ); ?>">
				<?php echo esc_html( $verdora_content['button'] ); ?>
				<?php verdora_the_icon( 'arrow-right', 18 ); ?>
			</a>
		<?php endif; ?>
	</div>

	<div class="vd-slide__art" aria-hidden="true">
		<?php if ( verdora_option( 'hero_art' ) ) : ?>
			<span class="vd-slide__ring"></span>
			<span class="vd-slide__dot vd-slide__dot--a"></span>
			<span class="vd-slide__dot vd-slide__dot--b"></span>
		<?php endif; ?>
		<?php
		if ( $verdora_image ) {
			echo wp_get_attachment_image(
				$verdora_image,
				'large',
				false,
				array(
					'class'         => 'vd-slide__cutout',
					'alt'           => '',
					'loading'       => $verdora_first ? 'eager' : 'lazy',
					'fetchpriority' => $verdora_first ? 'high' : 'auto',
				)
			);
		} elseif ( 'main' === $verdora_slide['type'] ) {
			verdora_banner_collage( verdora_banner_products( 'featured', 3 ), 'price', $verdora_first );
		} else {
			verdora_banner_collage( $verdora_slide['products'], $verdora_slide['badge'], $verdora_first );
		}
		?>
	</div>
</div>
