<?php

namespace adz_stripe_donations;

class Settings {
    
    function adz_stripe_register_plugin_settings() {
        register_setting('adz_stripe_settings', 'adz_stripe_settings', '\adz_stripe_donations\Settings::adz_stripe_settings_validate');
        add_settings_section('adz_stripe_settings', 'Orders settings', '\adz_stripe_donations\Settings::adz_stripe_options_section_stripe_settings', 'adz_stripe_settings_admin');
        
        add_settings_field('adz_stripe_public_key', 'Public Stripe Key', '\adz_stripe_donations\Settings::adz_stripe_public_key_form_input', 'adz_stripe_settings_admin', 'adz_stripe_settings');
        add_settings_field('adz_stripe_private_key', 'Private Stripe Key', '\adz_stripe_donations\Settings::adz_stripe_private_key_form_input', 'adz_stripe_settings_admin', 'adz_stripe_settings');
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
}
