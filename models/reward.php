<?php

namespace SEJOLI_REWARD\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
/**
 * Abandon model class
 * @since   1.0.0
 */
Class Reward extends \SEJOLI_REWARD\Model
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
        self::$order_status = !array_key_exists($status, $available_status) ? 'on-hold' : $status;

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

        if(in_array(self::$action, array('add', 'reduce', 'manual-input'))) :

            if(empty(self::$point)) :
                self::set_valid(false);
                self::set_message( __('Poin tidak boleh kosong', 'sejoli-reward'));
            endif;

            if(empty(self::$type)) :
                self::set_valid(false);
                self::set_message( __('Tipe poin tidak valid', 'sejoli-reward'));
            endif;

        endif;

        if(in_array(self::$action, array('add', 'reduce', 'get-single', 'manual-input'))) :

            if(!is_a(self::$user, 'WP_User')) :
                self::set_valid(false);
                self::set_message( __('User tidak valid', 'sejoli-reward'));
            endif;

        endif;

        if(in_array(self::$action, array('add', 'update-valid-point', 'get-single'))) :

            if(empty(self::$order_id)) :
                self::set_valid(false);
                self::set_message( __('Order ID tidak boleh kosong', 'sejoli-reward'));
            endif;

        endif;

        if(in_array(self::$action, array('add'))) :

            if(empty(self::$order_status)) :
                self::set_valid(false);
                self::set_message( __('Order status tidak valid', 'sejoli-reward'));
            endif;


            if(!is_a(self::$product, 'WP_Post') || 'sejoli-product' !== self::$product->post_type) :
                self::set_valid(false);
                self::set_message( __('Produk tidak valid', 'sejoli-reward'));
            endif;

        endif;

        if(in_array(self::$action, array('reduce'))) :

            if(empty(self::$reward_id)) :
                self::set_valid(false);
                self::set_message( __('Reward tidak boleh kosong', 'sejoli-reward'));
            endif;

        endif;

        if(in_array(self::$action, array('update-exhcange-valid-point', 'get-detail'))) :

            if(empty(self::$id)) :
                self::set_valid(false);
                self::set_message( __('ID tidak boleh kosong', 'sejoli-reward'));
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
                    'valid_point'  => self::$valid_point
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
     * Add data manually by access confirmed user
     * @since   1.1.0
     */
    static public function manual_input() {

        self::set_action('manual-input');
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $point = [
                'created_at'   => current_time('mysql'),
                'order_id'     => 0,
                'order_status' => 'in' === self::$type ? 'completed' : '',
                'product_id'   => 0,
                'user_id'      => self::$user->ID,
                'point'        => self::$point,
                'type'         => self::$type,
                'reward_id'    => 0,
                'valid_point'  => 1,
                'meta_data'    => serialize(self::$meta_data),
            ];

            $point['ID'] = Capsule::table(self::table())
                            ->insertGetId($point);

            self::set_valid     (true);
            self::set_respond   ('point', $point);

        endif;

        return new static;
    }

    /**
     * Get single user reward point from an order
     * @since   1.0.0
     */
    static public function get_single_point() {

        self::set_action('get-single');
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $query = Capsule::table(self::table())
                            ->where(array(
                                'order_id'    => self::$order_id,
                                'user_id'     => self::$user->ID,
                                'type'        => 'in',
                                'valid_point' => true
                            ));

            $point = $query->first();

            if($point) :

                self::set_valid(true);
                self::set_respond('point', $point);

            else :

                $query = Capsule::table(self::table())
                                ->where(array(
                                    'order_id'    => self::$order_id,
                                    'user_id'     => self::$user->ID,
                                ))
                            ->first();

                if($point) :

                    self::set_valid(true);
                    self::set_respond('point', $point);

                else :

                    self::set_valid(false);

                endif;

            endif;

        endif;

        return new static;
    }

    /**
     * Get single point detail
     * @since   1.0.0
     */
    static public function get_point_detail() {

        self::set_action('get-detail');
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $query = Capsule::table(self::table())
                            ->where(array(
                                'ID'    => self::$id,
                            ));

            $point = $query->first();

            if($point) :

                self::set_valid(true);
                self::set_respond('point', $point);

            else :

                self::set_valid(false);

            endif;

        endif;

        return new static;
    }

    /**
     * Get points by filter
     * @since   1.0.0
     * @return  void
     */
    static public function get() {

        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS reward' ))
                        ->select(
                            'reward.*',
                            'user.display_name',
                            'user.user_email'
                        )
                        ->join(
                            $wpdb->users . ' AS user', 'user.ID', '=', 'reward.user_id'
                        );

        // ini untuk tampilan detail point 
        
        $no_exp_date = get_option('point_expired_date', false);             
        
        if(boolval($no_exp_date) === false) :
            
        else:

            $now = date('Y-m-d');

            if ($no_exp_date > $now) {
                // Jika $no_exp_date lebih besar dari tanggal saat ini
                $query = $query->where('created_at', '<', $no_exp_date);
            } else {
                // Jika $no_exp_date lebih kecil atau sudah melewati tanggal saat ini
                $query = $query->where('created_at', '>', $no_exp_date);
            }

        endif;

        $query        = self::set_filter_query( $query );
        $recordsTotal = $query->count();
        $query        = self::set_length_query($query);
        $points       = $query->get()->toArray();

        if ( $points ) :
            self::set_respond('valid', true);
            self::set_respond('points', $points);
            self::set_respond('recordsTotal', $recordsTotal);
            self::set_respond('recordsFiltered', $recordsTotal);
        else:
            self::set_respond('valid', false);
            self::set_respond('points', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Get available all user point
     * @since   1.0.0
     */
    static public function get_available_point_all_users() {

        global $wpdb;

        parent::$table = self::$table;

        $query  = Capsule::table( Capsule::raw( self::table() . ' AS reward' ))
                    ->select(
                        'reward.user_id',
                        'user.display_name',
                        'user.user_email',
                        Capsule::raw(
                            'SUM(CASE WHEN type = "in" THEN point ELSE 0 END) AS added_point'
                        ),
                        Capsule::raw(
                            'SUM(CASE WHEN type = "out" THEN point ELSE 0 END) AS reduce_point'
                        ),
                        Capsule::raw(
                            'SUM(CASE WHEN type = "in" THEN point ELSE -point END) AS available_point'
                        )
                    )
                    ->join(
                        $wpdb->users . ' AS user', 'user.ID', '=', 'reward.user_id'
                    )
                    ->where('valid_point', true)
                    ->orderBy('available_point', 'DESC')
                    ->groupBy('user_id');

        $no_exp_date = get_option('point_expired_date', false);             
        
        if(boolval($no_exp_date) === false) :
            
        else:

            $now = date('Y-m-d');

            if ($no_exp_date > $now) {
                // Jika $no_exp_date lebih besar dari tanggal saat ini
                $query = $query->where('created_at', '<', $no_exp_date);
            } else {
                // Jika $no_exp_date lebih kecil atau sudah melewati tanggal saat ini
                $query = $query->where('created_at', '>', $no_exp_date);
            }

        endif;

        $query  = self::set_filter_query( $query );

        $result = $query->get();

        if($result) :

            self::set_valid(true);
            self::set_respond('points', $result);

        else :

            self::set_valid(false);
            self::set_message( __('No point data', 'sejoli-reward'));

        endif;

        return new static;
    }

    /**
     * Get available user point
     * @since   1.0.0
     */
    static public function get_available_point_for_single_user() {

        global $wpdb;

        parent::$table = self::$table;

        $query  = Capsule::table( self::table() )
                    ->select(
                        'user_id',
                        Capsule::raw(
                            'SUM(CASE WHEN type = "in" THEN point ELSE 0 END) AS added_point'
                        ),
                        Capsule::raw(
                            'SUM(CASE WHEN type = "out" THEN point ELSE 0 END) AS reduce_point'
                        ),
                        Capsule::raw(
                            'SUM(CASE WHEN type = "in" THEN point ELSE -point END) AS available_point'
                        )
                    )
                    ->where('valid_point', true)
                    ->where('user_id', self::$user_id);
        

        $no_exp_date = get_option('point_expired_date', false); 
            
        if(boolval($no_exp_date) === false) :
            
        else:

            $now = date('Y-m-d');

            if ($no_exp_date > $now) {
                // Jika $no_exp_date lebih besar dari tanggal saat ini
                $query = $query->where('created_at', '<', $no_exp_date);
            } else {
                // Jika $no_exp_date lebih kecil atau sudah melewati tanggal saat ini
                $query = $query->where('created_at', '>', $no_exp_date);
            }

        endif;

        $query = $query->first();

        if($query) :

            self::set_valid(true);
            self::set_respond('point', $query);

        else :

            self::set_valid(false);
            self::set_message( sprintf( __('No point for user %s', 'sejoli-reward'), self::$user_id));

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
                'order_id'     => 0,
                'order_status' => '',
                'product_id'   => 0,
                'user_id'      => self::$user->ID,
                'point'        => self::$point,
                'type'         => 'out',
                'reward_id'    => self::$reward_id,
                'meta_data'    => serialize(self::$meta_data),
                'valid_point'  => self::$valid_point
            ];

            $point['ID'] = Capsule::table(self::table())
                            ->insertGetId($point);

            self::set_valid(true);
            self::set_respond('point', $point);

        endif;

        return new static;
    }

    /**
     * Update valid point
     * @since
     */
    static public function update_valid_point() {

        self::set_action('update-valid-point');
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            Capsule::table(self::table())
                            ->where('order_id', self::$order_id)
                            ->update(array(
                                'valid_point'   => self::$valid_point
                            ));

            self::set_valid(true);

        endif;

        return new static;
    }

    /**
     * Update exchange valid point
     * @since
     */
    static public function update_exchange_valid_point() {

        self::set_action('update-exchange-valid-point');
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            Capsule::table(self::table())
                            ->where('ID', self::$id)
                            ->where('type', 'out')
                            ->update(array(
                                'valid_point'   => self::$valid_point
                            ));

            self::set_valid(true);

        endif;

        return new static;
    }
    
    /**
     * set_expired_point
     *
     * @return void
     */

    static public function set_expired_point($set_expired_date){

        $no_expired_date = get_option('point_expired_date', false);

        if($no_expired_date === false):

            add_option( 'point_expired_date', $set_expired_date , '', 'yes' );

        else:

            update_option( 'point_expired_date', $set_expired_date );            

        endif;

        return new static;

    }
}
