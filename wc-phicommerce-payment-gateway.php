<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/vermadarsh/
 * @since             1.0.0
 * @package           Wc_Phicommerce_Payment_Gateway
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce PhiCommerce Payment Gateway
 * Plugin URI:        https://github.com/vermadarsh/wc-phicommerce-payment-gateway
 * Description:       This plugin adds Phicommerce Payment Gateway to your WooCommerce store.
 * Version:           1.0.0
 * Author:            Adarsh Verma
 * Author URI:        https://github.com/vermadarsh/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-phicommerce-payment-gateway
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WCPP_PLUGIN_VERSION', '1.0.0' );

// Define the constants.
$uploads_dir = wp_upload_dir();
$cons        = array(
	'WCPP_PLUGIN_PATH'  => plugin_dir_path( __FILE__ ),
	'WCPP_PLUGIN_URL'   => plugin_dir_url( __FILE__ ),
	'WCPP_LOG_DIR_URL'  => $uploads_dir['baseurl'] . '/wc-logs/',
	'WCPP_LOG_DIR_PATH' => $uploads_dir['basedir'] . '/wc-logs/',
);
foreach ( $cons as $con => $value ) {
	define( $con, $value );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-phicommerce-payment-gateway-activator.php
 */
function activate_wc_phicommerce_payment_gateway() {
	require_once WCPP_PLUGIN_PATH . 'includes/class-wc-phicommerce-payment-gateway-activator.php';
	Wc_Phicommerce_Payment_Gateway_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-phicommerce-payment-gateway-deactivator.php
 */
function deactivate_wc_phicommerce_payment_gateway() {
	require_once WCPP_PLUGIN_PATH . 'includes/class-wc-phicommerce-payment-gateway-deactivator.php';
	Wc_Phicommerce_Payment_Gateway_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wc_phicommerce_payment_gateway' );
register_deactivation_hook( __FILE__, 'deactivate_wc_phicommerce_payment_gateway' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wc_phicommerce_payment_gateway() {
	// The core plugin class that is used to define internationalization, admin-specific hooks, and public-facing site hooks.
	require WCPP_PLUGIN_PATH . 'includes/class-wc-phicommerce-payment-gateway.php';
	$plugin = new Wc_Phicommerce_Payment_Gateway();
	$plugin->run();
}

/**
 * This initiates the plugin.
 * Checks for the required plugins to be installed and active.
 */
function wcpp_plugins_loaded_callback() {
	$active_plugins = get_option( 'active_plugins' );
	$is_wc_active   = in_array( 'woocommerce/woocommerce.php', $active_plugins, true );

	if ( false === $is_wc_active ) {
		add_action( 'admin_notices', 'wcpp_admin_notices_callback' );
	} else {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wcpp_plugin_actions_callback' );
		run_wc_phicommerce_payment_gateway();
	}
}

add_action( 'plugins_loaded', 'wcpp_plugins_loaded_callback' );

/**
 * This function is called to show admin notices for any required plugin not active || installed.
 */
function wcpp_admin_notices_callback() {
	$this_plugin_data = get_plugin_data( __FILE__ );
	$this_plugin      = $this_plugin_data['Name'];
	$wc_plugin        = __( 'WooCommerce', 'wc-phicommerce-payment-gateway' );
	?>
	<div class="error">
		<p>
			<?php
			/* translators: 1: %s: strong tag open, 2: %s: strong tag close, 3: %s: this plugin, 4: %s: woocommerce plugin, 5: anchor tag for woocommerce plugin, 6: anchor tag close */
			echo wp_kses_post( sprintf( __( '%1$s%3$s%2$s is ineffective as it requires %1$s%4$s%2$s to be installed and active. Click %5$shere%6$s to install or activate it.', 'wc-phicommerce-payment-gateway' ), '<strong>', '</strong>', esc_html( $this_plugin ), esc_html( $wc_plugin ), '<a target="_blank" href="' . admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) . '">', '</a>' ) );
			?>
		</p>
	</div>
	<?php
}

/**
 * This function adds custom plugin actions.
 *
 * @param array $links Links array.
 * @return array
 */
function wcpp_plugin_actions_callback( $links ) {
	$this_plugin_links = array(
		'<a title="' . __( 'Settings', 'wc-phicommerce-payment-gateway' ) . '" href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=phicommerce_payments' ) ) . '">' . __( 'Settings', 'wc-phicommerce-payment-gateway' ) . '</a>',
	);

	return array_merge( $this_plugin_links, $links );
}

/**
 * Debugger function which shall be removed in production.
 */
if ( ! function_exists( 'debug' ) ) {
	/**
	 * Debug function definition.
	 *
	 * @param string $params Holds the variable name.
	 */
	function debug( $params ) {
		echo '<pre>';
		print_r( $params );
		echo '</pre>';
	}
}
