<?php
/**
 * Integrate Pardot and WPForms
 *
 * @package    Integrate_Pardot_WPForms
 * @since      1.0.0
 * @copyright  Copyright (c) 2017, Bill Erickson
 * @license    GPL-2.0+
 */

class Integrate_Pardot_WPForms {

    /**
     * Primary Class Constructor
     *
     */
    public function __construct() {

        add_filter( 'wpforms_builder_settings_sections', array( $this, 'settings_section' ), 20, 2 );
        add_filter( 'wpforms_form_settings_panel_content', array( $this, 'settings_section_content' ), 20 );
        add_action( 'wpforms_process_complete', array( $this, 'send_data_to_pardot' ), 10, 4 );

    }

    /**
     * Add Settings Section
     *
     */
    function settings_section( $sections, $form_data ) {
        $sections['be_pardot'] = __( 'Pardot', 'integrate_pardot_wpforms' );
        return $sections;
    }


    /**
     * Pardot Settings Content
     *
     */
    function settings_section_content( $instance ) {
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-be_pardot">';
        echo '<div class="wpforms-panel-content-section-title">' . __( 'Pardot', 'integrate_pardot_wpforms' ) . '</div>';

        wpforms_panel_field(
            'text',
            'settings',
            'be_pardot_form_handler',
            $instance->form_data,
            __( 'Pardot Form Handler URL', 'integrate_pardot_wpforms' )
        );

        echo '</div>';
    }

    /**
     * Integrate WPForms with Pardot
     *
     */
    function send_data_to_pardot( $fields, $entry, $form_data, $entry_id ) {

		$url = false;
		if ( ! empty( $form_data['settings']['be_pardot_form_handler'] ) ) {
			$url = esc_url( $form_data['settings']['be_pardot_form_handler'] );
		}

		$args = array();
		foreach ( $fields as $field ) {
			$args[ 'field_' . $field['id'] ] = $field['value'];
		}

		if ( ! empty( $url ) && ! empty( $args ) ) {
			$request = wp_remote_post( $url, array( 'body' => $args ) );

			if ( function_exists( 'wpforms_log' ) ) {
				wpforms_log(
					'Partdot Response',
					$request,
					[
						'type'    => [ 'provider' ],
						'parent'  => $entry_id,
						'form_id' => $form_data['id'],
					]
				);
			}
		}

	}

}
new Integrate_Pardot_WPForms;
