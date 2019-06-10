<?php

namespace Kunoichi\Hiden;

use cli\Table;

/**
 * Utility commands for Kunoichi updater.
 *
 * @package hiden
 */
class Command extends \WP_CLI_Command {

	/**
	 * Get a list of available plugins.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Format of result. Default is 'table'.
	 *   'csv','json' are also available.
	 *
	 * @synopsis [--format=<format>]
	 * @param array $args
	 * @param array $assoc
	 */
	public function plugins( $args, $assoc ) {
		require_once  ABSPATH . WPINC . '/update.php';
		// Force to get plugins.
		$plugins = get_site_transient( 'update_plugins' );
		switch ( $assoc['format'] ?? '' ) {
			case 'csv';
				break;
			case 'json':
				echo json_encode( $plugins );
				break;
			default:
				// Render table.
				$table = new Table();
				$table->setHeaders( [ 'Plugin', 'Provider', 'Current', 'New Version', 'Update' ] );
				foreach ( $plugins->checked as $slug => $current ) {
					$plugin_name = explode( '/', $slug )[0];
					$new_version = '---';
					$provider    = 'unknown';
					$update = '---';
					if ( isset( $plugins->response[ $slug ] ) ) {
						$plugin = $plugins->response[ $slug ];
						$update = 'yes';
					} elseif ( isset( $plugins->no_update[ $slug ] ) ) {
						$plugin = $plugins->no_update[ $slug ];
					} else {
						$plugin = null;
					}
					if ( $plugin ) {
						$new_version = $plugin->new_version;
						$provider = explode( '/', $plugin->id )[0];
					}
					$table->addRow( [ $plugin_name, $provider, $current, $new_version, $update ] );
				}
				$table->display();
				break;
		}
	}



}
