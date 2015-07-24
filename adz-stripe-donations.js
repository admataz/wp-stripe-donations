(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
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


},{}]},{},[1]);
