<?php
/**
 * Customizer: registers everything defined in inc/customizer-options.php.
 *
 * @package Verdora
 * @author  Priyana Anggiyawan
 */

defined( 'ABSPATH' ) || exit;

require VERDORA_DIR . '/inc/customizer-options.php';

/**
 * Flat list of fields (key => field), headings excluded.
 *
 * @return array<string,array>
 */
function verdora_customizer_fields() {
	static $fields = null;
	if ( null === $fields ) {
		$fields = array();
		foreach ( verdora_customizer_schema() as $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				if ( 'heading' !== $field['type'] ) {
					$fields[ $key ] = $field;
				}
			}
		}
	}
	return $fields;
}

/**
 * Default values for every option.
 *
 * @return array<string,mixed>
 */
function verdora_defaults() {
	static $defaults = null;
	if ( null === $defaults ) {
		$defaults = array();
		foreach ( verdora_customizer_fields() as $key => $field ) {
			$defaults[ $key ] = isset( $field['default'] ) ? $field['default'] : '';
		}
	}
	return $defaults;
}

/**
 * Get a theme option (falls back to its default).
 *
 * @param string $key Option key without the "verdora_" prefix.
 * @return mixed
 */
function verdora_option( $key ) {
	$defaults = verdora_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return get_theme_mod( 'verdora_' . $key, $default );
}

/*
 * -------------------------------------------------------------------------
 * Sanitizers
 * -------------------------------------------------------------------------
 */

/**
 * Checkbox.
 *
 * @param mixed $value Value.
 * @return bool
 */
function verdora_sanitize_checkbox( $value ) {
	return ( true === $value || '1' === $value || 1 === $value || 'true' === $value );
}

/**
 * Select: value must be one of the control's choices.
 *
 * @param string               $value   Value.
 * @param WP_Customize_Setting $setting Setting.
 * @return string
 */
function verdora_sanitize_choice( $value, $setting ) {
	$key    = substr( $setting->id, strlen( 'verdora_' ) );
	$fields = verdora_customizer_fields();
	$value  = (string) $value;
	return isset( $fields[ $key ]['choices'][ $value ] ) ? $value : $setting->default;
}

/**
 * Number/range: integer clamped to the field's min/max.
 *
 * @param mixed                $value   Value.
 * @param WP_Customize_Setting $setting Setting.
 * @return int
 */
function verdora_sanitize_number( $value, $setting ) {
	$key    = substr( $setting->id, strlen( 'verdora_' ) );
	$fields = verdora_customizer_fields();
	$attrs  = isset( $fields[ $key ]['attrs'] ) ? $fields[ $key ]['attrs'] : array();
	$value  = (int) $value;
	if ( isset( $attrs['min'] ) ) {
		$value = max( (int) $attrs['min'], $value );
	}
	if ( isset( $attrs['max'] ) ) {
		$value = min( (int) $attrs['max'], $value );
	}
	return $value;
}

/**
 * Hex color, empty allowed.
 *
 * @param string $value Value.
 * @return string
 */
function verdora_sanitize_color( $value ) {
	$value = trim( (string) $value );
	return '' === $value ? '' : (string) sanitize_hex_color( $value );
}

/**
 * Datetime string.
 *
 * @param string $value Value.
 * @return string
 */
function verdora_sanitize_datetime( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	return false === strtotime( $value ) ? '' : sanitize_text_field( $value );
}

/**
 * Sanitizer for a field type.
 *
 * @param string $type Field type.
 * @return callable
 */
function verdora_sanitizer_for( $type ) {
	$map = array(
		'checkbox' => 'verdora_sanitize_checkbox',
		'select'   => 'verdora_sanitize_choice',
		'number'   => 'verdora_sanitize_number',
		'range'    => 'verdora_sanitize_number',
		'color'    => 'verdora_sanitize_color',
		'media'    => 'absint',
		'datetime' => 'verdora_sanitize_datetime',
		'url'      => 'esc_url_raw',
		'email'    => 'sanitize_email',
		'textarea' => 'sanitize_textarea_field',
	);
	return isset( $map[ $type ] ) ? $map[ $type ] : 'sanitize_text_field';
}

/*
 * -------------------------------------------------------------------------
 * Derived values used by templates
 * -------------------------------------------------------------------------
 */

/**
 * Flash sale end as a Unix timestamp. Defaults to the end of today (site timezone).
 *
 * @return int
 */
