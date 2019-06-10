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
//		add_filter( 'upgrader_source_selection', [ $this, 'upgrader_source_selection' ], 1 );
		// Filter plugin API.
		add_filter( 'plugins_api', [ $this->plugins, 'plugins_api' ], 10, 3 );
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
		$list = get_site_transient( $this->plugin_transient );
		if ( false === $list ) {
			$should_check = [];
			foreach ( $plugins->checked as $slug => $version ) {
				if ( isset( $plugins->response[ $slug ] ) || isset( $plugins->no_update[ $slug ] ) ) {
					// This plugin is on public repo.
					continue;
				}
				$should_check[ $slug ] = $version;
			}
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
			$list = is_wp_error( $response ) ? [] : $response;
			// set_site_transient( $this->plugin_transient, $list, 10 * MINUTE_IN_SECONDS );
		}
		foreach ( $list as $plugin ) {
			// If in list of no_update, remove it.
			if ( isset( $plugins->no_update[ $plugin->plugin ] ) ) {
				unset( $plugins->no_update[ $plugin->plugin ] );
			}
			// Add it to update list.
			$plugins->response[ $plugin->plugin ] = $plugin;
		}
		return $plugins;
	}



	public function upgrader_source_selection() {

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
