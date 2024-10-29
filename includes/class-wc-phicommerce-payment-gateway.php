<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Wc_Phicommerce_Payment_Gateway
 * @subpackage Wc_Phicommerce_Payment_Gateway/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wc_Phicommerce_Payment_Gateway
 * @subpackage Wc_Phicommerce_Payment_Gateway/includes
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Wc_Phicommerce_Payment_Gateway {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wc_Phicommerce_Payment_Gateway_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->version     = ( defined( 'WCPP_PLUGIN_VERSION' ) ) ? WCPP_PLUGIN_VERSION : '1.0.0';
		$this->plugin_name = 'wc-phicommerce-payment-gateway';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_payment_gateway();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wc_Phicommerce_Payment_Gateway_Loader. Orchestrates the hooks of the plugin.
	 * - Wc_Phicommerce_Payment_Gateway_i18n. Defines internationalization functionality.
	 * - Wc_Phicommerce_Payment_Gateway_Admin. Defines all hooks for the admin area.
	 * - Wc_Phicommerce_Payment_Gateway_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// The class responsible for orchestrating the actions and filters of the core plugin.
		require_once WCPP_PLUGIN_PATH . 'includes/class-wc-phicommerce-payment-gateway-loader.php';

		// The class responsible for defining internationalization functionality of the plugin.
		require_once WCPP_PLUGIN_PATH . 'includes/class-wc-phicommerce-payment-gateway-i18n.php';

		// The class responsible for defining custom payment gateway.
		require_once WCPP_PLUGIN_PATH . 'admin/payments/class-phicommerce-payment-gateway.php';

		// The file responsible for defining all custom reusable functions.
		require_once WCPP_PLUGIN_PATH . 'includes/phicommerce-payment-gateway-functions.php';

		// The class responsible for defining all actions that occur in the admin area.
		require_once WCPP_PLUGIN_PATH . 'admin/class-wc-phicommerce-payment-gateway-admin.php';

		// The class responsible for defining all actions that occur in the public-facing side of the site.
		require_once WCPP_PLUGIN_PATH . 'public/class-wc-phicommerce-payment-gateway-public.php';

		$this->loader = new Wc_Phicommerce_Payment_Gateway_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wc_Phicommerce_Payment_Gateway_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Wc_Phicommerce_Payment_Gateway_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_payment_gateway() {
		new WooCommerce_Phicommerce_Payment_Gateway();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Wc_Phicommerce_Payment_Gateway_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'wcpp_admin_enqueue_scripts_callback' );
		$this->loader->add_filter( 'woocommerce_payment_gateways', $plugin_admin, 'wcpp_woocommerce_payment_gateways_callback' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'wcpp_add_meta_boxes_callback' );
		$this->loader->add_action( 'wp_ajax_get_transaction_status', $plugin_admin, 'wcpp_get_transaction_status_callback' );
		$this->loader->add_filter( 'woocommerce_admin_order_actions', $plugin_admin, 'wcpp_woocommerce_admin_order_actions_callback', 10, 2 );
		$this->loader->add_action( 'woocommerce_order_item_add_action_buttons', $plugin_admin, 'wcpp_woocommerce_order_item_add_action_buttons_callback' );
		$this->loader->add_filter( 'woocommerce_admin_order_should_render_refunds', $plugin_admin, 'wcpp_woocommerce_admin_order_should_render_refunds_callback', 99, 3 );
		$this->loader->add_action( 'wp_ajax_process_refund', $plugin_admin, 'wcpp_process_refund_callback' );
		$this->loader->add_filter( 'manage_edit-shop_order_columns', $plugin_admin, 'wcpp_manage_shop_order_posts_columns_callback', 20 );
		$this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_admin, 'wcpp_manage_shop_order_posts_custom_column', 20, 2 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wc_Phicommerce_Payment_Gateway_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'wcpp_wp_enqueue_scripts_callback' );
		$this->loader->add_action( 'woocommerce_after_checkout_validation', $plugin_public, 'wcpp_woocommerce_after_checkout_validation_callback', 20, 2 );
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'wcpp_woocommerce_checkout_update_order_meta_callback' );
		$this->loader->add_action( 'woocommerce_checkout_order_processed', $plugin_public, 'cf_woocommerce_checkout_order_processed_callback', 20, 2 );
		$this->loader->add_action( 'wp_head', $plugin_public, 'wcpp_wp_head_callback' );
		$this->loader->add_filter( 'woocommerce_custom_gateway_icon', $plugin_public, 'wcpp_woocommerce_custom_gateway_icon_callback' );
		$this->loader->add_filter( 'woocommerce_thankyou_order_received_text', $plugin_public, 'wcpp_woocommerce_thankyou_order_received_text_callback', 99, 2 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wc_Phicommerce_Payment_Gateway_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
