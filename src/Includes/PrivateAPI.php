<?php
/**
 * API private class for SH
 *
 * @package Cf7hs
 */

namespace Procoders\Cf7hs\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Procoders\Cf7hs\Functions as Functions;

/**
 * Class PrivateAPI
 *
 * Provides access to private APIs with authentication.
 */
class PrivateAPI {

	/**
	 * The access token used for authentication.
	 *
	 * @var string|null
	 */
	private string $access_token;

	/**
	 * The URL of the api endpoint
	 *
	 * @var string
	 */
	private string $url;

	/**
	 * API constructor.
	 *
	 * @param string $url URL of the HubSpot API.
	 * @param string $access_token HS token key
	 */
	public function __construct( string $url, string $access_token ) {
		$this->url          = $url; // 'https://api.hubapi.com'.
		$this->access_token = $access_token;
	}

	/**
	 * Performs an HTTP request to a specified URL and endpoint.
	 *
	 * @param string $url The base URL to send the request to.
	 * @param string $endpoint The endpoint to append to the URL.
	 * @param string $method The HTTP method to use. Defaults to 'GET'.
	 * @param array $body The request body data. Defaults to an empty array.
	 * @param int|null $form_id The ID of the form being submitted. Optional.
	 *
	 * @return object The response from the remote server as a JSON-decoded object.
	 */
	private function performRequest( string $url, string $endpoint, string $method = 'GET', array $body = [], ?int $form_id = null ): null|object {
		$header = array(
			'Authorization' => 'Bearer ' . $this->access_token,
			'Content-Type'  => 'application/json',
		);

		$args = array(
			'method'      => $method,
			'timeout'     => 30,
			'httpversion' => '1.0',
			'headers'     => $header,
			'sslverify'   => false,
		);

		if ( ! empty( $body ) ) {
			$args['body'] = wp_json_encode( $body );
		}

		$wp_remote_response = wp_remote_request( $url . $endpoint, $args );
		$json_response      = '';

		if ( ! is_wp_error( $wp_remote_response ) ) {
			$json_response = $wp_remote_response['body'];
		}

		$response = json_decode( $json_response );

		// Handling error response.
		$this->handleErrorResponse( $response, $form_id );

		return $response;
	}

	/**
	 * Get account details.
	 *
	 * @return object Account details response.
	 */
	public function getAccountDetails(): object {
		return $this->performRequest(
			$this->url,
			'/account-info/v3/details',
			'GET',
			[],
			1
		);
	}

	/**
	 * Get HubSpot fields.
	 *
	 * @param string $hs_data Type of data (contacts or deals).
	 *
	 * @return array Array of HubSpot fields.
	 */
	public function getHSFields( string $hs_data ): array {
		$response = $this->performRequest(
			$this->url,
			'/crm/v3/properties/' . $hs_data,
			'GET',
			[],
			1
		);

		$fields = array();
		if ( isset( $response->results ) && null !== $response->results ) {
			foreach ( $response->results as $field ) {
				if ( ! $field->modificationMetadata->readOnlyValue ) {
					$fields[ $field->name ] = array(
						'label'    => $field->label,
						'type'     => $field->type,
						'required' => 0,
					);
				}
			}
			$fields['attachment_field'] = array(
				'label'    => 'Attachments',
				'type'     => 'relate',
				'required' => 0,
			);
		}

		return $fields;
	}

	/**
	 * Method to synchronize CF7 forms.
	 *
	 * @return object Response from the API.
	 */
	public function syncCF7Forms(): object {
		return $this->performRequest(
			$this->url,
			'/marketing/v3/forms',
			'GET',
			[],
			1
		);
	}

	/**
	 * Method to insert a record into HubSpot API.
	 *
	 * @param string $hs_data The HubSpot API data type.
	 * @param array $data The record data.
	 * @param int $form_id The ID of the form.
	 *
	 * @return object The response from the API.
	 */
	public function insertRecord( string $hs_data, array $data, int $form_id ): object {
		// Forming properties array
		$properties = array();
		foreach ( $data as $property_key => $property_value ) {
			$properties['properties'][ $property_key ] = $property_value;
		}

		return $this->performRequest(
			$this->url,
			'/crm/v3/objects/' . $hs_data,
			'POST',
			$properties,
			$form_id
		);
	}

	/**
	 * Method to get records by email.
	 *
	 * @param string $hs_data The HS data.
	 * @param string $email The email to search for.
	 *
	 * @return object   The response object.
	 */
	public function getRecordsByEmail( string $hs_data, string $email ): null|object {
		$data = array(
			'filterGroups' => array(
				array(
					'filters' => array(
						array(
							'value'        => $email,
							'propertyName' => 'email',
							'operator'     => 'EQ',
						),
					),
				),
			),
		);

		return $this->performRequest(
			$this->url,
			'/crm/v3/objects/' . $hs_data . '/search',
			'POST',
			$data,
			null
		);
	}

