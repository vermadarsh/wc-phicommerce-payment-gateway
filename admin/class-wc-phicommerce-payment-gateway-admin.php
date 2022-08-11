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
		$this->version = $version;

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

		$transaction_ctx          = get_post_meta( $post_id, 'phicommerce-payment-transaction-ctx', true );
		$merchant_transaction_no  = get_post_meta( $post_id, 'phicommerce-payment-merchant-transaction-no', true );
		$transaction_payload      = get_post_meta( $post_id, 'phicommerce-payment-transaction-payload', true );
		$transaction_api_response = get_post_meta( $post_id, 'phicommerce-payment-transaction-api-response', true );
		$final_api_response_code  = get_post_meta( $post_id, 'phicommerce-payment-transaction-api-final-response-code', true );
		$final_api_response_desc  = get_post_meta( $post_id, 'phicommerce-payment-transaction-api-final-response-desc', true );

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
		</div>
		<?php
		echo ob_get_clean();
	}
}
