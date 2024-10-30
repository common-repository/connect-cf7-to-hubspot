<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$uninstall = get_option( 'cfhs_uninstall' );
if ( $uninstall ) {
	delete_option( 'cfhs_api_key' );
	delete_option( 'cfhs_hf' );
	delete_option( 'cfhs_modules' );
	delete_option( 'cfhs_access_token' );
	delete_option( 'cfhs_portal_id' );
}