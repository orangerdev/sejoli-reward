<?php

/**
 * Add reward point
 * @since   1.0.0
 * @param   array   $args
 * @return  array
 */
function sejoli_reward_add_point(array $args) {

    $args   = wp_parse_args($args, array(
        'order_id'     => NULL,
        'product_id'   => NULL,
        'order_status' => NULL,
        'user_id'      => NULL,
        'point'        => 0,
        'reward_id'    => 0,
        'valid_point'  => false,
        'meta_data'    => array()
    ));

    $response   =  \SEJOLI_REWARD\Model\Reward::reset()
                        ->set_order_id($args['order_id'])
                        ->set_order_status($args['order_status'])
                        ->set_product_id($args['product_id'])
                        ->set_user_id($args['user_id'])
                        ->set_reward($args['reward_id'])
                        ->set_meta_data($args['meta_data'])
                        ->set_point($args['point'])
                        ->set_valid_point($args['valid_point'])
                        ->add_point()
                        ->respond();

    return wp_parse_args($response, array(
        'valid'    => false,
        'point'    => NULL,
        'messages' => array()
    ));
}

/**
 * Reduce reward point
 * @since   1.0.0
 * @param   array   $args
 * @return  array
 */
function sejoli_reward_reduce_point(array $args) {

    $args   = wp_parse_args($args, array(
        'user_id'      => NULL,
        'point'        => 0,
        'reward_id'    => 0,
        'meta_data'    => array()
    ));

    $response   =  \SEJOLI_REWARD\Model\Reward::reset()
                        ->set_user_id($args['user_id'])
                        ->set_reward($args['reward_id'])
                        ->set_meta_data($args['meta_data'])
                        ->set_point($args['point'])
                        ->reduce_point()
                        ->respond();

    return wp_parse_args($response, array(
        'valid'    => false,
        'point'    => NULL,
        'messages' => array()
    ));
}

/**
 * Get available user point
 * @since   1.0.0
 * @param   integer     $user_id
 * @return  array
 */
function sejoli_reward_get_user_point($user_id = 0) {

    $user_id = (0 === $user_id) ? get_current_user_id() : $user_id;

    $response   = \SEJOLI_REWARD\Model\Reward::reset()
                        ->set_user_id($user_id)
                        ->get_available_point_for_single_user()
                        ->respond();

    return $response;
}

/**
 * Get available all user point
 * @since   1.0.0
 * @param   array  $args
 * @return  array
 */
function sejoli_reward_get_all_user_point($args = array()) {
    $args = wp_parse_args($args, array(
                'user_id' => NULL
            ));
    $response    = \SEJOLI_REWARD\Model\Reward::reset()
                    ->set_filter_from_array($args)
                    ->get_available_point_all_users()
                        ->respond();

    return $response;
}

/**
 * Get available reward exchange
 * @since
 * @param  array    $args
 * @return array
 */
function sejoli_get_available_reward($args = array()) {

    $args   = wp_parse_args($args, array(
                'posts_per_page' => -1,
                'meta_key'       => '_reward_point',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC'
              ));

    $args   = array_merge($args, array(
                'post_type'   => SEJOLI_REWARD_CPT,
                'post_status' => 'publish'
              ));

    $rewards = array();
    $results = get_posts($args);

    if(is_array($results)) :
        foreach($results as $_reward) :
            $rewards[$_reward->ID]  = array(
                'ID'    => $_reward->ID,
                'point' => carbon_get_post_meta($_reward->ID, 'reward_point'),
                'name'  => $_reward->post_title
            );
        endforeach;
    endif;

    return $rewards;
}

/**
 * Do exchange reward
 * @since   1.0.0
 * @param   integer  $reward_id
 * @param   integer  $user_id
 * @return  array    Response
 */
function sejoli_exchange_reward($reward_id, $user_id = 0) {

    $user_id  = (0 === $user_id) ? get_current_user_id() : $user_id;

    $response = array(
        'valid'    => false,
        'messages' => array()
    );

    // check reward point first
    $reward_point = absint(carbon_get_post_meta($reward_id, 'reward_point'));

    if( 0 <  $reward_point ) :

        // check user availibility point
        $point_response = sejoli_reward_get_user_point($user_id);

        if(false !== $point_response['valid']) :

            $available_point = absint($point_response['point']->available_point);

            if($available_point >= $reward_point) :

                $reward = get_post($reward_id);

                $exchange_response = sejoli_reward_reduce_point(array(
                                        'user_id'   => $user_id,
                                        'point'     => $reward_point,
                                        'reward_id' => $reward_id,
                                        'meta_data' => array(
                                            'note'  => sprintf(
                                                            __('Penukaran poin dengan reward berupa %s', 'sejoli'),
                                                            $reward->post_title
                                        ))
                                    ));


                if(false !== $exchange_response['valid']) :

                    $response['valid']      = true;
                    $response['messages'][] = sprintf(
                                                __('Anda telah berhasil menukar poin dengan %s. Sisa poin anda adalah %s', 'sejoli'),
                                                $reward->post_title,
                                                $available_point - $reward_point
                                              );

                    do_action('sejoli/notification/reward/exchange', $exchange_response['point']);

                else :
                    $response['messages'][] = __('Telah terjadi kesalahan di dalam sistem. Silahkan kontak administrator', 'sejoli');
                endif;

            else :

                $response['messages'][] = sprintf(
                                            __('Poin anda tidak mencukupi untuk menukar reward sebesar %d. Sisa poin anda adalah %d.', 'sejoli'),
                                            $reward_point,
                                            $available_point
                                          );
            endif;

        else :

            $response['messages'][] = __('Anda belum memiliki poin reward', 'sejoli');

        endif;

    else :

        $response['messages'][] = __('Tidak bisa menukar reward. Silahkan kontak administrator', 'sejoli');

    endif;

    return $response;

}

