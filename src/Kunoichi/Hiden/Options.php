<?php

namespace Kunoichi\Hiden;


use Kunoichi\Hiden\Pattern\Singleton;

class Options extends Singleton {

	private $log_option_key = 'hiden_option_log';

	/**
	 * Constructor
	 */
	protected function init() {
		// Do something in constructor.
	}

	/**
	 * Clear log
	 */
	public function clear_log() {
		if ( is_multisite() ) {
			delete_site_option( $this->log_option_key );
		} else {
			delete_option( $this->log_option_key );
		}
	}

	/**
	 * Save log data.
	 *
	 * @param array $log
	 */
	public function save_log( $log ) {
		if ( is_multisite() ) {
			update_site_option( $this->log_option_key, $log );
		} else {
			update_option( $this->log_option_key, $log );
		}
	}

	/**
	 * Get log
	 *
	 * @return array
	 */
	public function get_log() {
		if ( is_multisite() ) {
			return get_site_option( $this->log_option_key, [] );
		} else {
			return get_option( $this->log_option_key, [] );
		}
	}
}
