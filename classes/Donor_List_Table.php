<?php


namespace adz_stripe_donations;
//Our class extends the WP_List_Table class, so we need to make sure that it's there
if (!class_exists('WP_List_Table')) {
    require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Donor_List_Table extends \WP_List_Table {
    
    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $this->process_bulk_action();
        
        $this->_column_headers = array(
            $this->get_columns() , // columns
            array() , // hidden
            $this->get_sortable_columns() , // sortable
            
        );
        // $table_name = $wpdb->prefix . "africa_check_suggested_claims_form";
        
        /*    if(isset($_REQUEST['archive']) && ($_REQUEST['archive'])){
        $status=1;
        } else {
        $status=0;
        }
        
        
        
        $this->items = $wpdb->get_results("SELECT * FROM $table_name WHERE status=$status ORDER BY date_created DESC" );*/
        //print_r($this->items);
        $this->items = \adz_stripe_donations\CustomSave::get_result_set(array() , true);
        // $reports = \adz_stripe_donations\Reports::get_instance('adz_stripe_donations_form');
        // $reports->export_donors_csv();
        
        function usort_reorder($a, $b) {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date_created'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            if ($orderby == 'date_created') {
                
                $result = (strtotime($a->$orderby) - strtotime($b->$orderby)); //Determine sort order
                //echo $result;
                
                
            } 
            else {
                $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
                
            }
            
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
            
        }
        usort($this->items, 'usort_reorder');
        //$columns = $this->get_columns();
        //$_wp_column_headers = $columns;
        
        
    }
    
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />',
        /*$1%s*/
        'donor', //$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
        /*$2%s*/
        $item->id
        //The value of the checkbox should be the record's id
        );
    }
    
    function get_columns() {
        
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'stripe_id' => 'stripe_id',
            'email' => 'email',
            'plan' => 'plan',
            'giftaid' => 'giftaid',
            'amount' => 'amount',
            'created' => 'created',
        );
        return $columns;
    }
    
    function process_bulk_action() {
        //print_r($this->current_action());
        //Detect when a bulk action is being triggered...
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . "africa_check_suggested_claims_form";
        
        if ('mark_processed' === $this->current_action()) {
            $sql = "UPDATE $table_name SET status=1 WHERE id IN ('" . implode(',', $_POST['suggestion']) . "')";
            $wpdb->query($sql);
        } 
        elseif ('mark_new' === $this->current_action()) {
            $sql = "UPDATE $table_name SET status=0 WHERE id IN ('" . implode(',', $_POST['suggestion']) . "')";
            $wpdb->query($sql);
        } 
        elseif ('delete' === $this->current_action()) {
            $sql = "UPDATE $table_name SET status=3 WHERE id IN ('" . implode(',', $_POST['suggestion']) . "')";
            $wpdb->query($sql);
        }
    }
    
    function get_bulk_actions() {
        $actions = array(
            'mark_processed' => 'Mark as processed',
            'mark_new' => 'Mark as not processed',
            'delete' => 'Delete'
        );
        return $actions;
    }
    
    function get_sortable_columns() {
        $columns = array(
            'stripe_id' => array(
                'stripe_id',
                false
            ) ,
            'email' => array(
                'email',
                true
            ) ,
            'plan' => array(
                'plan',
                true
            ) ,
            'giftaid' => array(
                'giftaid',
                false
            ) ,
            'amount' => array(
                'amount',
                true
            ) ,
            'created' => array(
                'created',
                true
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
        // if(!empty($_REQUEST['archive']) && $_REQUEST['archive']){
        //   echo  '<a href="'.admin_url().'admin.php?page=africa_check_suggested_claims&archive=0">View current items</a>';
        // } else {
        //   echo '<a href="'.admin_url().'admin.php?page=africa_check_suggested_claims&archive=1">View archived items</a>';
        
        // }
        
        
    }
}
