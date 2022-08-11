<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Wc_Phicommerce_Payment_Gateway
 * @subpackage Wc_Phicommerce_Payment_Gateway/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wc_Phicommerce_Payment_Gateway
 * @subpackage Wc_Phicommerce_Payment_Gateway/includes
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Wc_Phicommerce_Payment_Gateway_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Create a log directory within the WordPress uploads directory.
		$_upload     = wp_upload_dir();
		$_upload_dir = $_upload['basedir'];
		$_upload_dir = "{$_upload_dir}/wc-logs/";

		if ( ! file_exists( $_upload_dir ) ) {
			mkdir( $_upload_dir, 0755, true );
		}
	}
}