	/**
	 * Method to submit form.
	 *
	 * @param int $portal_id The portal ID.
	 * @param string $hsform_id The HS form ID.
	 * @param array $data The form data.
	 * @param int $form_id The form ID.
	 * @param int $page_id The page ID where form was placed.
	 *
	 * @return object Response from the API.
	 */
	public function submitForm( int $portal_id, string $hsform_id, array $data, int $form_id, int $page_id ): object {
		// Forming fields array
		$fields = array();

		foreach ( $data as $key => $value ) {
			$fields[] = array(
				'name'  => $key,
				'value' => $value,
			);
		}

		$data_['submittedAt'] = date_create()->format( 'Uv' );
		$data_['fields']      = $fields;

		// Adding context information.
		$data_ = $this->addContextInfo( $data_, $page_id );

		return $this->performRequest(
			'https://api.hsforms.com',
			'/submissions/v3/integration/submit/' . $portal_id . '/' . $hsform_id,
			'POST',
			$data_,
			$form_id
		);
	}

	/**
	 * Method to add context information.
	 *
	 * @param array $data The data array.
	 * @param int $page_id The Page ID where form was placed.
	 *
	 * @return array The data array with added context information.
	 */
	private function addContextInfo( array $data, int $page_id = 1 ): array {
		$ip = $this->clientIpInfo();

		if ( $ip ) {
			$data['context']['ipAddress'] = $ip;
		}

		if ( isset( $_COOKIE['hubspotutk'] ) ) {
			$data['context']['hutk'] = sanitize_text_field( $_COOKIE['hubspotutk'] );
		}

		if ( $page_id ) {
			$data['context']['pageUri']  = get_permalink( $page_id );
			$data['context']['pageName'] = get_the_title( $page_id );
		}

		return $data;
	}

	/**
	 * Method to retrieve the client's IP address.
	 *
	 * @return string The client's IP address.
	 */
	private function clientIpInfo(): string {
		$ipaddress = '';
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ipaddress = filter_var( $_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ipaddress = filter_var( $_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$ipaddress = filter_var( $_SERVER['HTTP_X_FORWARDED'], FILTER_VALIDATE_IP );
		} elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$ipaddress = filter_var( $_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP );
		} elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			$ipaddress = filter_var( $_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ipaddress = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
		}

		return $ipaddress;
	}

	/**
	 * Updates a record in the specified HubSpot data object.
	 *
	 * @param string $hs_data The name of the HubSpot data object.
	 * @param array $data The data to be updated in the record.
	 * @param int $record_id The ID of the record to be updated.
	 * @param int $form_id The ID of the form (optional).
	 *
	 * @return object The response from the HubSpot API.
	 */
	public function updateRecord( string $hs_data, array $data, int $record_id, int $form_id ): object {
		$properties = array();
		if ( null !== $data ) {
			foreach ( $data as $property_key => $property_value ) {
				$properties['properties'][ $property_key ] = $property_value;
			}
		}

		return $this->performRequest(
			$this->url,
			'/crm/v3/objects/' . $hs_data . '/' . $record_id,
			'PATH',
			$properties,
			$form_id
		);
	}

