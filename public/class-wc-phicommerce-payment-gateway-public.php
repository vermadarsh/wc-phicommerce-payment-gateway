<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Wc_Phicommerce_Payment_Gateway
 * @subpackage Wc_Phicommerce_Payment_Gateway/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wc_Phicommerce_Payment_Gateway
 * @subpackage Wc_Phicommerce_Payment_Gateway/public
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Wc_Phicommerce_Payment_Gateway_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wcpp_wp_enqueue_scripts_callback() {
		// Custom public style.
		wp_enqueue_style(
			$this->plugin_name,
			WCPP_PLUGIN_URL . 'public/css/wc-phicommerce-payment-gateway-public.css',
			array(),
			filemtime( WCPP_PLUGIN_PATH . 'public/css/wc-phicommerce-payment-gateway-public.css' ),
			'all'
		);

		// Custom public script.
		wp_enqueue_script(
			$this->plugin_name,
			WCPP_PLUGIN_URL . 'public/js/wc-phicommerce-payment-gateway-public.js',
			array( 'jquery' ),
			filemtime( WCPP_PLUGIN_PATH . 'public/js/wc-phicommerce-payment-gateway-public.js' ),
			true
		);
	}

	/**
	 * Fire the payment API now.
	 *
	 * @since 1.0.0
	 */
	public function wcpp_woocommerce_after_checkout_validation_callback( $checkout_data, $errors ) {
		// Get the selected payment method.
		$payment_method       = filter_input( INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING );
		$checkout_merchant_id = filter_input( INPUT_POST, 'checkout_merchant_id', FILTER_SANITIZE_STRING );
		$errors_count         = ( ! empty( $errors->errors ) ) ? count( $errors->errors ) : 0;

		// If it's not the phicommerce payment gateway, return.
		if ( ! empty( $payment_method ) && 'phicommerce_payments' !== $payment_method ) {
			return;
		}

		// If there are other checkout errors, return.
		if ( 0 < $errors_count ) {
			return;
		}

		// Check if the initial transaction request is already there.
		$final_transaction_response_code = get_transient( 'wcpp_payphi_final_response_code' );

		// If the last transaction failed, return to show the error.
		if ( ! empty( $final_transaction_response_code ) && ! is_bool( $final_transaction_response_code ) ) {
			// If there is any failure due to any reason.
			if ( ! ( '000' === $final_transaction_response_code || '0000' === $final_transaction_response_code ) ) {
				// Unset the session for the oayment captured in case the phicommerce payment is failed/canceled.
				WC()->session->__unset( 'wcpp_payphi_initial_transaction_request' );

				$payment_settings_error = sprintf( __( 'The last payment was canceled or failed. Please retry paying again for the order.', 'wc-phicommerce-payment-gateway' ), '<strong>', '</strong>' );
				wc_add_notice( $payment_settings_error, 'error' );

				// Delete the transients of the final transaction detail.
				delete_transient( 'wcpp_payphi_final_response_code' );
				delete_transient( 'wcpp_payphi_final_response_desc' );

				// Return to reprocess the payment.
				return;
			}
		}

		// Return, if the initial transaction request is already done.
		$initial_transaction_request = WC()->session->get( 'wcpp_payphi_initial_transaction_request' );
		if ( ! is_null( $initial_transaction_request ) && 'yes' === $initial_transaction_request ) {
			wcpp_write_payment_log( 'NOTICE: Payment request returned to merchant.' ); // Write the log.
			return;
		}

		// Get the gateway settings.
		$gateway_settings            = woocommerce_phicommerce_payment_gateway_settings();
		$payphi_sale_api_url         = ( ! empty( $gateway_settings['payphi_sale_api_url'] ) ) ? $gateway_settings['payphi_sale_api_url'] : '';
		$payphi_merchant_information = ( ! empty( $gateway_settings['payphi_merchant_information'] ) ) ? $gateway_settings['payphi_merchant_information'] : '';

		// Return false, if the transaction API URL or other settings are not available.
		if ( empty( $payphi_sale_api_url ) || empty( $payphi_merchant_information ) ) {
			$is_checkout_error      = true;
			$payment_settings_error = sprintf( __( 'Cannot process payment due to %1$sgateway settings%2$s error. Please contact site administrator.', 'wc-phicommerce-payment-gateway' ), '<strong>', '</strong>' );
			wc_add_notice( $payment_settings_error, 'error' );
		} else {
			// This means the merchant ID is not available, we need to see how many merchant IDs are configured in the admin.
			$admin_configured_merchant_ids       = json_decode( $payphi_merchant_information );
			$admin_configured_merchant_ids_count = count( $admin_configured_merchant_ids );

			/**
			 * If there is only 1 merchant configuration in the admin, then no need for any kind of validation.
			 * Proceed with that merchant details.
			 */
			if ( 1 === $admin_configured_merchant_ids_count ) {
				$payphi_api_credentials = wcpp_get_matching_merchant_id( $checkout_merchant_id, $admin_configured_merchant_ids );

				// Validate the payphi credentials.
				if ( empty( $payphi_api_credentials ) || ! is_array( $payphi_api_credentials ) ) {
					$is_checkout_error      = true;
					$payment_settings_error = sprintf( __( 'Cannot process payment due to %1$sgateway settings%2$s error. Please contact site administrator.', 'wc-phicommerce-payment-gateway' ), '<strong>', '</strong>' );
					wc_add_notice( $payment_settings_error, 'error' );
				} else {
					$payphi_merchant_id          = ( ! empty( $payphi_api_credentials['merchId'] ) ) ? $payphi_api_credentials['merchId'] : '';
					$payphi_hash_calculation_key = ( ! empty( $payphi_api_credentials['hashKey'] ) ) ? $payphi_api_credentials['hashKey'] : '';
		
					// Throw error if either of the detail is not present.
					if ( empty( $payphi_merchant_id ) || empty( $payphi_hash_calculation_key ) ) {
						$is_checkout_error      = true;
						$payment_settings_error = sprintf( __( 'Cannot process payment due to %1$sgateway settings%2$s error. Please contact site administrator.', 'wc-phicommerce-payment-gateway' ), '<strong>', '</strong>' );
						wc_add_notice( $payment_settings_error, 'error' );
					}
				}
			} else {
				/**
				 * If here, means there are multiple merchants in the admin, and you need to validate.
				 * Validate the checkout merchand ID.
				 */
				if ( empty( $checkout_merchant_id ) || is_null( $checkout_merchant_id ) ) {
					$is_checkout_error      = true;
					$payment_settings_error = sprintf( __( '%1$sMerchant ID%2$s is the required field.', 'wc-phicommerce-payment-gateway' ), '<strong>', '</strong>' );
					wc_add_notice( $payment_settings_error, 'error' );
				} else {
					/**
					 * Merchant ID is provided on the checkout.
					 * Find the matching merchant ID now.
					 */
					$db_merchant_ids            = array_column( $admin_configured_merchant_ids, 'merchId' );
					$matching_merchant_id_index = array_search( $checkout_merchant_id, $db_merchant_ids );

					// If the matching merchant ID is not found.
					if ( false === $matching_merchant_id_index ) {
						$is_checkout_error      = true;
						$payment_settings_error = sprintf( __( 'Multiple %1$smerchant ID%2$s present in the database and no matching merchant ID found.', 'wc-phicommerce-payment-gateway' ), '<strong>', '</strong>' );
						wc_add_notice( $payment_settings_error, 'error' );
					} else {
						$payphi_api_credentials = wcpp_get_matching_merchant_id( $checkout_merchant_id, $admin_configured_merchant_ids, $matching_merchant_id_index );

						// Validate the payphi credentials.
						if ( empty( $payphi_api_credentials ) || ! is_array( $payphi_api_credentials ) ) {
							$is_checkout_error      = true;
							$payment_settings_error = sprintf( __( 'Cannot process payment due to %1$sgateway settings%2$s error. Please contact site administrator.', 'wc-phicommerce-payment-gateway' ), '<strong>', '</strong>' );
							wc_add_notice( $payment_settings_error, 'error' );
						} else {
							$payphi_merchant_id          = ( ! empty( $payphi_api_credentials['merchId'] ) ) ? $payphi_api_credentials['merchId'] : '';
							$payphi_hash_calculation_key = ( ! empty( $payphi_api_credentials['hashKey'] ) ) ? $payphi_api_credentials['hashKey'] : '';
				
							// Throw error if either of the detail is not present.
							if ( empty( $payphi_merchant_id ) || empty( $payphi_hash_calculation_key ) ) {
								$is_checkout_error      = true;
								$payment_settings_error = sprintf( __( 'Cannot process payment due to %1$sgateway settings%2$s error. Please contact site administrator.', 'wc-phicommerce-payment-gateway' ), '<strong>', '</strong>' );
								wc_add_notice( $payment_settings_error, 'error' );
							}
						}
					}
				}
			}
		}

		// Return, if there is checkout error.
		if ( $is_checkout_error ) {
			return;
		}

		$customer_first_name = filter_input( INPUT_POST, 'billing_first_name', FILTER_SANITIZE_STRING );
		$customer_last_name  = filter_input( INPUT_POST, 'billing_last_name', FILTER_SANITIZE_STRING );
		$customer_email      = filter_input( INPUT_POST, 'billing_email', FILTER_SANITIZE_STRING );
		$customer_phone      = filter_input( INPUT_POST, 'billing_phone', FILTER_SANITIZE_STRING );
		$cart_total_data     = WC()->cart->get_totals(); // Cart totals data.
		$cart_totel_amt      = ( ! empty( $cart_total_data['total'] ) ) ? $cart_total_data['total'] : '0.00';
		$expiry_month        = ( 10 > $expiry_month ) ? "0{$expiry_month}" : $expiry_month;

		/**
		 * Fire the payment API now.
		 * Prepare the payment parameters.
		 */
		$payment_parameters               = array(
			'merchantId'       => $payphi_merchant_id,
			'merchantTxnNo'    => 'payphi-' . time(),
			'amount'           => $cart_totel_amt,
			'currencyCode'     => '356',
			'payType'          => '0',
			'customerEmailID'  => $customer_email,
			'transactionType'  => 'SALE',
			'txnDate'          => gmdate( 'YmdHis' ),
			'customerID'       => '12345',
			'returnURL'        => home_url( '?phipay_return=1' ),
			'customerMobileNo' => $customer_phone,
			'addlParam1'       => filter_input( INPUT_POST, 'addlParam1', FILTER_SANITIZE_STRING ),
		);
		$payment_parameters['secureHash'] = wcpp_get_secured_hash( $payment_parameters, $payphi_hash_calculation_key );

		/**
		 * PhiCommerce payment arguments.
		 *
		 * This filter helps to modify the phicommerce payment arguments.
		 *
		 * @param array $payment_parameters PhiCommerce payment arguments.
		 * @return array
		 * @since 1.0.0
		 */
		$payment_parameters = apply_filters( 'wcpp_phicommerce_payment_args', $payment_parameters );
		$payment_parameters = wp_json_encode( $payment_parameters ); // JSON encoded.

		// Set the woocommerce session.
		WC()->session->set( 'wcpp_payphi_transaction_payload', $payment_parameters );

		wcpp_write_payment_log( '::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::' ); // Write the log.
		wcpp_write_payment_log( 'NOTICE: New PayPhi transaction initiated..' ); // Write the log.
		wcpp_write_payment_log( "NOTICE: Payload: {$payment_parameters}" ); // Write the log.

		// Process the API now.
		$response = wp_remote_post(
			$payphi_sale_api_url,
			array(
				'method'  => 'POST',
				'timeout' => 600,
				'body'    => $payment_parameters,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
			)
		);

		$response_code = wp_remote_retrieve_response_code( $response ); // Get the response code.
		wcpp_write_payment_log( "NOTICE: Response code: {$response_code}" ); // Write the log.

		// Is it's a success.
		if ( 200 === $response_code ) {
			// Get the response body.
			$response_body = wp_remote_retrieve_body( $response );
			WC()->session->set( 'wcpp_payphi_transaction_api_response', $response_body ); // Set the response body in the session.
			wcpp_write_payment_log( "NOTICE: Response body: {$response_body}" ); // Write the log.
			$response_body        = json_decode( $response_body );
			$payphi_response_code = ( ! empty( $response_body->responseCode ) ) ? $response_body->responseCode : '';

			// If the response code is valid from PayPhi.
			if ( ! empty( $payphi_response_code ) ) {
				if ( 'R1000' === $payphi_response_code ) {
					wcpp_write_payment_log( "SUCCESS: Transaction request success. Code: {$payphi_response_code}" ); // Write the log.
					$payphi_redirect_uri     = ( ! empty( $response_body->redirectURI ) ) ? $response_body->redirectURI : '';
					$transaction_ctx         = ( ! empty( $response_body->tranCtx ) ) ? $response_body->tranCtx : '';
					$merchant_transaction_no = ( ! empty( $response_body->merchantTxnNo ) ) ? $response_body->merchantTxnNo : '';

					// Set a session to let the system know that the OTP verification is required.
					WC()->session->set( 'wcpp_payphi_initial_transaction_request', 'yes' );

					// If the tranCtx is available.
					if ( ! empty( $transaction_ctx ) ) {
						WC()->session->set( 'wcpp_payphi_transaction_ctx', $transaction_ctx );
					}

					// If the merchant transaction no. is available.
					if ( ! empty( $merchant_transaction_no ) ) {
						WC()->session->set( 'wcpp_payphi_merchant_transaction_no', $merchant_transaction_no );
					}

					// If the redirect is available.
					if ( ! empty( $payphi_redirect_uri ) ) {
						$payphi_redirect_uri = "{$payphi_redirect_uri}/?tranCtx={$transaction_ctx}";
						wcpp_write_payment_log( "NOTICE: Redirected to: {$payphi_redirect_uri}" ); // Write the log.
						wc_add_notice( 'Redirecting you to <a class="phicommerce-auth-otp-notice" href="' . $payphi_redirect_uri . '">PayPhi portal</a> for completing the payment now...', 'error' );
					}
				} else {
					// Just in case the payment was not made.
					wcpp_write_payment_log( "ERROR: Payment initial reqest failed. Response code received: {$payphi_response_code}" ); // Write the log.
					wc_add_notice( 'Order is not placed due to some technical error. Please contact site administrator.', 'error' );
				}
			}
		}
	}

	/**
	 * Save the card details in the database.
	 *
	 * @param int $order_id WooCommerce order ID.
	 * @since 1.0.0
	 */
	public function wcpp_woocommerce_checkout_update_order_meta_callback( $order_id ) {
		// Get the selected payment method.
		$payment_method = filter_input( INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING );
		$wc_order       = wc_get_order( $order_id );

		// If it's not the archway payment gateway, return.
		if ( ! empty( $payment_method ) && 'phicommerce_payments' !== $payment_method ) {
			return;
		}

		// Get the transaction details from the wc session.
		$transaction_ctx          = WC()->session->get( 'wcpp_payphi_transaction_ctx' );
		$merchant_transaction_no  = WC()->session->get( 'wcpp_payphi_merchant_transaction_no' );
		$transaction_payload      = WC()->session->get( 'wcpp_payphi_transaction_payload' );
		$transaction_api_response = WC()->session->get( 'wcpp_payphi_transaction_api_response' );
		$final_api_response_code  = get_transient( 'wcpp_payphi_final_response_code' );
		$final_api_response_desc  = get_transient( 'wcpp_payphi_final_response_desc' );
		$checkout_merchant_id     = filter_input( INPUT_POST, 'checkout_merchant_id', FILTER_SANITIZE_STRING );

		// Update the database.
		update_post_meta( $order_id, 'phicommerce-payment-transaction-ctx', $transaction_ctx );
		update_post_meta( $order_id, 'phicommerce-payment-merchant-transaction-no', $merchant_transaction_no );
		update_post_meta( $order_id, 'phicommerce-payment-transaction-payload', $transaction_payload );
		update_post_meta( $order_id, 'phicommerce-payment-transaction-api-response', $transaction_api_response );
		update_post_meta( $order_id, 'phicommerce-payment-transaction-api-final-response-code', $final_api_response_code );
		update_post_meta( $order_id, 'phicommerce-payment-transaction-api-final-response-desc', $final_api_response_desc );
		update_post_meta( $order_id, 'checkout-merchant-id', $checkout_merchant_id );
		wcpp_write_payment_log( 'NOTICE: Order meta updated with relevant payphi details.' ); // Log.

		// Update the order status.
		if( '000'== $final_api_response_code || '0000'== $final_api_response_code ) {
			$wc_order->update_status( 'wc-processing' ); // If the payment is a success.
		} else {
			$wc_order->update_status( 'wc-pending' ); // If the payment failed.
		}

		wcpp_write_payment_log( 'NOTICE: Session closed, order successfully placed.' ); // Log.
	}

	/**
	 * Update the transaction ID to the database.
	 *
	 * @param int   $order_id WooCommerce order ID.
	 * @param array $posted_data Checkout posted data.
	 * @since 1.0.0
	 */
	public function cf_woocommerce_checkout_order_processed_callback( $order_id, $posted_data ) {
		// Unset the transients & custom session variables.
		WC()->session->__unset( 'wcpp_payphi_initial_transaction_request' );
		WC()->session->__unset( 'wcpp_payphi_transaction_ctx' );
		WC()->session->__unset( 'wcpp_payphi_merchant_transaction_no' );
		WC()->session->__unset( 'wcpp_payphi_transaction_payload' );
		WC()->session->__unset( 'wcpp_payphi_transaction_api_response' );
		delete_transient( 'wcpp_payphi_final_response_code' );
		delete_transient( 'wcpp_payphi_final_response_desc' );
	}

	/**
	 * Make the redirections from the homepage to the checkout page to place the order after OTP verification.
	 *
	 * @since 1.0.0
	 */
	public function wcpp_wp_head_callback() {
		// If it's the front page.
		if ( is_front_page() ) {
			$payphi_return = filter_input( INPUT_GET, 'phipay_return', FILTER_SANITIZE_NUMBER_INT );
			$redirect_to   = '';

			// it's the payphi return.
			if ( '1' === $payphi_return ) {
				// Response from PayPhi.
				$payphi_response_code = filter_input( INPUT_POST, 'responseCode', FILTER_SANITIZE_STRING );
				$payphi_response_desc = filter_input( INPUT_POST, 'respDescription', FILTER_SANITIZE_STRING );
				$payphi_response      = wp_json_encode( $_POST );

				/**
				 * Set this response in the transient.
				 * We cannot set woocommerce database as when the user is redirected on the checkout page, the complete session is restored.
				 */
				set_transient( 'wcpp_payphi_final_response_code', $payphi_response_code, DAY_IN_SECONDS );
				set_transient( 'wcpp_payphi_final_response_desc', $payphi_response_desc, DAY_IN_SECONDS );

				// Write the log.
				wcpp_write_payment_log( 'NOTICE: Successful return to the merchant store.' );
				wcpp_write_payment_log( "NOTICE: PayPhi response: {$payphi_response}" );
				wcpp_write_payment_log( 'NOTICE: Heading to place the order.' );

				// Set the redirection.
				$redirect_to = wc_get_checkout_url() . '?place_order=1';
			}

			// If the redirect is available.
			if ( ! empty( $redirect_to ) ) {
				?>
				<script type="text/javascript">window.location.href='<?php echo $redirect_to; ?>';</script>
				<?php
			}
		}
	}

	/**
	 * Add icon to the payment gateways list.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function wcpp_woocommerce_custom_gateway_icon_callback() {
		return WCPP_PLUGIN_URL . 'public/images/phicommerce_logo.jpeg';
	}

	/**
	 * Modify the order received text.
	 *
	 * @param string $order_received_text WooCommerce order received text.
	 * @param object $wc_order WooCommerce order object.
	 * @return string
	 * @since 1.0.0
	 */
	public function wcpp_woocommerce_thankyou_order_received_text_callback( $order_received_text, $wc_order ) {
		// Return, if the order is null.
		if ( is_null( $wc_order ) ) {
			return;
		}

		$wc_order_status         = $wc_order->get_status(); // Get the order status.
		$wc_order_payment_method = $wc_order->get_payment_method(); // Get the payment method.
		$wc_order_payment_link   = $wc_order->get_checkout_payment_url(); // Get the order payment link.

		// If the order is in pending state.
		if ( 'pending' === $wc_order_status && 'phicommerce_payments' === $wc_order_payment_method ) {
			$order_received_text = sprintf( __( 'Thank you. Your order has been received but the payment is not completed. Click %1$shere%2$s to complete the payment.', 'wc-phicommerce-payment-gateway' ), '<a target="_blank" title="" href="' . $wc_order_payment_link . '">', '</a>' );
		}

		return $order_received_text;
	}
}
