// var payment  = require('jquery.payment');
var $ = jQuery;

console.log($);
console.log(adz_stripe_donations_vars);




function stripeResponseHandler(status, response) {
    console.log(status);

  if (response.error) {
    // Show the errors on the form
    console.log('ERROR');
  } 
    console.log(response);
}




$.getScript(adz_stripe_donations_vars.stripe_js_url, function(){
  console.log(Stripe);
  Stripe.setPublishableKey(adz_stripe_donations_vars.stripe_public_api_key);


  Stripe.card.createToken({
    number: '4242424242424242',
    cvc: '123',
    exp_month: '12',
    exp_year: '2016'
  }, stripeResponseHandler);


});

