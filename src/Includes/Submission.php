<?php
/**
 * Submisttion class for CF7
 *
 * @package Cf7hs
 */

namespace Procoders\Cf7hs\Includes;

use Procoders\Cf7hs\functions as Functions;
use Procoders\Cf7hs\Includes\PrivateAPI as API;
use Procoders\Cf7hs\Loader as Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

/**
 * Submission class
 *
 * This class handles the submission of data to HubSpot based on the Contact Form 7 plugin.
 *
 * @package Cf7hs
 */
class Submission {
	/** @var API|privateAPI HubSpot API instance */
	private $hubspot;

	/**
	 * Constructor to initialize the HubSpot API based on the available credentials.
	 */
	public function __construct() {
		$access_token  = get_option( 'cfhs_access_token' );
		$api_url       = Functions::get_HS_api_url();
		$this->hubspot = new API( $api_url, $access_token );
	}

	/**
	 * Retrieve HubSpot module fields.
	 *
	 * @param string $hs_data The name of the HubSpot module.
	 *
	 * @return array An array of HubSpot fields.
	 */
	public function get_module_fields( string $hs_data ): array {
		$fields = $this->hubspot->getHSFields( $hs_data );
		asort( $fields );

		return $fields;
	}

	/**
	 * Initialize the contact form submission process.
	 *
	 * @param object $form The contact form object.
	 * @param bool &$abort True if the submission should be aborted, false otherwise.
	 * @param object $object The main object for processing the form submission.
	 *
	 * @return void
	 */
	public function init( $form, &$abort, $object ): void {
		$template = new loader();
		// PCF7_Submission i`ts from contact form 7.
		$submission = \WPCF7_Submission::get_instance();
		if ( ! $submission ) {
			$template->set_template_data(
				array(
					'template' => $template,
					'message'  => array(
						'success' => false,
						'text'    => 'Contact Form 7 plugin is required.'
					)
				)
			)->get_template_part( 'admin/message' );
			die();
		}
		$post_id = $submission->get_meta( 'container_post_id' );
		$request = $submission->get_posted_data();
		$form_id = $submission->get_contact_form()->id();

		if ( $form_id ) {
			$cfhs = get_post_meta( $form_id, 'cfhs_active', true );
			if ( '0' === $cfhs || empty( $cfhs ) ) {
				return;
			}
			$cfhs_fields = get_post_meta( $form_id, 'cfhs_fields', true );
			if ( ! empty( $cfhs_fields ) ) {
				$data     = $this->prepare_data( $request, $cfhs_fields );
				$hs_data  = get_post_meta( $form_id, 'cfhs_module', true );
				$response = $this->process_data( $hs_data, $form_id, $post_id, $data );

				if ( isset( $response->status ) && 'error' === $response->status ) {
					$submission->set_status( 'validation_failed' );
					$errs = [];
					foreach ( $response->errors as $error ) {
						$errs[] = $error->message;
					}
					$abort = true;
					$submission->set_response( 'API submission errors: ' . implode( '. ', $errs ) );
				}
			}
		}
	}


	/**
	 * Prepare data for submission.
	 *
	 * @param array $request The form submission data.
	 * @param array $cfhs_fields Fields mapping configuration.
	 *
	 * @return array Prepared data for submission.
	 */
	private function prepare_data( array $request, array $cfhs_fields ): array {
		$data = array();
		foreach ( $cfhs_fields as $cfhs_field_key => $cfhs_field ) {
			if ( empty( $cfhs_field['key'] ) ) {
				continue;
			}

			$value = $this->format_value( $request[ $cfhs_field_key ] ?? null, $cfhs_field );

			if ( null === $value ) {
				continue;
			}

			$suffix = $prefix = '';
			if ( 'file' === $cfhs_field['type'] || 'hs_file_upload' === $cfhs_field['key'] ) {
				$suffix = '_file';
			} elseif ( '0-5' === $cfhs_field['object_type'] ) {
				$prefix = 'TICKET.';
			}

			$data[ $prefix . $cfhs_field['key'] ] = wp_strip_all_tags( $value . $suffix );

		}

		return $data;
	}

	/**
	 * Format the value based on its type.
	 *
	 * @param mixed $value The value to be formatted.
	 * @param array $cfhs_field Field configuration.
	 *
	 * @return mixed The formatted value.
	 */
	private function format_value( $value, array $cfhs_field ) {
		if ( is_array( $value ) ) {
			$value = implode( ';', $value );
		}

		if ( ( 'datetime' === $cfhs_field['type'] || 'date' === $cfhs_field['type'] ) && $value ) {
			$value = strtotime( $value ) . '000';
		}

		return $value;
	}

