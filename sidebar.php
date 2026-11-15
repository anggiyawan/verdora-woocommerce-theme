<?php
/**
 * Blog sidebar.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

if ( ! is_active_sidebar( 'blog-sidebar' ) ) {
	return;
}
?>
<aside class="vd-sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'verdora' ); ?>">
	<?php dynamic_sidebar( 'blog-sidebar' ); ?>
</aside>
