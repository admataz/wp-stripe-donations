// var payment  = require('jquery.payment');
// var $ = jQuery;

/*global adz_stripe_donations_vars, Stripe*/
function stripeResponseHandler(status, response) {
  if (response.error) {
    
  }
    // console.log(response);
}


function testCreateToken(){
  Stripe.setPublishableKey(adz_stripe_donations_vars.stripe_public_api_key);
  Stripe.card.createToken({
    number: '4242424242424242',
    cvc: '123',
    exp_month: '12',
    exp_year: '2016'
  }, stripeResponseHandler);
}


function onStripeJsLoaded(){

}

function init(){
  $.getScript(adz_stripe_donations_vars.stripe_js_url, onStripeJsLoaded);
}