	/**
	 * Process data for further actions based on the provided parameters.
	 *
	 * @param string $hs_data The type of data to process. Can be 'contacts', 'deals', or any other value.
	 * @param int $form_id The ID of the form associated with the data.
	 * @param int $post_id The ID of the post associated with the form submission.
	 * @param array $data The data to be processed.
	 *
	 * @return object The response object resulting from the data processing.
	 */
	private function process_data( string $hs_data, int $form_id, int $post_id, array $data ): object {
		$record_id = 0;
		$response  = new \stdClass();
		$file_keys = [];
		$file_ids  = [];

		// Get file fields.
		foreach ( $data as $key => $field ) {
			if ( str_contains( $field, '_file' ) ) {
				$data[ $key ] = str_replace( '_file', '', $field );
				$file_keys[]  = $key;
			}
		}

		if ( 'contacts' === $hs_data ) {
			$action    = get_post_meta( $form_id, 'cfhs_action', true ) ?? 'create_or_update';
			$record_id = $this->process_contact_data( $hs_data, $data, $action, $form_id );
		} elseif ( 'deals' === $hs_data ) {
			$record_id = $this->hubspot->insertRecord( $hs_data, $data, $form_id );
		} else {
			$hubspot_forms = get_option( 'cfhs_hf' );
			if ( isset( $hubspot_forms[ $hs_data ] ) ) {
				$portal_id = get_option( 'cfhs_portal_id' );
				if ( $file_keys ) {
					foreach ( $file_keys as $file_key ) {
						$file_info         = $this->process_attachments( 0, $form_id, $hs_data, true );
						$data[ $file_key ] = $file_info->url;
						$file_ids[]        = $file_info->id;
					}
				}
				$response = $this->hubspot->submitForm( $portal_id, $hs_data, $data, $form_id, $post_id );
				if ( isset( $response->errors ) ) {
					// Remove attachments, if form submission response have errors
					foreach ( $file_ids as $file_id ) {
						$this->hubspot->remove_attachments( $file_id, $form_id );
					}
				}
			}
		}
		if ( $record_id > 0 ) {
			$this->process_attachments( $record_id, $form_id, $hs_data, false );
		}

		return $response;
	}


	/**
	 * Process contact data based on the provided HubSpot module, data, action, and form_id.
	 *
	 * @param string $module The HubSpot module type.
	 * @param array $data The data to be submitted.
	 * @param string $action The action to perform (e.g., "create", "update").
	 * @param int $form_id The ID of the form being submitted.
	 *
	 * @return int The record ID of the processed contact data, or 0 if there was an error.
	 */
	private function process_contact_data( string $module, array $data, string $action, int $form_id ): int {
		if ( 'create' === $action ) {
			$record = $this->hubspot->insertRecord( $module, $data, $form_id );

			return isset( $record->id ) ?? 0;
		} else {
			$email = isset( $data['email'] ) ?? null;
			if ( $email ) {
				$records = $this->hubspot->getRecordsByEmail( $module, $email );
				if ( $records && isset( $records->results[0]->id ) ) {
					$record = $this->hubspot->updateRecord( $module, $data, $records->results[0]->id, $form_id );

					return isset( $record->id ) ?? 0;
				}
			}
			$record = $this->hubspot->insertRecord( $module, $data, $form_id );

			return isset( $record->id ) ?? 0;
		}
	}

	/**
	 * Process the attachments for a given record.
	 *
	 * @param int $record_id The ID of the record.
	 * @param int $form_id The ID of the form.
	 * @param string $module The HubSpot module type.
	 */
	private function process_attachments( int $record_id, int $form_id, string $module, bool $direct ): bool|object {

		$submission = \WPCF7_Submission::get_instance();
		if ( ! $submission ) {
			return false;
		}

		$files = $submission->uploaded_files();
		if ( ! $files ) {
			return false;
		}

		$attachment_fields = get_post_meta( $form_id, 'cfhs_fields', true );
		if ( ! $attachment_fields ) {
			return false;
		}

		foreach ( $attachment_fields as $attachment_field_key => $attachment_field ) {
			if ( ! isset( $files[ $attachment_field_key ] ) || ! $files[ $attachment_field_key ] ) {
				continue;
			}

			$file      = is_array( $files[ $attachment_field_key ] )
				? $files[ $attachment_field_key ][0]
				: $files[ $attachment_field_key ];
			$file_name = basename( $file );

			if ( class_exists( 'CURLFile' ) ) {
				$file = new \CURLFile( $file, 'application/octet-stream' );
			} else {
				$file = '@' . realpath( $file );
			}

			$file_options = array(
				'access'                      => 'PUBLIC_NOT_INDEXABLE',
				'ttl'                         => 'P12M',
				'overwrite'                   => 'false',
				'duplicateValidationStrategy' => 'NONE',
				'duplicateValidationScope'    => 'ENTIRE_PORTAL'
			);

			$file_data = array(
				'fileName'   => $file_name,
				'file'       => $file,
				'options'    => wp_json_encode( $file_options ),
				'folderPath' => '/docs',
			);

			return $this->hubspot->uploadFile( $file_data, $module, $record_id, $direct );
		}

		return false;
	}
}
