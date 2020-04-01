<?php

namespace Sejoli_Reward\Admin;

class Order {

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
     * Product data
     * @since   1.0.0
     * @var     WP_Post
     */
    protected $product;

    /**
     * Buyer ID
     * @since   1.0.0
     * @var     integer
     */
    protected $buyer_id;

    /**
     * Commission Data
     * @since   1.0.0
     * @var     array
     */
    protected $commissions;

    /**
     * Add reward point detailt
     * @since   1.0.0
     * @var     array
     */
    protected $order_meta = array();

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
     * Check order and add reward point if possible
     * Hooked via action sejoli/order/new, priority 8
     * @since   1.0.0
     * @param   array $order_data
     */
    public function add_reward_point_for_buyer(array $order_data) {

        $product_reward_point = 0;
        $this->product        = sejolisa_get_product($order_data['product_id']);
        $this->commissions    = $this->product->affiliate;
        $product_id           = $this->product->ID;
        $this->buyer_id       = intval($order_data['user_id']);
        $user_group           = sejolisa_get_user_group($order_data['user_id']);

        if(property_exists($this->product, 'reward_point')) :

            $product_reward_point = $this->product->reward_point;

        endif;

        $product_group_setup = $user_group;

        $enable_reward = false;

        if(array_key_exists($product_id, $product_group_setup['per_product'])) :

            $product_group_setup = $product_group_setup['per_product'][$product_id];

            if($product_group_setup['reward_enable']) :
                $enable_reward        = true;
                $product_reward_point = $product_group_setup['reward_point'];
                $calculate = 'user-group-per-product';
            endif;

        endif;

        if(
            false === $enable_reward &&
            $product_group_setup['reward_enable']
        ) :
            $enable_reward        = true;
            $product_reward_point = $product_group_setup['reward_point'];
            $calculate            = 'user-group';
        endif;

        if(
            false === $enable_reward &&
            0 < $product_reward_point
        ) :
            $calculate = 'product';
        endif;

        // Check if reward point is not zero
        if(0 < $product_reward_point) :

            $response = sejoli_reward_add_point(array(
                'order_id'     => $order_data['ID'],
                'product_id'   => $product_id,
                'order_status' => $order_data['status'],
                'user_id'      => $this->buyer_id,
                'point'        => $product_reward_point,
                'reward_id'    => 0,
                'valid_point'  => false,
                'meta_data'    => array(
                    'type'      => 'order',
                    'calculate' => $calculate
                )
            ));

            do_action(
                'sejoli/log/write',
                'add-reward-point',
                sprintf(
                    __('Add point %s from order ID %s for user %s', 'sejoli-reward'),
                    $product_reward_point,
                    $order_data['ID'],
                    $this->buyer_id
                )
            );

        endif;
    }

    /**
     * Add reward point for affiliate. We will just pass the commission value
     * Hooked via filter sejoli/order/commission, priority 122
     * @since   1.0.0
     * @param   float   $commission
     * @param   array   $commission_set
     * @param   array   $order_data
     * @param   integer $tier
     * @param   integer $affiliate_id
     * @return  float
     */
    public function add_reward_point_for_affiliate($commission, $commission_set, $order_data, $tier, $affiliate_id) {

        $product_id                = $this->product->ID;
        $per_product_commissions   = $product_commissions = array();
        $product_commissions       = $this->commissions;
        $affiliate_group           = sejolisa_get_user_group($affiliate_id);
        $general_group_commissions = $affiliate_group['commissions'];

        if(array_key_exists($product_id, $affiliate_group['per_product'])) :
            $per_product_commissions = $affiliate_group['per_product'][$product_id]['commissions'];
        endif;

        $enable_reward = false;
        $calculate = $point = null;

        /**
         * Check each product setup in user group
         */
        if(
            array_key_exists($tier, $per_product_commissions) &&
            false !== $per_product_commissions[$tier]['reward_enable']
        ) :

            $enable_reward = true;
            $calculate     = 'user-group-per-product';
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
            $calculate     = 'user-group';
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
            $calculate     = 'product';
            $point         = $product_commissions[$tier]['reward_point'];

        endif;

        if(0 < $point) :

            $response = sejoli_reward_add_point(array(
                'order_id'     => $order_data['ID'],
                'product_id'   => $product_id,
                'order_status' => $order_data['status'],
                'user_id'      => $affiliate_id,
                'point'        => $point,
                'reward_id'    => 0,
                'valid_point'  => false,
                'meta_data'    => array(
                    'type'      => 'affiliate',
                    'tier'      => $tier,
                    'calculate' => $calculate
                )
            ));

            do_action(
                'sejoli/log/write',
                'add-reward-point-for-affiliate',
                sprintf(
                    __('Add point %s from order ID %s for user %s with tier %s', 'sejoli-reward'),
                    $point,
                    $order_data['ID'],
                    $affiliate_id,
                    $tier
                )
            );

        endif;

        return $commission;

    }

    /**
     * Update point status to invalid
     * Hooked via action sejoli/order/set-status/on-hold,       priority 122
     * Hooked via action sejoli/order/set-status/in-progress,   priority 122
     * Hooked via action sejoli/order/set-status/shipped,       priority 122
     * Hooked via action sejoli/order/set-status/refunded,      priority 122
     * Hooked via action sejoli/order/set-status/cancelled,     priority 122
     * @param  array  $order_data
     * @return void
     */
    public function update_point_status_to_not_valid(array $order_data) {

    }

    /**
     * Update point status to valid
     * Hooked via action sejoli/order/set-status/completed,     priority 122
     * @param  array  $order_data
     * @return void
     */
    public function update_point_status_to_valid(array $order_data) {

    }
}
