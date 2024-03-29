<?php

namespace Kunoichi\Hiden\API;

/**
 * Endpoint traits
 *
 * @package Kunoichi\Hiden\API
 */
trait Endpoint {

	/**
	 * Get hiden network key.
	 *
	 * @return string
	 */
	protected function get_api_key() {
		return get_option( 'hiden_api_key', '' );
	}

	/**
	 * Detect if debug endpoint is set.
	 *
	 * @return bool
	 */
	protected function is_debug() {
		return defined( 'HIDEN_DEBUG_ENDPOINT' ) && preg_match( '#^https?://#u', HIDEN_DEBUG_ENDPOINT );
	}

	/**
	 * Get endpoint URL.
	 *
	 * @return string
	 */
	protected function endpoint() {
		if ( defined( 'HIDEN_DUMMY_ENDPOINT' ) && HIDEN_DUMMY_ENDPOINT ) {
			$endpoint = rest_url( '/hiden/v1' );
		} elseif ( $this->is_debug() ) {
			$endpoint = HIDEN_DEBUG_ENDPOINT;
		} else {
			$endpoint = 'https://kunoichiwp.com';
		}
		return untrailingslashit( $endpoint );
	}
	
	/**
	 * Detect if
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public function is_kunoichi_url( $url ) {
		foreach ( [ $this->endpoint(), 'kunoichiwp.com' ] as $haystack ) {
			if ( false !== strpos( $url, $haystack ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get plugin endpoint.
	 *
	 * @param string $plugin_slug
	 * @return string
	 */
	protected function plugin_endpoint( $plugin_slug = '' ) {
		if ( $plugin_slug ) {
			$endpoint = $this->endpoint() . sprintf( '/wp-json/makibishi/v1/plugin/%s', rawurlencode( $plugin_slug ) );
		} else {
			$endpoint = $this->endpoint() . '/wp-json/makibishi/v1/plugins/list';
		}
		return esc_url( $endpoint );
	}

	/**
	 * Get theme endpoint.
	 *
	 * @return string
	 */
	protected function theme_endpoint() {
		return $this->endpoint() . '/themes';
	}

	/**
	 * Get translation endpoint.
	 *
	 * @return string
	 */
	protected function translation_endpoint() {
		return $this->endpoint() . '/translations';
	}

	/**
	 * Get site URL.
	 *
	 * @return string
	 */
	protected function get_site_url() {
		return is_multisite() ? network_home_url() : home_url();
	}

	/**
	 * Detect if plugin is made by kunoichi.
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	protected function is_kunoichi( $url ) {
		return $url && preg_match( '#^https?://kunoichiwp\.com#u', $url );
	}
}
