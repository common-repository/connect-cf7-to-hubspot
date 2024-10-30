<?php
/**
 * Initialize and display main admin panel output.
 *
 * @package Cf7hs
 */

namespace Procoders\Cf7hs\Admin;

use Procoders\Cf7hs\Functions as Functions;
use Procoders\Cf7hs\Includes as Includes;
use Procoders\Cf7hs\Loader as Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Functions
 */
class Init {

	public array $object_types = array(
		'0-1' => 'Contact',
		'0-2' => 'Company',
		'0-3' => 'Deal',
		'0-5' => 'Ticket',
	);

	/**
	 * Initializes the callback function for the given request
	 *
	 * @return void
	 */
	public function init_callback(): void {
		$template = new Loader();
		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'cf7hs_submit_form' );
		}
		$token = sanitize_text_field( get_option( 'cfhs_access_token' ) );

		if ( empty( $token ) ) {
			$template->set_template_data(
				array(
					'template' => $template,
					'message'  => array(
						'success' => false,
						'text'    => __( 'Authentication credentials not found. This API supports OAuth 2.0 authentication and you can find more details at https://developers.hubspot.com/docs/methods/auth/oauth-overview', 'connect-cf7-hubspot' )
					)
				)
			)->get_template_part( 'admin/message' );
			die();
		}
		if ( ! isset( $_REQUEST['id'] ) ) {
			// Lets Get all forms.
			$this->getFormList();

			return;
		}

		$id = ctype_digit( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) )
			? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) )
			: 1;

		if ( isset( $_POST['submit'] ) ) {
			$this->updateMetaFields( $id );
			$message = $this->getSubmitStatusMessage();
		}

		if ( isset( $_POST['filter'] ) && isset( $_POST['cfhs_module'] ) ) {
			update_post_meta( $id, 'cfhs_module', sanitize_text_field( wp_unslash( $_POST['cfhs_module'] ) ) );
			$message = $this->getSubmitStatusMessage();
		}

		$form            = $this->getFormData( $id );
		$form['message'] = $message ?? false;
		$template->set_template_data(
			array(
				'template' => $template,
				'form'     => $form,
			)
		)->get_template_part( 'admin/form' );
	}


	/**
	 * Returns an array containing form data for the given ID
	 *
	 * @param int $id The ID of the form.
	 *
	 * @return array The form data including various fields and metadata
	 */
	private function getFormData( int $id ): array {
		$submission  = new Includes\Submission();
		$cfhs_module = get_post_meta( $id, 'cfhs_module', true );
		$hs_forms    = get_option( 'cfhs_hf' );
		$_form       = get_post_meta( $id, '_form', true );
		$fields      = $this->getFields( $cfhs_module, $submission, $hs_forms, $id );

		return array(
			'cfhs_module'   => $cfhs_module,
			'cfhs_active'   => get_post_meta( $id, 'cfhs_active', true ),
			'cfhs_fields'   => get_post_meta( $id, 'cfhs_fields', true ),
			'hubspot_forms' => $hs_forms,
			'action'        => get_post_meta( $id, 'cfhs_action', true ) ?? 'create_or_update',
			'title'         => get_the_title( $id ),
			'modules'       => unserialize( get_option( 'cfhs_modules' ) ),
			'_form'         => $_form,
			'fields'        => $fields,
			'cf7_fields'    => $this->get_cf7_fields( $_form ),
			'object_types'  => $this->object_types,
		);
	}


	/**
	 * Returns an array of fields based on the specified module, submission object, HubSpot forms, and ID
	 *
	 * @param string $cfhs_module The module name
	 * @param object $submission The submission object
	 * @param bool|array $hs_forms The HubSpot forms
	 * @param int $id The form post ID
	 *
	 * @return array|null The array of fields or null if $cfhs_module is empty
	 */
	private function getFields( string $cfhs_module, object $submission, bool|array $hs_forms, int $id ): ?array {
		if ( empty( $cfhs_module ) ) {
			return array();
		}

		return 'contacts' === $cfhs_module || 'deals' === $cfhs_module
			? $submission->get_module_fields( $cfhs_module )
			: $this->get_hs_fields( $hs_forms[ $cfhs_module ]->fieldGroups );
	}

	/**
	 * Retrieves a list of contact forms and their details
	 *
	 * @return void
	 */
	public function getFormList(): void {
		$template = new loader();
		if ( $this->syncCF7withHubSpot() ) {
			$forms = new \WP_Query(
				array(
					'post_type'      => 'wpcf7_contact_form',
					'order'          => 'ASC',
					'posts_per_page' => - 1,
				)
			);

			$forms_array = array();
			while ( $forms->have_posts() ) {
				$forms->the_post();
				$id                           = get_the_ID();
				$forms_array[ $id ]['title']  = get_the_title();
				$forms_array[ $id ]['status'] = get_post_meta( get_the_ID(), 'cfhs_active', true );
				$forms_array[ $id ]['link']   = menu_page_url( functions::get_plugin_slug(), 0 ) . '&id=' . $id;
			}
			wp_reset_postdata();

			$template->set_template_data(
				array(
					'template' => $template,
					'forms'    => $forms_array,
				)
			)->get_template_part( 'admin/formList' );
		} else {
			// TODO: Lets make the error template.
			$template->set_template_data(
				array(
					'template' => $template,
					'forms'    => false,
				)
			)->get_template_part( 'admin/formList' );
		}
	}

	/**
	 * Retrieve and sync forms from HubSpot.
	 */
	private function syncCF7withHubSpot(): bool {
		$template     = new loader();
		$access_token = get_option( 'cfhs_access_token' );
		$hubspot      = new Includes\privateAPI( functions::get_HS_api_url(), $access_token );
		$hp_forms     = $hubspot->syncCF7Forms();
		if ( $hp_forms && ! isset( $hp_forms->status ) ) {
			update_option( 'cfhs_hf', $this->extractForms( $hp_forms ) );

			return true;
		} else {
			$template->set_template_data(
				array(
					'template' => $template,
					'message'  => array(
						'success' => false,
						'text'    => $hp_forms->message
					)
				)
			)->get_template_part( 'admin/message' );
			die();
		}
	}

	/**
	 * Extract relevant form data from HubSpot API response.
	 *
	 * @param object $forms HubSpot API response containing form data.
	 *
	 * @return array Extracted form data.
	 */
	private function extractForms( object $forms ): array {
		$hubspot_forms = array();
		foreach ( $forms->results as $form ) {
			if ( isset( $form->id ) ) {
				$hubspot_forms[ $form->id ] = $form;
			}
		}

		return $hubspot_forms;
	}

	/**
	 * Returns an array containing a status message and a success flag for the submission status
	 *
	 * @return array The status message and a success flag
	 */
	private function getSubmitStatusMessage(): array {
		return array(
			'text'    => esc_html__( 'Integration settings saved.', 'connect-cf7-hubspot' ),
			'success' => true,
		);
	}

	/**
	 * Updates meta fields for a specifieds ID
	 *
	 * @param int $id The ID of the post to update meta fields for.
	 *
	 * @return void
	 */
	private function updateMetaFields( int $id ): void {
		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'cf7hs_submit_form' );
		}
		$meta_fields = array(
			'cfhs_active',
			'cfhs_fields',
			'cfhs_action'
		); // define the meta fields.


		// perform update_post_meta for each option field.
		foreach ( $meta_fields as $meta_field ) {
			$field_value = isset( $_POST[ $meta_field ] ) ? sanitize_post( wp_unslash( $_POST[ $meta_field ] ) ) : null;
			if ( 'cfhs_active' === $meta_field && null === $field_value ) {
				$field_value = '0';
			}
			// update_post_meta if $field_value is not null.
			if ( null !== $field_value ) {
				update_post_meta(
					$id,
					$meta_field,
					$field_value
				);
			}
		}
	}

	/**
	 * Returns an array of fields extracted from the provided array of field groups
	 *
	 * @param array $fieldsGroups An array of field groups.
	 *
	 * @return array An array containing the extracted fields
	 */
	private function get_hs_fields( array $fieldsGroups ): array {
		$fields = array();
		foreach ( $fieldsGroups as $field_group ) {
			if ( null !== $field_group->fields ) {
				foreach ( $field_group->fields as $group_field ) {
					$fields[ $group_field->name ]             = array(
						'label'       => $group_field->label,
						'type'        => $group_field->fieldType,
						'object_type' => $group_field->objectTypeId,
						'required'    => 0,
					);
					$fields[ $group_field->name ]['required'] = $group_field->required ? 1 : 0;
				}
			}
		}

		return $fields;
	}

	/**
	 * Returns an array containing CF7 fields extracted from the given form
	 *
	 * @param string $_form The form content from which to extract CF7 fields.
	 *
	 * @return array|null The CF7 fields extracted from the form content, or null if no fields found.
	 */
	private function get_cf7_fields( string $_form ): bool|array {
		preg_match_all( '#\[([^\]]*)\]#', $_form, $matches );
		if ( null === $matches ) {
			return false;
		}

		$cf7_fields = array();
		foreach ( $matches[1] as $match ) {
			$match_explode = explode( ' ', $match );
			$field_type    = str_replace( '*', '', $match_explode[0] );
			// Continue in iteration if the field type is 'submit'.
			if ( 'submit' === $field_type ) {
				continue;
			}
			if ( isset( $match_explode[1] ) ) {
				$cf7_fields[ $match_explode[1] ] = array(
					'key'  => $match_explode[1],
					'type' => $field_type,
				);
			}
		}

		return $cf7_fields;
	}
}
