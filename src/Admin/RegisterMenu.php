<?php
/**
 * Initialize the admin menu.
 *
 * @package Cf7hs
 */

namespace Procoders\Cf7hs\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Procoders\Cf7hs\Functions as Functions;
use Procoders\Cf7hs\Admin\Init as Init;
use Procoders\Cf7hs\Admin\Settings as Settings;
use Procoders\Cf7hs\Admin\Logs as Logs;
/**
 * Create the admin menu.
 */
class RegisterMenu {

	/**
	 * Main class runner.
	 */
	public static function run(): void {
		add_action( 'admin_menu', array( static::class, 'init_menu' ) );
	}

	/**
	 * Register the plugin menu.
	 */
	public static function init_menu(): void {

		$init           = new Init();
		$settings       = new Settings();
		$logs           = new Logs();

		$slug = functions::get_plugin_slug();

		add_menu_page(
			esc_html__( 'Contact Form 7 - HubSpot Integration', 'connect-cf7-hubspot' ),
			esc_html__( 'CF7 to HubSpot', 'connect-cf7-hubspot' ),
			'manage_options',
			$slug,
			array( $init, 'init_callback' ),
			'dashicons-forms'
		);

		add_submenu_page(
			$slug,
			esc_html__( 'CF7 to HubSpot: Settings', 'connect-cf7-hubspot' ),
			esc_html__( 'Settings', 'connect-cf7-hubspot' ),
			'manage_options',
			'cfhs_settings',
			array( $settings, 'settings_callback' ),
		);

		add_submenu_page(
			$slug,
			esc_html__( 'CF7 - HubSpot: Error Logs', 'connect-cf7-hubspot' ),
			esc_html__( 'Error Logs', 'connect-cf7-hubspot' ),
			'manage_options',
			'cfhs_api_error_logs',
			array( $logs, 'error_logs_callback' ),
		);

	}
}
