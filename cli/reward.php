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

    /**
     * Get possible point by an order
     *
     * <order_id>
     * : The order id
     *
     *  wp sejolisa reward calculate_point 2193
     *
     * @when after_wp_load
     */
    public function calculate_point(array $args) {

        list($order_id) = $args;

        $order_response        = sejolisa_get_order(array('ID' => $order_id));
        $user_group_product_point = $default_product_point = 0;
        $buyer_group           = '-';
        $affiliate_points      = array();

        if(false !== $order_response['valid']) :

            $order = $order_response['orders'];

            if(property_exists($order['product'], 'reward_point')) :

                $default_product_point = $order['product']->reward_point;
                $product_id            = $order['product_id'];

            endif;

            $user_group          = sejolisa_get_user_group($order['user_id']);
            $buyer_group         = (isset($user_group['name'])) ? $user_group['name'] : $buyer_group;
            $product_group_setup = $user_group;

            if(array_key_exists($product_id, $product_group_setup['per_product'])) :
                $product_group_setup = $product_group_setup['per_product'][$product_id];
            endif;

            if($product_group_setup['reward_enable']) :
                $user_group_product_point = $product_group_setup['reward_point'];
            endif;

            if(!empty($order['affiliate_id'])) :

                $affiliates    = array();
                $affiliates[1] = $affiliate_id = intval($order['affiliate_id']);
                $uplines       = sejolisa_user_get_uplines($affiliate_id, count($order['product']->affiliate));

                if( is_array($uplines) && 0 < count($uplines)) :
                    foreach( $uplines as $tier => $upline_id ) :
        				$affiliates[$tier + 1] = $upline_id;
        			endforeach;
                endif;

                foreach($affiliates as $tier => $affiliate_id) :

                    $per_product_commissions = $product_commissions = array();
                    $product_commissions = $order['product']->affiliate;

                    $affiliate_group = sejolisa_get_user_group($affiliate_id);
                    $general_group_commissions = $affiliate_group['commissions'];

                    if(array_key_exists($product_id, $affiliate_group['per_product'])) :
                        $per_product_commissions = $affiliate_group['per_product'][$product_id]['commissions'];
                    endif;

                    $enable_reward = false;
                    $type = $point = null;


                    /**
                     * Check each product setup in user group
                     */
                    if(
                        array_key_exists($tier, $per_product_commissions) &&
                        false !== $per_product_commissions[$tier]['reward_enable']
                    ) :

                        $enable_reward = true;
                        $type          = 'per product in user group';
                        $point         = $per_product_commissions[$tier]['reward_point'];

                    endif;

                    /**
                     * Check in general setup in user groups
                     */
                    if(
                        false === $enable_reward &&
                        array_key_exists($tier, $general_group_commissions)  &&
                        false !== $general_group_commissions[$tier]['reward_enable']
                    ) :

                        $enable_reward = true;
                        $type          = 'general user group setting';
                        $point         = $general_group_commissions[$tier]['reward_point'];

                    endif;

                    /**
                     * Check in general setup in user groups
                     */
                    if(
                        false === $enable_reward &&
                        array_key_exists($tier, $product_commissions)  &&
                        false !== $product_commissions[$tier]['reward_enable']
                    ) :

                        $enable_reward = true;
                        $type          = 'product setting';
                        $point         = $product_commissions[$tier]['reward_point'];

                    endif;


                    $affiliate_points[] = array(
                        'tier'  => $tier,
                        'id'    => $affiliate_id,
                        'group' => $affiliate_group['name'],
                        'point' => $point,
                        'type'  => $type
                    );

                endforeach;;

            endif;

        endif;

        $this->render(array(
            array(
                'default_product_point'    => $default_product_point,
                'user_group_product_point' => $user_group_product_point,
                'buyer_group'              => $buyer_group,
                'product_id'               => $order['product_id']
            )
        ),'yaml',array(
            'product_id',
            'default_product_point',
            'user_group_product_point',
            'buyer_group'
        ));

        $this->render(
            $affiliate_points,
            'table',
            array(
                'tier',
                'id',
                'group',
                'point',
                'type'
            )
        );
    }

}
