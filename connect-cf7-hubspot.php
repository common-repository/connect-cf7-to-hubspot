<?php
/**
 * Plugin Name:       Connect CF7 to HubSpot
 * Plugin URI:        #
 * Description:       Connect CF7 to HubSpot plugin allows you to send Contact Form 7 data to HubSpot.
 * Version:           1.1.6
 * Requires at least: 5.3
 * Requires PHP:      8.0
 * Author:            ProCoders
 * Author URI:        https://procoders.tech/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       connect-cf7-hubspot
 * Domain Path:       /languages
 *
 * @package cf7sh
 */

namespace Procoders\Cf7hs;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use \Procoders\Cf7hs\Admin\RegisterMenu as Menu;
use \Procoders\Cf7hs\Admin\SettingsLinks as Links;
use \Procoders\Cf7hs\Admin\ScriptsManager as Scripts;

define( 'CF7SH_VERSION', '1.1.6' );
define( 'CF7SH_FILE', __FILE__ );
define( 'CF7HS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * cf7sh class.
 */

class cf7sh {

	/**
	 * Holds the class instance.
	 *
	 * @var cf7sh $instance
	 */
	private static ?cf7sh $instance = null;

	/**
	 * Return an instance of the class
	 *
	 * @return cf7sh class instance.
	 * @since 1.0.0
	 */
	public static function get_instance(): cf7sh {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class initializer.
	 */
	public function plugins_loaded(): void {
		load_plugin_textdomain(
			'connect-cf7-hubspot',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);

		// Register the admin menu.
		Menu::run();
		Links::run();
		// Register Script.
		Scripts::run();
		$submission = new Includes\Submission();

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wpcf7_before_send_mail', array( $submission, 'init' ), 10, 3 );
	}

	/**
	 * Init plugin.
	 */
	public function init(): void {
		// Silent.
	}
}

add_action(
	'plugins_loaded',
	function () {
		$cf7sh = cf7sh::get_instance();
		$cf7sh->plugins_loaded();
	}
);

register_activation_hook(
	__FILE__,
	function () {
		update_option( 'cfhs_modules', 'a:2:{s:8:"contacts";s:8:"Contacts";s:5:"deals";s:5:"Deals";}' );
	}
);
