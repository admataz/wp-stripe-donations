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
add_action( 'wp_enqueue_scripts', 'adz_stripe_enqueue_scripts' );
add_action('admin_init', 'adz_stripe_register_plugin_settings');


function adz_stripe_admin_menu(){
  add_menu_page("Manage Stripe Options", "Manage Stripe Optionss", 'manage_options', 'adz_stripe_options', 'adz_stripe_options_form');
}

function adz_stripe_options_page(){
  $plans = \Stripe\Plan::all(array("limit" => 3));
  print_r($plans);
}

function adz_stripe_enqueue_scripts(){
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

  wp_enqueue_script( 'adz_stripe_donations', plugins_url( 'adz-stripe-donations.js', __FILE__ ), array( 'jquery'), '1.0');
  $local_vars = array(
    'stripe_js_url' => 'https://js.stripe.com/v2/',
    'stripe_public_api_key'=>$existing_settings['stripe_public_key'],
    'plugin_path'=>plugins_url( '', __FILE__ ),
    'ajaxurl' => admin_url('admin-ajax.php')
    );

  wp_localize_script('adz_stripe_donations', 'adz_stripe_donations_vars', $local_vars);

}




function adz_stripe_register_plugin_settings() {
    register_setting('adz_stripe_settings', 'adz_stripe_settings', 'adz_stripe_settings_validate');
    add_settings_section('adz_stripe_settings', 'Orders settings', 'adz_stripe_options_section_stripe_settings', 'adz_stripe_settings_admin');
    
    add_settings_field('adz_stripe_public_key', 'Public Stripe Key',  'adz_stripe_public_key_form_input', 'adz_stripe_settings_admin', 'adz_stripe_settings');
    add_settings_field('adz_stripe_private_key', 'Private Stripe Key',  'adz_stripe_private_key_form_input', 'adz_stripe_settings_admin', 'adz_stripe_settings');
    
  }
  
  function adz_stripe_settings_validate($input) {
    $existing_settings = get_option('adz_stripe_settings', array(
      'stripe_public_key' => '',
      'stripe_private_key' => ''
    ));
    
    foreach ($input as $k => $v) {
      $input[$k] = trim($v);
    }
    
    if (!strlen($input['stripe_public_key'])) {
      add_settings_error('adz_stripe_public_key', 'publicKey', 'Public Key is required', 'error');
      $input['stripe_public_key'] = $existing_settings['stripe_public_key'];
    }

    if (!strlen($input['stripe_private_key'])) {
      add_settings_error('adz_stripe_private_key', 'privateKey', 'private Key is required', 'error');
      $input['stripe_private_key'] = $existing_settings['stripe_private_key'];
    }
    
    
    return $input;
  }
  
  function adz_stripe_options_section_stripe_settings() {
    return __('Fill in the absolute path on the server where the MDL service will fetch ORD files.');
  }
  
  function adz_stripe_public_key_form_input() {
    $existing_settings = get_option('adz_stripe_settings', array(
      'stripe_public_key' => '',
      'stripe_private_key' => ''
    ));
?>
    <input type="text" name="adz_stripe_settings[stripe_public_key]" value="<?php echo $existing_settings['stripe_public_key'] ?>"  size="100">

    <?php
  }
  
  function adz_stripe_private_key_form_input() {
    $existing_settings = get_option('adz_stripe_settings', array(
      'stripe_public_key' => '',
      'stripe_private_key' => ''
    ));
?>
    <input type="text" name="adz_stripe_settings[stripe_private_key]" value="<?php echo $existing_settings['stripe_private_key'] ?>"  size="100">

    <?php
  }
  
  function adz_stripe_options_form() {
?>
    <div class="wrap">
      <h2>Stripe Settings</h2>

      <form method="post" action="options.php">
          <?php
    settings_errors();
    // This print out all hidden setting fields
    settings_fields('adz_stripe_settings');
    do_settings_sections('adz_stripe_settings_admin');
?>
          <?php submit_button(); ?>
      </form>

    </div>
    <?php
  }

