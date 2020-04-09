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
	 * Add new submenu under Reward menu
	 * Hooked via action admin_menu, priority 122
	 * @since 	1.0.0
	 * @return	void
	 */
	public function add_custom_point_menu() {

		add_submenu_page(
			'edit.php?post_type=' .  SEJOLI_REWARD_CPT,
			__('Sejoli - Poin User', 'sejoli'),
			__('Poin User', 'sejoli'),
			'manage_sejoli_sejoli',
			'sejoli-reward-point',
			array($this, 'display_user_point')
		);

		add_submenu_page(
			'edit.php?post_type=' .  SEJOLI_REWARD_CPT,
			__('Sejoli - Tukar Poin', 'sejoli'),
			__('Tukar Poin', 'sejoli'),
			'manage_sejoli_sejoli',
			'sejoli-reward-exchange',
			array($this, 'display_reward_exchange')
		);

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

	/**
	 * Add reward point setup in product fields
	 * Hooked via filter sejoli/product/fields, priority 8
	 * @since 	1.0.0
	 * @param 	array 	$fields
	 * @return 	array
	 */
	public function set_product_fields($fields) {

		$fields[]	= array(
			'title'		=> __('Poin', 'sejoli-reward'),
			'fields'	=> array(
				Field::make( 'separator', 'sep_reward' , __('Pengaturan Poin', 'sejoli-reward'))
					->set_classes('sejoli-with-help'),

				Field::make('text', 'reward_point', __('Poin yang didapatkan dari pembelian produk ini', 'sejoli-reward'))
					->set_attribute('type', 'number')
					->set_attribute('min', 0)
					->set_default_value(0)
			)
		);

		return $fields;
	}

	/**
	 * Add reward point setting  in user group fields
	 * Hooked via filter sejoli/user-group/fields, priority 12
	 * @since 	1.0.0
	 * @param 	array $fields
	 * @return  array
	 */
	public function set_user_group_fields($fields) {

		$extra_fields = array(

			Field::make('checkbox', 'group_reward_enable', __('Aktikan poin reward', 'sejoli-reward')),

			Field::make('text', 	'group_reward_point',  __('Poin reward', 'sejoli-reward'))
				->set_attribute('type', 'number')
				->set_attribute('min', 0)
				->set_default_value(0)
				->set_conditional_logic(array(
					array(
						'field'	=> 'group_reward_enable',
						'value'	=> true
					)
				))
		);

		array_splice($fields, 2, 0, $extra_fields);

		return $fields;
	}

	/**
	 * Add reward point setting in each product setup in user group fields
	 * Hooked via filter sejoli/user-group/per-product/fields, priority 12
	 * @since 	1.0.0
	 * @param 	array $fields
	 * @return 	array
	 */
	public function set_user_group_per_product_fields($fields) {

		$extra_fields = array(
			Field::make('checkbox', 'reward_enable', __('Aktikan poin reward', 'sejoli-reward')),

			Field::make('text', 	'reward_point',  __('Poin reward', 'sejoli-reward'))
				->set_attribute('type', 'number')
				->set_attribute('min', 0)
				->set_default_value(0)
				->set_conditional_logic(array(
					array(
						'field'	=> 'reward_enable',
						'value'	=> true
					)
				))
		);

		array_splice($fields, 2, 0, $extra_fields);

		return $fields;
	}

	/**
	 * Add reward point in commssing fields
	 * Hooked via filter sejoli/product/commission/fields, priority 12
	 * @since 	1.0.0
	 * @param 	array $fields
	 * @return 	array
	 */
	public function set_commission_fields($fields) {

		$fields = $fields + array(
			Field::make('checkbox',		'reward_enable', __('Aktifkan poin reward', 'sejoli-reward')),
			Field::make('text', 		'reward_point', __('Poin Reward', 'sejoli-reward'))
				->set_default_value(0)
				->set_attribute('type', 'number')
				->set_attribute('min',	0)
				->set_conditional_logic(array(
					array(
						'field'	=> 'reward_enable',
						'value'	=> true
					)
				))
		);

		return $fields;
	}

	/**
	 * Set reward point to product meta data
	 * Hooked via filter sejoli/product/meta-data, priority 122
	 * @since 	1.0.0
	 * @param 	WP_Post $product
	 * @return 	WP_Post
	 */
	public function set_product_point(\WP_Post $product) {

		$commissions           = carbon_get_post_meta($product->ID, 'sejoli_commission');
		$product->reward_point = absint(carbon_get_post_meta($product->ID, 'reward_point'));

		foreach( (array) $commissions as $i => $commission) :

			$tier = $i + 1;

			$product->affiliate[$tier]['reward_enable']	= $commission['reward_enable'];
			$product->affiliate[$tier]['reward_point']	= absint($commission['reward_point']);

		endforeach;

		// Modify reward point by user group
		if(is_user_logged_in()) :
			$group                 = sejolisa_get_user_group();
			$product->reward_point = (false !== $group['reward_enable']) ? absint($group['reward_point']) : $product->reward_point;

			if(
				array_key_exists('per_product', $group) &&
				array_key_exists($product->ID, $group['per_product'])
			) :
				$per_product = $group['per_product'][$product->ID];
				$product->reward_point = (false !== $per_product['reward_enable']) ? absint($per_product['reward_point']) : $product->reward_point;
			endif;
		endif;

		return $product;

	}

	/**
	 * Set reward point in user group detail
	 * Hooked via filter sejoli/user-group/detail, priority 12
	 * @since 	1.0.0
	 * @param 	array  	$group_detail
	 * @param 	integer $group_id
	 * @param 	array 	$commissions 	Commission field values
	 * @param 	array 	$per_product	Per product field values
	 * @return 	array
	 */
	public function set_user_group_detail(array $group_detail, $group_id, $commissions, $per_product) {

		$group_detail['reward_enable'] = carbon_get_post_meta($group_id, 'group_reward_enable');
		$group_detail['reward_point']  = absint(carbon_get_post_meta($group_id, 'group_reward_point'));

		// Setup reward point in commissions
		if(is_array($commissions) && 0 < count($commissions)) :

			foreach($commissions as $i => $commission) :
				$tier = $i + 1;
				$group_detail['commissions'][$tier]['reward_enable'] = $commission['reward_enable'];
				$group_detail['commissions'][$tier]['reward_point']  = absint($commission['reward_point']);
			endforeach;

		endif;

		// Setup reward point for each product
		if(is_array($per_product) && 0 < count($per_product)) :

			foreach($per_product as $i => $detail) :

				$product_id = absint($detail['product']);

				$group_detail['per_product'][$product_id]['reward_enable'] = $detail['reward_enable'];
				$group_detail['per_product'][$product_id]['reward_point']  = absint($detail['reward_point']);

				if(is_array($detail['commission']) && 0 < count($detail['commission'])) :

					$per_product_commissions = $group_detail['per_product'][$product_id]['commissions'];

					foreach($detail['commission'] as $i => $_commission) :

						$tier = $i + 1;
						$per_product_commissions[$tier]['reward_enable'] = $_commission['reward_enable'];
						$per_product_commissions[$tier]['reward_point']  = absint($_commission['reward_point']);

					endforeach;

					$group_detail['per_product'][$product_id]['commissions'] = $per_product_commissions;

				endif;

			endforeach;

		endif;

		return $group_detail;
	}


	/**
	 * Set reward point in user group detail
	 * Hooked via filter sejoli/user-group/detail, priority 12
	 * @since 	1.0.0
	 * @param 	array  	$group_detail
	 * @param 	integer $group_id
	 * @return 	array
	 */
	public function set_user_group_per_product_detail(array $group_per_product_detail, $detail) {

		$group_per_product_detail['reward_point'] = (isset($detail['point'])) ? absint($detail['point']) : 0;

		return $group_per_product_detail;
	}

	/**
	 * Add notification setting fields
	 * Hooke via filter sejoli/notification/fields, priority 124
	 * @since 	1.0.0
	 * @param 	array $fields
	 */
	public function set_notification_fields(array $fields) {

		$fields['point'] = [
			'title'		=> __('Informasi Poin', 'sejoli'),
			'fields'	=> array(
				Field::make('separator', 'sep_info_point_email', __('Email', 'sejoli'))
					->set_help_text(__('Pengturan konten untuk media email', 'sejoli')),

				Field::make('rich_text', 'info_point_email', __('Konten', 'sejoli'))
					->set_required(true)
					->set_default_value('Selamat, Anda mendapatkan poin sebesar {{new-point}}! Total point anda sekarang adalah {{all-point}}'),

				Field::make('separator', 'sep_info_point_whatsapp', __('Whatsapp', 'sejoli'))
					->set_help_text(__('Pengturan konten untuk media whatsapp', 'sejoli')),

				Field::make('textarea', 'info_point_whatsapp', __('Konten', 'sejoli'))
					->set_required(true)
					->set_default_value('Selamat, Anda mendapatkan poin sebesar {{new-point}}! Total point anda sekarang adalah {{all-point}}'),

				Field::make('separator', 'sep_info_point_sms', __('SMS', 'sejoli'))
					->set_help_text(__('Pengturan konten untuk media SMS', 'sejoli')),

				Field::make('textarea', 'info_point_sms', __('Konten', 'sejoli'))
					->set_required(true)
					->set_default_value('Total point anda sekarang {{all-point}}'),

			)
		];

		return $fields;
	}

	/**
	 * Check if current admin page is a sejoli page
	 * Hooked via filter sejoli/admin/is-sejoli-page, priority 1222
	 * @param  boolean $is_sejoli_page
	 * @return boolean
	 */
	public function is_current_page_sejoli_page($is_sejoli_page) {

		global $pagenow;

		if(
			'edit.php' === $pagenow &&
			isset($_GET['post_type']) && SEJOLI_REWARD_CPT === $_GET['post_type'] &&
			isset($_GET['page']) && in_array($_GET['page'], array('sejoli-reward-exchange', 'sejoli-reward-point'))
		) :
			return true;
		endif;

		return $is_sejoli_page;
	}

	/**
	 * Set local JS variables
	 * Hooked via filter sejoli/admin/js-localize-data, priority 12
	 * @since 	1.0.0
	 * @param 	array $js_vars
	 * @return 	array
	 */
	public function set_localize_js_vars($js_vars) {

		$js_vars['user_point'] = array(
			'table'	=> array(
				'ajaxurl'	=> add_query_arg(array(
						'action' => 'sejoli-user-point-table'
					), admin_url('admin-ajax.php')
				),
				'nonce'	=> wp_create_nonce('sejoli-render-user-point-table')
			),
			'single_table'	=> array(
				'ajaxurl'	=> add_query_arg(array(
						'action' => 'sejoli-single-user-point-table'
					), admin_url('admin-ajax.php')
				),
				'nonce'   => wp_create_nonce('sejoli-render-single-user-point-table'),
				'user_id' => (isset($_GET['user_id'])) ? intval($_GET['user_id']) : get_current_user_id()
			),
			'reward_table'	=> array(
				'ajaxurl'	=> add_query_arg(array(
						'action' => 'sejoli-reward-table'
					), admin_url('admin-ajax.php')
				),
				'nonce'	=> wp_create_nonce('sejoli-render-reward-table')
			),
			'reward'		=> array(
				'placeholder'	=> __('Pilih reward', 'sejoli-reward'),
				'ajaxurl'		=> add_query_arg(array(
									'action'	=> 'sejoli-reward-options',
									'nonce'		=> wp_create_nonce('sejoli-render-reward-options')
								), admin_url('admin-ajax.php')
						)
			)
		);

		return $js_vars;
	}

	/**
	 * Display all user point
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_user_point() {
		if(isset($_GET['user_id'])) :
			$user 	= sejolisa_get_user(intval($_GET['user_id']));
			require_once( plugin_dir_path( __FILE__ ) . 'partials/single-user-point.php' );
		else :
			require_once( plugin_dir_path( __FILE__ ) . 'partials/user-point.php' );
		endif;
	}

	/**
	 * Display all reward exchange
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_reward_exchange() {
		require_once( plugin_dir_path( __FILE__ ) . 'partials/reward-exchange.php' );
	}
}
