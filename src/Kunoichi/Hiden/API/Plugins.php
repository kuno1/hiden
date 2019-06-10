<?php

namespace Kunoichi\Hiden\API;


use Kunoichi\Hiden\Pattern\Singleton;

/**
 * Get plugin API
 *
 * @package hiden
 */
class Plugins extends Singleton {

	use Endpoint;

	/**
	 * Retrieve plugin list.
	 *
	 * @param array $plugins
	 *
	 * @return array|\WP_Error
	 */
	public function get_plugin_list( $plugins ) {
		if ( ! $plugins ) {
			return [];
		}
		$plugin_list = [];
		foreach ( $plugins as $slug => $version ) {
			$plugin_list[] = [
				'slug'    => $slug,
				'version' => $version,
			];
		}
		$request = [
			'api_key'  => $this->get_api_key(),
			'site_url' => $this->get_site_url(),
			'plugins'  => json_encode( $plugin_list ),
		];
		$response = wp_remote_post( $this->plugin_endpoint(), [
			'timeout'    => 10,
			'user-agent' => 'WordPress / Hiden 1.0',
			'body'       => $request,
			'sslverify'  => false,
		] );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		return json_decode( $response['body'] );
	}

	/**
	 * Get single plugin information.
	 *
	 * @param string $slug
	 * @return array
	 */
	public function get_single_plugin_info( $slug ) {
		return false;
	}

	/**
	 * Handle plugin information.
	 *
	 * @param bool      $result
	 * @param string    $action
	 * @param \stdClass $args
	 *
	 * @return bool
	 */
	public function plugins_api( $result, $action, $args ) {
		switch ( $action ) {
			case 'plugin_information':
				return $this->plugin_information( $args );
			case 'query_plugins':
			case 'hot_tags':
			case 'hot_categories':
			default:
				return $result;
		}
	}

	/**
	 * Get plugin information if they were kunoichi.
	 *
	 * @param \stdClass $args
	 *
	 * @return bool
	 */
	protected function plugin_information( $args ) {
		// Search readme and find URL.
		$info = $this->find_readme_from_slug( $args->slug );
		if ( ! $info || empty( $info['PluginURI'] ) || ! $this->is_kunoichi( $info['PluginURI'] ) ) {
			return false;
		}
		$result = $this->get_plugin_list( [ $info['MainFile'] => $info['Version'] ] );
		if ( ! $result || is_wp_error( $result ) ) {
			return false;
		}
		return $result;
		var_dump( $result );
		exit;
		return false;
	}

	/**
	 * Get plugin data from slug.
	 *
	 * @param string $slug
	 *
	 * @return array
	 */
	public function find_readme_from_slug( $slug ) {
		$path = WP_PLUGIN_DIR . '/' . $slug;
		if ( ! is_dir( $path ) ) {
			return [];
		}
		$found_data = [];
		foreach ( scandir( $path ) as $file ) {
			if ( ! preg_match( '#\.php$#u', $file ) ) {
				continue;
			}
			$plugin_data = get_plugin_data( $path . '/' . $file, false, false );
			if ( empty( $plugin_data['Name'] ) ) {
				continue;
			}
			$plugin_data['MainFile'] = $slug . '/' . $file;
			$found_data = $plugin_data;
			break;
		}
		return $found_data;
	}
}
