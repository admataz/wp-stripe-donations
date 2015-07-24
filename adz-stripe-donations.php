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

add_action('admin_menu', 'adz_stripe_admin_menu');
add_action('wp_enqueue_scripts', 'adz_stripe_enqueue_scripts');
add_action('admin_init', array( '\adz_stripe_donations\Settings', 'adz_stripe_register_plugin_settings'));

function adz_stripe_admin_menu() {
    add_menu_page("Manage Stripe Options", "Manage Stripe Options", 'manage_options', 'adz_stripe_options', array( '\adz_stripe_donations\Settings', 'adz_stripe_options_form'));
}


function adz_stripe_enqueue_scripts() {
    // potentially make the loading of this js conditional - but we may want it available to a widget that can appear on any page
    // global $post;
    //   $post_slug=$post->post_name;
    
    //   if($post_slug != 'donate'){
    //     return;
    //   }
    
    $existing_settings = get_option('adz_stripe_settings', array(
        'stripe_public_key' => '',
        'stripe_private_key' => ''
    ));
    
    wp_enqueue_script('adz_stripe_donations', plugins_url('adz-stripe-donations.js', __FILE__) , array(
        'jquery'
    ) , '1.0');
    $local_vars = array(
        'stripe_js_url' => 'https://js.stripe.com/v2/',
        'stripe_public_api_key' => $existing_settings['stripe_public_key'],
        'plugin_path' => plugins_url('', __FILE__) ,
        'ajaxurl' => admin_url('admin-ajax.php')
    );
    
    wp_localize_script('adz_stripe_donations', 'adz_stripe_donations_vars', $local_vars);
}






