<?php
/**
 *
 * Plugin Name: The admataz Stripe Donations manager
 * Version: 1.0.0
 * Description: Provide common Stripe management and data functions from within WP admin
 * Author: Adam Davis
 * Author URI: http://admataz.com
 * Plugin URI: http://admataz.com
 * Text Domain: adz-stripe
 * Domain Path: /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/admataz/wp-stripe-donations
 */

require 'vendor/autoload.php';
/**
 * Callback for the admin menu items
 */
function adz_stripe_admin_menu() {
    $adz_stripe_settings = \adz_stripe_donations\Settings::get_instance('adz_stripe_settings');
    $reports = \adz_stripe_donations\Reports::get_instance('adz_stripe_donor_reports');


    add_menu_page("Manage Stripe Options", "Manage Stripe Options", 'manage_options', 'adz_stripe_options', 'adz_stripe_admin_landing_page');
    add_submenu_page('adz_stripe_options', 'Stripe Settings', 'Stripe Settings', 'manage_options', $adz_stripe_settings->get_slug() , array(
        $adz_stripe_settings,
        'options_form'
    ));
    add_submenu_page('adz_stripe_options', 'Donation reports', 'Donation Reports', 'manage_options', $reports->get_slug() , array(
        $reports,
        'list_donors'
    ));
};

function adz_stripe_donations_activate() {
    \adz_stripe_donations\CustomSave::activate();
}

function adz_stripe_admin_landing_page() {
    echo '<h1>settings landing page</h1>';
}

function adz_stripe_donations_default_form() {
    $adz_stripe_donation_form = \adz_stripe_donations\DonationForm::get_instance('adz_stripe_donations_form');
    return $adz_stripe_donation_form->show_form();
}

function adz_stripe_donations_form_submit() {
    $adz_stripe_donation_form = \adz_stripe_donations\DonationForm::get_instance('adz_stripe_donations_form');
    $adz_stripe_donation_form->form_submit();
}

function adz_stripe_donations_stripe_webhook_listener() {
    if(isset($_GET['stripe_webhook_event']) && $_GET['stripe_webhook_event'] == 'charge.create'){
        error_log('LOG:  - Stripe endpoint activated');
        $adz_stripe_donation_form = \adz_stripe_donations\DonationForm::get_instance('adz_stripe_donations_form');
        $adz_stripe_donation_form->stripe_webhook_charge_endpoint();
    }
}


function adz_stripe_donations_donations_export_csv(){

    if(isset($_GET['donation_report_export']) && $_GET['donation_report_export'] == 'csv' && current_user_can('manage_options')){
            // admin_export_donations.csv
            $reports = \adz_stripe_donations\Reports::get_instance('adz_stripe_donations_form');
            $reports->export_donors_csv();
    }

}


add_shortcode('adz_stripe_donations_default_form', 'adz_stripe_donations_default_form');
add_action('admin_menu', 'adz_stripe_admin_menu');
add_action('wp_ajax_submit_donation', 'adz_stripe_donations_form_submit');
add_action('wp_ajax_nopriv_submit_donation', 'adz_stripe_donations_form_submit');
add_action('init', 'adz_stripe_donations_stripe_webhook_listener');
add_action( 'init', 'adz_stripe_donations_donations_export_csv' );


register_activation_hook(__FILE__, 'adz_stripe_donations_activate');

