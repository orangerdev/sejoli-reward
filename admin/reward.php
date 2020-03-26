<?php

namespace Sejoli_Reward\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

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
class Reward {

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
     * Register reward post type
     * Hooked via action init, priority 1222
     * @return void
     */
    public function register_post_type() {

		if(false === sejolisa_check_own_license()) :
			return;
		endif;

		$labels = [
    		'name'               => _x( 'Rewards', 'post type general name', 'sejoli' ),
    		'singular_name'      => _x( 'Reward', 'post type singular name', 'sejoli' ),
    		'menu_name'          => _x( 'Rewards', 'admin menu', 'sejoli' ),
    		'name_admin_bar'     => _x( 'Reward', 'add new on admin bar', 'sejoli' ),
    		'add_new'            => _x( 'Add New', 'reward', 'sejoli' ),
    		'add_new_item'       => __( 'Add New Reward', 'sejoli' ),
    		'new_item'           => __( 'New Reward', 'sejoli' ),
    		'edit_item'          => __( 'Edit Reward', 'sejoli' ),
    		'view_item'          => __( 'View Reward', 'sejoli' ),
    		'all_items'          => __( 'All Rewards', 'sejoli' ),
    		'search_items'       => __( 'Search Rewards', 'sejoli' ),
    		'parent_item_colon'  => __( 'Parent Rewards:', 'sejoli' ),
    		'not_found'          => __( 'No rewards found.', 'sejoli' ),
    		'not_found_in_trash' => __( 'No rewards found in Trash.', 'sejoli' )
    	];

    	$args = [
    		'labels'             => $labels,
            'description'        => __( 'Description.', 'sejoli' ),
    		'public'             => true,
    		'publicly_queryable' => false,
    		'show_ui'            => true,
    		'show_in_menu'       => true,
    		'query_var'          => true,
    		'rewrite'            => [ 'slug' => 'reward' ],
    		'capability_type'    => 'sejoli_product',
			'capabilities'		 => array(
				'publish_posts'      => 'publish_sejoli_products',
				'edit_posts'         => 'edit_sejoli_products',
				'edit_others_posts'  => 'edit_others_sejoli_products',
				'read_private_posts' => 'read_private_sejoli_products',
				'edit_post'          => 'edit_sejoli_product',
				'delete_posts'       => 'delete_sejoli_product',
				'read_post'          => 'read_sejoli_product'
			),
    		'has_archive'        => true,
    		'hierarchical'       => false,
    		'menu_position'      => null,
    		'supports'           => [ 'title', 'editor', 'thumbnail' ],
			'menu_icon'			 => SEJOLISA_URL . 'admin/images/icon.png'
    	];

    	register_post_type( SEJOLI_REWARD_CPT, $args );
    }

    /**
     * Setup reward post meta fields
     * Hooked via carbon_fields_register_fields, priority 1222
     * @since   1.0.0
     * @return  void
     */
    public function setup_reward_fields() {

        Container::make( 'post_meta', __('Setup Reward', 'sejoli-reward'))
            ->where('post_type', '=', 'sejoli-reward')
			->add_tab(
				__('Pengaturan', 'sejoli-reward'),
				array(
	                Field::make('text', 'reward_point', __('Poin penukaran', 'sejoli-reward'))
	                    ->set_attribute('type', 'number')
	                    ->set_default_value(1)
	                    ->set_help_text( __('Nilai minimun poin penukaran reward', 'ttsb'))
	                    ->set_required(true)
            	)
			);
    }

	/**
	 * Modify reward post columns
	 * Hooked via action manage_posts_columns, priority 1222
	 * @since 	1.0.0
	 * @param 	array 	$post_columns
	 * @param 	array 	$post_type
	 * @return 	array
	 */
	public function modify_post_columns($post_columns, $post_type) {

		if( 'sejoli-reward' === $post_type ) :

			unset($post_columns['date']);

			$post_columns['sejoli-reward-point']	= __('Poin Penukaran', 'sejoli-reward');

		endif;

		return $post_columns;
	}

	/**
	 * Display reward data in reward-data table column
	 * Hooked via manage_posts_custom_column, priority 1222
	 * @since 	1.0.0
	 * @param  	string 	$column_name
	 * @param  	integer $post_id
	 * @return 	void
	 */
	public function display_data_in_post_columns($column_name, $post_id) {

		switch($column_name) :

			case 'sejoli-reward-point' :
			
				echo carbon_get_post_meta($post_id, 'reward_point');
				break;

		endswitch;

	}

}
