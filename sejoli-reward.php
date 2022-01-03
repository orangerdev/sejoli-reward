<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ridwan-arifandi.com
 * @since             1.0.0
 * @package           Sejoli_Reward
 *
 * @wordpress-plugin
 * Plugin Name:       Sejoli - Reward
 * Plugin URI:        https://sejoli.co.id
 * Description:       Implement reward system into SEJOLI premium membership WordPress plugin
 * Version:           1.2.0
 * Author:            Ridwan Arifandi
 * Author URI:        https://ridwan-arifandi.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sejoli-reward
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $sejoli_reward;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SEJOLI_REWARD_VERSION'	, '1.2.0' );
define( 'SEJOLI_REWARD_CPT'		, 'sejoli-reward');
define( 'SEJOLI_REWARD_DIR' 	, plugin_dir_path( __FILE__ ) );
define( 'SEJOLI_REWARD_URL' 	, plugin_dir_url( __FILE__ ) );

require SEJOLI_REWARD_DIR . '/third-parties/autoload.php';

add_action('muplugins_loaded', 'sejoli_reward_check_sejoli');

function sejoli_reward_check_sejoli() {

	if(!defined('SEJOLISA_VERSION')) :

		add_action('admin_notices', 'sejolp_no_sejoli_functions');

		function sejolp_no_sejoli_functions() {
			?><div class='notice notice-error'>
			<p><?php _e('Anda belum menginstall atau mengaktifkan SEJOLI terlebih dahulu.', 'sejoli-reward'); ?></p>
			</div><?php
		}

		return;
	endif;

}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sejoli-reward-activator.php
 */
function activate_sejoli_reward() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-reward-activator.php';
	Sejoli_Reward_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sejoli-reward-deactivator.php
 */
function deactivate_sejoli_reward() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-reward-deactivator.php';
	Sejoli_Reward_Deactivator::deactivate();
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-reward.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sejoli_reward() {

	$plugin = new Sejoli_Reward();
	$plugin->run();

}

require_once(SEJOLI_REWARD_DIR . 'third-parties/yahnis-elsts/plugin-update-checker/plugin-update-checker.php');

$update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/orangerdev/sejoli-reward',
	__FILE__,
	'sejoli-reward'
);

$update_checker->setBranch('master');

add_action('sejoli/init', 	'run_sejoli_reward');

register_activation_hook( __FILE__, 'activate_sejoli_reward' );
register_deactivation_hook( __FILE__, 'deactivate_sejoli_reward' );
