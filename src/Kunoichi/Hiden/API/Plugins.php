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
			return new \WP_Error( 'no_plugins_specified', __( 'No plugin is specified.', 'hiden' ), [
				'status' => 404,
			] );
		}
		$request = [
			'api_key'  => $this->get_api_key(),
			'site_url' => $this->get_site_url(),
			'plugins'  => json_encode( $plugins ),
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
		$response_json = json_decode( $response['body'] );
		if ( is_null( $response_json ) ) {
			return $this->invalid_error();
		}
		return array_map( [ $this, 'object_to_assocs' ], $response_json );
	}
	
	/**
	 * get plugin detail.
	 *
	 * @param string $slug
	 * @return \stdClass|\WP_Error
	 */
	public function get_plugin_detail( $slug ) {
		$endpoint = add_query_arg( [
			'api_key'  => $this->get_api_key(),
			'site_url' => $this->get_site_url(),
		], $this->plugin_endpoint( $slug ) );
		$response = wp_remote_get( $endpoint, [
			'timeout'    => 10,
			'user-agent' => 'WordPress / Hiden 1.0',
			'sslverify'  => false,
		] );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$data = json_decode( $response['body'] );
		if ( ! $data ) {
			return $this->invalid_error();
		}
		return $this->object_to_assocs( $data );
	}
	
	/**
	 * Convert JSON properties to array.
	 *
	 * @param \stdClass $json
	 *
	 * @return \stdClass
	 */
	private function object_to_assocs( $json ) {
		foreach ( $json as $key => $value ) {
			if ( ! is_object( $value ) ) {
				continue;
			}
			switch ( $key ) {
				case 'contributors':
					$value = (array) $value;
					array_walk( $value, function( &$value, $key ) {
						$value = (array) $value;
					} );
					$json->{$key} = $value;
					break;
				default:
					$json->{$key} = (array) $value;
					break;
			}
		}
		return $json;
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
		$result = $this->get_plugin_detail( $args->slug );
		if ( is_wp_error( $result ) ) {
			wp_die( $result->get_error_message() );
		}
		return $result;
	}
	
	/**
	 * Grab all plugins from Kunoichi.
	 *
	 * @return array[]
	 */
	public function grab_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			return [];
		}
		$should_return = [];
		foreach ( get_plugins() as $slug => $plugin ) {
			if ( isset( $plugin['PluginURI'] ) && preg_match( '#^https?://kunoichiwp.com#u', $plugin['PluginURI'] ) ) {
				$should_return[ $slug ] = $plugin;
			}
		}
		return $should_return;
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
	
	/**
	 * Returns invalid error.
	 *
	 * @return \WP_Error
	 */
	private function invalid_error() {
		return new \WP_Error( 'invalid_result', __( 'Invalid response from Plugin API.', 'hiden' ), [
			'status' => 500,
		] );
	}
}
