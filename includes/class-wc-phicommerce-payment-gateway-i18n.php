<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Wc_Phicommerce_Payment_Gateway
 * @subpackage Wc_Phicommerce_Payment_Gateway/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wc_Phicommerce_Payment_Gateway
 * @subpackage Wc_Phicommerce_Payment_Gateway/includes
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Wc_Phicommerce_Payment_Gateway_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wc-phicommerce-payment-gateway',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
