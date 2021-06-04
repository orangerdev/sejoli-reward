<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Sejoli_Reward
 * @subpackage Sejoli_Reward/includes
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
 * @package    Sejoli_Reward
 * @subpackage Sejoli_Reward/includes
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Sejoli_Reward {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sejoli_Reward_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'SEJOLI_REWARD_VERSION' ) ) {
			$this->version = SEJOLI_REWARD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'sejoli-reward';

		$this->load_dependencies();
		$this->set_locale();
		$this->register_cli();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sejoli_Reward_Loader. Orchestrates the hooks of the plugin.
	 * - Sejoli_Reward_i18n. Defines internationalization functionality.
	 * - Sejoli_Reward_Admin. Defines all hooks for the admin area.
	 * - Sejoli_Reward_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sejoli-reward-loader.php';

		/**
		 * The class responsible for modelling data.
		 */
		if(!class_exists('\SejoliSA\Model')) :
			require_once SEJOLISA_DIR . 'models/main.php';
		endif;

		require_once SEJOLI_REWARD_DIR . 'models/reward.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SEJOLI_REWARD_DIR . 'includes/class-sejoli-reward-i18n.php';

		/**
		 * The files responsible for defining all functions that will work as helper
		 */
		require_once SEJOLI_REWARD_DIR . 'functions/reward.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once SEJOLI_REWARD_DIR . 'admin/admin.php';
		require_once SEJOLI_REWARD_DIR . 'admin/json.php';
		require_once SEJOLI_REWARD_DIR . 'admin/notification.php';
		require_once SEJOLI_REWARD_DIR . 'admin/order.php';
		require_once SEJOLI_REWARD_DIR . 'admin/reward.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once SEJOLI_REWARD_DIR . 'public/checkout.php';
		require_once SEJOLI_REWARD_DIR . 'public/member.php';
		require_once SEJOLI_REWARD_DIR . 'public/public.php';

		/**
		 * The class responsible for defining CLI command
		 */
		require_once SEJOLI_REWARD_DIR . 'cli/reward.php';

		$this->loader = new Sejoli_Reward_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sejoli_Reward_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Sejoli_Reward_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register CLI command
	 * @since 	1.0.0
	 * @return 	void
	 */
	private function register_cli() {

		if ( !class_exists( 'WP_CLI' ) ) :
			return;
		endif;

		$reward 	= new Sejoli_Reward\CLI\Reward();

		WP_CLI::add_command('sejolisa reward', $reward);
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$admin = new Sejoli_Reward\Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli/database/setup',			$admin, 'register_database', 1);
		$this->loader->add_action( 'admin_bar_menu',				$admin, 'add_point_link',	 12222);
		$this->loader->add_action( 'admin_enqueue_scripts',			$admin, 'enqueue_css_js_scripts', 1099);

		$json  = new Sejoli_Reward\Admin\Json( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_ajax_sejoli-user-point-table',			$json, 'ajax_set_for_table', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-reward-table',				$json, 'ajax_set_reward_for_table', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-single-user-point-table',	$json, 'ajax_set_single_user_for_table', 1);
		$this->loader->add_action( 'sejoli_ajax_single-user-point-table',		$json, 'ajax_set_single_user_for_table', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-available-reward-table',		$json, 'ajax_get_available_reward_for_table', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-reward-exchange',			$json, 'ajax_set_reward_exchange', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-reward-options',				$json, 'ajax_get_reward_options', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-update-reward-point-status', $json, 'ajax_update_reward_point_status', 1);

		$notification  = new Sejoli_Reward\Admin\Notification( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/email/template-directory',		$notification, 'set_notification_directory', 12, 4);
		$this->loader->add_filter( 'sejoli/sms/template-directory',			$notification, 'set_notification_directory', 12, 4);
		$this->loader->add_filter( 'sejoli/whatsapp/template-directory',	$notification, 'set_notification_directory', 12, 4);

		$this->loader->add_filter( 'sejoli/notification/libraries',			$notification, 'add_libraries', 12);
		$this->loader->add_action( 'sejoli/notification/reward/exchange',	$notification, 'send_reward_exchange_notification', 12);
		$this->loader->add_action( 'sejoli/notification/reward/cancel',		$notification, 'send_reward_cancel_notification', 12);

		$order  = new Sejoli_Reward\Admin\Order( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli/order/new',							$order, 'add_reward_point_for_buyer', 		8);
		$this->loader->add_action( 'sejoli/order/commission', 					$order, 'add_reward_point_for_affiliate',	122, 5);
		$this->loader->add_action( 'sejoli/order/set-status/on-hold',			$order, 'update_point_status_to_not_valid', 122);
		$this->loader->add_action( 'sejoli/order/set-status/in-progress',		$order, 'update_point_status_to_not_valid', 122);
		$this->loader->add_action( 'sejoli/order/set-status/shipped',			$order, 'update_point_status_to_not_valid', 122);
		$this->loader->add_action( 'sejoli/order/set-status/refunded',			$order, 'update_point_status_to_not_valid', 122);
		$this->loader->add_action( 'sejoli/order/set-status/cancelled',			$order, 'update_point_status_to_not_valid', 122);
		$this->loader->add_action( 'sejoli/order/set-status/completed',			$order, 'update_point_status_to_valid', 	122);
		$this->loader->add_action( 'sejoli/notification/content/order-detail',	$order, 'add_point_info',					122, 4);

		$reward  = new Sejoli_Reward\Admin\Reward( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',									$reward, 'register_post_type', 		1222);
		$this->loader->add_action( 'carbon_fields_register_fields',			$reward, 'setup_reward_fields', 	1222);
		$this->loader->add_action( 'wp_ajax_add-input-point-data',			$reward, 'add_manual_input_data',	1222);
		$this->loader->add_action( 'admin_notices',							$reward, 'display_notice',			1222);
		$this->loader->add_filter( 'manage_posts_columns',					$reward, 'modify_post_columns',		1222, 2);
		$this->loader->add_action( 'manage_posts_custom_column',			$reward, 'display_data_in_post_columns', 1222, 2);
		$this->loader->add_filter( 'sejoli/product/meta-data',				$reward, 'set_product_point',		122);
		$this->loader->add_action( 'admin_menu',							$reward, 'add_custom_point_menu', 	122);
		$this->loader->add_action( 'admin_footer',							$reward, 'admin_footer',			122);
		$this->loader->add_filter( 'sejoli/admin/js-localize-data', 		$reward, 'set_localize_js_vars',	12);
		$this->loader->add_filter( 'sejoli/admin/is-sejoli-page',			$reward, 'is_current_page_sejoli_page', 1222);

		$this->loader->add_filter( 'sejoli/notification/fields',    		$reward, 'set_notification_fields', 120);
		$this->loader->add_filter( 'sejoli/product/fields',					$reward, 'set_product_fields',		12);
		$this->loader->add_filter( 'sejoli/user-group/fields',				$reward, 'set_user_group_fields', 	12);
		$this->loader->add_filter( 'sejoli/user-group/per-product/fields',	$reward, 'set_user_group_per_product_fields', 12, 2);
		$this->loader->add_filter( 'sejoli/product/commission/fields',		$reward, 'set_commission_fields', 	12);
		$this->loader->add_filter( 'sejoli/user-group/detail',				$reward, 'set_user_group_detail', 	12, 4);
		$this->loader->add_filter( 'sejoli/user-group/per-product/detail',	$reward, 'set_user_group_per_product_detail', 12, 2);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$checkout = new Sejoli_Reward\Front\Checkout( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli/checkout-template/after-product', 	$checkout, 'display_point', 12);

		$public = new Sejoli_Reward\Front( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts',	$public, 'enqueue_styles', 1222);

		$member = new Sejoli_Reward\Front\Member( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/member-area/menu',			$member, 'register_menu', 12);
		$this->loader->add_filter( 'sejoli/member-area/backend/menu',	$member, 'add_menu_in_backend', 1222);
		$this->loader->add_filter( 'sejoli/member-area/menu-link',		$member, 'display_link_list_in_menu', 12, 4);
		$this->loader->add_filter( 'sejoli/template-file',				$member, 'set_template_file', 122, 2);

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
	 * @return    Sejoli_Reward_Loader    Orchestrates the hooks of the plugin.
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
