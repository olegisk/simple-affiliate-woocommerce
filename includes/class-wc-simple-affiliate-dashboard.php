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
class WC_Simple_Affiliate_Dashboard {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', __CLASS__ . '::add_endpoints' );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		add_filter( 'woocommerce_account_menu_items', array( $this, 'my_account_menu_items' ) );
		add_filter( 'the_title', array( $this, 'my_account_affiliate_title' ) );
		add_action( 'woocommerce_account_affiliate-profile_endpoint', array( $this, 'my_account_affiliate_content' ) );

		add_action( 'template_redirect', array( __CLASS__, 'save_affiliate_details' ) );
	}

	/**
	 * Register new endpoint to use inside My Account page
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public static function add_endpoints() {
		add_rewrite_endpoint( 'affiliate-profile', EP_ROOT | EP_PAGES );
	}

	/**
	 * Add new query var
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'affiliate-profile';

		return $vars;
	}

	/**
	 * Flush rewrite rules on plugin activation
	 */
	public static function flush_rewrite_rules() {
		self::add_endpoints();

		flush_rewrite_rules();
	}

	/**
	 * Insert the new endpoint into the My Account menu
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function my_account_menu_items( $items ) {
		$items['affiliate-profile'] = __( 'Affiliate Profile', 'simple-affiliate' );

		return $items;
	}

	/**
	 * Change endpoint title.
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public function my_account_affiliate_title( $title ) {
		global $wp_query;

		$is_endpoint = isset( $wp_query->query_vars['affiliate-profile'] );

		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = __( 'Affiliate Profile', 'simple-affiliate' );
			//remove_filter( 'the_title', 'my_custom_endpoint_title' );
		}

		return $title;
	}

	/**
	 * Endpoint HTML content
	 */
	public function my_account_affiliate_content() {
		$user_id = get_current_user_id();

		// Get Base Currency (Affiliate Preferred Currency)
		$base_currency = WC_Simple_Affiliate::get_affiliate_currency( $user_id );

		// Reference Code
		$reference = get_user_meta( $user_id, '_affiliate_reference', true );

		// Balance
		$balance = WC_Simple_Affiliate::get_affiliate_balance( $user_id );

		wc_get_template(
			'affiliate/dashboard.php',
			array(
				'user_id'   => $user_id,
				'currency'  => $base_currency,
				'reference' => $reference,
				'balance'   => $balance
			),
			'',
			dirname( __FILE__ ) . '/../templates/'
		);
	}

	/**
	 * Form Handler
	 */
	public static function save_affiliate_details() {
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if ( empty( $_POST['action'] )
		     || 'save_affiliate_details' !== $_POST['action']
		     || empty( $_POST['_wpnonce'] )
		     || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_affiliate_details' )
		) {
			return;
		}

		$user_id = (int) get_current_user_id();
		if ( $user_id <= 0 ) {
			return;
		}

		// Handle required fields
		$required_fields = array(
			'currency'  => __( 'Preferred currency', 'simple-affiliate' ),
			'reference' => __( 'Affiliate ID', 'simple-affiliate' )
		);
		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $_POST[ $field_key ] ) ) {
				wc_add_notice( '<strong>' . esc_html( $field_name ) . '</strong> ' . __( 'is a required field.',
						'woocommerce' ), 'error' );

				return;
			}
		}

		$reference = ! empty( $_POST['reference'] ) ? wc_clean( $_POST['reference'] ) : '';
		if ( ! empty( $reference ) ) {
			// Check is already registered
			$aff_user_id = WC_Simple_Affiliate::get_affiliate_user_id( $reference );
			if ( $aff_user_id !== false && $aff_user_id !== $user_id ) {
				wc_add_notice( __( 'This Affiliate ID is already registered.', 'simple-affiliate' ), 'error' );

				return;
			}

			update_user_meta( $user_id, '_affiliate_reference', $reference );
		}

		$currency = ! empty( $_POST['currency'] ) ? wc_clean( $_POST['currency'] ) : '';
		if ( ! empty( $currency ) ) {
			$base_currency = WC_Simple_Affiliate::get_affiliate_currency( $user_id );

			// Currency should be changed
			if ( $currency !== $base_currency ) {
				// Check balance
				$balance = WC_Simple_Affiliate::get_affiliate_balance( $user_id );
				if ( $balance > 0.4 ) {
					wc_add_notice( __( 'Unable to change currency for exists balance.', 'simple-affiliate' ), 'error' );

					return;
				}

				update_user_meta( $user_id, '_affiliate_currency', $currency );
			}
		}

		// Set role
		if ( ! current_user_can( 'administrator' ) ) {
			wp_update_user( array( 'ID' => $user_id, 'role' => 'affiliate' ) );
		}

		wc_add_notice( __( 'Affiliate details changed successfully.', 'woocommerce' ) );
	}
}

new WC_Simple_Affiliate_Dashboard();