	/**
	 * Method to update a record in HubSpot.
	 *
	 * @param array $data The data to be updated.
	 * @param string $hs_data The HubSpot data type.
	 * @param int $record_id The ID of the record to be updated.
	 *
	 * @return object The response from the API.
	 */
	public function uploadFile( array $data, string $hs_data, int $record_id, bool $direct ): object {
		$url    = $this->url . '/files/v3/files';
		$header = array(
			'Authorization: Bearer ' . $this->access_token,
			'Content-Type: multipart/form-data',
		);
		$ch     = \curl_init( $url );
		\curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
		\curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		\curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		\curl_setopt( $ch, CURLOPT_POST, true );
		\curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		$json_response = \curl_exec( $ch );
		\curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		\curl_close( $ch );
		$response = json_decode( $json_response );
		if ( $direct ) {
			return $response;
		}
		if ( isset( $response->id ) && $record_id > 0 ) {
			$attachment_id      = $response->id;
			$header             = array(
				'Authorization' => 'Bearer ' . $this->access_token,
				'Content-Type'  => 'application/json',
			);
			$url                = $this->url . '/crm/v3/objects/notes';
			$data               = array(
				'properties' => array(
					'hs_timestamp'      => gmdate( 'Y-m-d\TH:i:s.000\Z' ),
					'hs_attachment_ids' => $attachment_id,
					'hs_note_body'      => $response->url
				)
			);
			$data               = wp_json_encode( $data );
			$args               = array(
				'headers'   => $header,
				'body'      => $data,
				'sslverify' => false,
			);
			$wp_remote_response = wp_remote_post( $url, $args );
			if ( ! is_wp_error( $wp_remote_response ) ) {
				$json_response = wp_remote_retrieve_body( $wp_remote_response );
				$response      = json_decode( $json_response );
				if ( isset( $response->id ) && $response > 0 ) {
					$note_id            = $response->id;
					$header             = array(
						'Authorization' => 'Bearer ' . $this->access_token,
						'Content-Type'  => 'application/json',
					);
					$hs_data            = 'contacts' !== $hs_data && 'deals' !== $hs_data ? 'contacts' : $hs_data;
					$url                = $this->url . '/crm/v3/associations/notes/' . $hs_data . '/batch/create';
					$type               = 'deals' === $hs_data ? 'note_to_deal' : 'note_to_contact';
					$data               = array(
						'inputs' => array(
							array(
								'from' => array(
									'id' => $note_id,
								),
								'to'   => array(
									'id' => $record_id,
								),
								'type' => $type,
							),
						),
					);
					$data               = wp_json_encode( $data );
					$args               = array(
						'headers'   => $header,
						'body'      => $data,
						'sslverify' => false,
					);
					$wp_remote_response = wp_remote_post( $url, $args );;
					if ( ! is_wp_error( $wp_remote_response ) ) {
						$json_response = wp_remote_retrieve_body( $wp_remote_response );
						$response      = json_decode( $json_response );
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Removes attachments by sending a POST request to the specified URL and endpoint.
	 *
	 * @param int $file_id The ID of the file to be deleted.
	 * @param int $form_id The ID of the form being submitted.
	 *
	 * @return bool Returns true if the attachment was successfully deleted, false otherwise.
	 */
	public function remove_attachments( int $file_id, int $form_id ): bool {
		$response = $this->performRequest(
			$this->url,
			'/filemanager/api/v2/files/' . $file_id . '/full-delete',
			'POST',
			[],
			$form_id
		);

		return $response->succeeded ?? false;
	}

	/**
	 * Method to handle error response.
	 *
	 * @param object $response The response object.
	 * @param int|null $form_id The ID of the form (optional).
	 *
	 * @return void
	 */
	private function handleErrorResponse( object|null $response, ?int $form_id = null ): bool {
		global $wp_filesystem;

		// Must include this line to use the WP_Filesystem methods.
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', dirname( __FILE__ ) . 'PrivateAPI.php/' );
		}

		// Include the WP_Filesystem class.
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		// Initialize the WP_Filesystem.
		WP_Filesystem();

		// Check if directory and file exist, if not create them
		$log_path = Functions::get_plugin_path() . 'Logs';
		$log_file = $log_path . '/debug.log';

		if ( ! $wp_filesystem->is_dir( $log_path ) ) {
			$wp_filesystem->mkdir( $log_path );
		}

		if ( ! $wp_filesystem->exists( $log_file ) ) {
			$wp_filesystem->put_contents( $log_file, '', FS_CHMOD_FILE ); // empty file
		}

		if ( isset( $response->status ) && 'error' === $response->status ) {
			$log     = $wp_filesystem->get_contents( $log_file );
			$log     .= 'Message: ' . $response->message . "\n";
			$log     .= 'Response: ' . wp_json_encode( $response ) . "\n";
			$log     .= 'Date: ' . gmdate( 'Y-m-d H:i:s' ) . "\n\n";
			$send_to = get_option( 'cfhs_notification_send_to' );

			if ( $send_to ) {
				$to      = $send_to;
				$subject = get_option( 'cfhs_notification_subject' );
				$body    = '<ul style="list-style:none; padding-left:0; margin-left:0;" >';
				$body    .= '<li><strong>Form ID:</strong> ' . $form_id ?? 'Not set' . '</li>';
				$body    .= '<li><strong>Message:</strong> ' . $response->message . '</li>';
				$body    .= '<li><strong>Response: </strong><pre>' . wp_json_encode( $response ) . '</pre></li>';
				$body    .= '<li><strong>Date:</strong> ' . gmdate( 'Y-m-d H:i:s' ) . '</li>';
				$body    .= '</ul>';

				$headers = array(
					'Content-Type: text/html; charset=UTF-8',
				);

				wp_mail( $to, $subject, $body, $headers );
			}
			$wp_filesystem->put_contents( Functions::get_plugin_path() . 'Logs/debug.log', $log, FS_CHMOD_FILE );

			// pre-defining permissions to 0644 i.e. FS_CHMOD_FILE.
			return false;
		}

		return true;
	}
}

