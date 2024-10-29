<?php
/**
 * This file is used for writing all the re-usable custom functions.
 *
 * @since 1.0.0
 * @package Wc_Phicommerce_Payment_Gateway
 * @subpackage Wc_Phicommerce_Payment_Gateway/includes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'wcpp_is_localhost' ) ) {
	/**
	 * Check if the user is in localhost.
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	function wcpp_is_localhost() {
		$localhost_ip_addresses = array(
			'127.0.0.1',
			'::1',
		);

		$current_ip = $_SERVER['REMOTE_ADDR'];

		return ( in_array( $current_ip, $localhost_ip_addresses, true ) ) ? true : false;
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'wcpp_get_transaction' ) ) {
	/**
	 * Get the archway transaction details.
	 *
	 * @param int $transaction_id Archway transaction ID.
	 * @return array|boolean
	 * @since 1.0.0
	 */
	function wcpp_get_transaction( $transaction_id ) {
		$gateway_settings = woocommerce_archway_payments_settings();
		$api_url          = ( ! empty( $gateway_settings['get_transaction_api_url'] ) ) ? $gateway_settings['get_transaction_api_url'] : '';
		$api_key          = ( ! empty( $gateway_settings['api_key'] ) ) ? $gateway_settings['api_key'] : '';

		// Return false, if the transaction API URL is not available.
		if ( empty( $api_url ) || empty( $api_key ) ) {
			return false;
		}

		// Fire the API now.
		$response = wp_remote_get(
			str_replace( '$transaction_id', $transaction_id, $api_url ),
			array(
				'headers' => array(
					'X-Api-Key' => $api_key,
				)
			)
		);

		// Get response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $response_code ) {
			$response_body = json_decode( wp_remote_retrieve_body( $response ) );

			return (array) $response_body;
		}

		return false;
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'woocommerce_phicommerce_payment_gateway_settings' ) ) {
	/**
	 * Get the archway payment settings.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	function woocommerce_phicommerce_payment_gateway_settings() {
		$settings                           = get_option( 'woocommerce_phicommerce_payments_settings' );
		$is_sandbox                         = ( ! empty( $settings['is_sandbox'] ) && 'yes' === $settings['is_sandbox'] ) ? true : false; // Is sandbox mode on.
		$process_sale_api_url               = ( ! empty( $settings['process_sale_api_url'] ) ) ? $settings['process_sale_api_url'] : '';
		$process_sandbox_sale_api_url       = ( ! empty( $settings['process_sandbox_sale_api_url'] ) ) ? $settings['process_sandbox_sale_api_url'] : '';
		$merchant_information               = ( ! empty( $settings['merchant_information'] ) ) ? $settings['merchant_information'] : '';
		$sandbox_merchant_information       = ( ! empty( $settings['sandbox_merchant_information'] ) ) ? $settings['sandbox_merchant_information'] : '';
		$transaction_status_api_url         = ( ! empty( $settings['check_transaction_status_url'] ) ) ? $settings['check_transaction_status_url'] : '';
		$sandbox_transaction_status_api_url = ( ! empty( $settings['check_sandbox_transaction_status_url'] ) ) ? $settings['check_sandbox_transaction_status_url'] : '';
		$transaction_refund_api_url         = ( ! empty( $settings['process_transaction_refund_url'] ) ) ? $settings['process_transaction_refund_url'] : '';
		$sandbox_transaction_refund_api_url = ( ! empty( $settings['process_sandbox_transaction_refund_url'] ) ) ? $settings['process_sandbox_transaction_refund_url'] : '';

		// Return the settings array.
		return array(
			'payphi_sale_api_url'         => ( $is_sandbox ) ? $process_sandbox_sale_api_url : $process_sale_api_url,
			'payphi_sale_status_api_url'  => ( $is_sandbox ) ? $sandbox_transaction_status_api_url : $transaction_status_api_url,
			'payphi_sale_refund_api_url'  => ( $is_sandbox ) ? $sandbox_transaction_refund_api_url : $transaction_refund_api_url,
			'payphi_merchant_information' => ( $is_sandbox ) ? $sandbox_merchant_information : $merchant_information,
		);
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'wcpp_write_payment_log' ) ) {
	/**
	 * Write log to the log file.
	 *
	 * @param string $message Holds the log message.
	 * @return void
	 */
	function wcpp_write_payment_log( $message = '' ) {
		global $wp_filesystem;

		if ( empty( $message ) ) {
			return;
		}

		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();

		$local_file = WCPP_LOG_DIR_PATH . 'transactions-log.log';

		// Fetch the old content and add the new content.
		$content  = $wp_filesystem->get_contents( $local_file );
		$content .= "\n" . gmdate( 'Y-m-d h:i:s' ) . ' :: ' . $message;

		$wp_filesystem->put_contents(
			$local_file,
			$content,
			FS_CHMOD_FILE // predefined mode settings for WP files.
		);
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'wcpp_get_secured_hash' ) ) {
	/**
	 * Write log to the log file.
	 *
	 * @param string $message Holds the log message.
	 * @return void
	 */
	function wcpp_get_secured_hash( $hash_fields, $hash_calculation_key ) {
		ksort( $hash_fields ); // Sort the data by keys in alphabetic order.

		$hash_input = '';

		// Iterate through the fields to prepare the hash string.
		foreach( $hash_fields as $key => $value ) {
			if ( 0 < strlen( $value ) ) {
				$hash_input .= $value; 
			}
		}

		/**
		 * Calculate the hmac 256 signature.
		 * Use the secret key corresponding to your merchantid.
		 */
		$signature = hash_hmac( 'sha256', $hash_input, $hash_calculation_key );

		return $signature;
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'wcpp_get_matching_merchant_id' ) ) {
	/**
	 * Write log to the log file.
	 *
	 * @param string $message Holds the log message.
	 * @return void
	 */
	function wcpp_get_matching_merchant_id( $checkout_merchant_id, $admin_configured_merchant_ids, $matching_index = '' ) {
		$matching_merchant = array();

		// If the matching index is available.
		if ( ! empty( $matching_index ) ) {
			$matching_merchant = ( ! empty( $admin_configured_merchant_ids[ $matching_index ] ) ) ? (array) $admin_configured_merchant_ids[ $matching_index ] : array();
		} else {
			$matching_merchant = ( ! empty( $admin_configured_merchant_ids[0] ) ) ? (array) $admin_configured_merchant_ids[0] : array();
		}

		return $matching_merchant;
	}
}