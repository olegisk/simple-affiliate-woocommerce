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
class WC_Simple_Affiliate_User {
	/**
	 * Constructor
	 */
	public function __construct() {
		// Add user edit
		add_action( 'show_user_profile', array( $this, 'user_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'user_profile_fields' ), 99 );

		add_action( 'personal_options_update', array( $this, 'save_user_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_profile_fields' ) );
	}

	/**
	 * User Edit Profile
	 */
	public function user_profile_fields( $user ) {
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		//$user_id = get_current_user_id();
		$user_id = $user->ID;

		$reference       = get_user_meta( $user_id, '_affiliate_reference', true );
		$discount_rate   = WC_Simple_Affiliate::get_discount_rate( $user_id );
		$commission_rate = WC_Simple_Affiliate::get_commission_rate( $user_id );

		$currency = WC_Simple_Affiliate::get_affiliate_currency( $user_id );
		$balance  = WC_Simple_Affiliate::get_affiliate_balance( $user_id );

		wc_get_template(
			'affiliate/user-edit.php',
			array(
				'reference'       => $reference,
				'discount_rate'   => $discount_rate,
				'commission_rate' => $commission_rate,
				'currency'        => $currency,
				'balance'         => round( $balance, 2 )
			),
			'',
			dirname( __FILE__ ) . '/../templates/'
		);
	}

	/**
	 * Save Handler
	 */
	public function save_user_profile_fields() {
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		$user_id = wc_clean( $_POST['user_id'] );

		if ( ! empty( $_POST['affiliate_reference'] ) ) {
			update_user_meta(
				$user_id,
				'_affiliate_reference',
				wc_clean( $_POST['affiliate_reference'] )
			);
		}

		if ( isset( $_POST['affiliate_discount_rate'] ) ) {
			update_user_meta(
				$user_id,
				'_affiliate_discount_rate',
				wc_clean( $_POST['affiliate_discount_rate'] )
			);
		}

		if ( isset( $_POST['affiliate_commission_rate'] ) ) {
			update_user_meta(
				$user_id,
				'_affiliate_commission_rate',
				wc_clean( $_POST['affiliate_commission_rate'] )
			);
		}

		if ( isset( $_POST['affiliate_currency'] ) ) {
			update_user_meta(
				$user_id,
				'_affiliate_currency',
				wc_clean( $_POST['affiliate_currency'] )
			);
		}
	}
}

new WC_Simple_Affiliate_User();
