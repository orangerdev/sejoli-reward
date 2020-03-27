<?php

use Illuminate\Database\Capsule\Manager as Capsule;

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

		if(!Capsule::schema()->hasTable( $table )):
            Capsule::schema()->create( $table, function($table){
                $table->increments  ('ID');
                $table->date        ('created_at');
                $table->integer     ('order_id');
                $table->string      ('order_status');
                $table->integer     ('product_id');
                $table->integer     ('user_id');
                $table->integer     ('point');
                $table->enum        ('type', array('in', 'out'));
                $table->integer     ('reward_id')->default(0);
                $table->text        ('meta_data')->nullable();
				$table->boolean		('valid_point');
            });
        endif;
	}

}
