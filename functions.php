<?php
/**
 * Hiden utility functions.
 *
 * @package hiden
 */

/**
 * Get hiden version.
 *
 * @return string
 */
function hiden_version() {
	static $info = null;
	if ( is_null( $info ) ) {
		$info = get_file_data( __DIR__ . '/hiden.php', [
			'version' => 'Version',
		] );
	}
	return $info['version'];
}

/**
 * Get hiden's root directory.
 *
 * @return string
 */
function hiden_dir() {
	return __DIR__;
}

/**
 * Get directory URL.
 *
 * @return string
 */
function hiden_url() {
	return untrailingslashit( plugin_dir_url( __FILE__ ) );
}
