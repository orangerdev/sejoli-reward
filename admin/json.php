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
class Json extends \SejoliSA\JSON {

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
     * Set for table data via AJAX
     * Hooked via action wp_ajax_sejoli-user-point-table, priority 1
     * @since   1.0.0
     * @return  array
     */
    public function ajax_set_for_table() {

        $table  = $this->set_table_args($_POST);
        $params = wp_parse_args($_POST, array(
            'nonce' => NULL
        ));

        $total = 0;
        $data  = [];

        if(wp_verify_nonce($params['nonce'], 'sejoli-render-user-point-table')) :

    		$return = sejoli_reward_get_all_user_point($table['filter']);

            if(false !== $return['valid']) :

                foreach($return['points'] as $_data) :

                    $data[] = array(
                        'user_id'         => $_data->user_id,
                        'display_name'    => $_data->display_name,
                        'user_email'      => $_data->user_email,
                        'added_point'     => $_data->added_point,
                        'reduce_point'    => $_data->reduce_point,
                        'available_point' => $_data->available_point,
                        'detail_url'      => add_query_arg(array(
                                                'post_type' => SEJOLI_REWARD_CPT,
                                                'page'      => 'sejoli-reward-point',
                                                'user_id'   => $_data->user_id
                                             ), admin_url('edit.php'))
                    );

                endforeach;

                $total = count($data);

            endif;

        endif;

        echo wp_send_json([
            'table'           => $table,
            'draw'            => $table['draw'],
            'data'            => $data,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total
        ]);

        exit;
    }

    /**
     * Get point history for a user
     * Hooked via filter wp_ajax_sejoli-single-user-point-table, priority 1
     * @since   1.0.0
     * @return  array
     */
    public function ajax_set_single_user_for_table() {

        $table  = $this->set_table_args($_POST);
        $params = wp_parse_args($_POST, array(
            'nonce' 	=> NULL,
			'user_id'   => NULL
        ));

        $total = 0;
        $data  = [];

        if(wp_verify_nonce($params['nonce'], 'sejoli-render-single-user-point-table')) :

			$table['filter']['user_id']	= (empty($params['user_id'])) ? get_current_user_id() : intval($params['user_id']);

    		$return = sejoli_reward_get_history($table['filter'], $table);

            if(false !== $return['valid']) :

                foreach($return['points'] as $_data) :

					$detail = '';

					if('in' === $_data->type) :

						switch($_data->meta_data['type']) :

							case 'order' :
								$product = sejolisa_get_product($_data->product_id);
								$detail = sprintf(__('Poin dari order %s untuk produk %s', 'sejoli-reward'), $_data->order_id, $product->post_title);
								break;

							case 'affiliate' :
								$product = sejolisa_get_product($_data->product_id);
								$detail = sprintf(__('Poin dari affiliasi order %s untuk produk %s, tier %s', 'sejoli-reward'), $_data->order_id, $product->post_title, $_data->meta_data['tier']);
								break;

						endswitch;

					else :

						$detail = $_data->meta_data['note'];

					endif;

                    $data[] = array(
						'created_at' => date('Y/m/d', strtotime($_data->created_at)),
						'detail'   	 => $detail,
                        'point' 	 => $_data->point,
                        'type'  	 => $_data->type
                    );

                endforeach;

                $total = count($data);

            endif;

        endif;

        echo wp_send_json([
            'table'           => $table,
            'draw'            => $table['draw'],
            'data'            => $data,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total
        ]);

        exit;
    }

	/**
     * Get reward exchange data
     * Hooked via filter wp_ajax_sejoli-reward-table, priority 1
     * @since   1.0.0
     * @return  array
     */
    public function ajax_set_reward_for_table() {

        $table  = $this->set_table_args($_POST);
        $params = wp_parse_args($_POST, array(
            'nonce' 	=> NULL
        ));

        $total = 0;
        $data  = [];

        if(wp_verify_nonce($params['nonce'], 'sejoli-render-reward-table')) :

			$table['filter']['type']        = 'out';
			$table['filter']['valid_point'] = NULL;

    		$return = sejoli_reward_get_history($table['filter'], $table);

            if(false !== $return['valid']) :

                foreach($return['points'] as $_data) :

                    $data[] = array(
						'user_id'         => $_data->user_id,
                        'display_name'    => $_data->display_name,
                        'user_email'      => $_data->user_email,
						'created_at' => date('Y/m/d', strtotime($_data->created_at)),
						'detail'   	 => $_data->meta_data['note'],
                        'point' 	 => $_data->point,
                    );

                endforeach;

                $total = count($data);

            endif;

        endif;

        echo wp_send_json([
            'table'           => $table,
            'draw'            => $table['draw'],
            'data'            => $data,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total
        ]);

        exit;
    }

	/**
     * Ger reward options
     * Hooked via action wp_ajax_sejoli-reward-options, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function ajax_get_reward_options() {

        global $post;

        $options = [];
        $args    = wp_parse_args($_GET,[
            'term'    => ''
        ]);

        $rewards = new \WP_Query([
            's'              => $args['term'],
            'post_type'      => SEJOLI_REWARD_CPT,
            'posts_per_page' => 80
        ]);

        if($rewards->have_posts()) :
            while($rewards->have_posts()) :

                $rewards->the_post();


                $options[] = [
                    'id'   => get_the_ID(),
                    'text' => get_the_title()
                ];
            endwhile;
        endif;

        wp_reset_query();

        wp_send_json([
            'results' => $options
        ]);

        exit;
    }
}
