<?php
/**
 * Initialize and display admin panel output.
 *
 * @package Cf7hs
 */

namespace Procoders\Cf7hs\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Procoders\Cf7hs\Functions as Functions;
use Procoders\Cf7hs\Includes as Includes;
use Procoders\Cf7hs\Loader as Loader;

/**
 * Class Settings
 */
class Settings {

	/**
	 * Handles and updates settings submitted from the admin panel.
	 * Renders the settings template with updated values.
	 */
	public function settings_callback(): void {
		$template = new Loader();
		$settings = array();

		$notification_subject_default = esc_html__( 'API Error Notification', 'connect-cf7-hubspot' );

		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'cf7hs_submit_form' );
		}

		// Check for 'connect' submission.
		if ( isset( $_POST['connect'] ) ) {
			$settings['message'] = $this->getConnectionStatusMessage();
		}

		// Check for 'submit' submission.
		if ( isset( $_POST['submit'] ) ) {
			$this->updateOptionFields();
		}

		// Get saved options.
		$settings['access_token'] = get_option( 'cfhs_access_token' );

		// Get notification_subject, set default if not exists.
		$settings['notification_subject'] = get_option( 'cfhs_notification_subject', $notification_subject_default );
		$settings['notification_send_to'] = get_option( 'cfhs_notification_send_to' );
		$settings['uninstall']            = get_option( 'cfhs_uninstall' );

		$template->set_template_data(
			array(
				'template' => $template,
				'settings' => $settings,
			)
		)->get_template_part( 'admin/settings' );
	}

	/**
	 * Returns an array containing a status message and a success flag for the connection status
	 *
	 * @return array The status message and a success flag
	 */
	private function getConnectionStatusMessage(): array {
		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'cf7hs_submit_form' );
		}

		if ( ! isset( $_POST['cfhs_access_token'] ) ) {
			return array();
		}
		$connectionStatus = $this->setToken( sanitize_text_field( wp_unslash( $_POST['cfhs_access_token'] ) ) );

		return array(
			'text'    => $connectionStatus
				? esc_html__( 'Connection Successful.', 'connect-cf7-hubspot' )
				: esc_html__( 'Connection Error.', 'connect-cf7-hubspot' ),
			'success' => $connectionStatus,
		);
	}

	/**
	 * Iterates over defined option fields and updates each with the submitted value
	 * Casts to int if the option is 'cfhs_uninstall'
	 */
	private function updateOptionFields(): void {
		$option_fields = array(
			'cfhs_notification_subject',
			'cfhs_notification_send_to',
			'cfhs_uninstall',
		); // define the option fields.

		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'cf7hs_submit_form' );
		}

		// perform update_option for each option field.
		foreach ( $option_fields as $option_field ) {
			$field_value = isset( $_POST[ $option_field ] )
				? sanitize_text_field( wp_unslash( $_POST[ $option_field ] ) )
				: null;

			// update_option only if $field_value is not null; casting to int if it's 'cfhs_uninstall'.
				update_option(
					$option_field,
					'cfhs_uninstall' === $option_field
						? (int) $field_value
						: $field_value
				);
		}
	}

	/**
	 * Sets the access token.
	 *
	 * This function sets the access token as an option if a successful connection to the HubSpot API is established.
	 * It also sets the Portal ID as an option if it is available.
	 * The function will return true if the API connection is successful and false otherwise.
	 *
	 * @param string $token The access token.
	 *
	 * @return bool Returns true if the fields are not empty (meaning, API connection is established), false otherwise.
	 */
	public function setToken( string $token ): bool {
		$hubspot = new Includes\privateAPI( Functions::get_HS_api_url(), $token );
		$fields  = $hubspot->getHSFields( 'contacts' );
		if ( ! empty( $fields ) ) {
			update_option( 'cfhs_access_token', $token );
			$this->reset_forms();
			$account_details = $hubspot->getAccountDetails();
			if ( isset( $account_details->portalId ) && $account_details->portalId ) {
				update_option( 'cfhs_portal_id', $account_details->portalId );
			}
			return true;
		} else {
			return false;
		}
	}

	private function reset_forms(): void {
		$all_forms_ids = get_posts(array(
			'fields'          => 'ids',
			'posts_per_page'  => -1,
			'post_type' => 'wpcf7_contact_form'
		));

		foreach ( $all_forms_ids as $id ) {
			delete_post_meta( $id, 'cfhs_active' );
			delete_post_meta($id, 'cfhs_module');
		}
	}
}
