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
$adz_stripe_enqueue_scripts = function () use ($adz_stripe_settings) {
    // potentially make the loading of this js conditional - but we may want it available to a widget that can appear on any page
    // global $post;
    //   $post_slug=$post->post_name;
    
    //   if($post_slug != 'donate'){
    //     return;
    //   }
    
    wp_enqueue_script('adz_stripe_donations', plugins_url('adz-stripe-donations.js', __FILE__) , array(
        'jquery'
    ) , '1.0');
    $local_vars = array(
        'stripe_js_url' => 'https://js.stripe.com/v2/',
        'stripe_public_api_key' => $adz_stripe_settings->get('adz_stripe_public_key'),
        'plugin_path' => plugins_url('', __FILE__) ,
        'ajaxurl' => admin_url('admin-ajax.php')
    );
    
    wp_localize_script('adz_stripe_donations', 'adz_stripe_donations_vars', $local_vars);
};



function adz_stripe_admin_landing_page() {
  echo 'settings landing page';
}


add_action('wp_enqueue_scripts', $adz_stripe_enqueue_scripts);
add_action('admin_menu', $adz_stripe_admin_menu);

