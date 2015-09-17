<?php

namespace adz_stripe_donations;
use \Exception as Exception;




class DonationForm extends Base {
    var $formTemplate = '';
    private $stripe_js_url = 'https://js.stripe.com/v2/';
    private $stripe_public_key = '';
    private $stripe_private_key = '';
    var $nonce_id = 'legend of big jo and phantom 309';
    static $zero_decimal_currencies = array(
        'bif',
        'clp',
        'djf',
        'gnf',
        'jpy',
        'kmf',
        'krw',
        'mga',
        'pyg',
        'rwf',
        'vnd',
        'vuv',
        'xaf',
        'xof',
        'xpf'
    );
    
    function __construct($slug = '') {
        $adz_stripe_settings = \adz_stripe_donations\Settings::get_instance('adz_stripe_settings');
        $this->stripe_public_key = $adz_stripe_settings->get('adz_stripe_public_key');
        $this->stripe_private_key = $adz_stripe_settings->get('adz_stripe_private_key');
        parent::__construct($slug);
    }
    
    public function set_form_template($path = '') {
        if (!$path) {
            $path = realpath(dirname(__FILE__) . '/../views/donationFormView.php');
        }
        $this->formTemplate = $path;
    }
    
    public function show_form() {
        $this->enqueue_scripts();
        $output = $this->construct_form();
        echo $output;
    }
    
    public function construct_form() {
        
        \Stripe\Stripe::setApiKey($this->stripe_private_key);
        $stripe_account = \Stripe\Account::retrieve();
        $default_currency_symbol = \Symfony\Component\Intl\Intl::getCurrencyBundle()->getCurrencySymbol(strtoupper($stripe_account->default_currency));
        
        $plans = \Stripe\Plan::all(array(
            "limit" => 10
        ));
        
        if (empty($this->formTemplate) || !file_exists($this->formTemplate)) {
            $this->set_form_template('');
        }
        
        $wp_nonce_field = wp_nonce_field($this->nonce_id, '_wpnonce', true, false);
        
        $donate['defaults']['currency'] = $stripe_account->default_currency;
        $currencies = $stripe_account->currencies_supported;
        $index = array_search($stripe_account->default_currency, $currencies);
        array_splice($currencies, $index, 1);
        array_unshift($currencies, $stripe_account->default_currency);
        
        ob_start();
        include $this->formTemplate;
        $return = ob_get_contents();
        ob_end_clean();
        
        return $return;
    }
    
    public function enqueue_scripts() {
        
        wp_enqueue_script('adz_stripe_donations', plugins_url('js/adz-stripe-donations.js', dirname(__FILE__)) , array(
            'jquery'
        ) , '1.0');
        $local_vars = array(
            'stripe_js_url' => $this->stripe_js_url,
            'stripe_public_api_key' => $this->stripe_public_key,
            'plugin_path' => plugins_url('', dirname(__FILE__)) ,
            'ajaxurl' => admin_url('admin-ajax.php')
        );
        wp_localize_script('adz_stripe_donations', 'adz_stripe_donations_vars', $local_vars);
    }
    
    function valid_email_address($mail) {
        return (bool)filter_var($mail, FILTER_VALIDATE_EMAIL);
    }
    
    function form_errors() {
        $errors = array();
        if (!$this->valid_email_address($_POST['email'])) {
            $errors[] = 'Please enter a valid email address';
        }
        if (empty($_POST['stripeToken'])) {
            $errors[] = 'Your credit card was not verified. Please try again. ';
        }
        if (empty($_POST['plan'])) {
            $errors[] = 'A plan was not selected';
        }
        
        if ($_POST['plan'] == 'once_custom') {
            if ($_POST['amount'] < 50) {
                $errors[] = 'Less than 50p? We require a higher amount to process your donation';
            }
        }
        
        return $errors;
    }
    
    function save_extra_data($response) {
        // \Stripe\Stripe::setApiKey($this->stripe_private_key);
        
        $stripe = $response['stripe'];
        
        $customer_data = array();
        
        $extra_fields = array();
        
        foreach ($_POST['customer'] as $key => $value) {
            $extra_fields[$key] = htmlspecialchars($value);
        }
        
        $customer_data['extra_fields'] = json_encode($extra_fields);
        
        $customer_data['plan'] = htmlspecialchars($_POST['plan']);
        $customer_data['created'] = $response['created'];
        $customer_data['email'] = htmlspecialchars($_POST['email']);
        $customer_data['name'] = htmlspecialchars($_POST['cc-name']);
        
        if (isset($_POST['giftaid'])) {
            $customer_data['giftaid'] = 1;
        } 
        else {
            $customer_data['giftaid'] = 0;
        }
        // no longer saving the charge in this step  - as it doesn't work for repeated payments - going to rely on Stripe webhooks
        if ($stripe['object'] == 'customer') {
            $customer_data['stripe_type'] = 'customer';
            $customer_data['stripe_id'] = $stripe['id'];
        } 
        else {
            $customer_data['stripe_type'] = 'charge';
            $customer_data['stripe_id'] = $stripe['id'];
        }
        \adz_stripe_donations\CustomSave::save_donor_detail($customer_data);
    }
    
