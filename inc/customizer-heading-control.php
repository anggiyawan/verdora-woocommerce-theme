<?php
/**
 * Visual group heading inside a Customizer section (no setting attached).
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'Verdora_Customize_Heading_Control' ) ) {
	/**
	 * Heading control.
	 */
	class Verdora_Customize_Heading_Control extends WP_Customize_Control {

		/**
		 * Control type.
		 *
		 * @var string
		 */
		public $type = 'verdora-heading';

		/**
		 * Render the heading.
		 */
		protected function render_content() {
			echo '<div class="verdora-heading">' . esc_html( $this->label );
			if ( $this->description ) {
				echo '<span class="description">' . esc_html( $this->description ) . '</span>';
			}
			echo '</div>';
		}
	}
}
