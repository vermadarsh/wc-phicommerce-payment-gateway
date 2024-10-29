<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Wc_Phicommerce_Payment_Gateway
 * @subpackage Wc_Phicommerce_Payment_Gateway/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wc_Phicommerce_Payment_Gateway
 * @subpackage Wc_Phicommerce_Payment_Gateway/admin
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Wc_Phicommerce_Payment_Gateway_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function wcpp_admin_enqueue_scripts_callback() {
		// Custom admin style.
		wp_enqueue_style(
			$this->plugin_name,
			WCPP_PLUGIN_URL . 'admin/css/wc-phicommerce-payment-gateway-admin.css',
			array(),
			filemtime( WCPP_PLUGIN_PATH . 'admin/css/wc-phicommerce-payment-gateway-admin.css' ),
			'all'
		);

		// Custom admin jquery script.
		wp_enqueue_script(
			$this->plugin_name,
			WCPP_PLUGIN_URL . 'admin/js/wc-phicommerce-payment-gateway-admin.js',
			array( 'jquery' ),
			filemtime( WCPP_PLUGIN_PATH . 'admin/js/wc-phicommerce-payment-gateway-admin.js' ),
			true
		);

		// Localize the script.
		wp_localize_script(
			$this->plugin_name,
			'WCPP_Admin_JS_Vars',
			array(
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'please_wait_text' => __( 'Please wait...', 'wc-phicommerce-payment-gateway' ),
			)
		);
	}

	/**
	 * Register Archway payment gateway with WooCommerce.
	 *
	 * @param array $methods WC registered payemnt methods.
	 * @return array
	 * @since 1.0.0 
	 */
	public function wcpp_woocommerce_payment_gateways_callback( $methods ) {
	    $methods[] = 'WooCommerce_Phicommerce_Payment_Gateway';

		return $methods;
	}

	/**
	 * Add custom metaboxes.
	 *
	 * @since 1.0.0
	 */
	public function wcpp_add_meta_boxes_callback() {
		// Add metabox for showing phicommerce transaction details.
		add_meta_box(
			'wcpp-phicommerce-details',
			__( 'PhiCommerce Payment Details', 'wc-phicommerce-payment-gateway' ),
			array( $this, 'wcpp_phicommerce_payment_details_callback' ),
			'shop_order',
			'advanced',
			'high'
		);
	}

	/**
	 * Custom metabox for phicommerce payment details.
	 *
	 * @since 1.0.0
	 */
	public function wcpp_phicommerce_payment_details_callback() {
		$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		// Return, if there is no post ID.
		if ( is_null( $post_id ) ) {
			return;
		}

		$transaction_ctx             = get_post_meta( $post_id, 'phicommerce-payment-transaction-ctx', true );
		$merchant_transaction_no     = get_post_meta( $post_id, 'phicommerce-payment-merchant-transaction-no', true );
		$transaction_payload         = get_post_meta( $post_id, 'phicommerce-payment-transaction-payload', true );
		$transaction_api_response    = get_post_meta( $post_id, 'phicommerce-payment-transaction-api-response', true );
		$final_api_response_code     = get_post_meta( $post_id, 'phicommerce-payment-transaction-api-final-response-code', true );
		$final_api_response_desc     = get_post_meta( $post_id, 'phicommerce-payment-transaction-api-final-response-desc', true );
		$transaction_last_status_arr = get_post_meta( $post_id, 'phicommerce-payment-transaction-status-api-response-array', true );
		$refund_transaction_arr      = get_post_meta( $post_id, 'phicommerce-payment-refund-status-api-response-array', true );

		ob_start();
		// Start preparing the HTML now.
		?>
		<div class="admin-phicommerce-payment-details">
			<!-- TRANSACTION PAYLOAD -->
			<div class="transaction-payload">
				<strong><?php esc_html_e( 'Transaction Payload:', 'wc-phicommerce-payment-gateway' ); ?></strong>
				<span data-tip="<?php esc_html_e( 'Click on the text to copy', 'wc-phicommerce-payment-gateway' ); ?>" class="woocommerce-help-tip"></span>
				<span title="<?php esc_html_e( 'Click to Copy', 'wc-phicommerce-payment-gateway' ); ?>" class="click-to-copy"><?php echo ( ! empty( $transaction_payload ) ) ? $transaction_payload : '--'; ?></span>
			</div>

			<!-- TRANSACTION API RESPONSE -->
			<div class="transaction-api-response">
				<strong><?php esc_html_e( 'Transaction API Response:', 'wc-phicommerce-payment-gateway' ); ?></strong>
				<span data-tip="<?php esc_html_e( 'Click on the text to copy', 'wc-phicommerce-payment-gateway' ); ?>" class="woocommerce-help-tip"></span>
				<span title="<?php esc_html_e( 'Click to Copy', 'wc-phicommerce-payment-gateway' ); ?>" class="click-to-copy"><?php echo ( ! empty( $transaction_api_response ) ) ? $transaction_api_response : '--'; ?></span>
			</div>

			<!-- TRANSACTION CTX -->
			<div class="transaction-ctx">
				<strong><?php esc_html_e( 'Transaction CTX:', 'wc-phicommerce-payment-gateway' ); ?></strong>
				<span data-tip="<?php esc_html_e( 'Click on the text to copy', 'wc-phicommerce-payment-gateway' ); ?>" class="woocommerce-help-tip"></span>
				<span title="<?php esc_html_e( 'Click to Copy', 'wc-phicommerce-payment-gateway' ); ?>" class="click-to-copy"><?php echo ( ! empty( $transaction_ctx ) ) ? $transaction_ctx : '--'; ?></span>
			</div>

			<!-- MERCHANT TRANSACTION NUMBER -->
			<div class="merchant-transaction-number">
				<strong><?php esc_html_e( 'Merchant Transaction Number:', 'wc-phicommerce-payment-gateway' ); ?></strong>
				<span data-tip="<?php esc_html_e( 'Click on the text to copy', 'wc-phicommerce-payment-gateway' ); ?>" class="woocommerce-help-tip"></span>
				<span class="click-to-copy"><?php echo ( ! empty( $merchant_transaction_no ) ) ? $merchant_transaction_no : '--'; ?></span>
			</div>

			<!-- FINAL TRANSACTION RESPONSE CODE -->
			<div class="transaction-api-response-code">
				<strong><?php esc_html_e( 'Transaction API Response Code:', 'wc-phicommerce-payment-gateway' ); ?></strong>
				<span data-tip="<?php esc_html_e( 'Click on the text to copy', 'wc-phicommerce-payment-gateway' ); ?>" class="woocommerce-help-tip"></span>
				<span class="click-to-copy"><?php echo ( ! empty( $final_api_response_code ) ) ? $final_api_response_code : '--'; ?></span>
			</div>

			<!-- FINAL TRANSACTION RESPONSE DESCRIPTION -->
			<div class="transaction-api-response-desc">
				<strong><?php esc_html_e( 'Transaction API Response Message:', 'wc-phicommerce-payment-gateway' ); ?></strong>
				<span data-tip="<?php esc_html_e( 'Click on the text to copy', 'wc-phicommerce-payment-gateway' ); ?>" class="woocommerce-help-tip"></span>
				<span class="click-to-copy"><?php echo ( ! empty( $final_api_response_desc ) ) ? $final_api_response_desc : '--'; ?></span>
			</div>

			<!-- TRANSACTION STATUS -->
			<div class="transaction-status">
				<strong><?php esc_html_e( 'Last Transaction Status:', 'wc-phicommerce-payment-gateway' ); ?></strong>
				<span data-tip="<?php esc_html_e( 'Click on the text to copy', 'wc-phicommerce-payment-gateway' ); ?>" class="woocommerce-help-tip"></span>
				<span class="click-to-copy"><?php echo ( ! empty( $transaction_last_status_arr['txn_status_message'] ) ) ? $transaction_last_status_arr['txn_status_message'] : '--'; ?></span>
				<button type="button" data-orderid="<?php echo esc_attr( $post_id ); ?>" class="button secondary-button get-transaction-status"><?php esc_html_e( 'Get latest status', 'wc-phicommerce-payment-gateway' ); ?></button>
			</div>

			<!-- IF REFUND DATA IS AVAILABLE -->
			<?php if ( ! empty( $refund_transaction_arr ) ) { ?>
				<div class="refund-transaction-api-response">
					<strong><?php esc_html_e( 'Refund Transaction:', 'wc-phicommerce-payment-gateway' ); ?></strong>
					<span data-tip="<?php esc_html_e( 'Click on the text to copy', 'wc-phicommerce-payment-gateway' ); ?>" class="woocommerce-help-tip"></span>
					<span class="click-to-copy"><?php echo wp_json_encode( $refund_transaction_arr ); ?></span>
				</div>
			<?php } ?>
		</div>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Get transaction status.
	 *
	 * @since 1.0.0
	 */
	public function wcpp_get_transaction_status_callback() {
		// Posted data.
		$order_id = filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );

		// Get the payment gateway settings.
		$gateway_settings            = woocommerce_phicommerce_payment_gateway_settings();
		$payphi_status_api_url       = ( ! empty( $gateway_settings['payphi_sale_status_api_url'] ) ) ? $gateway_settings['payphi_sale_status_api_url'] : '';
		$payphi_merchant_information = ( ! empty( $gateway_settings['payphi_merchant_information'] ) ) ? $gateway_settings['payphi_merchant_information'] : '';
		$payphi_status_message       = '';

		// Return, if the transaction API URL or other settings are not available.
		if ( empty( $payphi_status_api_url ) || empty( $payphi_merchant_information ) ) {
			return;
		}

		// This means the merchant ID is not available, we need to see how many merchant IDs are configured in the admin.
		$admin_configured_merchant_ids       = json_decode( $payphi_merchant_information );
		$admin_configured_merchant_ids_count = count( $admin_configured_merchant_ids );

		/**
		 * If there is only 1 merchant configuration in the admin, then no need for any kind of validation.
		 * Proceed with that merchant details.
		 */
		if ( 1 === $admin_configured_merchant_ids_count ) {
			$payphi_api_credentials = wcpp_get_matching_merchant_id( $checkout_merchant_id, $admin_configured_merchant_ids );

			// Return if the payphi credentials are invalid.
			if ( empty( $payphi_api_credentials ) || ! is_array( $payphi_api_credentials ) ) {
				return;
			} else {
				$payphi_merchant_id          = ( ! empty( $payphi_api_credentials['merchId'] ) ) ? $payphi_api_credentials['merchId'] : '';
				$payphi_hash_calculation_key = ( ! empty( $payphi_api_credentials['hashKey'] ) ) ? $payphi_api_credentials['hashKey'] : '';
	
				// Return, if either of the detail is not present.
				if ( empty( $payphi_merchant_id ) || empty( $payphi_hash_calculation_key ) ) {
					return;
				}
			}
		} else {
			$checkout_merchant_id = '';
			/**
			 * If here, means there are multiple merchants in the admin, and you need to validate.
			 * Validate the checkout merchand ID.
			 */
			if ( empty( $checkout_merchant_id ) || is_null( $checkout_merchant_id ) ) {
				return;
			} else {
				/**
				 * Merchant ID is provided on the checkout.
				 * Find the matching merchant ID now.
				 */
				$db_merchant_ids            = array_column( $admin_configured_merchant_ids, 'merchId' );
				$matching_merchant_id_index = array_search( $checkout_merchant_id, $db_merchant_ids );

				// If the matching merchant ID is not found.
				if ( false === $matching_merchant_id_index ) {
					return;
				} else {
					$payphi_api_credentials = wcpp_get_matching_merchant_id( $checkout_merchant_id, $admin_configured_merchant_ids, $matching_merchant_id_index );

					// Validate the payphi credentials.
					if ( empty( $payphi_api_credentials ) || ! is_array( $payphi_api_credentials ) ) {
						return;
					} else {
						$payphi_merchant_id          = ( ! empty( $payphi_api_credentials['merchId'] ) ) ? $payphi_api_credentials['merchId'] : '';
						$payphi_hash_calculation_key = ( ! empty( $payphi_api_credentials['hashKey'] ) ) ? $payphi_api_credentials['hashKey'] : '';
			
						// Throw error if either of the detail is not present.
						if ( empty( $payphi_merchant_id ) || empty( $payphi_hash_calculation_key ) ) {
							return;
						}
					}
				}
			}
		}

		/**
		 * Fire the payment API now.
		 * Prepare the status parameters.
		 */
		$status_parameters               = array(
			'merchantID'      => $payphi_merchant_id,
			'merchantTxnNo'   => get_post_meta( $order_id, 'phicommerce-payment-merchant-transaction-no', true ),
			'originalTxnNo'   => get_post_meta( $order_id, 'phicommerce-payment-merchant-transaction-no', true ),
			'paymentMode'     => '',
			'amount'          => get_post_meta( $order_id, '_order_total', true ),
			'transactionType' => 'STATUS',
			'addlParam1'      => '',
			'addlParam2'      => '',
		);
		$status_parameters['secureHash'] = wcpp_get_secured_hash( $status_parameters, $payphi_hash_calculation_key );

		/**
		 * PhiCommerce sale status arguments.
		 *
		 * This filter helps to modify the phicommerce sale status arguments.
		 *
		 * @param array $payment_parameters PhiCommerce sale status arguments.
		 * @return array
		 * @since 1.0.0
		 */
		$status_parameters = apply_filters( 'wcpp_phicommerce_sale_status_args', $status_parameters );
		$status_parameters  = substr( add_query_arg( $status_parameters, '' ), 1 );

		wcpp_write_payment_log( '::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::' ); // Write the log.
		wcpp_write_payment_log( 'NOTICE: PayPhi sale status retrieving..' ); // Write the log.
		wcpp_write_payment_log( "NOTICE: Payload: {$status_parameters}" ); // Write the log.

		// Process the API now.
		$response = wp_remote_post(
			$payphi_status_api_url,
			array(
				'method'  => 'POST',
				'timeout' => 600,
				'body'    => $status_parameters,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			)
		);

		$response_code = wp_remote_retrieve_response_code( $response ); // Get the response code.
		wcpp_write_payment_log( "NOTICE: Response code: {$response_code}" ); // Write the log.

		// Is it's a success.
		if ( 200 === $response_code ) {
			$response_body = wp_remote_retrieve_body( $response );
			WC()->session->set( 'wcpp_payphi_transaction_status_api_response', $response_body ); // Set the response body in the session.
			wcpp_write_payment_log( "NOTICE: Response body: {$response_body}" ); // Write the log.
			$response_body        = json_decode( $response_body );

			$payphi_transaction_response_code = ( ! empty( $response_body->txnResponseCode ) ) ? $response_body->txnResponseCode : '';
			$payphi_transaction_status        = ( ! empty( $response_body->txnStatus ) ) ? $response_body->txnStatus : '';
			$payphi_status_message            = ( ! empty( $response_body->txnRespDescription ) ) ? $response_body->txnRespDescription : '';

			// Update the database.
			update_post_meta(
				$order_id,
				'phicommerce-payment-transaction-status-api-response-array',
				array(
					'txn_response_code'  => $payphi_transaction_response_code,
					'txn_status_code'    => $payphi_transaction_status,
					'txn_status_message' => $payphi_status_message,
				)
			);
		}

		// Send the AJAX response.
		wp_send_json_success(
			array(
				'code'                  => 'transaction-status-fetched',
				'message'               => __( 'Transaction status fetched and updated.', 'wc-phicommerce-payment-gateway' ),
				'payphi_status_message' => $payphi_status_message,
			)
		);
		wp_die();
	}

	/**
	 * Hook the transaction status option in order listing page in admin.
	 *
	 * @param array  $actions Holds the actions array.
	 * @param object $order Holds the WooCommerce order object.
	 * @return array
	 */
	public function wcpp_woocommerce_admin_order_actions_callback( $actions, $order ) {
		$payment_method = $order->get_payment_method();

		// Return, if the payment is made through the phicommerce payment gateway.
		if ( ! empty( $payment_method ) && 'phicommerce_payments' !== $payment_method ) {
			return $actions;
		}

		// Add status action to the 
		$actions['wcpp-get-transaction-status'] = array(
			'url'    => '#',
			'name'   => '',
			/* translators: 1: %d: order ID. */
			'title'  => sprintf( __( 'Get Payphi transaction status for order #%1$d', 'wc-phicommerce-payment-gateway' ), $order->get_id() ),
			'action' => 'wcpp-get-transaction-status',
		);

		return $actions;
	}

	/**
	 * Hook the transaction refund option on the order edit details page.
	 *
	 * @param object $wc_order Holds the WooCommerce order object.
	 */
	public function wcpp_woocommerce_order_item_add_action_buttons_callback( $wc_order ) {
		$payment_method = $wc_order->get_payment_method();

		// Return, if the payment is made through the phicommerce payment gateway.
		if ( ! empty( $payment_method ) && 'phicommerce_payments' !== $payment_method ) {
			return $actions;
		}

		// Check if the refund has already been processed.
		$refund_transaction_arr = get_post_meta( $wc_order->get_id(), 'phicommerce-payment-refund-status-api-response-array', true );
		$refund_response_code   = ( ! empty( $refund_transaction_arr['refund_response_code'] ) ) ? $refund_transaction_arr['refund_response_code'] : '';

		// If the refund processed was a success, remove the button.
		if ( 'P1000' !== $refund_response_code ) {
			?>
			<button type="button" data-orderid="<?php echo esc_attr( $wc_order->get_id() ); ?>" class="button payphi-refund-order"><?php esc_html_e( 'Refund via PayPhi', 'woocommerce' ); ?></button>
			<span class="payphi-refund-error"></span>
			<span class="payphi-refund-success"></span>
			<?php
		}
	}

	/**
	 * The render button should be hidden if the refund from payphi is activated.
	 *
	 * @param boolean  $should_render Should render the refund button.
	 * @param int      $order_id WooCommerce order ID.
	 * @param WC_Order $wc_order WooCommerce order object.
	 * @return boolean
	 */
	public function wcpp_woocommerce_admin_order_should_render_refunds_callback( $should_render, $order_id, $wc_order ) {
		$payment_method = $wc_order->get_payment_method();

		// Return, if the payment is made through the phicommerce payment gateway.
		if ( ! empty( $payment_method ) && 'phicommerce_payments' === $payment_method ) {
			$should_render = false;
		}

		return $should_render;
	}

	/**
	 * Process refund via AJAX.
	 */
	public function wcpp_process_refund_callback() {
		// Posted data.
		$order_id = filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );

		// Get the payment gateway settings.
		$gateway_settings            = woocommerce_phicommerce_payment_gateway_settings();
		$payphi_refund_api_url       = ( ! empty( $gateway_settings['payphi_sale_refund_api_url'] ) ) ? $gateway_settings['payphi_sale_refund_api_url'] : '';
		$payphi_merchant_information = ( ! empty( $gateway_settings['payphi_merchant_information'] ) ) ? $gateway_settings['payphi_merchant_information'] : '';
		$payphi_refund_response_desc = '';
		$do_reload                   = false;

		// Return, if the transaction API URL or other settings are not available.
		if ( empty( $payphi_refund_api_url ) || empty( $payphi_merchant_information ) ) {
			return;
		}

		// This means the merchant ID is not available, we need to see how many merchant IDs are configured in the admin.
		$admin_configured_merchant_ids       = json_decode( $payphi_merchant_information );
		$admin_configured_merchant_ids_count = count( $admin_configured_merchant_ids );

		/**
		 * If there is only 1 merchant configuration in the admin, then no need for any kind of validation.
		 * Proceed with that merchant details.
		 */
		if ( 1 === $admin_configured_merchant_ids_count ) {
			$payphi_api_credentials = wcpp_get_matching_merchant_id( $checkout_merchant_id, $admin_configured_merchant_ids );

			// Return if the payphi credentials are invalid.
			if ( empty( $payphi_api_credentials ) || ! is_array( $payphi_api_credentials ) ) {
				return;
			} else {
				$payphi_merchant_id          = ( ! empty( $payphi_api_credentials['merchId'] ) ) ? $payphi_api_credentials['merchId'] : '';
				$payphi_hash_calculation_key = ( ! empty( $payphi_api_credentials['hashKey'] ) ) ? $payphi_api_credentials['hashKey'] : '';
	
				// Return, if either of the detail is not present.
				if ( empty( $payphi_merchant_id ) || empty( $payphi_hash_calculation_key ) ) {
					return;
				}
			}
		} else {
			$checkout_merchant_id = '';
			/**
			 * If here, means there are multiple merchants in the admin, and you need to validate.
			 * Validate the checkout merchand ID.
			 */
			if ( empty( $checkout_merchant_id ) || is_null( $checkout_merchant_id ) ) {
				return;
			} else {
				/**
				 * Merchant ID is provided on the checkout.
				 * Find the matching merchant ID now.
				 */
				$db_merchant_ids            = array_column( $admin_configured_merchant_ids, 'merchId' );
				$matching_merchant_id_index = array_search( $checkout_merchant_id, $db_merchant_ids );

				// If the matching merchant ID is not found.
				if ( false === $matching_merchant_id_index ) {
					return;
				} else {
					$payphi_api_credentials = wcpp_get_matching_merchant_id( $checkout_merchant_id, $admin_configured_merchant_ids, $matching_merchant_id_index );

					// Validate the payphi credentials.
					if ( empty( $payphi_api_credentials ) || ! is_array( $payphi_api_credentials ) ) {
						return;
					} else {
						$payphi_merchant_id          = ( ! empty( $payphi_api_credentials['merchId'] ) ) ? $payphi_api_credentials['merchId'] : '';
						$payphi_hash_calculation_key = ( ! empty( $payphi_api_credentials['hashKey'] ) ) ? $payphi_api_credentials['hashKey'] : '';
			
						// Throw error if either of the detail is not present.
						if ( empty( $payphi_merchant_id ) || empty( $payphi_hash_calculation_key ) ) {
							return;
						}
					}
				}
			}
		}

		/**
		 * Fire the payment API now.
		 * Prepare the refund parameters.
		 */
		$refund_parameters               = array(
			'merchantID'      => $payphi_merchant_id,
			'merchantTxnNo'   => 'payphi-' . time(),
			'originalTxnNo'   => get_post_meta( $order_id, 'phicommerce-payment-merchant-transaction-no', true ),
			'paymentMode'     => '',
			'amount'          => get_post_meta( $order_id, '_order_total', true ),
			'transactionType' => 'REFUND',
			'addlParam1'      => '',
			'addlParam2'      => '',
		);
		$refund_parameters['secureHash'] = wcpp_get_secured_hash( $refund_parameters, $payphi_hash_calculation_key );

		/**
		 * PhiCommerce sale refund arguments.
		 *
		 * This filter helps to modify the phicommerce sale refund arguments.
		 *
		 * @param array $payment_parameters PhiCommerce sale refund arguments.
		 * @return array
		 * @since 1.0.0
		 */
		$refund_parameters = apply_filters( 'wcpp_phicommerce_sale_refund_args', $refund_parameters );
		$refund_parameters  = substr( add_query_arg( $refund_parameters, '' ), 1 );

		wcpp_write_payment_log( '::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::' ); // Write the log.
		wcpp_write_payment_log( 'NOTICE: PayPhi sale refund retrieving..' ); // Write the log.
		wcpp_write_payment_log( "NOTICE: Payload: {$refund_parameters}" ); // Write the log.

		// Process the API now.
		$response = wp_remote_post(
			$payphi_refund_api_url,
			array(
				'method'  => 'POST',
				'timeout' => 600,
				'body'    => $refund_parameters,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			)
		);

		$response_code = wp_remote_retrieve_response_code( $response ); // Get the response code.
		wcpp_write_payment_log( "NOTICE: Response code: {$response_code}" ); // Write the log.

		// Is it's a success.
		if ( 200 === $response_code ) {
			$response_body = wp_remote_retrieve_body( $response );
			WC()->session->set( 'wcpp_payphi_transaction_status_api_response', $response_body ); // Set the response body in the session.
			wcpp_write_payment_log( "NOTICE: Response body: {$response_body}" ); // Write the log.
			$response_body                = json_decode( $response_body );
			$payphi_refund_response_code  = ( ! empty( $response_body->responseCode ) ) ? $response_body->responseCode : '';
			$payphi_refund_transaction_id = ( ! empty( $response_body->txnID ) ) ? $response_body->txnID : '';
			$payphi_refund_date_time      = ( ! empty( $response_body->paymentDateTime ) ) ? $response_body->paymentDateTime : '';
			$payphi_refund_response_desc  = ( ! empty( $response_body->respDescription ) ) ? $response_body->respDescription : '';
			$wc_order                     = wc_get_order( $order_id ); // Get the WooCommerce order object.

			// If the refund is successful.
			if ( 'P1000' === $payphi_refund_response_code ) {
				$do_reload = true;
				$wc_order->update_status( 'wc-refunded' ); // Update the order status to refund.
				$wc_order->add_order_note(
					sprintf(
						__( 'Refund processed by PayPhi payment gateway. Transaction ID: %1$s', 'wc-phicommerce-payment-gateway' ),
						$payphi_refund_transaction_id
					)
				);

				wcpp_write_payment_log( "SUCCESS: Refund processed. Transaction ID: {$payphi_refund_transaction_id}" ); // Write the log.
			} else {
				// Just in case the refund is not success, do the log.
				wcpp_write_payment_log( "ERROR: Refund could not be processed. Response code: {$payphi_refund_response_code}. Response message: {$payphi_refund_response_desc}" );
			}

			// Update the database.
			update_post_meta(
				$order_id,
				'phicommerce-payment-refund-status-api-response-array',
				array(
					'refund_response_code'         => $payphi_refund_response_code,
					'refund_transaction_id'        => $payphi_refund_transaction_id,
					'refund_transaction_date_time' => $payphi_refund_date_time,
					'refund_response_desc'         => $payphi_refund_response_desc,
				)
			);
		}

		// Send the AJAX response.
		wp_send_json_success(
			array(
				'code'            => 'payphi-refund-processed',
				'success_message' => __( 'Refund processed successfully. Reloading...', 'wc-phicommerce-payment-gateway' ),
				'show_error'      => ( true === $do_reload ) ? 'no' : 'yes',
				'error_message'   => sprintf( __( 'Refund not processed. Try again later. Response from gateway: %1$s', 'wc-phicommerce-payment-gateway' ), $payphi_refund_response_desc ),
			)
		);
		wp_die();
	}

	/**
	 * Function to add custom columns to the woocommerce orders.
	 *
	 * @param $columns array Holds the default columns array.
	 * @return array
	 */
	public function wcpp_manage_shop_order_posts_columns_callback( $columns = array() ) {
		$columns['sale_merchant_txn_number'] = __( 'Sale: Merchant Txn. Number', 'wc-phicommerce-payment-gateway' );

		return $columns;
	}

	/**
	 * Function to add custom columns content on the woocommerce orders.
	 *
	 * @param $column_name array Holds the column name.
	 * @param $post_id int Holds the post ID.
	 */
	public function wcpp_manage_shop_order_posts_custom_column( $column_name, $post_id ) {
		// Check for sale merchant transaction number column.
		if ( 'sale_merchant_txn_number' === $column_name ) {
			$merchant_transaction_no = get_post_meta( $post_id, 'phicommerce-payment-merchant-transaction-no', true );
			echo ( ! empty( $merchant_transaction_no ) ) ? $merchant_transaction_no : '-';
		}
	}
}
