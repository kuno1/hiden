<?php

/**
 * Test plugins api.
 *
 * @property-read \Kunoichi\Hiden\API\Plugins $plugins
 */
class PluginsTest extends WP_UnitTestCase {
	
	/**
	 * Test list functions.
	 */
	public function test_list() {
		// Test list.
		$this->assertInstanceOf( 'WP_Error', $this->plugins->get_plugin_list( [] ) );
	}
	
	function __get( $name ) {
		switch ( $name ) {
			case 'plugins':
				return \Kunoichi\Hiden\API\Plugins::get_instance();
			default:
				return parent::__get( $name ); // TODO: Change the autogenerated stub
		}
	}
	
	
}
