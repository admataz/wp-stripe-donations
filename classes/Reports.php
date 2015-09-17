<?php

namespace adz_stripe_donations;

class Reports extends Base {
    
    function __construct($slug = '') {
        
        parent::__construct($slug);
    }
    
    private function get_donor_list($params = array()) {
        $start = (!empty($_REQUEST['start'])) ? $_REQUEST['start'] : date('d M Y', time() - 60 * 60 * 24 * 30); 
        $end = (!empty($_REQUEST['end'])) ? $_REQUEST['end'] : 'today'; 
        // TODO: add date filters as the first argument
        $donations = \adz_stripe_donations\CustomSave::get_result_set(array() , true);
    }
    
    public function list_donors() {
        
        $start = (!empty($_REQUEST['start'])) ? $_REQUEST['start'] : date('d M Y', time() - 60 * 60 * 24 * 30); //If no order, default to asc
        $end = (!empty($_REQUEST['end'])) ? $_REQUEST['end'] : 'today'; //If no order, default to asc
        

        $start_time = strtotime($start);
        $end_time = strtotime($end);



        if($start_time && $end_time){
            
        $params = array(
            'start' => $start_time,
            'end' => $end_time + 60 * 60 * 24 // make it to the end of the day - do we need to be more exact?
        );

        $items = \adz_stripe_donations\CustomSave::get_result_set($params, true);
        } else {
            $items = array();
        }
        include_once  realpath(dirname(__FILE__) . '/../views/admin_list_donors.php');
    }
    
    public function export_donors_csv() {
        // CSV download headers
        


        $start_time = (!empty($_REQUEST['start'])) ? $_REQUEST['start'] :  time() - 60 * 60 * 24 * 30; 
        $end_time = (!empty($_REQUEST['end'])) ? $_REQUEST['end'] : strtotime('today'); 
         $params = array(
            'start' => $start_time,
            'end' => $end_time + 60 * 60 * 24
        );

         $start = date('Y-m-d', $start_time);
         $end = date('Y-m-d', $end_time);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="StripeDonations-'.$start.'--'.$end.'.csv"');
        
        $items = \adz_stripe_donations\CustomSave::get_result_set($params, false);

        // error_log(var_export($items,1));



        include_once realpath(dirname(__FILE__) . '/../views/admin_export_donors_csv.php');
        // die();
        
        
    }
}