function verdora_flash_end_timestamp() {
	$tz  = wp_timezone();
	$raw = verdora_option( 'flash_end' );
	if ( $raw ) {
		try {
			$date = new DateTimeImmutable( $raw, $tz );
			return $date->getTimestamp();
		} catch ( Exception $e ) {
			unset( $e );
		}
	}
	$end = new DateTimeImmutable( 'today 23:59:59', $tz );
	return $end->getTimestamp();
}

/*
 * -------------------------------------------------------------------------
 * Registration
 * -------------------------------------------------------------------------
 */

/**
 * Build an active_callback from a show_if rule.
 *
 * @param array $rule array( key, expected ).
 * @return callable
 */
function verdora_show_if_callback( $rule ) {
	list( $other, $expected ) = $rule;
	return function ( $control ) use ( $other, $expected ) {
		$setting = $control->manager->get_setting( 'verdora_' . $other );
		$value   = $setting ? $setting->value() : verdora_option( $other );
		return true === $expected ? ! empty( $value ) : (string) $value === (string) $expected;
	};
}

/**
 * Register the panel, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function verdora_customize_register( $wp_customize ) {
	require_once VERDORA_DIR . '/inc/customizer-heading-control.php';

	$wp_customize->add_panel(
		'verdora',
		array(
			'title'       => __( 'Verdora Settings', 'verdora' ),
			'description' => __( 'All Verdora theme settings. Changes show up in the preview right away, before you publish them.', 'verdora' ),
			'priority'    => 25,
		)
	);

	$priority = 10;
	foreach ( verdora_customizer_schema() as $section_id => $section ) {
		$wp_customize->add_section(
			$section_id,
			array(
				'title'       => $section['title'],
				'description' => isset( $section['description'] ) ? $section['description'] : '',
				'panel'       => 'verdora',
				'priority'    => $priority,
			)
		);
		$priority += 10;

		$control_priority = 10;
		foreach ( $section['fields'] as $key => $field ) {
			$id   = 'verdora_' . $key;
			$args = array(
				'label'       => $field['label'],
				'description' => isset( $field['description'] ) ? $field['description'] : '',
				'section'     => $section_id,
				'priority'    => $control_priority,
			);
			$control_priority += 5;

			if ( isset( $field['show_if'] ) ) {
				$args['active_callback'] = verdora_show_if_callback( $field['show_if'] );
			}

			if ( 'heading' === $field['type'] ) {
				$args['settings'] = array();
				$wp_customize->add_control( new Verdora_Customize_Heading_Control( $wp_customize, $id, $args ) );
				continue;
			}

			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $field['default'],
					'sanitize_callback' => verdora_sanitizer_for( $field['type'] ),
					'transport'         => 'refresh',
				)
			);

			switch ( $field['type'] ) {
				case 'color':
					$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, $args ) );
					break;

				case 'media':
					$args['mime_type'] = 'image';
					$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $id, $args ) );
					break;

				case 'datetime':
					$args['allow_past_date']    = true;
					$args['include_time']       = true;
					$args['twelve_hour_format'] = false;
					$wp_customize->add_control( new WP_Customize_Date_Time_Control( $wp_customize, $id, $args ) );
					break;

				default:
					$args['type'] = $field['type'];
					if ( isset( $field['choices'] ) ) {
						$args['choices'] = $field['choices'];
					}
					if ( isset( $field['attrs'] ) ) {
						$args['input_attrs'] = $field['attrs'];
					}
					$wp_customize->add_control( $id, $args );
			}
		}
	}
}
add_action( 'customize_register', 'verdora_customize_register' );

/**
 * Small styles for heading controls inside the Customizer.
 */
function verdora_customizer_controls_css() {
	wp_add_inline_style(
		'customize-controls',
		'.verdora-heading{margin:18px -12px 4px;padding:10px 12px;background:#ecfdf5;border-left:3px solid #059669;font-weight:600;font-size:13px;color:#064e3b}.verdora-heading .description{display:block;font-weight:400;margin-top:4px;color:#334155}'
	);
}
add_action( 'customize_controls_enqueue_scripts', 'verdora_customizer_controls_css' );

/**
 * Shortcut under Appearance → "Verdora Settings" that opens the panel.
 */
function verdora_admin_menu_shortcut() {
	add_theme_page(
		__( 'Verdora Settings', 'verdora' ),
		__( 'Verdora Settings', 'verdora' ),
		'edit_theme_options',
		'customize.php?autofocus%5Bpanel%5D=verdora'
	);
}
add_action( 'admin_menu', 'verdora_admin_menu_shortcut' );
