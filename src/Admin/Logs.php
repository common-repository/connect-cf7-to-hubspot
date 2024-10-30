<?php
/**
 * Initialize and display admin logs page
 *
 * @package Cf7hs
 */

namespace Procoders\Cf7hs\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Procoders\Cf7hs\Functions as Functions;
use Procoders\Cf7hs\Loader as Loader;

/**
 * Class Functions
 */
class Logs {

	/**
	 * This function handles logging of errors. It uses a file located by life_path as a storage medium for errors.
	 * The function opens the file, reads its content and then closes it.
	 *
	 * @return void
	 */
	public static function error_logs_callback(): void {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$template  = new Loader();
		$file_path = Functions::get_plugin_path() . 'Logs/debug.log';

		$file_data = $wp_filesystem->get_contents( $file_path );
		if ( false === $file_data ) {
			$file_data = esc_html__( 'No Error Logs found.', 'connect-cf7-hubspot' );
		}

		$template->set_template_data(
			array(
				'template'  => $template,
				'file_data' => $file_data,
			)
		)->get_template_part( 'admin/logs' );
	}
}
