<?php

namespace SEJOLI_REWARD\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
/**
 * Abandon model class
 * @since   1.0.0
 */
Class Reward extends \SejoliSA\Model
{
    static protected $point        = 0;
    static protected $type         = 'out';
    static protected $reward_id    = 0;
    static protected $order_status = NULL;
    static protected $valid_point  = true;
    static protected $table        = 'sejolisa_reward_points';

    /**
     * Reset all property values
     * @since   1.0.0
     */
    static public function reset() {

        self::$point        = 0;
        self::$type         = 'out';
        self::$reward_id    = 0;
        self::$valid_point  = true;
        self::$order_status = NULL;

        parent::reset();

        return new static;
    }

    /**
     * Set point value
     * @since   1.0.0
     * @param   integer     $point
     */
    static public function set_point($point) {

        self::$point = absint($point);

        return new static;
    }

    /**
     * Set point type value
     * @since   1.0.0
     * @param   string  $type
     */
    static public function set_type($type) {

        self::$type = (!in_array($type, array('in', 'out'))) ? 'out' : $type;

        return new static;
    }

    /**
     * Set reward id
     * @since   1.0.0
     * @param   integer     $reward_id
     */
    static public function set_reward($reward_id) {

        self::$reward_id = absint($reward_id);

        return new static;
    }

    /**
     * Set order status
     * @since   1.0.0
     * @param   string $status
     */
    static public function set_order_status($status) {

        $available_status   = apply_filters('sejoli/order/status', []);
        self::$order_status = (!in_array($status, $available_status)) ? 'on-hold' : $status;

        return new static;
    }

    /**
     * Set if point is valid or not
     * @since   1.0.0
     * @param   boolean $valid_point
     */
    static public function set_valid_point($valid_point) {

        self::$valid_point = boolval($valid_point);

        return new static;
    }

    /**
     * Validate property values based on action
     * @since   1.0.0
     */
    static protected function validate() {

        if(in_array(self::$action, array('add', 'reduce'))) :

            if(empty(self::$order_status)) :
                self::set_valid(false);
                self::set_message( __('Order status tidak valid', 'sejoli-reward'));
            endif;


            if(!is_a(self::$product, 'WP_Post') || 'sejoli-product' !== self::$product->post_type) :
                self::set_valid(false);
                self::set_message( __('Produk tidak valid', 'sejoli-reward'));
            endif;

            if(!is_a(self::$user, 'WP_User')) :
                self::set_valid(false);
                self::set_message( __('User tidak valid', 'sejoli-reward'));
            endif;

            if(empty(self::$point)) :
                self::set_valid(false);
                self::set_message( __('Poin tidak boleh kosong', 'sejoli-reward'));
            endif;

            if(empty(self::$type)) :
                self::set_valid(false);
                self::set_message( __('Tipe poin tidak valid', 'sejoli-reward'));
            endif;

        endif;

        if(in_array(self::$action, array('add'))) :

            if(empty(self::$order_id)) :
                self::set_valid(false);
                self::set_message( __('Order ID tidak boleh kosong', 'sejoli-reward'));
            endif;

        endif;

        if(in_array(self::$action, array('reduce'))) :

            if(empty(self::$reward_id)) :
                self::set_valid(false);
                self::set_message( __('Reward tidak boleh kosong', 'sejoli-reward'));
            endif;

        endif;
    }

    /**
     * Check existing point by order_id and user_id
     * @since   1.0.0
     * @return  boolean
     */
    static protected function check_existing_point() {

        parent::$table = self::$table;

        $data = Capsule::table(self::table())
                    ->where(array(
                        'order_id'    => self::$order_id,
                        'user_id'     => self::$user->ID,
                        'type'        => self::$type,
                        'valid_point' => true,
                    ))
                    ->first();

        return boolval($data);
    }

    /**
     * Add point with IN type
     * @since   1.0.0
     */
    static public function add_point() {

        self::set_action('add');
        self::validate();

        if(false !== self::$valid) :

            self::$type = 'in';

            if(false === self::check_existing_point()) :

                parent::$table = self::$table;

                $point = [
                    'created_at'   => current_time('mysql'),
                    'order_id'     => self::$order_id,
                    'order_status' => self::$order_status,
                    'product_id'   => self::$product->ID,
                    'user_id'      => self::$user->ID,
                    'point'        => self::$point,
                    'type'         => 'in',
                    'reward_id'    => self::$reward_id,
                    'meta_data'    => serialize(self::$meta_data),
                    'valid_point'  => true
                ];

                $point['ID'] = Capsule::table(self::table())
                                ->insertGetId($point);

                self::set_valid(true);
                self::set_respond('point', $point);

            else :

                self::set_valid(false);
                self::set_message(
                    sprintf(
                        __('Point for order %s and user %s already exists', 'sejoli'),
                        self::$order_id,
                        self::$user->ID
                    )
                );

            endif;

        endif;

        return new static;
    }


    /**
     * Add point with OUT type
     * @since   1.0.0
     */
    static public function reduce_point() {

        self::set_action('reduce');
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $point = [
                'created_at'   => current_time('mysql'),
                'order_id'     => self::$order_id,
                'order_status' => self::$order_status,
                'product_id'   => self::$product->ID,
                'user_id'      => self::$user->ID,
                'point'        => self::$point,
                'type'         => 'out',
                'reward_id'    => self::$reward_id,
                'meta_data'    => serialize(self::$meta_data),
                'valid_point'  => true
            ];

            $point['ID'] = Capsule::table(self::table())
                            ->insertGetId($point);

            self::set_valid(true);
            self::set_respond('point', $point);

        endif;

        return new static;
    }
}
