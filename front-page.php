<?php
/**
 * Front page: marketplace home when WooCommerce is active, otherwise the regular index.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	get_template_part( 'index' );
	return;
}

get_header();
?>
<main id="main" class="vd-main vd-home">
	<?php
	if ( verdora_option( 'hero_show' ) ) {
		get_template_part( 'template-parts/home/hero' );
	} else {
		echo '<h1 class="screen-reader-text">' . esc_html( get_bloginfo( 'name' ) ) . '</h1>';
	}
	get_template_part( 'template-parts/home/quicklinks' );

	// Sections sorted by their "Urutan" setting; each part checks its own on/off toggle.
	$verdora_sections = array(
		'categories' => (int) verdora_option( 'home_cats_order' ),
		'flash-sale' => (int) verdora_option( 'flash_order' ),
		'featured'   => (int) verdora_option( 'home_featured_order' ),
		'products'   => (int) verdora_option( 'home_reco_order' ),
	);
	asort( $verdora_sections );
	foreach ( array_keys( $verdora_sections ) as $verdora_part ) {
		get_template_part( 'template-parts/home/' . $verdora_part );
	}

	// If a static front page is set and has content, show it below the store sections.
	if ( 'page' === get_option( 'show_on_front' ) ) {
		while ( have_posts() ) :
			the_post();
			if ( '' !== trim( get_the_content() ) ) :
				?>
				<section class="vd-section vd-container">
					<div class="vd-entry vd-panel"><?php the_content(); ?></div>
				</section>
				<?php
			endif;
		endwhile;
	}
	?>
</main>
<?php
get_footer();
