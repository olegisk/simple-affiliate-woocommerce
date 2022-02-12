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
class WC_Simple_Affiliate_History {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode( 'affiliate_history', array( $this, 'shortcode_affiliate_history' ) );
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::add_scripts' );

		add_action( 'wp_ajax_affiliate_history_grid', array( $this, 'ajax_affiliate_history_grid' ) );
		add_action( 'wp_ajax_nopriv_affiliate_history_grid', array( $this, 'ajax_affiliate_history_grid' ) );
	}

	/**
	 * Short Code
	 *
	 * @param $atts
	 */
	public function shortcode_affiliate_history( $atts ) {
		$defaults = array();

		$atts = shortcode_atts( $defaults, $atts );
		extract( $atts );

		?>
        <table id="jqGrid1"></table>
        <div id="jqGridPager1"></div>
		<?php
	}

	/**
	 * Add Scripts
	 */
	public static function add_scripts() {
		//global $post;
		// Check page have short code
		//if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'affiliate_history' ) ) {
		//return;
		//}

		$vendor = untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/../assets/vendor/';

		// jQuery UI
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_style(
			'jquery-ui-style',
			$vendor . 'jquery-ui/themes/ui-lightness/jquery-ui.min.css',
			array(),
			null,
			'all'
		);
		wp_enqueue_style(
			'jquery-ui-theme',
			$vendor . 'jquery-ui/themes/ui-lightness/theme.css',
			array( 'jquery-ui-style' ),
			null,
			'all'
		);

		// Add jqGrid
		wp_enqueue_script(
			'jqgrid-i18n',
			$vendor . 'jqGrid/i18n/grid.locale-' . substr( get_locale(), 0, 2 ) . '.js',
			array(),
			'5.6.0'
		);
		wp_enqueue_script(
			'jqgrid-js',
			$vendor . 'jqGrid/jquery.jqGrid.min.js',
			array(),
			'5.6.0'
		);
		wp_enqueue_style(
			'jqgrid-css',
			$vendor . 'jqGrid/css/ui.jqgrid.css',
			array(),
			'5.6.0',
			'all'
		);

		// Plugin Scripts
		wp_register_script( 'affiliate-history-js', $vendor . '../js/history.js' );

		// Localize the script with new data
		$translation_array = array(
			'ajax_url'         => admin_url( 'admin-ajax.php' ),
			'history_grid_url' => add_query_arg(
				array( 'action' => 'affiliate_history_grid' ),
				admin_url( 'admin-ajax.php' )
			),
			'nonce'            => wp_create_nonce( 'simple-affiliate' ),
			'text_order_total' => __( 'Order Total', 'simple-affiliate' ),
			'text_credited'    => __( 'Credited', 'simple-affiliate' ),
			'text_status'      => __( 'Status', 'simple-affiliate' ),
			'text_ordered_at'  => __( 'Date', 'simple-affiliate' ),
		);
		wp_localize_script(
			'affiliate-history-js',
			'SimpleAffiliateHistoryGrid',
			$translation_array
		);

		// Enqueued script with localized data.
		wp_enqueue_script( 'affiliate-history-js' );
	}


	/**
	 * Grid Ajax Action
	 */
	public function ajax_affiliate_history_grid() {
		global $wpdb;

		// User Id
		$user_id = is_user_logged_in() ? get_current_user_id() : 0;

		// Operation
		$oper = isset( $_POST['oper'] ) ? wc_clean( $_POST['oper'] ) : 'view';
		switch ( $oper ) {
			case 'view':
				$page = isset( $_GET['page'] ) ? wc_clean( $_GET['page'] ) : '';
				$rows = isset( $_GET['rows'] ) ? wc_clean( $_GET['rows'] ) : '';
				$sidx = isset( $_GET['sidx'] ) ? wc_clean( $_GET['sidx'] ) : '';
				$sord = isset( $_GET['sord'] ) ? wc_clean( $_GET['sord'] ) : '';

				// Validate
				if ( ! in_array( strtoupper( $sord ), array( 'ASC', 'DESC' ) ) ) {
					$sord = 'ASC';
				}

				if ( ! in_array( $sidx, array( 'creation_date', 'order_id' ) ) ) {
					$sidx = 'creation_date';
				}

				$page  = $page ? ( $page - 1 ) : 0;
				$start = abs( $page * $rows );

				$sql     = "
				(SELECT order_id, commission as total, commission_currency as currency, comment, creation_date FROM {$wpdb->prefix}affiliate_earnings WHERE user_id = %d) 
				UNION
				(SELECT order_id, total_spent as total, currency, comment, creation_date FROM {$wpdb->prefix}affiliate_spending WHERE user_id = %d)
				ORDER BY {$sidx} {$sord} LIMIT %d, %d;
				";
				$query   = $wpdb->prepare( $sql,
					$user_id,
					$user_id,
					$start,
					$rows
				);
				$results = $wpdb->get_results( $query, ARRAY_A );

				// Get total rows
				//$total_rows = $wpdb->get_var( "SELECT FOUND_ROWS()" );
				$sql        = str_replace( "ORDER BY {$sidx} {$sord} LIMIT %d, %d", '', $sql );
				$query      = $wpdb->prepare( $sql,
					$user_id,
					$user_id,
					$start,
					$rows
				);
				$total_rows = count( $wpdb->get_results( $query, ARRAY_A ) );

				// Format date using WordPress settings
				foreach ( $results as $key => &$row ) {
					$order = wc_get_order( $row['order_id'] );
					if ( ! $order ) {
						continue;
					}

					//$row['total'] = $order->get_formatted_order_total('incl');
					//$row['credited'] = wc_price($row['earning'], array('currency' => $row['currency']));
					$row['order_amount'] = $order->get_total() . ' ' . $order->get_order_currency();
					$row['order_status'] = $order->get_status();
					$row['ordered_at']   = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
						$order->get_date_created() );
				}

				$result = array(
					'total'   => ceil( $total_rows / $rows ),
					'page'    => $page + 1,
					'records' => count( $results ),
					'rows'    => $results
				);

				echo json_encode( $result );
				exit();
			default:
				//
		}
	}
}

new WC_Simple_Affiliate_History();
