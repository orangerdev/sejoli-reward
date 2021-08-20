<?php

namespace Sejoli_Reward;

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
class Admin {

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

	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sejoli-reward-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sejoli-reward-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add point menu in admin bar
	 * Hooked via action admin_bar_menu, priority 12
	 * @since 	1.0.0
	 * @param 	stdClass 	$admin_bar
	 */
	public function add_point_link($admin_bar) {

		if(is_admin()) :
			return;
		endif;

		$point = 0;
		$point_response = sejoli_reward_get_user_point();

		if(false !== $point_response['valid']) :
			$point = absint($point_response['point']->available_point);
		endif;


		$admin_bar->add_menu(array(
			'id'	=> 'sejoli-point',
			'title'	=> sprintf(__('Poin anda : %s', 'sejoli'), $point),
			'href'	=> site_url('member-area/your-point')
		));

		$admin_bar->add_menu(array(
			'id'		=> 'sejoli-point-history',
			'parent'	=> 'sejoli-point',
			'title'		=> __('Transaksi Poin', 'sejoli'),
			'href'		=> site_url('member-area/your-point')
		));

		$admin_bar->add_menu(array(
			'id'		=> 'sejoli-point-reward',
			'parent'	=> 'sejoli-point',
			'title'		=> __('Tukar Poin', 'sejoli'),
			'href'		=> site_url('member-area/reward-exchange')
		));
	}

	/**
	 * Enqueue needed css and js files
	 * Hooked via action admin_enqueue_scripts, priority 1099
	 * @since 	1.1.0
	 * @return 	void
	 */
	public function enqueue_css_js_scripts() {

		global $pagenow;

		if(
			'admin.php' === $pagenow &&
			isset($_GET['page']) &&
			'sejoli-point-input-form' === $_GET['page']
		) :

			wp_enqueue_style 	( 'select2' );
			wp_enqueue_script 	( 'select2' );

		endif;
	}

	/**
	 * add_date_picker
	 * enque jquery ui date picker
	 * Hooked via action 
	 *
	 * @return void
	 */

	public function add_datepicker_css_js_scripts(){
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'jquery-ui' );
	}

}
