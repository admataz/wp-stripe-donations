<?php

namespace adz_stripe_donations;

class CustomSave {
    
    public static function activate() {
        
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $table_name = $wpdb->prefix . "adz_stripe_donations"; 
        
        $sql = "CREATE TABLE $table_name (
          id int(11) unsigned NOT NULL AUTO_INCREMENT,
          stripe_id varchar(255) NOT NULL DEFAULT '',
          created datetime DEFAULT NULL,
          giftaid int(1) DEFAULT '0',
          plan varchar(255) DEFAULT NULL,
          currency varchar(10) DEFAULT NULL,
          amount int(11) DEFAULT NULL,
          amount_converted int(11) DEFAULT NULL,
          stripe_type varchar(255) DEFAULT NULL,
          email varchar(255) DEFAULT NULL,
          extra_fields text,
          PRIMARY KEY (id)
        );";
        dbDelta($sql);
    }


    public static function add_record($to_save = array()){
      global $wpdb;

    

      $table_name = $wpdb->prefix . "adz_stripe_donations"; 
      $to_save['created'] = date("Y-m-d H:i:s", $to_save['created']);      
      $q = $wpdb->insert($table_name, $to_save);

    }


}

