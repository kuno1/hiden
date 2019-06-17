<?php

namespace Kunoichi\Hiden;


use function foo\func;
use Kunoichi\Hiden\API\Plugins;
use Kunoichi\Hiden\Pattern\Singleton;

/**
 * Updater
 *
 * @package hiden
 *
 * @property Plugins $plugins
 * @property Options $options
 */
class Updater extends Singleton {

	private $plugin_transient = 'kunoichi_plugin_list';

	public function update_available() {
		return true;
	}

	/**
	 * Constructor
	 */
	protected function init() {
		// Filter plugin API.
		// add_filter( 'plugins_api_result', [ $this, 'plugins_api_result' ], 10, 3 );
//		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'pre_set_site_transient_update_plugins' ] );
		// Filter upgrader source.
		add_filter( 'upgrader_source_selection', [ $this, 'upgrader_source_selection' ], 10, 4 );
		// Filter plugin API.
		add_filter( 'plugins_api', [ $this->plugins, 'plugins_api' ], 10, 3 );
		// Filter plugin list.
		$this->options->register_filter();
		// Filter plugin updater list.
		add_filter( 'site_transient_update_plugins', [ $this, 'site_transient_update_plugins' ] );
	}
	
	/**
	 * Add additional plugin update information.
	 *
	 * @param \stdClass $plugins
	 * @return $plugins
	 */
	public function site_transient_update_plugins( $plugins ) {
		if ( ! $plugins ) {
			return $plugins;
		}
		$list = get_site_transient( $this->plugin_transient );
		if ( false === $list ) {
			$should_check = $this->plugins->grab_plugins();
			array_walk( $should_check, function( &$plugin, $key ) {
				$plugin = $plugin['Version'];
			} );
			$response = $this->plugins->get_plugin_list( $should_check );
			if ( is_wp_error( $response ) ) {
				$this->options->save_log( [
					'messagage' => $response->get_error_message(),
					'code'      => $response->get_error_code(),
					'updated'   => current_time( 'mysql' ),
				] );
			} else {
				$this->options->clear_log();
			}
			$list = is_wp_error( $response ) || ! is_array( $response ) ? [] : $response;
			set_site_transient( $this->plugin_transient, $list, 10 * MINUTE_IN_SECONDS );
		}
		if ( $list ) {
			foreach ( $list as $plugin ) {
				$new_version = $plugin->new_version;
				$plugin_file = $plugin->plugin;
				if ( isset( $plugins->checked ) ) {
					
					foreach ( $plugins->checked as $file => $old_version ) {
						if ( $file !== $plugin_file ) {
							continue;
						}
						if ( version_compare( $new_version, $old_version, '>' ) ) {
							$plugins->response[ $plugin_file ] = $plugin;
						} else {
							$plugins->no_update[ $plugin_file ] = $plugin;
						}
						break;
					}
				}
			}
		}
		return $plugins;
	}
	
	/**
	 * Filter upgrade resource to change directory name.
	 *
	 * @param $source
	 * @param $remote_source
	 * @param $upgrader
	 * @param $args
	 *
	 * @return mixed
	 */
	public function upgrader_source_selection( $source, $remote_source, $upgrader, $args ) {
		// Check if this plugin is kunoichi?
		$files = glob( $source . '*.php' );
		if ( $files ) {
			foreach ( $files as $file ) {
				$info = get_plugin_data( $file );
				if ( empty( $info['PluginURI'] ) || ! $this->plugins->is_kunoichi_url( $info['PluginURI'] ) ) {
					continue;
				}
				// This is kunoichi plugin.
				// Rename directory and move it to plugin dir.
				$path_parts = pathinfo( $source );
				$slug       = preg_split( '/-\d+\.\d+\.\d+-/u', basename( $source ) );
				if ( 2 > count( $slug ) ) {
					continue;
				}
				$new_source = trailingslashit( $path_parts['dirname'] ) . trailingslashit( $slug[0] );
				rename( $source, $new_source );
				$source = $new_source;
			}
		}
		return $source;
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch( $name ) {
			case 'plugins':
				return Plugins::get_instance();
			case 'themes':
				break;
			case 'options':
				return Options::get_instance();
			default:
				return null;
		}
	}

}
