<?php
/**
 * Nothing found.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

?>
<section class="vd-panel vd-empty">
	<span class="vd-empty__icon"><?php verdora_the_icon( 'search', 32 ); ?></span>
	<h2 class="vd-page-title"><?php esc_html_e( 'Nothing to show yet', 'verdora' ); ?></h2>
	<p><?php esc_html_e( 'Try another search term or go back to the home page.', 'verdora' ); ?></p>
	<?php get_search_form(); ?>
</section>
