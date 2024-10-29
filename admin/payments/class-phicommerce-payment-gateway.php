<?php
/**
 * The file that defines the phicommerce payment gateway class.
 *
 * A class definition that holds the API calls to handle transactions with Phicommerce.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Core_Functions
 * @subpackage Core_Functions/includes
 */

/**
 * The file that defines the phicommerce payment gateway class.
 *
 * A class definition that holds the API calls to handle transactions with Phicommerce.
 *
 * @since      1.0.0
 * @package    Core_Functions
 * @author     Adarsh Verma <adarsh@cmsminds.com>
 */
class WooCommerce_Phicommerce_Payment_Gateway extends WC_Payment_Gateway {
	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'phicommerce_payments';
		$this->icon               = apply_filters( 'woocommerce_custom_gateway_icon', '' );
		$this->has_fields         = false;
		$this->method_title       = __( 'Phicommerce', 'wc-phicommerce-payment-gateway' );
		$this->method_description = __( 'Phicommerce works by adding payment fields on checkout and then sending details to Phicommerce.', 'wc-phicommerce-payment-gateway' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions', $this->description );
		$this->api_url      = $this->get_option( 'api_url' );
		$this->api_key      = $this->get_option( 'api_key' );

		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Customer Emails
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'                                => array(
				'title'   => __( 'Enable/Disable', 'wc-phicommerce-payment-gateway' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Custom Payment', 'wc-phicommerce-payment-gateway' ),
				'default' => 'yes'
			),
			'title'                                  => array(
				'title'       => __( 'Title', 'wc-phicommerce-payment-gateway' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wc-phicommerce-payment-gateway' ),
				'default'     => __( 'Phicommerce Payments', 'wc-phicommerce-payment-gateway' ),
				'desc_tip'    => true,
			),
			'description'                            => array(
				'title'       => __( 'Description', 'wc-phicommerce-payment-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'wc-phicommerce-payment-gateway' ),
				'default'     => __('Payment Information', 'wc-phicommerce-payment-gateway'),
				'desc_tip'    => true,
			),
			'is_sandbox'                             => array(
				'title' => __( 'Is Sandbox?', 'wc-phicommerce-payment-gateway' ),
				'desc'  => __( 'If you\'re testing your payments, keep this checked.', 'wc-phicommerce-payment-gateway' ),
				'id'    => 'payphi_is_sandbox',
				'type'  => 'checkbox',
			),
			'process_sale_api_url'                   => array(
				'title'       => __( 'Production: Sale API URL', 'wc-phicommerce-payment-gateway' ),
				'type'        => 'url',
				'description' => __( 'This API URL helps in processing a sale. Put the production mode URL here.', 'wc-phicommerce-payment-gateway' ),
				'placeholder' => 'https://secure-ptg.payphi.com/...',
				'desc_tip'    => true,
			),
			'process_sandbox_sale_api_url'           => array(
				'title'       => __( 'Sandbox: Sale API URL', 'wc-phicommerce-payment-gateway' ),
				'type'        => 'url',
				'description' => __( 'This API URL helps in processing a sale. Put the sandbox mode URL here.', 'wc-phicommerce-payment-gateway' ),
				'placeholder' => 'https://qa.phicommerce.com/...',
				'desc_tip'    => true,
			),
			'check_transaction_status_url'           => array(
				'title'       => __( 'Production: Check Transaction Status API URL', 'wc-phicommerce-payment-gateway' ),
				'type'        => 'url',
				'description' => __( 'This API URL helps in checking the transaction status. Put the production mode URL here.', 'wc-phicommerce-payment-gateway' ),
				'placeholder' => 'https://secure-ptg.payphi.com/...',
				'desc_tip'    => true,
			),
			'check_sandbox_transaction_status_url'   => array(
				'title'       => __( 'Sandbox: Check Transaction Status API URL', 'wc-phicommerce-payment-gateway' ),
				'type'        => 'url',
				'description' => __( 'This API URL helps in checking the transaction status. Put the sandbox mode URL here.', 'wc-phicommerce-payment-gateway' ),
				'placeholder' => 'https://qa.phicommerce.com/...',
				'desc_tip'    => true,
			),
			'process_transaction_refund_url'         => array(
				'title'       => __( 'Production: Transaction Refund API URL', 'wc-phicommerce-payment-gateway' ),
				'type'        => 'url',
				'description' => __( 'This API URL helps in processing refund for a transaction. Put the production mode URL here.', 'wc-phicommerce-payment-gateway' ),
				'placeholder' => 'https://secure-ptg.payphi.com/...',
				'desc_tip'    => true,
			),
			'process_sandbox_transaction_refund_url' => array(
				'title'       => __( 'Sandbox: Transaction Refund API URL', 'wc-phicommerce-payment-gateway' ),
				'type'        => 'url',
				'description' => __( 'This API URL helps in processing refund for a transaction. Put the sandbox mode URL here.', 'wc-phicommerce-payment-gateway' ),
				'placeholder' => 'https://qa.phicommerce.com/...',
				'desc_tip'    => true,
			),
			'merchant_information'                   => array(
				'title'       => __( 'Production: Merchant Information (key-value pair)', 'wc-phicommerce-payment-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'Key value pair of the marchant IDs and hash calculation keys. Sample: [{"merchId":"xyz","hashKey":"abc"},{"merchId":"xyz1","hashKey":"abc1"},{"merchId":"xyz2","hashKey":"abc2"}]', 'wc-phicommerce-payment-gateway' ),
				'placeholder' => 'E.g.: [{"merchId":"xyz","hashKey":"abc"},{"merchId":"xyz1","hashKey":"abc1"},{"merchId":"xyz2","hashKey":"abc2"}]',
				'desc_tip'    => true,
			),
			'sandbox_merchant_information'           => array(
				'title'       => __( 'Sandbox: Merchant Information (key-value pair)', 'wc-phicommerce-payment-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'Key value pair of the marchant IDs and hash calculation keys. Sample: [{"merchId":"xyz","hashKey":"abc"},{"merchId":"xyz1","hashKey":"abc1"},{"merchId":"xyz2","hashKey":"abc2"}]', 'wc-phicommerce-payment-gateway' ),
				'placeholder' => 'E.g.: [{"merchId":"xyz","hashKey":"abc"},{"merchId":"xyz1","hashKey":"abc1"},{"merchId":"xyz2","hashKey":"abc2"}]',
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && 'phicommerce_payments' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
			echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
		}
	}

	/*
	* Function to display Front end Credit card form.
	*/
	public function payment_fields() {
		// Print the payment gateway description, if we have.
		echo ( ! empty( $this->get_description() ) ) ? wpautop( wptexturize( $this->get_description() ) ) : '';
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$order->reduce_order_stock(); // Reduce stock levels.
		WC()->cart->empty_cart(); // Remove cart.

		// Return thankyou redirect
		return array(
			'result'    => 'success',
			'redirect'  => $this->get_return_url( $order )
		);
	}
}
