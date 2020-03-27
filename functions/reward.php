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
        'order_id'     => NULL,
        'product_id'   => NULL,
        'order_status' => NULL,
        'user_id'      => NULL,
        'point'        => 0,
        'reward_id'    => 0,
        'meta_data'    => array()
    ));

    $response   =  \SEJOLI_REWARD\Model\Reward::reset()
                        ->set_order_id($args['order_id'])
                        ->set_order_status($args['order_status'])
                        ->set_product_id($args['product_id'])
                        ->set_user_id($args['user_id'])
                        ->set_reward_id($args['reward_id'])
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
