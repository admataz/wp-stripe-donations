<?php
namespace adz_stripe_donations;

class DonationForm extends Base {
    var $formTemplate = '';
    private $stripe_js_url = 'https://js.stripe.com/v2/';
    private $stripe_public_key = '';
    private $stripe_private_key = '';
    var $nonce_id = 'legend of big jo and phantom 309';
    
    function __construct($slug = '') {
        $adz_stripe_settings = \adz_stripe_donations\Settings::get_instance('adz_stripe_settings');
        $this->stripe_public_key = $adz_stripe_settings->get('adz_stripe_public_key');
        $this->stripe_private_key = $adz_stripe_settings->get('adz_stripe_private_key');
        
        // add_action('wp_enqueue_scripts', array(
        //     $this,
        //     'enqueue_scripts'
        // ));

        
        
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
        $default_currency_symbol  = \Symfony\Component\Intl\Intl::getCurrencyBundle()->getCurrencySymbol(strtoupper($stripe_account->default_currency));

        $plans = \Stripe\Plan::all(array(
            "limit" => 10
        ));
        
        if (empty($this->formTemplate) || !file_exists($this->formTemplate)) {
            $this->set_form_template('');
        }
        
        $wp_nonce_field = wp_nonce_field($this->nonce_id, '_wpnonce',  true, false);
        
        ob_start();
        include $this->formTemplate;
        $return = ob_get_contents();
        ob_end_clean();
        
        return $return;
    }
    
    public function enqueue_scripts() {
        
        wp_enqueue_script('adz_stripe_donations', plugins_url('adz-stripe-donations.js', dirname(__FILE__)) , array(
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
  





  function save_giftaid_data($response) {
    if (isset($_POST['giftaid'])) {
      $customer_data = $_POST['customer'];
      foreach ($customer_data as $key => $value) {
        $customer_data[$key] = htmlspecialchars($value);
      }
      $customer_data['amount'] = htmlspecialchars($_POST['amount']);
      $customer_data['plan'] = htmlspecialchars($_POST['plan']);
      $stripe = $response['stripe'];
      file_put_contents('giftaid/' . $stripe->id . '.json', json_encode($customer_data));
    }
  }
  




  function generate_payload() {
    
    $payload = array(
      'description' => "Donation from " . $_POST['email'],
      // 'receipt_email' => $_POST['email'],
      'card' => $_POST['stripeToken'], // obtained with Stripe.js
      // 'statement_description' => 'ABF donation',
      'metadata' => array_merge(array(
        'email' => htmlspecialchars($_POST['email'])
      ))
    );


    if (in_array($_POST['plan'], array(
      'once_small',
      'once_medium',
      'once_large',
      'once_custom'
    ))) {
      $payload['amount'] = $_POST['amount'] * 100;
      $payload['currency'] = 'gbp';
    } else {
      $payload['plan'] = $_POST['plan'];
      $payload['email'] = $_POST['email'];
      if ($_POST['plan'] == 'monthly_custom') {
        $payload['quantity'] = $_POST['amount'];
      } else {
        $payload['quantity'] = 1;
      }
    }



    return $payload;
  }

  function handle_stripe_response($payload) {
    // Send the request to Stripe
    
    try {
      
      if (isset($payload['plan'])) {
        $stripe_response = \Stripe\Customer::create($payload);
        $response = array(
          'type' => 'monthly',
          'amount' => htmlspecialchars($_POST['amount']) . ' (monthly)',
          'email' => htmlspecialchars($_POST['email']) ,
          'success' => true,
          'error' => false,
          'stripe' => $stripe_response
        );
      } else {
        $stripe_response = \Stripe\Charge::create($payload);
        $response = array(
          'type' => 'once_off',
          'amount' => $stripe_response->amount/100,
          'success' => true,
          'error' => false,
          'stripe' => $stripe_response
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
  
  public function form_submit() {

    header("Content-type: application/json");
    $input_errors = $this->form_errors();
    if (!count($input_errors)) {
     \Stripe\Stripe::setApiKey($this->stripe_private_key);
      
      $payload = $this->generate_payload();
      $response = $this->handle_stripe_response($payload);
      if($response['success']){
        $this->save_giftaid_data($response);
      }
    } else {
      $response = array(
        'success' => false,
        'type' => 'ValidationError',
        'message' => 'There was an error with the input - please check the details',
        'validation_errors' => $input_errors,
        'error' => $err
      );
    }
    // TODO: turn this into an Exception and try/catch and generally be more test driven
    print(json_encode($response));
    exit();
  }

}
