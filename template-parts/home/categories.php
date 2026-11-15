<?php
/**
 * Home: category shortcuts.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

if ( ! verdora_option( 'home_cats_show' ) ) {
	return;
}

$verdora_cats = verdora_get_product_categories( max( 1, min( 24, (int) verdora_option( 'home_cats_count' ) ) ) );
if ( ! $verdora_cats ) {
	return;
}
?>
<section class="vd-section vd-container vd-reveal" aria-labelledby="vd-cats-title">
	<div class="vd-panel">
		<div class="vd-section__head">
			<h2 class="vd-section__title" id="vd-cats-title"><?php echo esc_html( verdora_option( 'home_cats_title' ) ); ?></h2>
			<a class="vd-section__link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
				<?php esc_html_e( 'See all', 'verdora' ); ?><?php verdora_the_icon( 'chevron-right', 16 ); ?>
			</a>
		</div>
		<?php $verdora_style = 'icon' === verdora_option( 'home_cats_style' ) ? 'icon' : 'photo'; ?>
		<ul class="vd-cats vd-cats--<?php echo esc_attr( $verdora_style ); ?>">
			<?php
			foreach ( array_values( $verdora_cats ) as $verdora_i => $verdora_term ) :
				$verdora_link = get_term_link( $verdora_term );
				if ( is_wp_error( $verdora_link ) ) {
					continue;
				}
				?>
				<li>
					<a class="vd-cats__item" href="<?php echo esc_url( $verdora_link ); ?>">
						<span class="vd-cats__icon"><?php verdora_category_visual( $verdora_term, $verdora_style, $verdora_i ); ?></span>
						<span class="vd-cats__name"><?php echo esc_html( $verdora_term->name ); ?></span>
						<span class="vd-cats__count">
							<?php
							/* translators: %s: product count */
							echo esc_html( sprintf( _n( '%s product', '%s products', $verdora_term->count, 'verdora' ), number_format_i18n( $verdora_term->count ) ) );
							?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
