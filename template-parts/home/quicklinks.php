<?php
/**
 * Home: quick shortcut icons under the banner.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

if ( ! verdora_option( 'home_quick_show' ) ) {
	return;
}

$verdora_links = verdora_quick_links();
if ( ! $verdora_links ) {
	return;
}
?>
<nav class="vd-quick vd-container" aria-label="<?php esc_attr_e( 'Quick menu', 'verdora' ); ?>">
	<ul class="vd-quick__list">
		<?php foreach ( $verdora_links as $verdora_link ) : ?>
			<li>
				<a class="vd-quick__item" href="<?php echo esc_url( $verdora_link['url'] ); ?>"<?php echo empty( $verdora_link['external'] ) ? '' : ' target="_blank" rel="noopener noreferrer"'; ?>>
					<?php echo verdora_tile( $verdora_link['icon'], $verdora_link['color'], 24, 'vd-quick__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
					<span class="vd-quick__label"><?php echo esc_html( $verdora_link['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
