<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Services_Installer {

	/**
	 * @var Jetpack
	 **/
	private $jetpack;

	/**
	 * @var WC_Services_Installer
	 **/
	private static $instance = null;

	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new WC_Services_Installer();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->jetpack = Jetpack::init();

		add_action( 'admin_init', array( $this, 'add_error_notice' ) );
		add_action( 'admin_init', array( $this, 'try_install' ) );
	}

	/**
	 * Verify the intent to install WooCommerce Services, and kick off installation.
	 */
	public function try_install() {
		if ( isset( $_GET['wc-services-action'] ) && ( 'install' === $_GET['wc-services-action'] ) ) {
			check_admin_referer( 'wc-services-install' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}

			$result   = $this->install();
			$redirect = wp_get_referer();

			if ( false === $result ) {
				$redirect = add_query_arg( 'wc-services-install-error', true, $redirect );
			} else {
				$this->jetpack->stat( 'jitm', 'wooservices-activated-' . JETPACK__VERSION );
			}

			wp_safe_redirect( $redirect );

			exit;
		}
	}

	/**
	 * Set up installation error admin notice.
	 */
	public function add_error_notice() {
		if ( ! empty( $_GET['wc-services-install-error'] ) ) {
			add_action( 'admin_notices', array( $this, 'error_notice' ) );
		}
	}

	/**
	 * Notify the user that the installation of WooCommerce Services failed.
	 */
	public function error_notice() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'There was an error installing WooCommerce Services.', 'jetpack' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Download, install, and activate the WooCommerce Services plugin.
	 *
	 * @return bool result of installation/activation
	 */
	private function install() {
		$this->jetpack->stat( 'jitm', 'wooservices-install-' . JETPACK__VERSION );

		include_once( ABSPATH . '/wp-admin/includes/admin.php' );
		include_once( ABSPATH . '/wp-admin/includes/plugin-install.php' );
		include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		include_once( ABSPATH . '/wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . '/wp-admin/includes/class-plugin-upgrader.php' );

		$api = plugins_api( 'plugin_information', array( 'slug' => 'woocommerce-services' ) );

		if ( is_wp_error( $api ) ) {
			return false;
		}

		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
		$result   = $upgrader->install( $api->download_link );

		if ( true !== $result ) {
			return false;
		}

		$result = activate_plugin( 'woocommerce-services/woocommerce-services.php' );

		// activate_plugin() returns null on success
		return is_null( $result );
	}
}

WC_Services_Installer::init();
