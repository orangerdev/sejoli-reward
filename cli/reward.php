<?php

namespace Sejoli_Reward\CLI;

class Reward extends \SejoliSA\CLI {

    /**
     * Add point reward
     *
     * <order_id>
     * : The order id
     *
     *  wp sejolisa reward add_point
     *
     * @when after_wp_load
     */
    public function add_point(array $args) {

        list($order_id) = $args;

        $response = sejolisa_get_order(array('ID' => $order_id));

        if(false !== $response['valid']) :

            $order  = $response['orders'];

            if(
                property_exists($order['product'], 'reward_point') &&
                0 < $order['product']->reward_point
            ) :

                $point_response = sejoli_reward_add_point(array(
                    'order_id'     => $order['ID'],
                    'product_id'   => $order['product']->ID,
                    'order_status' => $order['status'],
                    'user_id'      => $order['user_id'],
                    'point'        => $order['product']->reward_point
                ));

                if(false !== $point_response['valid']) :

                    $this->message(
                        sprintf(
                            __('%s Point from order %s and user %s already added', 'ttom'),
                            $point_response['point']['point'],
                            $point_response['point']['order_id'],
                            $point_response['point']['user_id']
                        ), 'success');

                else :

                    $this->message($point_response['messages']['error'], 'error');
                    
                endif;

            endif;

        endif;
    }

}
