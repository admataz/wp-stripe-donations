<?php

namespace adz_stripe_donations;

class CustomSave {
    
    public static function activate() {
        
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $table_name = $wpdb->prefix . "adz_stripe_donation_charges";
        
        $sql = "CREATE TABLE $table_name (
          id int(11) unsigned NOT NULL AUTO_INCREMENT,
          stripe_id varchar(255) NOT NULL DEFAULT '',
          created int(11) DEFAULT '0',
          amount int(11) DEFAULT NULL,
          amount_converted int(11) DEFAULT NULL,
          currency varchar(10) DEFAULT NULL,
          email varchar(255) DEFAULT NULL,
          PRIMARY KEY (id)
        );";
        dbDelta($sql);
        
        $table_name2 = $wpdb->prefix . "adz_stripe_donors";
        
        $sql = "CREATE TABLE $table_name2 (
          id int(11) unsigned NOT NULL AUTO_INCREMENT,
          stripe_id varchar(255) NOT NULL DEFAULT '',
          created int(11) DEFAULT '0',
          giftaid int(1) DEFAULT '0',
          plan varchar(255) DEFAULT NULL,
          stripe_type varchar(255) DEFAULT NULL,
          name varchar(255) DEFAULT NULL,
          email varchar(255) DEFAULT NULL,
          extra_fields text,
          PRIMARY KEY (id),
          UNIQUE KEY email (email)
        );";
        dbDelta($sql);
    }
    
    public static function save_donor_detail($to_save = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . "adz_stripe_donors";
        $to_save['created'] = $to_save['created'];
        // date("Y-m-d H:i:s", $to_save['created']);
        $existing_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE email=%s", $to_save['email']));
        if ($existing_id) {
            $q = $wpdb->update($table_name, $to_save, array(
                'id' => $existing_id
            ));
        } 
        else {
            // create the record if no existing one was found
            $q = $wpdb->insert($table_name, $to_save);
        }
    }
    
    public static function save_stripe_charge_detail($charge_data, $stripe_id) {
        global $wpdb;
        $charges_table_name = $wpdb->prefix . "adz_stripe_donation_charges";
        $donors_table_name = $wpdb->prefix . "adz_stripe_donors";
        
        $existing = $wpdb->get_var($wpdb->prepare("SELECT email FROM $donors_table_name WHERE stripe_id=%s", $stripe_id));
        
        if ($existing) {
            $charge_data['email'] = $existing;
        } 
        else {
            $charge_data['email'] = 'unknown user';
        }
        
        $q = $wpdb->insert($charges_table_name, $charge_data);
    }
    
    public static function get_result_set($params, $aggregated = false) {
        global $wpdb;
        
        $options = array_merge(array(
            'start' => time() - 60 * 60 * 24 * 30, // 30 days ago by default
            'end' => time() ,
            'order' => 'desc',
            'orderby' => 'created'
        ) , $params);
        
        $charges_table = $wpdb->prefix . "adz_stripe_donation_charges";
        $donors_table = $wpdb->prefix . "adz_stripe_donors";
        
        $query = $wpdb->prepare("SELECT 
          d.name as name, 
          d.email as email, 
          d.plan as plan, 
          d.giftaid as giftaid, 
          d.stripe_id as stripe_id, 
          d.extra_fields as extra_fields, 
          c.amount as amount,
          c.created as charge_date,
          c.currency as currency,
          c.stripe_id as charge_id
        FROM 
          $charges_table as c, 
          $donors_table as d 
        WHERE c.created >= %d 
        AND c.created <= %d
        AND c.email = d.email
        ORDER BY c.created DESC", 
        $options['start'], $options['end']);

//         error_log(var_export($query, 1));

        $dbresult = $wpdb->get_results($query , ARRAY_A);
        //expand the user data json
        
        foreach ($dbresult as $key => $value) {
            $userdata = json_decode($value['extra_fields'], true);
            unset($value['extra_fields']);
            $dbresult[$key] = array_merge($value, $userdata);
        }
        // just want the unaggregated charges? return now
        if (!$aggregated) {
            return $dbresult;
        }
        // still here? you must want the compiled aggregated results
        $results = array();
        
        foreach ($dbresult as $key => $value) {
            if (!isset($results[$value['email']])) {
                $results[$value['email']] = $value;
                $results[$value['email']]['payments'] = array(
                  array(
                    'amount' => $value['amount'],
                    'currency' => $value['currency'],
                    'date' => $value['charge_date'],
                    'charge_id' => $value['charge_id'],
                    )
                  );
            } 
            else {
                $results[$value['email']]['payments'][]= array(
                    'amount' => $value['amount'],
                    'currency' => $value['currency'],
                    'date' => $value['charge_date'],
                    'charge_id' => $value['charge_id'],
                    );
                $results[$value['email']]['amount']+= $value['amount'];
            }
        }
        
        return $results;
    }
}
/*
CREATE TABLE `wp_2_adz_stripe_donations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stripe_id` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `giftaid` int(1) DEFAULT '0',
  `plan` varchar(255) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `amount_converted` int(11) DEFAULT NULL,
  `stripe_type` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `extra_fields` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1;


CREATE TABLE `wp_2_adz_stripe_donations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stripe_id` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `giftaid` int(1) DEFAULT '0',
  `plan` varchar(255) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `amount_converted` int(11) DEFAULT NULL,
  `stripe_type` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `extra_fields` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

*/
