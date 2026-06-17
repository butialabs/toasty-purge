<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Toasty_Purge_Admin_API
 */
class Toasty_Purge_Admin_API {

	/**
	 * Generate HTML for displaying fields
	 *
	 * @param  array      $data Field data
	 * @param object|bool $post WP_Post
	 * @param  boolean    $echo Whether to echo the field HTML or return it
	 *
	 * @return string
	 * @source  : //github.com/hlashbrooke/WordPress-Plugin-Template/
	 * @since   v0.0.1
	 */
	public function display_field( $data = array(), $post = false, $echo = true ) {

		// Get plugin settings
		$plugin  = Toasty_Purge::instance();
		$options = get_site_option( $plugin->_option_name );

		// Get field info
		if ( isset( $data['field'] ) ) {
			$field = $data['field'];
		} else {
			$field = $data;
		}

		// Check for prefix on option name
		if ( isset( $data['prefix'] ) ) {
			$prefix = $data['prefix'];
		} else {
			$prefix = '';
		}

		// Get saved data
		$data        = '';
		$option_name = $field['id'];
		if ( $post ) {
			// Get saved field data
			$option = get_post_meta( $post->ID, $field['id'], true );

			// Get data to display in field
			if ( isset( $option ) ) {
				$data = $option;
			}
		} else {
			// Get saved option
			$option = $options[ $option_name ];

			// Get data to display in field
			if ( isset( $option ) ) {
				$data = $option;
			}
		}

		// Show default data if no option saved and default is supplied
		if ( $data === false && isset( $field['default'] ) ) {
			$data = $field['default'];
		} elseif ( $data === false ) {
			$data = '';
		}

		$html = '';

		switch ( $field['type'] ) {

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' == $data ) {
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $prefix . $option_name ) . '" ' . $checked . '/>' . "\n";
				break;

			case 'checkbox_multi':
				if ( empty( $data ) ) {
					$data = array();
				}
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( in_array( $k, $data ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $prefix . $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . esc_html( $v ) . '</label> ';
				}
				break;

			case 'radio':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( $k == $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $prefix . $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . esc_html( $v ) . '</label> ';
				}
				break;

		}

		switch ( $field['type'] ) {

			case 'checkbox_multi':
			case 'radio':
				$html .= '<br/><span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
				break;

			default:
				if ( ! $post ) {
					$html .= '<label for="' . esc_attr( $field['id'] ) . '">' . "\n";
				}

				$html .= '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>' . "\n";

				if ( ! $post ) {
					$html .= '</label>' . "\n";
				}
				break;
		}

		if ( ! $echo ) {
			return $html;
		}

		$allowed = array(
			'input'  => array( 'id' => true, 'type' => true, 'name' => true, 'checked' => true, 'value' => true ),
			'label'  => array( 'for' => true, 'class' => true ),
			'span'   => array( 'class' => true ),
			'br'     => array(),
			'strong' => array(),
			'a'      => array( 'href' => true, 'title' => true, 'target' => true ),
			'em'     => array(),
			'code'   => array(),
		);
		echo wp_kses( $html, $allowed );
	}

}
