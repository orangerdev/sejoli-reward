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

    /**
     * Exchange reward
     *
     * <user_id>
     * : The user id
     *
     * <reward_id>
     * : The reward id
     *
     *  wp sejolisa reward exchange 14 985
     *
     * @when after_wp_load
     */
    public function exchange(array $args) {
        list($user_id, $reward_id) = $args;

        $response = sejoli_exchange_reward($reward_id, $user_id);

        if(false !== $response['valid']) :
            $this->message($response['messages'], 'success');
        else :
            $this->message($response['messages'], 'error');
        endif;
    }

    /**
     * Get available user point
     *
     * <user_id>
     * : The user id
     *
     *  wp sejolisa reward get_user_point 14
     *
     * @when after_wp_load
     */
    public function get_user_point(array $args) {

        list($user_id)  = $args;

        $response = sejoli_reward_get_user_point($user_id);

        if(false !== $response['valid']) :
            $this->render(
                array(
                    (array) $response['point']
                ),
                'table',
                array(
                    'user_id',
                    'added_point',
                    'reduce_point',
                    'available_point',
                )
            );
        else :
            $this->message($response['messages']);
        endif;
    }

    /**
     * Get available user point
     *
     *  wp sejolisa reward get_all_user_point
     *
     * @when after_wp_load
     */
    public function get_all_user_point() {

        $response = sejoli_reward_get_all_user_point();

        if(false !== $response['valid']) :

            $data = array();

            foreach($response['points'] as $i => $_data) :
                $data[$i]   = (array) $_data;
            endforeach;

            $this->render(
                $data,
                'table',
                array(
                    'user_id',
                    'display_name',
                    'user_email',
                    'added_point',
                    'reduce_point',
                    'available_point',
                )
            );
        else :
            $this->message($response['messages']);
        endif;
    }

    /**
     * Get all available rewards
     *
     *  wp sejolisa reward get_rewards
     *
     * @when after_wp_load
     */
    public function get_rewards() {

        $rewards = sejoli_get_available_reward();

        $this->render(
            $rewards,
            'table',
            array(
                'ID',
                'point',
                'name'
            )
        );
    }

}
