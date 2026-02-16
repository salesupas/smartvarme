<?php
/**
 * Formidable Forms Placeholder Automation
 *
 * Automatically sets field labels as placeholders when placeholder is empty.
 * Works with all Formidable Forms across the site.
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Smartvarme_Formidable_Placeholders {

	/**
	 * Constructor - register hooks
	 */
	public function __construct() {
		// Only run if Formidable Forms is active
		if ( ! class_exists( 'FrmForm' ) ) {
			return;
		}

		// Hook into field updates to automatically set placeholders
		add_action( 'frm_after_duplicate_field', array( $this, 'set_placeholder_on_duplicate' ), 10, 3 );
		add_action( 'frm_after_create_field', array( $this, 'set_placeholder_on_create' ), 10, 2 );
	}

	/**
	 * Set placeholder when field is duplicated
	 *
	 * @param int $field_id New field ID
	 * @param object $field Original field object
	 * @param int $form_id Form ID
	 */
	public function set_placeholder_on_duplicate( $field_id, $field, $form_id ) {
		$this->maybe_set_placeholder( $field_id );
	}

	/**
	 * Set placeholder when new field is created
	 *
	 * @param int $field_id Field ID
	 * @param array $values Field values
	 */
	public function set_placeholder_on_create( $field_id, $values ) {
		$this->maybe_set_placeholder( $field_id );
	}

	/**
	 * Check and set placeholder if empty
	 *
	 * @param int $field_id Field ID
	 */
	private function maybe_set_placeholder( $field_id ) {
		global $wpdb;

		// Get field data
		$field = $wpdb->get_row( $wpdb->prepare(
			"SELECT id, name, type, field_options FROM {$wpdb->prefix}frm_fields WHERE id = %d",
			$field_id
		) );

		if ( ! $field ) {
			return;
		}

		// Check if this is a text input field
		if ( ! $this->is_placeholder_field_type( $field->type ) ) {
			return;
		}

		// Unserialize field options
		$field_options = maybe_unserialize( $field->field_options );

		// Set placeholder from label if empty
		if ( empty( $field_options['placeholder'] ) && ! empty( $field->name ) ) {
			$field_options['placeholder'] = $field->name;

			// Update field
			$wpdb->update(
				$wpdb->prefix . 'frm_fields',
				array( 'field_options' => serialize( $field_options ) ),
				array( 'id' => $field->id ),
				array( '%s' ),
				array( '%d' )
			);
		}
	}

	/**
	 * Check if field type should have placeholder
	 * Uses same logic as Formidable Forms
	 *
	 * @param string $field_type Field type
	 * @return bool Whether field type should have placeholder
	 */
	private function is_placeholder_field_type( $field_type ) {
		// Same as FrmFieldsHelper::is_placeholder_field_type()
		return ! in_array( $field_type, array( 'radio', 'checkbox', 'hidden', 'file' ), true );
	}

	/**
	 * Manually update all existing form fields with placeholders
	 * Run this once to update existing forms
	 *
	 * @return array Results with success count and updated fields
	 */
	public static function update_existing_forms() {
		global $wpdb;

		if ( ! class_exists( 'FrmForm' ) ) {
			return array(
				'success' => false,
				'message' => 'Formidable Forms er ikke aktivert',
			);
		}

		// Get all fields that support placeholders
		$fields = $wpdb->get_results(
			"SELECT id, name, type, field_options
			FROM {$wpdb->prefix}frm_fields
			WHERE form_id IS NOT NULL
			AND type NOT IN ('radio', 'checkbox', 'hidden', 'file')"
		);

		$updated_count = 0;
		$updated_fields = array();

		foreach ( $fields as $field ) {
			// Unserialize field options
			$field_options = maybe_unserialize( $field->field_options );

			// Check if placeholder is empty or missing
			if ( empty( $field_options['placeholder'] ) && ! empty( $field->name ) ) {
				// Set placeholder to label
				$field_options['placeholder'] = $field->name;

				// Update field
				$result = $wpdb->update(
					$wpdb->prefix . 'frm_fields',
					array( 'field_options' => serialize( $field_options ) ),
					array( 'id' => $field->id ),
					array( '%s' ),
					array( '%d' )
				);

				if ( $result !== false ) {
					$updated_count++;
					$updated_fields[] = array(
						'id'    => $field->id,
						'name'  => $field->name,
						'type'  => $field->type,
					);
				}
			}
		}

		return array(
			'success'        => true,
			'updated_count'  => $updated_count,
			'updated_fields' => $updated_fields,
			'message'        => sprintf(
				'Oppdaterte %d felt med placeholders fra labels.',
				$updated_count
			),
		);
	}
}
