/**
 * This is essentially an interface to Stripe, providing the ability to capture extra data in the WP system
 *
 * This can be attached to an embedded form on the page, or can be called via some other JS
 *
 * exposes a single initialiser function with the following signature:
 *
 * form_id, launchedCallback, submittedCallback
 *
 * 
 * 
 */

var $ = jQuery;

require( 'jquery.payment' );

/*global adz_stripe_donations_vars, Stripe*/

// the id for the dom element that will contain the stripe form
var stripe_donation_form = '#adz-stripe-donation-form';
var form_submitted_callback = null;
var form_response_callback = null;
var form_error_callback = null;

function testCreateToken() {
  Stripe.card.createToken( {
    number: '4242424242424242',
    cvc: '123',
    exp_month: '12',
    exp_year: '2016'
  }, stripeResponseHandler );
}


function onStripeJsLoaded() {
  Stripe.setPublishableKey( adz_stripe_donations_vars.stripe_public_api_key );
}


function setupInterface() {
  $( '#donation_custom_once' )
    .payment( 'restrictNumeric' );
  $( '#donation_custom_monthly' )
    .payment( 'restrictNumeric' );
}


function setupValidation() {
  $( '#donation_custom_once' )
    .payment( 'restrictNumeric' );
  $( '#donation_custom_monthly' )
    .payment( 'restrictNumeric' );



  $( '.cc-number' )
    .payment( 'formatCardNumber' );
  $( '.cc-cvc' )
    .payment( 'formatCardCVC' );
  $( '.exp-month' )
    .payment( 'restrictNumeric' );
  $( '.exp-year' )
    .payment( 'restrictNumeric' );

  $.fn.toggleInputError = function( erred ) {
    this.closest( '.form-row' )
      .toggleClass( 'has-error', erred );
    return this;
  };
}

function doValidation() {
  var cardType = $.payment.cardType( $( '.cc-number' )
    .val() );

  var giftaid_required = [ '#input-title', '#input-firstname', '#input-lastname', '#input-address1', '#input-postcode' ];

  $( '.cc-number' )
    .toggleInputError( !$.payment.validateCardNumber( $( '.cc-number' )
      .val() ) );
  $( '.cc-cvc' )
    .toggleInputError( !$.payment.validateCardCVC( $( '.cc-cvc' )
      .val(), cardType ) );
  $( '.cc-exp' )
    .toggleInputError( !Stripe.card.validateExpiry( $( '.exp-month' )
      .val(), $( '.exp-year' )
      .val() ) );

  $( '.validation' )
    .removeClass( 'text-danger text-success' );

  $( '.validation' )
    .addClass( $( '.has-error' )
      .length ? 'text-danger' : 'text-success' );

  if ( $( '#giftaidcheckbox:checked' )
    .length ) {
    $( '.giftaid-fields .required' )
      .each( function( i, itm ) {
        $( itm )
          .toggleInputError( !$( itm )
            .val()
            .length );
      } );
  }

  return $( '.has-error' )
    .length;
}


function onFormDataSaved( res ) {

  if(form_response_callback){
    form_response_callback(res);
  }

}


//curl 'http://localhost:8001/wp-admin/admin-ajax.php' -H 'Pragma: no-cache' -H 'Origin: http://localhost:8001' -H 'Accept-Encoding: gzip, deflate' -H 'Accept-Language: en-US,en;q=0.8' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.107 Safari/537.36' -H 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' -H 'Accept: */*' -H 'Cache-Control: no-cache' -H 'X-Requested-With: XMLHttpRequest' -H 'Cookie: wordpress_2960987f325c78c0786dfca7add156d6=admin%7C1437941179%7CvdnXhprdYGO5MLm4kt6DVJiElmJFWqRKmOW6LGdOnB4%7C6f1d686213db3cc55e7530bd88998af690cf65126bf0fb1821179b08b9657768; _jsuid=1249203764; _ga=GA1.1.692927670.1427208732; wordpress_test_cookie=WP+Cookie+check; wordpress_logged_in_2960987f325c78c0786dfca7add156d6=admin%7C1437941179%7CvdnXhprdYGO5MLm4kt6DVJiElmJFWqRKmOW6LGdOnB4%7Cc880f8998d0b68958f0f9761d13fe89e9920772c91af58f0d6e4b5760a4fca10; wp-settings-1=hidetb%3D0; wp-settings-time-1=1437827900' -H 'Connection: keep-alive' -H 'Referer: http://localhost:8001/?p=1' --data '_wpnonce=c47f4ec1ee&_wp_http_referer=%2F%3Fp%3D1&amount=&plan=monthly_large&custom_amount=&email=adam-test%40admataz.com&cc-number=4242+4242+4242+4242&cc-cvc=123&exp-month=12&exp-year=2016&customer%5Btitle%5D=&customer%5Bfirst_name%5D=&customer%5Blast-name%5D=&customer%5Baddress_1%5D=&customer%5Baddress_2%5D=&customer%5Baddress_3%5D=&customer%5Bpostcode%5D=&stripeToken=tok_16SYIs2etNjjAlsK0UnC6LKF&action=submit_donation' --compressed
function stripeResponseHandler( status, response ) {
  var $form = $( stripe_donation_form );
  if ( response.error ) {
    // Show the errors on the form
    $form.find( '.payment-errors' )
      .text( response.error.message );
    $form.find( 'button' )
      .prop( 'disabled', false );
  } else {
    // response contains id and card, which contains additional card details
    var token = response.id;
    // Insert the token into the form so it gets submitted to the server
    $form.append( $( '<input type="hidden" name="stripeToken" />' )
      .val( token ) );

    // for the WP ajax hook
    $form.append( $( '<input type="hidden" name="action" value="submit_donation" />' ) );

    $.post(
      adz_stripe_donations_vars.ajaxurl,
      $form.serialize(),
      onFormDataSaved
    );
  }
}

function onFormSubmit( event ) {

  var $form = $( stripe_donation_form );


  event.preventDefault();
  if ( doValidation() ) {
    if(form_error_callback){
      form_error_callback();
    }
    return false;
  }


  // Disable the submit button to prevent repeated clicks
  $form.find( 'button' )
    .prop( 'disabled', true );

  Stripe.card.createToken( $form, stripeResponseHandler );

  if(form_submitted_callback){
    form_submitted_callback();
  }
  
  // Prevent the form from submitting with the default action
  return false;
}

function init( form_id, launchedCallback, submittedCallback, responseCallback, errorCallback ) {
  var formObj;
  if ( form_id ) {
    stripe_donation_form = form_id;
  }

  if(submittedCallback) {
    form_submitted_callback = submittedCallback;
  }

    if(responseCallback) {
    form_response_callback = responseCallback;
  }

  if(errorCallback) {
    form_error_callback = errorCallback;
  }

  formObj = $( stripe_donation_form );

  setupInterface();
  setupValidation();
  formObj.submit( onFormSubmit );

  $.getScript( adz_stripe_donations_vars.stripe_js_url, function() {
    onStripeJsLoaded()
    if ( launchedCallback ) {
      launchedCallback();
    }
  } );


  return formObj;
  
}


module.exports = init;


// $(init);
