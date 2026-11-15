<?php
/**
 * 404 page.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

get_header();
?>
<main id="main" class="vd-main">
	<div class="vd-container">
		<section class="vd-panel vd-empty">
			<span class="vd-empty__icon"><?php verdora_the_icon( 'search', 36 ); ?></span>
			<p class="vd-empty__code">404</p>
			<h1 class="vd-page-title"><?php esc_html_e( 'Page not found', 'verdora' ); ?></h1>
			<p><?php esc_html_e( 'The page you are looking for may have been moved or is no longer available. Try searching for the product below.', 'verdora' ); ?></p>
			<div class="vd-empty__search"><?php get_search_form( array( 'verdora_store' => true ) ); ?></div>
			<a class="vd-btn vd-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'verdora' ); ?></a>
		</section>
	</div>
</main>
<?php
get_footer();
