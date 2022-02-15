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
class WC_Simple_Affiliate {
	/**
	 * Constructor
	 */
	public function __construct() {
		// Add scripts and styles
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::add_scripts' );

		// add affiliate form on the checkout
		add_action( 'woocommerce_before_checkout_form', array( $this, 'print_affiliate_form_on_checkout' ) );
		add_shortcode( 'simple_affiliate_form', array( $this, 'shortcode_affiliate_form' ) );

		add_action( 'init', array( $this, 'check_affid' ), 0 );

		add_action( 'wp_ajax_simple_affiliate_apply', array( $this, 'ajax_affiliate_apply' ) );
		add_action( 'wp_ajax_nopriv_simple_affiliate_apply', array( $this, 'ajax_affiliate_apply' ) );

		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_discount' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'checkout_order_processed' ), 10, 3 );

		add_action( 'woocommerce_payment_complete', array( $this, 'payment_complete' ) );
		add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'payment_complete' ) );
		add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'payment_complete' ) );

		add_action( 'woocommerce_cart_emptied', array( $this, 'cart_emptied' ) );
		add_shortcode( 'simple_affiliate_balance', array( $this, 'shortcode_affiliate_balance' ) );
		add_action( 'woocommerce_checkout_before_order_review', array( $this, 'print_balance_form_on_checkout' ) );

		add_action( 'wp_ajax_simple_aff_balance_apply', array( $this, 'ajax_balance_apply' ) );
		add_action( 'wp_ajax_nopriv_simple_aff_balance_apply', array( $this, 'ajax_balance_apply' ) );

		add_action( 'wp_ajax_simple_aff_balance_remove', array( $this, 'ajax_balance_remove' ) );
		add_action( 'wp_ajax_nopriv_simple_aff_balance_remove', array( $this, 'ajax_balance_remove' ) );

		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_aff_discount' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'aff_checkout_order_processed' ), 10, 3 );

		// Rollback on failed/pending --> cancelled
		//add_action( 'woocommerce_order_status_pending_to_failed', __CLASS__ . '::aff_checkout_order_rollback' );
		add_action( 'woocommerce_order_status_failed_to_cancelled', __CLASS__ . '::aff_checkout_order_rollback' );
		add_action( 'woocommerce_order_status_pending_to_cancelled', __CLASS__ . '::aff_checkout_order_rollback' );
	}


	/**
	 * Add Scripts and Styles
	 */
	public static function add_scripts() {
		//global $post;
		// Check page have short code
		//if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'simple_affiliate_form' ) ) {
		//    return;
		//}

		// Styles
		wp_enqueue_style(
			'simple-affiliate-css',
			untrailingslashit(
				plugins_url(
					'/',
					__FILE__
				)
			) . '/../assets/css/style.css',
			array(),
			null,
			'all'
		);

		// jQuery
		wp_enqueue_script( 'jquery' );

		// Plugin Scripts
		wp_register_script(
			'simple-affiliate-js',
			untrailingslashit(
				plugins_url(
					'/',
					__FILE__
				)
			) . '/../assets/js/script.js'
		);

		// Localize the script with new data
		$translation_array = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'simple-affiliate' )
		);
		wp_localize_script( 'simple-affiliate-js', 'SimpleAffiliatePlugin', $translation_array );

		// Enqueued script with localized data.
		wp_enqueue_script( 'simple-affiliate-js' );
	}

	/**
	 * Print affiliate form on checkout page
	 */
	public function print_affiliate_form_on_checkout() {
		echo do_shortcode( '[simple_affiliate_form]' );
	}

	/**
	 * @param array $atts
	 *
	 * @return string
	 */
	public function shortcode_affiliate_form( $atts = array() ) {
		$defaults = array(
			'affiliate_token' => false
		);

		$atts = shortcode_atts( $defaults, $atts );
		extract( $atts );

		$affiliate       = '';
		$permanent_token = false;
		if ( isset( $_COOKIE['affiliate_ref'] ) ) {
			$user_id = self::get_affiliate_user_id( wc_clean( $_COOKIE['affiliate_ref'] ) );
			if ( $user_id && $user_id !== get_current_user_id() ) {
				$affiliate       = wc_clean( $_COOKIE['affiliate_ref'] );
				$permanent_token = true;
			}
		}

		ob_start();
		wc_get_template(
			'affiliate/affiliate-form.php',
			array(
				'atts'            => $atts,
				'affiliate'       => $affiliate,
				'permanent_token' => $permanent_token
			),
			'',
			dirname( __FILE__ ) . '/../templates/'
		);

		return ob_get_clean();
	}

	/**
	 * Check request for affid param
	 */
	public function check_affid() {
		if ( ! empty( $_GET['affid'] ) ) {
			$token   = wc_clean( $_GET['affid'] );
			$user_id = self::get_affiliate_user_id( $token );
			if ( $user_id !== false ) {
				setcookie(
					'affiliate_ref',
					$token,
					time() + 128 * 24 * 60 * 60,
					COOKIEPATH,
					COOKIE_DOMAIN,
					false,
					true
				);
			}
		}
	}

	/**
	 * Ajax Action for Apply Affiliate Code
	 */
	public function ajax_affiliate_apply() {
		if ( ! check_ajax_referer( 'simple-affiliate', 'nonce', false ) ) {
			exit( 'No naughty business' );
		}

		if ( ! empty( $_POST['referrer_token'] ) && $token = wc_clean( $_POST['referrer_token'] ) ) {
			// Validate token
			$user_id = self::get_affiliate_user_id( wc_clean( $_POST['referrer_token'] ) );
			if ( ! $user_id ) {
				wc_add_notice(
					__( 'The affiliate code you provided is not valid; please, double check it!', 'simple-affiliate' ),
					'error'
				);
			} elseif ( $user_id == get_current_user_id() ) {
				wc_add_notice(
					__( 'You can\'t apply this code to your order', 'simple-affiliate' ),
					'error'
				);
			} else {
				wc_add_notice( __( 'Thanks! We will give this user special thanks!', 'simple-affiliate' ), 'success' );
				setcookie(
					'affiliate_ref',
					$token,
					time() + 128 * 24 * 60 * 60,
					COOKIEPATH,
					COOKIE_DOMAIN,
					false,
					true
				);
			}
		} else {
			wc_add_notice( __( 'Please, enter the affiliate code', 'simple-affiliate' ), 'error' );
		}

		wc_print_notices();
		die();
	}

	/**
	 * Apply discount
	 */
	public function add_discount() {
		if ( isset( $_COOKIE['affiliate_ref'] ) ) {
			$user_id = self::get_affiliate_user_id( wc_clean( $_COOKIE['affiliate_ref'] ) );
			if ( $user_id && $user_id !== get_current_user_id() ) {
				$total = self::get_cart_total();
				if ( $total > 0 ) {
					$rate     = self::get_discount_rate( $user_id );
					$discount = $rate * ( $total / 100 );

					// Apply discount for cart
					WC()->cart->add_fee( __( 'Discount', 'simple-affiliate' ), - 1 * $discount, false, '' );

					// Save applied discount total in session
					WC()->session->set( 'aff_discount', $discount );
				}
			}
		}
	}

	/**
	 * Checkout Order processed
	 *
	 * @param $order_id
	 * @param $posted
	 */
	public function checkout_order_processed( $order_id, $posted_data, $order ) {
		// Save Affiliate User Id and Affiliate Discount in order
		if ( isset( $_COOKIE['affiliate_ref'] ) ) {
			// Get user Id of Affiliate Reference
			$user_id = self::get_affiliate_user_id( wc_clean( $_COOKIE['affiliate_ref'] ) );
			if ( $user_id && $user_id !== get_current_user_id() ) {
				update_post_meta( $order_id, '_aff_user_id', $user_id );

				// Save discount total
				$discount = WC()->session->get( 'aff_discount' );
				if ( $discount ) {
					update_post_meta( $order_id, '_aff_discount', $discount );
				}
			}

			// Save "invited" user_id in customer profile
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$customer_id = $order->get_user_id();
				$invited     = get_user_meta( $customer_id, '_affiliate_invited_by', true );
				if ( empty( $invited ) ) {
					update_user_meta( $customer_id, '_affiliate_invited_by', $invited );
				}
			}
		}
	}

	/**
	 * Payment Complete
	 *
	 * @param $order_id
	 */
	public function payment_complete( $order_id ) {
		global $wpdb;

		// To prevent double handling
		$approved = get_post_meta( $order_id, '_aff_balance_approved', true );
		if ( ! empty( $approved ) ) {
			return;
		}

		// Add bonus points for ref
		$user_id  = get_post_meta( $order_id, '_aff_user_id', true );
		$discount = get_post_meta( $order_id, '_aff_discount', true );
		if ( $user_id && $discount > 0 ) {
			// Get History to prevent double earnings
			$history = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}affiliate_earnings WHERE order_id = %d;",
				$order_id
			), ARRAY_A );
			if ( count( $history ) === 0 ) {
				// Get Base Currency (Affiliate Preferred Currency)
				$base_currency   = self::get_affiliate_currency( $user_id );
				$discount_rate   = self::get_discount_rate( $user_id );
				$commission_rate = self::get_commission_rate( $user_id );

				// Calculate earning value by commission rate
				$order    = wc_get_order( $order_id );
				$total    = $order->get_total();
				$currency = $order->get_order_currency();
				$earning  = round( $total / 100 * $commission_rate, 2 );
				if ( $base_currency !== $currency ) {
					$earning = $this->convert_currency( $currency, $base_currency, $earning );
				}

				// Save balance history
				$wpdb->insert( $wpdb->prefix . 'affiliate_earnings', array(
					'user_id'             => $user_id, // Affiliate Id
					'order_id'            => $order->get_id(),
					'customer_id'         => $order->get_user_id(),
					'discount_rate'       => $discount_rate,
					'discount'            => round( $discount, 2 ),
					'discount_currency'   => $currency,
					'commission_rate'     => $commission_rate,
					'commission'          => $earning,
					'commission_currency' => $base_currency,
					'comment'             => sprintf( 'Reward "%s" for order #%s.', $earning . ' ' . $base_currency,
						$order_id ),
					'creation_date'       => date( 'Y-m-d H:i:s' )
				) );

				// Update earning balance of Affiliate
				$balance = self::get_affiliate_balance( $user_id );
				update_user_meta( $user_id, '_affiliate_balance', $balance + $earning );

				// Flag
				update_post_meta( $order_id, '_aff_balance_approved', true );
			}
		}
	}

	/**
	 * Cart Empty
	 */
	public function cart_emptied() {
		WC()->session->__unset( 'aff_discount' );
	}

	/**
	 * Print Affiliate Balance on Checkout page
	 */
	public function print_balance_form_on_checkout() {
		echo do_shortcode( '[simple_affiliate_balance]' );
	}

	public function shortcode_affiliate_balance() {
		$user_id = get_current_user_id();
		if ( $user_id > 0 ) {
			// Get Base Currency (Affiliate Preferred Currency)
			$base_currency = self::get_affiliate_currency( $user_id );
			$balance       = self::get_affiliate_balance( $user_id );

			if ( $balance > 0 ) {
				$to_use              = WC()->session->get( 'aff_use_balance' );
				$total               = self::get_cart_total();
				$max_allowed_balance = $balance;
				if ( $balance > $total ) {
					$max_allowed_balance = $total;
				}

				ob_start();
				wc_get_template(
					'affiliate/balance.php',
					array(
						'balance'             => $balance,
						'max_allowed_balance' => $max_allowed_balance,
						'currency'            => $base_currency,
						'to_use'              => $to_use
					),
					'',
					dirname( __FILE__ ) . '/../templates/'
				);
				$output = ob_get_contents();
				ob_end_clean();

				return $output;
			}
		}

		return '';
	}

	/**
	 * Ajax Action for Apply Balance
	 */
	public function ajax_balance_apply() {
		if ( isset( $_REQUEST['amount'] ) ) {
			$amount = (float) wc_clean( $_REQUEST['amount'] );
			if ( $amount > 0 ) {
				// Save in session
				WC()->session->set( 'aff_use_balance', $amount );
			}
		}
	}

	/**
	 * Ajax Action for Remove Balance
	 */
	public function ajax_balance_remove() {
		WC()->session->__unset( 'aff_use_balance' );
	}

	/**
	 * Add Discount for Affiliate Balance
	 */
	public function add_aff_discount() {
		$user_id  = get_current_user_id();
		$discount = WC()->session->get( 'aff_use_balance' );

		if ( $user_id > 0 && $discount > 0 ) {
			// Get Base Currency (Affiliate Preferred Currency)
			$base_currency = self::get_affiliate_currency( $user_id );

			// Check currency
			$cart_currency = self::get_currency();
			if ( $cart_currency !== $base_currency ) {
				// Exchange discount
				$discount = $this->convert_currency( $base_currency, $cart_currency, $discount );
			}

			// Verify cart total amount
			$total = self::get_cart_total();
			if ( $discount > $total ) {
				// Fault
				$this->ajax_balance_remove();

				return;
			}

			WC()->cart->add_fee( __( 'Discount', 'simple-affiliate' ), - 1 * $discount, false, '' );
			WC()->session->set( 'aff_use_balance_calculated', $discount );
		}
	}

	public function aff_checkout_order_processed( $order_id, $posted_data, $order ) {
		global $wpdb;

		$user_id  = get_current_user_id();
		$discount = WC()->session->get( 'aff_use_balance_calculated' );
		$spent    = WC()->session->get( 'aff_use_balance' );

		if ( $user_id > 0 && $spent > 0 ) {
			$spent = round( $spent, 2 );
			// Get Base Currency (Affiliate Preferred Currency)
			$base_currency = self::get_affiliate_currency( $user_id );

			// Save balance history
			$wpdb->insert( $wpdb->prefix . 'affiliate_spending', array(
				'user_id'       => $user_id, // Affiliate Id
				'order_id'      => $order_id,
				'total_spent'   => - 1 * $spent,
				'currency'      => $base_currency,
				'comment'       => sprintf( 'Spent "%s" for order #%s.', $spent . ' ' . $base_currency, $order_id ),
				'creation_date' => date( 'Y-m-d H:i:s' )
			) );

			// Update balance
			$balance = self::get_affiliate_balance( $user_id );
			update_user_meta( $user_id, '_affiliate_balance', $balance - $spent );

			// Flag
			update_post_meta( $order_id, '_aff_balance_spent', true );

			WC()->session->__unset( 'aff_use_balance_calculated' );
			WC()->session->__unset( 'aff_use_balance' );
		}
	}

	/**
	 * Rollback earnings when failed order
	 *
	 * @param $order_id
	 */
	public static function aff_checkout_order_rollback( $order_id ) {
		global $wpdb;

		$order = wc_get_order( $order_id );

		// Already done
		$flag = get_post_meta( $order_id, '_aff_balance_spent_rollback', true );
		if ( $flag === true ) {
			return;
		}

		$id = (int) get_post_meta( $order_id, '_aff_balance_spent_id', true );
		if ( $id > 0 ) {
			// @todo $wpdb->prepare()
			$data    = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}affiliate_spending` WHERE id = {$id};", ARRAY_A );
			$user_id = $data['user_id'];
			$spent   = abs( (float) $data['total_spent'] );
			if ( $spent > 0 ) {
				$base_currency = self::get_affiliate_currency( $user_id );

				// Save balance history
				$wpdb->insert( $wpdb->prefix . 'affiliate_earnings', array(
					'user_id'             => $user_id, // Affiliate Id
					'order_id'            => $order_id,
					'customer_id'         => $user_id,
					'discount_rate'       => 0,
					'discount'            => 0,
					'discount_currency'   => $order->get_currency(),
					'commission_rate'     => 0,
					'commission'          => $spent,
					'commission_currency' => $base_currency,
					'comment'             => sprintf( 'Rollback earnings %s. Reason: Order #%s is failed or cancelled.',
						$spent . ' ' . $base_currency,
						$order_id
					),
					'creation_date'       => date( 'Y-m-d H:i:s' )
				) );

				// Update balance
				$balance = self::get_affiliate_balance( $user_id );
				update_user_meta( $user_id, '_affiliate_balance', $balance + $spent );

				// Flag
				update_post_meta( $order_id, '_aff_balance_spent_rollback', true );
			}
		}
	}

	/**
	 * Convert Currency
	 *
	 * @param $from
	 * @param $to
	 * @param $amount
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public static function convert_currency( $from, $to, $amount ) {
		// @todo Currency conversation
		return $amount;
	}

	/**
	 * Get User ID by Affiliate Reference
	 *
	 * @param $affiliate_reference
	 *
	 * @return bool|int
	 */
	public static function get_affiliate_user_id( $affiliate_reference ) {
		$users = get_users( array( 'meta_key' => '_affiliate_reference', 'meta_value' => $affiliate_reference ) );
		if ( count( $users ) === 0 ) {
			return false;
		}

		/** @var WP_User $user */
		$user = array_shift( $users );

		return $user->ID;
	}

	/**
	 * Get Current Currency Code.
	 *
	 * @return string
	 */
	public static function get_currency() {
		// Get Currency
		$currency = get_option( 'woocommerce_currency' );
		if ( function_exists( 'wmcs_get_customers_currency' ) ) {
			$currency = wmcs_get_customers_currency();
		}

		return $currency;
	}

	/**
	 * Affiliate Preferred Currency
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	public static function get_affiliate_currency( $user_id ) {
		$base_currency = get_user_meta( $user_id, '_affiliate_currency', true );
		if ( empty( $base_currency ) ) {
			$base_currency = 'USD';
			update_user_meta( $user_id, '_affiliate_currency', $base_currency );
		}

		return $base_currency;
	}

	/**
	 * Get Discount Rate
	 *
	 * @param $user_id
	 *
	 * @return int
	 */
	public static function get_discount_rate( $user_id ) {
		$rate = get_user_meta( $user_id, '_affiliate_discount_rate', true );
		if ( ! empty( $rate ) ) {
			return (int) $rate;
		}

		return get_option( 'default_discount_rate', 5 );
	}

	/**
	 * Get Commission Rate
	 *
	 * @param $user_id
	 *
	 * @return int
	 */
	public static function get_commission_rate( $user_id ) {
		$rate = get_user_meta( $user_id, '_affiliate_commission_rate', true );
		if ( ! empty( $rate ) ) {
			return (int) $rate;
		}

		return get_option( 'default_commission_rate', 5 );
	}

	/**
	 * Get Balance
	 *
	 * @param $user_id
	 *
	 * @return float
	 */
	public static function get_affiliate_balance( $user_id ) {
		return (float) get_user_meta( $user_id, '_affiliate_balance', true );
	}

	/**
	 * Get Cart Total.
	 *
	 * @return float
	 */
	public static function get_cart_total() {
		return WC()->cart->get_cart_contents_total() + WC()->cart->get_taxes_total() +
		       WC()->cart->get_shipping_total() + WC()->cart->get_fee_total();
	}
}

new WC_Simple_Affiliate();
