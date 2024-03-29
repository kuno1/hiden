<?php
/**
Plugin Name: Kunoichi Updater
Plugin URI: https://kunoichiwp.com/product/plugin/hiden
Description: Plugin and theme updater for kunoichi.
Version: nightly
PHP Version: 5.6
Author: Kunoichi INC.
Author URI: https://kunoichiwp.com
License: GPL 3.0 or later
Text Domain: hiden
Domain Path: /languages
 */

defined( 'ABSPATH' ) || die();

/**
 * Plugin init.
 */
function hiden_plugins_loaded() {
	// Register text domain.
	load_plugin_textdomain( 'hiden', false, basename( __DIR__ ) . '/languages' );
	// Load functions.
	// Check PHP version.
	if ( version_compare( '5.4.0', phpversion(), '>' ) ) {
		add_action( 'admin_notices', 'hiden_php_error' );
		return;
	}
	require __DIR__ . '/functions.php';
	require __DIR__ . '/vendor/autoload.php';
	call_user_func( [ 'Kunoichi\\Hiden\\Updater', 'get_instance' ] );
	// If this is CLI, register command.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::add_command( 'hiden', 'Kunoichi\\Hiden\\Command' );
	}
	// If dummy API is registered, enable REST API.
	if ( defined( 'HIDEN_DUMMY_ENDPOINT' ) && HIDEN_DUMMY_ENDPOINT ) {
		\Kunoichi\Hiden\API\Dummy::get_instance();
	}
}
add_action( 'plugins_loaded', 'hiden_plugins_loaded' );

/**

 * Display PHP version error.
 */
function hiden_php_error() {
	printf( '<div class="error"><p>%s</p></div>', esc_html( sprintf( __( 'Kunoichi Updater requires PHP 5.4 and over, but yours is %s. Please consider upgrading PHP.', 'hiden' ), phpversion() ) ) );
}
