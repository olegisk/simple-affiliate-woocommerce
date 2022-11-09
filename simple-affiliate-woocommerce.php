<?php
/*
 * Plugin Name: Simple Affiliate for WooCommerce
 * Plugin URI: https://github.com/olegisk/simple-affiliate-woocommerce
 * Description: Simple Affiliate for WooCommerce
 * Author: olegisk
 * Author URI: https://github.com/olegisk
 * License: Apache License 2.0
 * License URI: http://www.apache.org/licenses/LICENSE-2.0
 * Version: 1.0.2
 * Text Domain: simple-affiliate
 * Domain Path: /i18n
 * WC requires at least: 5.5.1
 * WC tested up to: 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 *
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 * @SuppressWarnings(PHPMD.MissingImport)
 */
class Simple_Affiliate {
	public function __construct() {
		// Activation
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		register_activation_hook( __FILE__, array( 'WC_Simple_Affiliate_Dashboard', 'flush_rewrite_rules' ) );
		register_deactivation_hook( __FILE__, array( 'WC_Simple_Affiliate_Dashboard', 'flush_rewrite_rules' ) );

		// Action
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ), 0 );

		require_once __DIR__ . '/includes/class-wc-simple-affiliate.php';
		require_once __DIR__ . '/includes/class-wc-simple-affiliate-dashboard.php';
		require_once __DIR__ . '/includes/class-wc-simple-affiliate-history.php';
		require_once __DIR__ . '/includes/class-wc-simple-affiliate-user.php';
		require_once __DIR__ . '/includes/class-wc-simple-affiliate-settings.php';

	}

	/**
	 * Install
	 */
	public static function install() {
		global $wpdb;
		$query = "
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}affiliate_earnings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Affiliate User Id',
  `order_id` int(11) NOT NULL COMMENT 'Customer Order ID',
  `customer_id` int(11) NOT NULL COMMENT 'Customer Id',
  `discount_rate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Discount Rate',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Discount Total',
  `discount_currency` varchar(255) NOT NULL COMMENT 'Currency of Discount total',
  `commission_rate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Commission Rate',
  `commission` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Commission Total',
  `commission_currency` varchar(255) NOT NULL COMMENT 'Currency of Commission Total',
  `comment` text NOT NULL,
  `creation_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=INNODB DEFAULT CHARSET={$wpdb->charset};
";
		$wpdb->query( $query );

		$query = "
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}affiliate_spending` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Affiliate User Id',
  `order_id` int(11) NOT NULL COMMENT 'Order ID',
  `total_spent` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Spent Total',
  `currency` varchar(255) NOT NULL COMMENT 'Currency of amount',
  `comment` text NOT NULL,
  `creation_date` datetime NOT NULL COMMENT 'Creation Date',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=INNODB DEFAULT CHARSET={$wpdb->charset};
";
		$wpdb->query( $query );

		self::create_roles();
	}

	/**
	 * Add relevant links to plugins page
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=affiliate_settings' ) . '">' .
			__( 'Settings', 'simple-affiliate' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Init localisations and files
	 */
	public function init() {
		// Localization
		load_plugin_textdomain(
			'simple-affiliate',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/i18n'
		);
	}

	/**
	 * Create User roles.
	 *
	 * @return void
	 */
	public static function create_roles() {
		// Customer role
		add_role( 'affiliate', __( 'Affiliate', 'simple-affiliate' ), array(
			'read' => true
		) );
	}

}

new Simple_Affiliate();
