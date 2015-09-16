<?php

namespace adz_stripe_donations;

class Reports extends Base {
    
    function __construct($slug = '') {
        
        parent::__construct($slug);
    }
    
    private function get_donor_list($params = array()) {
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
        // TODO: add date filters as the first argument
        $donations = \adz_stripe_donations\CustomSave::get_result_set(array() , true);
    }
    
    public function list_donors() {
        
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
        
        $params = array(
            'orderby' => $orderby,
            'order' => $order
        );
        
        $items = \adz_stripe_donations\CustomSave::get_result_set($params);
        $list_table = new \adz_stripe_donations\Donor_List_Table();
        $list_table->items = $items;
        
        $list_table->prepare_items();
?>

<div class="wrap">
            <h2>Public Suggestions and Support for claims</h2>
        <form id="process-suggestions" method="POST">
                    <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <?php $list_table->display() ?>
        </form> 

        </div>

            <?
    }
    
    public function export_donors_csv() {
        // CSV download headers
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="NowPensionsFAQs.csv"');
        // TODO: add date filters as the first argument
        $items = $this->get_donor_list(array() , false);
        include_once ('../views/admin_export_donors.php');
        // die();
        
    }
}

