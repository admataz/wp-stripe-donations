<?php
/*
Plugin Name: The admataz Stripe Donations manager
Version: 1.0.0
Description: Provide common Stripe management and data functions from within WP admin
Author: Adam Davis
Author URI: http://admataz.com
Plugin URI: http://admataz.com
Text Domain: adz-stripe
Domain Path: /languages
*/

require 'vendor/autoload.php';

$adz_stripe_settings = \adz_stripe_donations\Settings::get_instance('adz_stripe_settings');

/**
 * Callback using closure for the admin menu items
 */
$adz_stripe_admin_menu = function() use($adz_stripe_settings) {
    add_menu_page("Manage Stripe Options", "Manage Stripe Options", 'manage_options', 'adz_stripe_options', 'adz_stripe_admin_landing_page');
    add_submenu_page('adz_stripe_options', 'Stripe Settings', 'Stripe Settings', 'manage_options', $adz_stripe_settings->get_slug() , array(
        $adz_stripe_settings,
        'options_form'
    ));
};

/**
 * Callback using closure for the scripts
 */
$adz_stripe_enqueue_scripts = function() use ($adz_stripe_settings) {
    // potentially make the loading of this js conditional - but we may want it available to a widget that can appear on any page
    // global $post;
    //   $post_slug=$post->post_name;
    
    //   if($post_slug != 'donate'){
    //     return;
    //   }
    

};

function adz_stripe_donations_activate(){
    \adz_stripe_donations\CustomSave::activate();
}


function adz_stripe_admin_landing_page() {
  echo '<h1>settings landing page</h1>';
}

function adz_stripe_donations_default_form(){
    $adz_stripe_donation_form = \adz_stripe_donations\DonationForm::get_instance('adz_stripe_donations_form');
    return  $adz_stripe_donation_form->show_form();
}

function adz_stripe_donations_form_submit(){
    $adz_stripe_donation_form = \adz_stripe_donations\DonationForm::get_instance('adz_stripe_donations_form');
    $adz_stripe_donation_form->form_submit();
}

add_shortcode('adz_stripe_donations_default_form', 'adz_stripe_donations_default_form');
add_action('admin_menu', $adz_stripe_admin_menu);
add_action( 'wp_ajax_submit_donation', 'adz_stripe_donations_form_submit');
add_action( 'wp_ajax_nopriv_submit_donation', 'adz_stripe_donations_form_submit');

register_activation_hook( __FILE__, 'adz_stripe_donations_activate' );

