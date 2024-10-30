<?php
/**
 * Helper functions for the plugin.
 *
 * @package Cf7hs
 */

namespace Procoders\Cf7hs;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

/**
 * Class Functions
 */
class Functions {


	/**
	 * Return the plugin slug.
	 *
	 * @return string plugin slug.6
	 */
	public static function get_plugin_slug(): string {
		return dirname( plugin_basename( CF7SH_FILE ) );
	}

	/**
	 * Return the basefile for the plugin.
	 *
	 * @return string base file for the plugin.
	 */
	public static function get_plugin_file(): string {
		return plugin_basename( CF7SH_FILE );
	}

	/**
	 * Return the plugin path.
	 *
	 * @return string path to plugin
	 */
	public static function get_plugin_path(): string {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Return the version for the plugin.
	 *
	 * @return float version for the plugin.
	 */
	public static function get_plugin_version(): float {
		return CF7SH_VERSION;
	}


	/**
	 * Return HS api url
	 *
	 * @return string return Hub Spot API url
	 */
	public static function get_HS_api_url(): string {
		return 'https://api.hubapi.com';
	}
}