/**
 * Update multiple reward point valid point by order ID
 * @since   1.0.0
 * @param   integer  $order_id
 * @param   boolean  $valid
 * @return  void
 */
function sejoli_update_reward_point_validity($order_id, $valid = false) {

    $response   = \SEJOLI_REWARD\Model\Reward::reset()
                        ->set_order_id($order_id)
                        ->set_valid_point($valid)
                        ->update_valid_point()
                        ->respond();
}

/**
 * Get single user reward point from and order
 * @since   1.0.0
 * @param   array  $args
 * @return  array
 */
function sejoli_get_single_user_point_from_an_order(array $args) {

    $args   = wp_parse_args($args, array(
        'order_id'  => NULL,
        'user_id'   => NULL
    ));

    $response   = \SEJOLI_REWARD\Model\Reward::reset()
                        ->set_order_id($args['order_id'])
                        ->set_user($args['user_id'])
                        ->get_single_point()
                        ->respond();

    return $response;
}

/**
 * Get reward history
 * @since   1.0.0
 * @param   array  $args
 * @param   array  $table
 * @return  array
 */
function sejoli_reward_get_history(array $args, $table = array()) {

    $args = wp_parse_args($args,[
        'user_id'     => NULL,
        'product_id'  => NULL,
        'reward_id'   => NULL,
        'type'        => NULL,
        'valid_point' => true
    ]);

    $table = wp_parse_args($table, [
        'start'   => NULL,
        'length'  => NULL,
        'order'   => NULL,
        'filter'  => NULL
    ]);

    if(isset($args['date-range']) && !empty($args['date-range'])) :
        $table['filter']['date-range'] = $args['date-range'];
        unset($args['date-range']);
    endif;

    $query = SEJOLI_REWARD\Model\Reward::reset()
                ->set_filter_from_array($args)
                ->set_data_start($table['start']);

    if(isset($table['filter']['date-range']) && !empty($table['filter']['date-range'])) :
        list($start, $end) = explode(' - ', $table['filter']['date-range']);
        $query = $query->set_filter('created_at', $start , '>=')
                    ->set_filter('created_at', $end, '<=');
    endif;

    if(0 < $table['length']) :
        $query->set_data_length($table['length']);
    endif;

    if(!is_null($table['order']) && is_array($table['order'])) :
        foreach($table['order'] as $order) :
            $query->set_data_order($order['column'], $order['sort']);
        endforeach;
    endif;

    $response = $query->get()->respond();

    foreach($response['points'] as $i => $point) :
        $response['points'][$i]->meta_data = maybe_unserialize($point->meta_data);
    endforeach;

    return wp_parse_args($response,[
        'valid'    => false,
        'points'   => NULL,
        'messages' => []
    ]);
}

/**
 * Update single reward exchange point validity
 * @since   1.0.0
 * @param   integer  $id
 * @param   boolean  $valid
 * @return  void
 */
function sejoli_update_exchange_point_validity($id, $valid = false) {

    $response   = \SEJOLI_REWARD\Model\Reward::reset()
                        ->set_id($id)
                        ->set_valid_point($valid)
                        ->update_exchange_valid_point()
                        ->respond();

    $point_response = sejoli_get_single_point_detail($id);

    if(false !== $point_response['valid']) :

        if(false === $valid) :
            do_action('sejoli/notification/reward/cancel', $point_response['point']);
        else :
            do_action('sejoli/notification/reward/exchange', $point_response['point']);
        endif;

    endif;

    return $response;
}

/**
 * Get single point detail
 * @since   1.0.0
 * @param   interger    $id
 * @return  array
 */
function sejoli_get_single_point_detail($id) {
    return \SEJOLI_REWARD\Model\Reward::reset()
                ->set_id($id)
                ->get_point_detail()
                ->respond();
}

/**
 * Manual input point
 * @since   1.1.0
 * @param   array   $args
 * @return  array   Response
 */
function sejoli_manual_input_point( $args ) {

    if( ! current_user_can('manage_sejoli_sejoli') ) :

        return array(
            'valid' => false,
            'messages'  => array(
                'error' => array(
                    __('Current user doesn\'t have capability to process this function', 'sejoli')
                )
            )
        );

    endif;

    $args = wp_parse_args($args, array(
        'user_id'     => NULL,
        'value'       => NULL,
        'type'        => 'in',
        'label'       => 'manual',
        'valid_point' => false,
        'meta_data'   => array()
    ));

    $response   =  \SEJOLI_REWARD\Model\Reward::reset()
                        ->set_user_id($args['user_id'])
                        ->set_type($args['type'])
                        ->set_point($args['value'])
                        ->set_meta_data($args['meta_data'])
                        ->set_valid_point(1)
                        ->manual_input()
                        ->respond();

    return wp_parse_args($response, array(
        'valid'    => false,
        'point'   => NULL,
        'messages' => array()
    ));
}
