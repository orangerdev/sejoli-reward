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
class Json extends \SEJOLI_REWARD\JSON {

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
	 * Create CSV file with user point data
	 * Hooked via action wp_ajax_sejoli-user-point-csv-export, priority 1
	 * @since 	1.1.3
	 * @return 	void
	 */
	public function export_user_point_csv() {

		if(
			isset($_GET['sejoli-nonce']) &&
			wp_verify_nonce($_GET['sejoli-nonce'], 'sejoli-user-point-export')
		):

			$table = [];
			if ( isset( $_GET['user_id'] ) && !empty( $_GET['user_id'] ) ) :
				$table['filter']['user_id'] = $_GET['user_id'];
			endif;

    		$return = sejoli_reward_get_all_user_point($table['filter']);

    		$user_point_data = array();

			$user_point_data[] = array(
				'user_id',
				'display_name',
				'user_email',
				'added_point',
				'reduce_point',
				'available_point'			
			);

            if(false !== $return['valid']) :

                foreach($return['points'] as $_data) :

                    $user_point_data[] = array(
                        'user_id'         => $_data->user_id,
                        'display_name'    => $_data->display_name,
                        'user_email'      => $_data->user_email,
                        'added_point'     => $_data->added_point,
                        'reduce_point'    => $_data->reduce_point,
                        'available_point' => $_data->available_point
                    );

                endforeach;

            endif;

			$filename = 'data-user-point-'.date('Y-m-d').'.csv';

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');

			$fp = fopen('php://output', 'wb');
			foreach($user_point_data as $_data) :
				fputcsv($fp, $_data);
			endforeach;
			fclose($fp);

			exit;

		endif;

	}

