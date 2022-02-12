<?php

/**
 *
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 * @SuppressWarnings(PHPMD.MissingImport)
 */
class WC_Simple_Affiliate_Settings {
	/**
	 * Constructor
	 */
	public function __construct() {
		if ( is_admin() ) {
			// Add admin menu
			add_action( 'admin_menu', array( &$this, 'admin_menu' ), 99 );

			// Init Settings for admin backend
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}
	}

	/**
	 * Add Admin menu
	 */
	public function admin_menu() {
		add_menu_page(
			'Affiliate',
			'Affiliate',
			'manage_options',
			'affiliate',
			'__return_false'
		);

		add_submenu_page(
			'affiliate',
			__( 'Settings' ),
			__( 'Settings' ),
			'activate_plugins',
			'affiliate_settings',
			array(
				$this,
				'admin_page_settings'
			) );
	}

	public function admin_page_settings() {
		require dirname( __FILE__ ) . '/../templates/admin/settings.php';
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting( 'simple_affiliate', 'default_discount_rate' );
		register_setting( 'simple_affiliate', 'default_commission_rate' );
	}
}

new WC_Simple_Affiliate_Settings();
