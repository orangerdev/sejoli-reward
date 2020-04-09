<?php

namespace Sejoli_Reward\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Sejoli_Reward
 * @subpackage Sejoli_Reward/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sejoli_Reward
 * @subpackage Sejoli_Reward/admin
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Notification {

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
	 * Series of notification files
	 * @since	1.0.0
	 * @var 	array
	 */
	protected $notification_files = array(
		'point-exchange-admin',
		'point-exchange-user',
		'cancel-exchange-admin',
		'cancel-exchange-user'
	);

	/**
	 * Notification libraries
	 * @since	1.0.0
	 * @var 	array
	 */
	protected $libraries = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @param   string    $plugin_name  The name of this plugin.
	 * @param   string    $version      The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('admin_init',	array($this, 'test'));

	}

	public function test() {

		if(isset($_GET['dylan'])) :
			$request_id = intval($_GET['dylan']);
			$response = sejoli_get_single_point_detail($request_id);

			do_action('sejoli/notification/reward/exchange', $response['point']);
			exit;
		endif;
	}

	/**
	 * Modification notification directory
	 *
	 * Hooked via filter sejoli/email/template-directory, 	 priority 12
	 * Hooked via filter sejoli/sms/template-directory, 	 priority 12
	 * Hooked via filter sejoli/whatsapp/template-directory, priority 12
	 *
	 * @since 	1.0.0
	 * @param 	string 	$directory_path
	 * @param 	string 	$filename
	 * @param 	string 	$media
	 * @param 	array 	$vars
	 * @return 	string
	 */
	public function set_notification_directory($directory_path, $filename, $media, $vars) {

		if(in_array($filename, $this->notification_files)) :
			$directory_path = SEJOLI_REWARD_DIR . 'template/' . $media . '/';
		endif;

		return $directory_path;
	}

    /**
     * Add custom notification libraries
     * Hooked via filter sejoli/notification/libraries, priority 12
     * @since   1.0.0
     * @param   $libraries [description]
     */
    public function add_libraries($libraries) {

        require_once( SEJOLI_REWARD_DIR . 'notification/reward-exchange.php');
        require_once( SEJOLI_REWARD_DIR . 'notification/reward-cancel.php');

        $libraries['reward-exchange'] = new \Sejoli_Reward\Notification\RewardExchange;
        $libraries['reward-cancel']   = new \Sejoli_Reward\Notification\RewardCancel;

		$this->libraries = $libraries;

        return $libraries;
    }

	/**
	 * Send reward exchange notification
	 * Hooked via action sejoli/notification/reward/exchange, priority 1
	 * @since 	1.0.0
	 * @param  	array $point_data
	 * @return 	void
	 */
	public function send_reward_exchange_notification($point_data) {

		$point_data                = (array) $point_data;
		$reward                    = get_post($point_data['reward_id']);
		$user                      = sejolisa_get_user($point_data['user_id']);
		$point_data['reward-name'] = $reward->post_title;

		$this->libraries['reward-exchange']->trigger(
			(array) $point_data,
			array(
				'user_name'  => $user->display_name,
				'user_email' => $user->user_email,
				'user_phone' => $user->meta->user_phone
			));

	}
}