    /**
     * Get point history for a user
     * Hooked via filter wp_ajax_sejoli-single-user-point-table, priority 1
     * @since   1.0.0
     * @since 	1.1.0	Add meta_data type manual description
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

			if ( isset( $_GET['date_range'] ) && !empty( $_GET['date_range'] ) ) :
				$table['filter']['date-range'] = $_GET['date_range'];
			endif;
			if ( isset( $_GET['user_id'] ) && !empty( $_GET['user_id'] ) ) :
				$table['filter']['user_id'] = $_GET['user_id'];
			endif;
			if ( isset( $_GET['product_id'] ) && !empty( $_GET['product_id'] ) ) :
				$table['filter']['product_id'] = $_GET['product_id'];
			endif;
			if ( isset( $_GET['type'] ) && !empty( $_GET['type'] ) ) :
				$table['filter']['type'] = $_GET['type'];
			endif;

    		$return = sejoli_reward_get_history($table['filter'], $table);

            if(false !== $return['valid']) :

                foreach($return['points'] as $_data) :

					$detail = '';

					if('in' === $_data->type) :

						switch($_data->meta_data['type']) :

							case 'order' :

								$product = sejolisa_get_product($_data->product_id);
								$detail  = sprintf(
												__('Poin dari order %s untuk produk %s', 'sejoli-reward'),
												$_data->order_id,
												$product->post_title
										   );
								break;

							case 'affiliate' :

								$product = sejolisa_get_product($_data->product_id);
								$detail  = sprintf(
												__('Poin dari affiliasi order %s untuk produk %s, tier %s', 'sejoli-reward'),
												$_data->order_id,
												$product->post_title,
												$_data->meta_data['tier']
										  );
								break;

							case 'manual' :
							
								$detail 	= $_data->meta_data['note'] . '. ' . $_data->meta_data['input'];
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
	 * Create CSV file with user point data
	 * Hooked via action wp_ajax_sejoli-single-user-point-csv-export, priority 1
	 * @since 	1.1.3
	 * @return 	void
	 */
	public function export_single_user_point_csv() {

		if(
			isset($_GET['sejoli-nonce']) &&
			wp_verify_nonce($_GET['sejoli-nonce'], 'sejoli-single-user-point-export')
		):

			$table = [];
			if ( isset( $_GET['date_range'] ) && !empty( $_GET['date_range'] ) ) :
				$table['filter']['date-range'] = $_GET['date_range'];
			endif;
			if ( isset( $_GET['user_id'] ) && !empty( $_GET['user_id'] ) ) :
				$table['filter']['user_id'] = $_GET['user_id'];
			endif;
			if ( isset( $_GET['product_id'] ) && !empty( $_GET['product_id'] ) ) :
				$table['filter']['product_id'] = $_GET['product_id'];
			endif;
			if ( isset( $_GET['type'] ) && !empty( $_GET['type'] ) ) :
				$table['filter']['type'] = $_GET['type'];
			endif;

    		$return = sejoli_reward_get_history($table['filter'], $table);

    		$single_user_point_data = array();

			$single_user_point_data[] = array(
				'created_at',
				'detail',
				'point',
				'type'
			);

            if(false !== $return['valid']) :

                foreach($return['points'] as $_data) :

					$detail = '';

					if('in' === $_data->type) :

						switch($_data->meta_data['type']) :

							case 'order' :

								$product = sejolisa_get_product($_data->product_id);
								$detail  = sprintf(
												__('Poin dari order %s untuk produk %s', 'sejoli-reward'),
												$_data->order_id,
												$product->post_title
										   );
								break;

							case 'affiliate' :

								$product = sejolisa_get_product($_data->product_id);
								$detail  = sprintf(
												__('Poin dari affiliasi order %s untuk produk %s, tier %s', 'sejoli-reward'),
												$_data->order_id,
												$product->post_title,
												$_data->meta_data['tier']
										  );
								break;

							case 'manual' :
							
								$detail 	= $_data->meta_data['note'] . '. ' . $_data->meta_data['input'];
								break;

						endswitch;

					else :

						$detail = $_data->meta_data['note'];

					endif;

                    $single_user_point_data[] = array(
						'created_at' => date('Y/m/d', strtotime($_data->created_at)),
						'detail'   	 => $detail,
                        'point' 	 => $_data->point,
                        'type'  	 => $_data->type
                    );

                endforeach;

            endif;

			$filename = 'data-single-user-point-'.date('Y-m-d').'-'.$_GET['user_id'].'.csv';

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');

			$fp = fopen('php://output', 'wb');
			foreach($single_user_point_data as $_data) :
				fputcsv($fp, $_data);
			endforeach;
			fclose($fp);

			exit;

		endif;

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
						'user_id'       => $_data->user_id,
                        'display_name'  => $_data->display_name,
                        'user_email'    => $_data->user_email,
						'created_at' 	=> date('Y/m/d', strtotime($_data->created_at)),
						'detail'   	 	=> $_data->meta_data['note'],
                        'point' 	 	=> $_data->point,
						'valid'			=> boolval($_data->valid_point),
						'update_valid'	=> add_query_arg(array(
												'ID'          => $_data->ID,
												'valid_point' => !(boolval($_data->valid_point)),
												'action'      => 'sejoli-update-reward-point-status',
												'nonce'       => wp_create_nonce('sejoli-update-reward-point-status')
										   ), admin_url('admin-ajax.php'))
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
	 * Create CSV file with reward exchanges data
	 * Hooked via action wp_ajax_sejoli-reward-exchanges-csv-export, priority 1
	 * @since 	1.1.3
	 * @return 	void
	 */
	public function export_reward_exchanges_csv() {

		if(
			isset($_GET['sejoli-nonce']) &&
			wp_verify_nonce($_GET['sejoli-nonce'], 'sejoli-reward-exchanges-export')
		):

			$table = [];
			if ( isset( $_GET['date_range'] ) && !empty( $_GET['date_range'] ) ) :
				$table['filter']['date-range'] = $_GET['date_range'];
			endif;
			if ( isset( $_GET['reward_id'] ) && !empty( $_GET['reward_id'] ) ) :
				$table['filter']['reward_id'] = $_GET['reward_id'];
			endif;
			if ( isset( $_GET['user_id'] ) && !empty( $_GET['user_id'] ) ) :
				$table['filter']['user_id'] = $_GET['user_id'];
			endif;

			$table['filter']['type']        = 'out';
			$table['filter']['valid_point'] = NULL;

    		$return = sejoli_reward_get_history($table['filter'], $table);

    		$reward_exchange_data = array();

			$reward_exchange_data[] = array(
				'user_id',
				'display_name',
				'user_email',
				'created_at',
				'detail',
				'point'			
			);

            if(false !== $return['valid']) :

                foreach($return['points'] as $exchange_data) :

                    $reward_exchange_data[] = array(
						'user_id'       => $exchange_data->user_id,
                        'display_name'  => $exchange_data->display_name,
                        'user_email'    => $exchange_data->user_email,
						'created_at' 	=> date('Y/m/d', strtotime($exchange_data->created_at)),
						'detail'   	 	=> $exchange_data->meta_data['note'],
                        'point' 	 	=> $exchange_data->point                    
                    );

                endforeach;

            endif;

			$filename = 'data-reward-exchange-'.date('Y-m-d').'.csv';

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');

			$fp = fopen('php://output', 'wb');
			foreach($reward_exchange_data as $_data) :
				fputcsv($fp, $_data);
			endforeach;
			fclose($fp);

			exit;

		endif;

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

	/**
	 * Update reward point status
	 * Hooked via action wp_ajax_sejoli-update-reward-point-status, priority
	 * @return 	void
	 */
	public function ajax_update_reward_point_status() {

		$params 	= wp_parse_args($_GET, array(
			'nonce'       => false,
			'ID'          => NULL,
			'valid_point' => false,
		));

		if(wp_verify_nonce($params['nonce'], 'sejoli-update-reward-point-status')):
			sejoli_update_exchange_point_validity($params['ID'], $params['valid_point']);
		endif;

		echo wp_send_json(array());
		exit;
	}

	/**
	 * Get available reward for table display
	 * @since 	1.0.0
	 * @return 	array
	 */
	public function ajax_get_available_reward_for_table() {

		$total  = 0;
		$table  = $this->set_table_args($_POST);
        $params = wp_parse_args($_POST, array(
            'nonce' 	=> NULL
        ));

        if(wp_verify_nonce($params['nonce'], 'sejoli-render-reward-table')) :

			$rewards = new \WP_Query([
	            'post_type'      => SEJOLI_REWARD_CPT,
	            'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_key'       => '_reward_point',
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC'
	        ]);

	        if($rewards->have_posts()) :

				while($rewards->have_posts()) :

	                $rewards->the_post();

	                $data[] = [
	                    'id'   		=> get_the_ID(),
						'image'     => get_the_post_thumbnail_url(get_the_ID(), 'lager'),
	                    'title' 	=> get_the_title(),
						'content'   => wpautop(get_the_content()),
						'point'     => carbon_get_the_post_meta('reward_point')
	                ];

	            endwhile;

				$total = $rewards->post_count;

	        endif;

	        wp_reset_query();

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
	 * Do exchange reward
	 * Hooked via action wp_ajax_sejoli-reward-exchange, priority 1
	 * @since 	1.0.0
	 * @return 	array
	 */
	public function ajax_set_reward_exchange() {

		$response = array(
			'valid'   => false,
			'message' => __('Terjadi kesalahan di sistem', 'sejoli')
		);

		$params = wp_parse_args($_POST, array(
						'nonce'     => NULL,
						'reward_id' => NULL
					));

		if(
			wp_verify_nonce($params['nonce'], 'sejoli-reward-exchange') &&
			!empty($params['reward_id'])
		) :

			$exchange_response   = sejoli_exchange_reward($params['reward_id']);
			$response['valid']   = $exchange_response['valid'];
			$response['message'] = implode('. ', $exchange_response['messages']);

		endif;


		echo wp_send_json($response);
		exit;
	}
}
