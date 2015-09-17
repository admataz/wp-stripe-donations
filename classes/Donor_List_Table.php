<?php


namespace adz_stripe_donations;
//Our class extends the WP_List_Table class, so we need to make sure that it's there
if (!class_exists('WP_List_Table')) {
    require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Donor_List_Table extends \WP_List_Table {
    
    function prepare_items() {
        
        $this->_column_headers = array(
            $this->get_columns() , // columns
            array() , // hidden
            $this->get_sortable_columns() , // sortable
            
            
        );
    }
    
    function usort_reorder($a, $b) {
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'created'; //If no sort, default to title
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
        if ($orderby == 'date_created') {
            $result = (strtotime($a->$orderby) - strtotime($b->$orderby)); //Determine sort order
        } 
        else {
            $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
        }
        return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        
    }
    
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />',
        'donor',
        $item->email
        );
    }
    
    function get_columns() {
        $columns = array(
            'stripe_id' => 'stripe_id',
            'email' => 'email',
            'plan' => 'plan',
            'giftaid' => 'giftaid',
            'amount' => 'amount',
            'created' => 'created',
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $columns = array(
            'stripe_id' => array(
                'stripe_id',
                false
            ) ,
            'email' => array(
                'email',
                false
            ) ,
            'plan' => array(
                'plan',
                false
            ) ,
            'giftaid' => array(
                'giftaid',
                false
            ) ,
            'amount' => array(
                'amount',
                false
            ) ,
            'created' => array(
                'created',
                false
            ) ,
        );
        return $columns;
    }
    
    function column_default($item, $column) {
        return $item->$column;
    }
    
    function extra_tablenav($which) {
        if ($which == "top") {
        }
        
        
    }
}
