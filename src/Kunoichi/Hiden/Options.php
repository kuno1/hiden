<?php

namespace Kunoichi\Hiden;


use Kunoichi\Hiden\API\Endpoint;
use Kunoichi\Hiden\Pattern\Singleton;

/**
 * Options API
 *
 * @package Kunoichi\Hiden
 */
class Options extends Singleton {

	use Endpoint;
	
	private $log_option_key = 'hiden_option_log';

	private $api_key_name   = 'hiden_api_key';
	
	/**
	 * Register filter functions.
	 */
	public function register_filter() {
		add_action( 'init', [ $this, 'register_assets' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ], 1 );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'rest_api_init', [ $this, 'rest_api' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}
	
	/**
	 * Register assets.
	 */
	public function register_assets() {
		$asset_dir = plugin_dir_url( HIDEN_ROOT_DIR . '/assets' ) . '/dist';
		wp_register_script( 'hiden-option-helper', $asset_dir . '/js/hiden-option-helper.js', [ 'wp-api-request' ], HIDEN_VERSION, true );
		wp_register_style( 'hiden-admin', $asset_dir . '/css/admin.css', [], HIDEN_VERSION );
	}
	
	/**
	 * Enqueue admin assets.
	 *
	 * @param string $page
	 */
	public function admin_enqueue_scripts( $page ) {
		wp_enqueue_style( 'hiden-admin' );
	}
	
	/**
	 * Get page title.
	 *
	 * @return string
	 */
	private function get_page_title() {
		return __( 'Kunoichi Updater Setting', 'hiden' );
	}
	
	/**
	 * Register menu.
	 */
	public function admin_menu() {
		add_options_page( $this->get_page_title(), 'Kunoichi', 'install_plugins', 'hiden', [ $this, 'render' ] );
	}
	
	/**
	 * Display notices.
	 */
	public function admin_notices() {
		if ( $this->get_api_key() ) {
			// Do nothing.
			return;
		}
		if ( ! current_user_can( 'update_plugins' ) ) {
			// Only admin
			return;
		}
		$screen = get_current_screen();
		if ( 'settings_page_hiden' === $screen->id ) {
			return;
		}
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php echo wp_kses_post( sprintf( __( 'Please enter <a href="%s">Kunoichi API key</a> to get continuous update.', 'hiden' ), admin_url( '' ) ) ); ?>
			</p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss notice', 'hidden' ) ?></span>
			</button>
		</div>
		<?php
	}
	
	/**
	 * Register settings.
	 */
	public function register_settings() {
		// Register settings.
		add_settings_section( 'hiden_credentials', __( 'Credentials', 'hiden' ), function() {
			// Do something.
		}, 'hiden' );
		// Register fields.
		add_settings_field( 'hiden_api_key', __( 'API Key', 'hiden' ), function() {
			?>
			<input type="text" name="hiden_api_key" id="hiden_api_key" placeholder="999baded-9999-99a6-9fcc-a99ad5eb9999"
				   value="<?php echo esc_attr( $this->get_api_key() ) ?>" class="widefat" />
			<p id="hiden_api_key_result" class="loading">
				<span class="invalid-string"><span class="dashicons dashicons-no"></span> <?php esc_html_e( 'API key is invalid.', 'hiden' ) ?></span>
				<span class="valid-string"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'API key is valid.', 'hiden' ) ?>: <strong></strong></span>
			</p>
			<?php
		}, 'hiden', 'hiden_credentials' );
		register_setting( 'hiden', 'hiden_api_key' );
	}
	
	/**
	 * Render option screen.
	 */
	public function render() {
		wp_enqueue_script( 'hiden-option-helper' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( $this->get_page_title() ) ?></h1>
			<p class="description">
				<?php esc_html_e( 'You can get your API key at Kunoichi WP Marketplace.', 'hiden' ) ?>
				<a class="button" target="_blank" href="https://kunoichiwp.com/my-account/license"><?php esc_html_e( 'Visit Kunoichi', 'hiden' ) ?></a>
			</p>
			<form method="POST" action="options.php">
				<?php
					settings_fields( 'hiden' );
					do_settings_sections( 'hiden' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Register REST API.
	 */
	public function rest_api() {
		register_rest_route( 'hiden/v1', 'validator', [
			[
				'methods' => 'GET',
				'args' => [],
				'callback' => function( \WP_REST_Request $request ) {
					$api_key = $this->get_api_key();
					if ( ! $api_key ) {
						return new \WP_Error( 'invalid_api_key', __( 'API key is not set.', 'hiden' ), [
							'status' => 400,
						] );
					}
					$response = wp_remote_get( add_query_arg( [
						'api_key'  => rawurlencode( $api_key ),
						'site_url' => rawurlencode( home_url( '' ) ),
					], $this->endpoint() . '/wp-json/makibishi/v1/validator' ) );
					if ( is_wp_error( $response ) ) {
						return $response;
					}
					$data = json_decode( $response['body'], true );
					if ( ! $data ) {
						return new \WP_Error( 'invalid_response', __( 'Failed to get valid results.', 'hiden' ), [
							'status' => 500,
						] );
					}
					return new \WP_REST_Response( $data );
				},
				'permission_callback' => function() {
					return current_user_can( 'update_plugins' );
				}
			]
		] );
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
