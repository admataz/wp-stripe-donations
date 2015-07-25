<?php

namespace adz_stripe_donations;

class Settings extends Base {
   

    function __construct($slug='') {
        //
        add_action('admin_init', array(
            $this,
            'register_plugin_settings'
        ));

        parent::__construct($slug);
    }
    
    
    
    function register_plugin_settings() {
        register_setting('adz_stripe_settings', 'adz_stripe_settings', array(
            $this,
            'settings_validate'
        ));
        add_settings_section('adz_stripe_settings', 'API settings', array(
            $this,
            'options_section_stripe_settings'
        ) , 'adz_stripe_settings_admin');
        
        
        add_settings_field('adz_stripe_private_key', 'Stripe Secret Key', array(
            $this,
            'private_key_form_input'
        ) , 'adz_stripe_settings_admin', 'adz_stripe_settings');

        add_settings_field('adz_stripe_public_key', 'Stripe Publishable Key', array(
            $this,
            'public_key_form_input'
        ) , 'adz_stripe_settings_admin', 'adz_stripe_settings');
    }
    
    function settings_validate($input) {

        foreach ($input as $k => $v) {
            $input[$k] = trim($v);
        }
        
        if (!strlen($input['adz_stripe_public_key'])) {
            add_settings_error('adz_stripe_public_key', 'publicKey', 'Public Key is required', 'error');
            $input['adz_stripe_public_key'] = $this->get('adz_stripe_public_key');
        }
        
        if (!strlen($input['adz_stripe_private_key'])) {
            add_settings_error('adz_stripe_private_key', 'privateKey', 'private Key is required', 'error');
            $input['adz_stripe_private_key'] = $this->get('adz_stripe_private_key');
        }
        
        return $input;
    }
    
    function options_section_stripe_settings() {
        return __('Fill in the absolute path on the server where the MDL service will fetch ORD files.');
    }
    
    function public_key_form_input() {
?>
    <input type="text" name="adz_stripe_settings[adz_stripe_public_key]" value="<?php echo $this->get('adz_stripe_public_key') ?>"  size="100">

    <?php
    }
    
    function private_key_form_input() {
?>
    <input type="text" name="adz_stripe_settings[adz_stripe_private_key]" value="<?php echo $this->get('adz_stripe_private_key') ?>"  size="100">

    <?php
    }
    
    function options_form() {
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