    function generate_payload() {
        
        $payload = array(
            'description' => "Donation from " . $_POST['email'],
            'card' => $_POST['stripeToken'],
            'metadata' => array_merge(array(
                'email' => htmlspecialchars($_POST['email'])
            ))
        );
        
        \Stripe\Stripe::setApiKey($this->stripe_private_key);
        
        try {
            $plan = \Stripe\Plan::retrieve($_POST['plan']);
        }
        catch(\Stripe\Error\Base $e) {
            $plan = null;
        }
        catch(Exception $e) {
            $plan = null;
        }
        
        if ($plan) {
            $payload['plan'] = $_POST['plan'];
            $payload['email'] = $_POST['email'];
            if ($_POST['plan'] == 'monthly_custom') {
                $payload['quantity'] = $_POST['custom_amount'];
            } 
            else {
                $payload['quantity'] = 1;
            }
        } 
        else {
            $payload['currency'] = $_POST['currency'];
            $payload['receipt_email'] = $_POST['email'];
            if (!in_array(strtolower($payload['currency']) , self::zero_decimal_currencies)) {
                $payload['amount'] = $_POST['amount'] * 100;
            } 
            else {
                $payload['amount'] = $_POST['amount'];
            }
        }
        
        return $payload;
    }
    
    function handle_stripe_response($payload) {
        // Send the request to Stripe
        try {
            if (isset($payload['plan'])) {
                $stripe_response = \Stripe\Customer::create($payload);
                $stripe = $stripe_response->jsonSerialize();
                // error_log(var_export($stripe_response->jsonSerialize(), 1));
                $response = array(
                    'type' => 'monthly',
                    'amount' => htmlspecialchars($stripe['subscriptions']['data'][0]['plan']['amount']) . ' (monthly)',
                    'email' => htmlspecialchars($_POST['email']) ,
                    'success' => true,
                    'error' => false,
                    'stripe' => $stripe,
                    'created' => $stripe['created']
                );
            } 
            else {
                $stripe_response = \Stripe\Charge::create($payload);
                $stripe = $stripe_response->jsonSerialize();
                if (!in_array(strtolower($payload['currency']) , self::zero_decimal_currencies)) {
                    $amount = $stripe['amount'] / 100;
                } 
                else {
                    $amount = $stripe['amount'];
                }
                
                $response = array(
                    'type' => 'once_off',
                    'amount' => $amount,
                    'success' => true,
                    'error' => false,
                    'stripe' => $stripe,
                    'created' => $stripe['created']
                );
            }
        }
        
        catch(\Stripe\Error\Card $e) {
            // Since it's a decline, Stripe_CardError will be caught
            $body = $e->getJsonBody();
            $err = $body['error'];
            $response = array(
                'success' => false,
                'type' => 'Card',
                'message' => $err['message'],
                'error' => $err
            );
        }
        catch(\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            $body = $e->getJsonBody();
            $err = $body['error'];
            $response = array(
                'success' => false,
                'type' => 'InvalidRequest',
                'message' => $err['message'],
                'error' => $err
            );
        }
        catch(\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            $body = $e->getJsonBody();
            $err = $body['error'];
            $response = array(
                'success' => false,
                'type' => 'Authentication',
                'message' => $err['message'],
                'error' => $err
            );
        }
        catch(\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            $body = $e->getJsonBody();
            $err = $body['error'];
            $response = array(
                'success' => false,
                'type' => 'ApiConnection',
                'message' => $err['message'],
                'error' => $err
            );
        }
        catch(\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            $body = $e->getJsonBody();
            $err = $body['error'];
            $response = array(
                'success' => false,
                'type' => 'Base',
                'message' => $err['message'],
                'error' => $err
            );
        }
        catch(Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $response = array(
                'success' => false,
                'type' => 'Exception',
                'message' => $err['message'],
                'error' => $e
            );
        }
        return $response;
    }
    
    public function save_stripe_charge($stripe) {
        \Stripe\Stripe::setApiKey($this->stripe_private_key);
        // $stripe = $stripe_response->jsonSerialize();
        $charge_obj = $stripe['data']['object'];
        $charge_data = array();
        $charge_data['stripe_id'] = $charge_obj['id'];
        $charge_data['currency'] = $charge_obj['currency'];
        $charge_data['amount'] = $charge_obj['amount'];
        $charge_data['created'] = $charge_obj['created'];
        $bt = \Stripe\BalanceTransaction::retrieve($charge_obj['balance_transaction']);
        $charge_data['amount_converted'] = $bt->amount;

        if ($charge_obj['source']['customer']) {
            $stripe_id = $charge_obj['source']['customer'];
        } 
        else {
            $stripe_id = $charge_obj['id'];
        }
        
        \adz_stripe_donations\CustomSave::save_stripe_charge_detail($charge_data, $stripe_id);
    }
    /**
     * call this on hook_init
     */
    public function stripe_webhook_charge_endpoint() {
        
        // if (isset($_GET['wps-listener']) && $_GET['wps-listener'] == 'charge.succeeded') {
            // we will process the events here
            
            // Set your secret key: remember to change this to your live secret key in production
            // See your keys here https://dashboard.stripe.com/account/apikeys
            \Stripe\Stripe::setApiKey($this->stripe_private_key);
            // Retrieve the request's body and parse it as JSON
            $input = file_get_contents("php://input");


            $charge_json = json_decode($input, TRUE);
            // Do something with $event_json
            $this->save_stripe_charge($charge_json);
            
            http_response_code(500); // PHP 5.4 or greater
            return;
        // }
    }
    
    public function form_submit() {
        
        header("Content-type: application/json");
        
        $input_errors = $this->form_errors();
        
        if (!count($input_errors)) {
            \Stripe\Stripe::setApiKey($this->stripe_private_key);
            $payload = $this->generate_payload();
            $response = $this->handle_stripe_response($payload);
            if ($response['success']) {
                $this->save_extra_data($response);
            }
        } 
        else {
            $response = array(
                'success' => false,
                'type' => 'ValidationError',
                'message' => 'There was an error with the input - please check the details',
                'validation_errors' => $input_errors,
                'error' => $err
            );
        }
        // TODO: turn this into an Exception and try/catch and generally be more test driven
        // $response->stripe = $response->stripe->jsonSerialize();
        print (json_encode($response));
        exit();
    }
}
