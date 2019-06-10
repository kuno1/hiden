<?php

namespace Kunoichi\Hiden\API;


use Kunoichi\Hiden\Pattern\Singleton;


/**
 * Dummy endpoint for local development.
 *
 * @package Kunoichi\Hiden\API
 */
class Dummy extends Singleton {

	/**
	 * Register REST Endpoint
	 */
	protected function init() {
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
	}

	/**
	 * Get arguments.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected function get_args( $args ) {
		return array_merge( $args, [
			'api_key' => [
				'required'    => true,
				'type'        => 'string',
				'description' => 'API key on kunoichiwp.com to detect who is accessing. Should be UUID.',
				'validate_callback' => function ( $var ) {
					return preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/u', $var );
				},
			],
			'site_url' => [
				'required'    => true,
				'type'        => 'string',
				'description' => 'Site URL of plugin user.',
				'validate_callback' => function( $var ) {
					return preg_match( '#^https?://#u', $var );
				},
			],
		] );
	}

	/**
	 * Register REST route.
	 */
	public function rest_api_init() {
		register_rest_route( 'hiden/v1', 'plugins', [
			[
				'methods' => 'POST',
				'args'    => $this->get_args( [
					'plugins' => [
						'required' => true,
						'type'     => 'string',
						'description' => 'JSON encoded list of plugins.',
						'validate_callback' => function( $var ) {
							return $var && json_decode( $var );
						},
					],
				] ),
				'permission_callback' => '__return_true',
				'callback' => [ $this, 'handle_plugin' ],
			],
		] );

		register_rest_route( 'hiden/v1', 'plugins/(?P<slug>[^/]+)', [
			[
				'methods' => 'GET',
				'args'    => $this->get_args( [
					'slug' => [
						'required' => true,
						'type'     => 'string',
						'description' => 'Plugin slug name.',
						'validate_callback' => function( $var ) {
							return $var && preg_match( '#^[a-z0-9\-_]+$#u', $var );
						},
					],
				] ),
				'permission_callback' => '__return_true',
				'callback' => [ $this, 'handle_information' ],
			]
		] );
	}

	/**
	 * Handle plugin response.
	 *
	 * @see https://github.com/kuno1/hiden/wiki/WordPress-Plugin-API
	 */
	public function handle_plugin() {
		return new \WP_REST_Response( [
			[
				'id'          => 'kuno1/plugins/hiden',
				'slug'        => 'hiden',
				'plugin'      => 'hiden/hiden.php',
				'new_version' => '2.0.0',
				'url'         => 'https://kunoichiwp.com/hiden',
				'package'     => 'https://github.com/kuno1/hagakure/releases/download/0.8.0/hagakure.zip',
				'icons'       => [
					'2x' => 'https://ps.w.org/wp-yomigana/assets/icon-256x256.png?rev=1051235',
					'1x' => 'https://ps.w.org/wp-yomigana/assets/icon-128x128.png?rev=1051235',
				],
				'banners' => [
					'1x' => 'https://ps.w.org/wp-yomigana/assets/banner-772x250.png?rev=495765',
				],
			],
		] );
	}

	/**
	 *
	 * @param \WP_REST_Request $request
	 * @see
	 */
	public function handle_information( $request ) {
		return [
			'name' => '',
		];
	}

}
