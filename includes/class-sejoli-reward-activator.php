<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sejoli_Reward
 * @subpackage Sejoli_Reward/includes
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Sejoli_Reward_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

        global $wpdb;

        $table = $wpdb->prefix . 'sejolisa_reward_points';

        // Check if the table already exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) :
            
            // Table does not exist, create it
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "
            CREATE TABLE $table (
                ID INT(11) NOT NULL AUTO_INCREMENT,
                created_at DATE NOT NULL,
                order_id INT(11) NOT NULL,
                order_status VARCHAR(255) NOT NULL,
                product_id INT(11) NOT NULL,
                user_id INT(11) NOT NULL,
                point INT(11) NOT NULL,
                type ENUM('in', 'out') NOT NULL,
                reward_id INT(11) DEFAULT 0,
                meta_data TEXT NULL,
                valid_point BOOLEAN NOT NULL,
                PRIMARY KEY (ID)
            ) $charset_collate;
            ";

            // Run the query to create the table
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

        endif;

    }

}
