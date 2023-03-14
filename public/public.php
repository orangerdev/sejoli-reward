<?php

namespace Sejoli_Reward;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Sejoli_Reward
 * @subpackage Sejoli_Reward/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Sejoli_Reward
 * @subpackage Sejoli_Reward/public
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Front {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 * Hooked via action wp_enqueue_scripts, priority 1222
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		
		if('reward-exchange' === sejolisa_get_current_member_page()) :
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sejoli-reward-public.css', array(), $this->version, 'all' );
		endif;

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_register_script( 'sejoli-reward-exchange', plugin_dir_url( __FILE__ ) . 'js/sejoli-reward-public.js', 'jquery', $this->version, false );

		wp_enqueue_script( 'sejoli-reward-exchange' );

		wp_localize_script( 'sejoli-reward-exchange', 'sejoli_reward_exchange', array(
			'poin_reward_label'   => __(' Poin', 'sejoli-reward')
		) );

	}

}
