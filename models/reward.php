<?php

namespace SEJOLI_REWARD\Model;

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
    
        global $wpdb;

        $table = $wpdb->prefix . self::$table;
        $query = "
            SELECT *
            FROM $table
            WHERE order_id = %d
              AND user_id = %d
              AND type = %s
              AND valid_point = true
            LIMIT 1
        ";

        $data = $wpdb->get_var($wpdb->prepare($query, self::$order_id, self::$user->ID, self::$type));

        return boolval($data);

    }


    /**
     * Add point with IN type
     * @since   1.0.0
     */
    static public function add_point() {
     
        self::set_action('add');
        self::validate();

        if (false !== self::$valid) {
            self::$type = 'in';

            if (false === self::check_existing_point()) {
                global $wpdb;

                $table = $wpdb->prefix . self::$table;

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

                $wpdb->insert($table, $point);
                $point['ID'] = $wpdb->insert_id;

                self::set_valid(true);
                self::set_respond('point', $point);
            } else {
                self::set_valid(false);
                self::set_message(sprintf(__('Point for order %s and user %s already exists', 'sejoli-reward'), self::$order_id, self::$user->ID));
            }
        }

        return new static;

    }

    /**
     * Add data manually by access confirmed user
     * @since   1.1.0
     */
    static public function manual_input() {
     
        self::set_action('manual-input');
        self::validate();

        if (false !== self::$valid) {
            global $wpdb;

            $table = $wpdb->prefix . self::$table;

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

            $wpdb->insert($table, $point);
            $point['ID'] = $wpdb->insert_id;

            self::set_valid(true);
            self::set_respond('point', $point);
        }

        return new static;

    }

    /**
     * Get single user reward point from an order
     * @since   1.0.0
     */
    static public function get_single_point() {
     
        self::set_action('get-single');
        self::validate();

        if (false !== self::$valid) {
            global $wpdb;

            $table = $wpdb->prefix . self::$table;
            $query = "
                SELECT *
                FROM $table
                WHERE order_id = %d
                  AND user_id = %d
                  AND type = 'in'
                  AND valid_point = true
                LIMIT 1
            ";

            $point = $wpdb->get_row($wpdb->prepare($query, self::$order_id, self::$user->ID));

            if ($point) {
                self::set_valid(true);
                self::set_respond('point', $point);
            } else {
                $query = "
                    SELECT *
                    FROM $table
                    WHERE order_id = %d
                      AND user_id = %d
                    LIMIT 1
                ";

                $point = $wpdb->get_row($wpdb->prepare($query, self::$order_id, self::$user->ID));

                if ($point) {
                    self::set_valid(true);
                    self::set_respond('point', $point);
                } else {
                    self::set_valid(false);
                }
            }
        }

        return new static;

    }

    /**
     * Get single point detail
     * @since   1.0.0
     */
    static public function get_point_detail() {
     
        self::set_action('get-detail');
        self::validate();

        if (false !== self::$valid) {
            global $wpdb;

            $table = $wpdb->prefix . self::$table;
            $query = "
                SELECT *
                FROM $table
                WHERE ID = %d
            ";

            $point = $wpdb->get_row($wpdb->prepare($query, self::$id));

            if ($point) {
                self::set_valid(true);
                self::set_respond('point', $point);
            } else {
                self::set_valid(false);
            }
        }

        return new static;
    
    }

    /**
     * Get points by filter
     * @since   1.0.0
     * @return  void
     */
    static public function get() {

        global $wpdb;

        $table = $wpdb->prefix . self::$table;
        $query = "
            SELECT reward.*, user.display_name, user.user_email
            FROM $table AS reward
            JOIN {$wpdb->users} AS user ON user.ID = reward.user_id
        ";

        $no_exp_date = get_option('point_expired_date', false);           
        
        if(boolval($no_exp_date) === false) :
            
        else:

            $now = date('Y-m-d');

            if ($no_exp_date > $now) {
                // Jika $no_exp_date lebih besar dari tanggal saat ini
                $query .= " WHERE created_at < %s";
            } else {
                // Jika $no_exp_date lebih kecil atau sudah melewati tanggal saat ini
                $query .= " WHERE created_at > %s";
            }

        endif;

        $query = self::set_filter_query($query);
        $query = self::set_length_query($query);

        $results = $wpdb->get_results($wpdb->prepare($query, $no_exp_date));
        $recordsTotal = is_array($results) ? count($results) : 0;

        if ($results) {
            self::set_respond('valid', true);
            self::set_respond('points', $results);
            self::set_respond('recordsTotal', $recordsTotal);
            self::set_respond('recordsFiltered', $recordsTotal);
        } else {
            self::set_respond('valid', false);
            self::set_respond('points', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        }

        return new static;

    }

    /**
     * Get available all user point
     * @since   1.0.0
     */
    static public function get_available_point_all_users() {

        global $wpdb;

        $table = $wpdb->prefix . self::$table;

        $query = "
            SELECT reward.user_id, user.display_name, user.user_email,
                SUM(CASE WHEN type = 'in' THEN point ELSE 0 END) AS added_point,
                SUM(CASE WHEN type = 'out' THEN point ELSE 0 END) AS reduce_point,
                SUM(CASE WHEN type = 'in' THEN point ELSE -point END) AS available_point
            FROM $table AS reward
            JOIN {$wpdb->users} AS user ON user.ID = reward.user_id
            WHERE valid_point = true
        ";

        $no_exp_date = get_option('point_expired_date', false);            

        if (boolval($no_exp_date) !== false) : // Only proceed if $no_exp_date is not false
            $now = date('Y-m-d');

            if ($no_exp_date > $now) {
                // If $no_exp_date is greater than today, add the condition for "created_at < $no_exp_date"
                $query .= " AND created_at < %s";
            } else {
                // If $no_exp_date is less than or equal to today, add the condition for "created_at > $no_exp_date"
                $query .= " AND created_at > %s";
            }
        endif;

        // Apply additional filters
        $query  = self::set_filter_query( $query );

        $query .= "GROUP BY user_id
            ORDER BY available_point DESC";

        // Prepare the query, passing $no_exp_date correctly
        $results = $wpdb->get_results($wpdb->prepare($query, $no_exp_date));

        if ($results) {
            self::set_valid(true);
            self::set_respond('points', $results);
        } else {
            self::set_valid(false);
            self::set_message(__('No point data', 'sejoli-reward'));
        }

        return new static;

    }

    /**
     * Get available user point
     * @since   1.0.0
     */
    static public function get_available_point_for_single_user() {
     
        global $wpdb;

        $table = $wpdb->prefix . self::$table; 

        $query = "
            SELECT 
                user_id,
                SUM(CASE WHEN type = 'in' THEN point ELSE 0 END) AS added_point,
                SUM(CASE WHEN type = 'out' THEN point ELSE 0 END) AS reduce_point,
                SUM(CASE WHEN type = 'in' THEN point ELSE -point END) AS available_point
            FROM $table
            WHERE valid_point = 1
            AND user_id = %d
        ";

        $no_exp_date = get_option('point_expired_date', false);
        if (boolval($no_exp_date) !== false) :
            $now = date('Y-m-d');

            if ($no_exp_date > $now) :
                $query .= " AND created_at < %s";
                $query = $wpdb->prepare($query, self::$user_id, $no_exp_date);
            else:
                $query .= " AND created_at > %s";
                $query = $wpdb->prepare($query, self::$user_id, $no_exp_date);
            endif;
        else:
            $query = $wpdb->prepare($query, self::$user_id);  // Prepare query with user_id
        endif;

        $results = $wpdb->get_row($query);

        if ($results) {
            self::set_valid(true);
            self::set_respond('point', $results);
        } else {
            self::set_valid(false);
            self::set_message(sprintf(__('No point for user %s', 'sejoli-reward'), self::$user_id));
        }

        return new static;

    }

    /**
     * Add point with OUT type
     * @since   1.0.0
     */
    static public function reduce_point() {
     
        self::set_action('reduce');
        self::validate();

        if (false !== self::$valid) {
            global $wpdb;

            $table = $wpdb->prefix . self::$table;

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

            $wpdb->insert($table, $point);
            $point['ID'] = $wpdb->insert_id;

            self::set_valid(true);
            self::set_respond('point', $point);
        }

        return new static;

    }

    /**
     * Update valid point
     * @since
     */
    static public function update_valid_point() {
     
        self::set_action('update-valid-point');
        self::validate();

        if (false !== self::$valid) {
            global $wpdb;

            $table = $wpdb->prefix . self::$table;
            $wpdb->update(
                $table,
                ['valid_point' => self::$valid_point],
                ['order_id' => self::$order_id]
            );

            self::set_valid(true);
        }

        return new static;

    }

    /**
     * Update exchange valid point
     * @since
     */
    static public function update_exchange_valid_point() {
     
        self::set_action('update-exchange-valid-point');
        self::validate();

        if (false !== self::$valid) {
            global $wpdb;

            $table = $wpdb->prefix . self::$table;
            $wpdb->update(
                $table,
                ['valid_point' => self::$valid_point],
                ['ID' => self::$id, 'type' => 'out']
            );

            self::set_valid(true);
        }

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
