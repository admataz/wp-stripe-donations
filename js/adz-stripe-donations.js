(function(f){if(typeof exports==="object"&&typeof module!=="undefined"){module.exports=f()}else if(typeof define==="function"&&define.amd){define([],f)}else{var g;if(typeof window!=="undefined"){g=window}else if(typeof global!=="undefined"){g=global}else if(typeof self!=="undefined"){g=self}else{g=this}g.AdzStripeDonations = f()}})(function(){var define,module,exports;return (function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){

var donationForm = require('./lib/donationForm');


module.exports = {
  donationForm: donationForm
}
},{"./lib/donationForm":2}],2:[function(require,module,exports){
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

},{"jquery.payment":3}],3:[function(require,module,exports){
// Generated by CoffeeScript 1.7.1
(function() {
  var cardFromNumber, cardFromType, cards, defaultFormat, formatBackCardNumber, formatBackExpiry, formatCardNumber, formatExpiry, formatForwardExpiry, formatForwardSlashAndSpace, hasTextSelected, luhnCheck, reFormatCVC, reFormatCardNumber, reFormatExpiry, reFormatNumeric, restrictCVC, restrictCardNumber, restrictExpiry, restrictNumeric, setCardType,
    __slice = [].slice,
    __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  $.payment = {};

  $.payment.fn = {};

  $.fn.payment = function() {
    var args, method;
    method = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
    return $.payment.fn[method].apply(this, args);
  };

  defaultFormat = /(\d{1,4})/g;

  $.payment.cards = cards = [
    {
      type: 'visaelectron',
      pattern: /^4(026|17500|405|508|844|91[37])/,
      format: defaultFormat,
      length: [16],
      cvcLength: [3],
      luhn: true
    }, {
      type: 'maestro',
      pattern: /^(5(018|0[23]|[68])|6(39|7))/,
      format: defaultFormat,
      length: [12, 13, 14, 15, 16, 17, 18, 19],
      cvcLength: [3],
      luhn: true
    }, {
      type: 'forbrugsforeningen',
      pattern: /^600/,
      format: defaultFormat,
      length: [16],
      cvcLength: [3],
      luhn: true
    }, {
      type: 'dankort',
      pattern: /^5019/,
      format: defaultFormat,
      length: [16],
      cvcLength: [3],
      luhn: true
    }, {
      type: 'visa',
      pattern: /^4/,
      format: defaultFormat,
      length: [13, 16],
      cvcLength: [3],
      luhn: true
    }, {
      type: 'mastercard',
      pattern: /^(5[0-5]|2[2-7])/,
      format: defaultFormat,
      length: [16],
      cvcLength: [3],
      luhn: true
    }, {
      type: 'amex',
      pattern: /^3[47]/,
      format: /(\d{1,4})(\d{1,6})?(\d{1,5})?/,
      length: [15],
      cvcLength: [3, 4],
      luhn: true
    }, {
      type: 'dinersclub',
      pattern: /^3[0689]/,
      format: /(\d{1,4})(\d{1,6})?(\d{1,4})?/,
      length: [14],
      cvcLength: [3],
      luhn: true
    }, {
      type: 'discover',
      pattern: /^6([045]|22)/,
      format: defaultFormat,
      length: [16],
      cvcLength: [3],
      luhn: true
    }, {
      type: 'unionpay',
      pattern: /^(62|88)/,
      format: defaultFormat,
      length: [16, 17, 18, 19],
      cvcLength: [3],
      luhn: false
    }, {
      type: 'jcb',
      pattern: /^35/,
      format: defaultFormat,
      length: [16],
      cvcLength: [3],
      luhn: true
    }
  ];

  cardFromNumber = function(num) {
    var card, _i, _len;
    num = (num + '').replace(/\D/g, '');
    for (_i = 0, _len = cards.length; _i < _len; _i++) {
      card = cards[_i];
      if (card.pattern.test(num)) {
        return card;
      }
    }
  };

  cardFromType = function(type) {
    var card, _i, _len;
    for (_i = 0, _len = cards.length; _i < _len; _i++) {
      card = cards[_i];
      if (card.type === type) {
        return card;
      }
    }
  };

  luhnCheck = function(num) {
    var digit, digits, odd, sum, _i, _len;
    odd = true;
    sum = 0;
    digits = (num + '').split('').reverse();
    for (_i = 0, _len = digits.length; _i < _len; _i++) {
      digit = digits[_i];
      digit = parseInt(digit, 10);
      if ((odd = !odd)) {
        digit *= 2;
      }
      if (digit > 9) {
        digit -= 9;
      }
      sum += digit;
    }
    return sum % 10 === 0;
  };

  hasTextSelected = function($target) {
    var _ref;
    if (($target.prop('selectionStart') != null) && $target.prop('selectionStart') !== $target.prop('selectionEnd')) {
      return true;
    }
    if ((typeof document !== "undefined" && document !== null ? (_ref = document.selection) != null ? _ref.createRange : void 0 : void 0) != null) {
      if (document.selection.createRange().text) {
        return true;
      }
    }
    return false;
  };

  reFormatNumeric = function(e) {
    return setTimeout(function() {
      var $target, value;
      $target = $(e.currentTarget);
      value = $target.val();
      value = value.replace(/\D/g, '');
      return $target.val(value);
    });
  };

  reFormatCardNumber = function(e) {
    return setTimeout(function() {
      var $target, value;
      $target = $(e.currentTarget);
      value = $target.val();
      value = $.payment.formatCardNumber(value);
      return $target.val(value);
    });
  };

  formatCardNumber = function(e) {
    var $target, card, digit, length, re, upperLength, value;
    digit = String.fromCharCode(e.which);
    if (!/^\d+$/.test(digit)) {
      return;
    }
    $target = $(e.currentTarget);
    value = $target.val();
    card = cardFromNumber(value + digit);
    length = (value.replace(/\D/g, '') + digit).length;
    upperLength = 16;
    if (card) {
      upperLength = card.length[card.length.length - 1];
    }
    if (length >= upperLength) {
      return;
    }
    if (($target.prop('selectionStart') != null) && $target.prop('selectionStart') !== value.length) {
      return;
    }
    if (card && card.type === 'amex') {
      re = /^(\d{4}|\d{4}\s\d{6})$/;
    } else {
      re = /(?:^|\s)(\d{4})$/;
    }
    if (re.test(value)) {
      e.preventDefault();
      return setTimeout(function() {
        return $target.val(value + ' ' + digit);
      });
    } else if (re.test(value + digit)) {
      e.preventDefault();
      return setTimeout(function() {
        return $target.val(value + digit + ' ');
      });
    }
  };

  formatBackCardNumber = function(e) {
    var $target, value;
    $target = $(e.currentTarget);
    value = $target.val();
    if (e.which !== 8) {
      return;
    }
    if (($target.prop('selectionStart') != null) && $target.prop('selectionStart') !== value.length) {
      return;
    }
    if (/\d\s$/.test(value)) {
      e.preventDefault();
      return setTimeout(function() {
        return $target.val(value.replace(/\d\s$/, ''));
      });
    } else if (/\s\d?$/.test(value)) {
      e.preventDefault();
      return setTimeout(function() {
        return $target.val(value.replace(/\d$/, ''));
      });
    }
  };

  reFormatExpiry = function(e) {
    return setTimeout(function() {
      var $target, value;
      $target = $(e.currentTarget);
      value = $target.val();
      value = $.payment.formatExpiry(value);
      return $target.val(value);
    });
  };

  formatExpiry = function(e) {
    var $target, digit, val;
    digit = String.fromCharCode(e.which);
    if (!/^\d+$/.test(digit)) {
      return;
    }
    $target = $(e.currentTarget);
    val = $target.val() + digit;
    if (/^\d$/.test(val) && (val !== '0' && val !== '1')) {
      e.preventDefault();
      return setTimeout(function() {
        return $target.val("0" + val + " / ");
      });
    } else if (/^\d\d$/.test(val)) {
      e.preventDefault();
      return setTimeout(function() {
        return $target.val("" + val + " / ");
      });
    }
  };

  formatForwardExpiry = function(e) {
    var $target, digit, val;
    digit = String.fromCharCode(e.which);
    if (!/^\d+$/.test(digit)) {
      return;
    }
    $target = $(e.currentTarget);
    val = $target.val();
    if (/^\d\d$/.test(val)) {
      return $target.val("" + val + " / ");
    }
  };

  formatForwardSlashAndSpace = function(e) {
    var $target, val, which;
    which = String.fromCharCode(e.which);
    if (!(which === '/' || which === ' ')) {
      return;
    }
    $target = $(e.currentTarget);
    val = $target.val();
    if (/^\d$/.test(val) && val !== '0') {
      return $target.val("0" + val + " / ");
    }
  };

  formatBackExpiry = function(e) {
    var $target, value;
    $target = $(e.currentTarget);
    value = $target.val();
    if (e.which !== 8) {
      return;
    }
    if (($target.prop('selectionStart') != null) && $target.prop('selectionStart') !== value.length) {
      return;
    }
    if (/\d\s\/\s$/.test(value)) {
      e.preventDefault();
      return setTimeout(function() {
        return $target.val(value.replace(/\d\s\/\s$/, ''));
      });
    }
  };

  reFormatCVC = function(e) {
    return setTimeout(function() {
      var $target, value;
      $target = $(e.currentTarget);
      value = $target.val();
      value = value.replace(/\D/g, '').slice(0, 4);
      return $target.val(value);
    });
  };

  restrictNumeric = function(e) {
    var input;
    if (e.metaKey || e.ctrlKey) {
      return true;
    }
    if (e.which === 32) {
      return false;
    }
    if (e.which === 0) {
      return true;
    }
    if (e.which < 33) {
      return true;
    }
    input = String.fromCharCode(e.which);
    return !!/[\d\s]/.test(input);
  };

  restrictCardNumber = function(e) {
    var $target, card, digit, value;
    $target = $(e.currentTarget);
    digit = String.fromCharCode(e.which);
    if (!/^\d+$/.test(digit)) {
      return;
    }
    if (hasTextSelected($target)) {
      return;
    }
    value = ($target.val() + digit).replace(/\D/g, '');
    card = cardFromNumber(value);
    if (card) {
      return value.length <= card.length[card.length.length - 1];
    } else {
      return value.length <= 16;
    }
  };

  restrictExpiry = function(e) {
    var $target, digit, value;
    $target = $(e.currentTarget);
    digit = String.fromCharCode(e.which);
    if (!/^\d+$/.test(digit)) {
      return;
    }
    if (hasTextSelected($target)) {
      return;
    }
    value = $target.val() + digit;
    value = value.replace(/\D/g, '');
    if (value.length > 6) {
      return false;
    }
  };

  restrictCVC = function(e) {
    var $target, digit, val;
    $target = $(e.currentTarget);
    digit = String.fromCharCode(e.which);
    if (!/^\d+$/.test(digit)) {
      return;
    }
    if (hasTextSelected($target)) {
      return;
    }
    val = $target.val() + digit;
    return val.length <= 4;
  };

  setCardType = function(e) {
    var $target, allTypes, card, cardType, val;
    $target = $(e.currentTarget);
    val = $target.val();
    cardType = $.payment.cardType(val) || 'unknown';
    if (!$target.hasClass(cardType)) {
      allTypes = (function() {
        var _i, _len, _results;
        _results = [];
        for (_i = 0, _len = cards.length; _i < _len; _i++) {
          card = cards[_i];
          _results.push(card.type);
        }
        return _results;
      })();
      $target.removeClass('unknown');
      $target.removeClass(allTypes.join(' '));
      $target.addClass(cardType);
      $target.toggleClass('identified', cardType !== 'unknown');
      return $target.trigger('payment.cardType', cardType);
    }
  };

  $.payment.fn.formatCardCVC = function() {
    this.on('keypress', restrictNumeric);
    this.on('keypress', restrictCVC);
    this.on('paste', reFormatCVC);
    this.on('change', reFormatCVC);
    this.on('input', reFormatCVC);
    return this;
  };

  $.payment.fn.formatCardExpiry = function() {
    this.on('keypress', restrictNumeric);
    this.on('keypress', restrictExpiry);
    this.on('keypress', formatExpiry);
    this.on('keypress', formatForwardSlashAndSpace);
    this.on('keypress', formatForwardExpiry);
    this.on('keydown', formatBackExpiry);
    this.on('change', reFormatExpiry);
    this.on('input', reFormatExpiry);
    return this;
  };

  $.payment.fn.formatCardNumber = function() {
    this.on('keypress', restrictNumeric);
    this.on('keypress', restrictCardNumber);
    this.on('keypress', formatCardNumber);
    this.on('keydown', formatBackCardNumber);
    this.on('keyup', setCardType);
    this.on('paste', reFormatCardNumber);
    this.on('change', reFormatCardNumber);
    this.on('input', reFormatCardNumber);
    this.on('input', setCardType);
    return this;
  };

  $.payment.fn.restrictNumeric = function() {
    this.on('keypress', restrictNumeric);
    this.on('paste', reFormatNumeric);
    this.on('change', reFormatNumeric);
    this.on('input', reFormatNumeric);
    return this;
  };

  $.payment.fn.cardExpiryVal = function() {
    return $.payment.cardExpiryVal($(this).val());
  };

  $.payment.cardExpiryVal = function(value) {
    var month, prefix, year, _ref;
    value = value.replace(/\s/g, '');
    _ref = value.split('/', 2), month = _ref[0], year = _ref[1];
    if ((year != null ? year.length : void 0) === 2 && /^\d+$/.test(year)) {
      prefix = (new Date).getFullYear();
      prefix = prefix.toString().slice(0, 2);
      year = prefix + year;
    }
    month = parseInt(month, 10);
    year = parseInt(year, 10);
    return {
      month: month,
      year: year
    };
  };

  $.payment.validateCardNumber = function(num) {
    var card, _ref;
    num = (num + '').replace(/\s+|-/g, '');
    if (!/^\d+$/.test(num)) {
      return false;
    }
    card = cardFromNumber(num);
    if (!card) {
      return false;
    }
    return (_ref = num.length, __indexOf.call(card.length, _ref) >= 0) && (card.luhn === false || luhnCheck(num));
  };

  $.payment.validateCardExpiry = function(month, year) {
    var currentTime, expiry, _ref;
    if (typeof month === 'object' && 'month' in month) {
      _ref = month, month = _ref.month, year = _ref.year;
    }
    if (!(month && year)) {
      return false;
    }
    month = $.trim(month);
    year = $.trim(year);
    if (!/^\d+$/.test(month)) {
      return false;
    }
    if (!/^\d+$/.test(year)) {
      return false;
    }
    if (!((1 <= month && month <= 12))) {
      return false;
    }
    if (year.length === 2) {
      if (year < 70) {
        year = "20" + year;
      } else {
        year = "19" + year;
      }
    }
    if (year.length !== 4) {
      return false;
    }
    expiry = new Date(year, month);
    currentTime = new Date;
    expiry.setMonth(expiry.getMonth() - 1);
    expiry.setMonth(expiry.getMonth() + 1, 1);
    return expiry > currentTime;
  };

  $.payment.validateCardCVC = function(cvc, type) {
    var card, _ref;
    cvc = $.trim(cvc);
    if (!/^\d+$/.test(cvc)) {
      return false;
    }
    card = cardFromType(type);
    if (card != null) {
      return _ref = cvc.length, __indexOf.call(card.cvcLength, _ref) >= 0;
    } else {
      return cvc.length >= 3 && cvc.length <= 4;
    }
  };

  $.payment.cardType = function(num) {
    var _ref;
    if (!num) {
      return null;
    }
    return ((_ref = cardFromNumber(num)) != null ? _ref.type : void 0) || null;
  };

  $.payment.formatCardNumber = function(num) {
    var card, groups, upperLength, _ref;
    num = num.replace(/\D/g, '');
    card = cardFromNumber(num);
    if (!card) {
      return num;
    }
    upperLength = card.length[card.length.length - 1];
    num = num.slice(0, upperLength);
    if (card.format.global) {
      return (_ref = num.match(card.format)) != null ? _ref.join(' ') : void 0;
    } else {
      groups = card.format.exec(num);
      if (groups == null) {
        return;
      }
      groups.shift();
      groups = $.grep(groups, function(n) {
        return n;
      });
      return groups.join(' ');
    }
  };

  $.payment.formatExpiry = function(expiry) {
    var mon, parts, sep, year;
    parts = expiry.match(/^\D*(\d{1,2})(\D+)?(\d{1,4})?/);
    if (!parts) {
      return '';
    }
    mon = parts[1] || '';
    sep = parts[2] || '';
    year = parts[3] || '';
    if (year.length > 0) {
      sep = ' / ';
    } else if (sep === ' /') {
      mon = mon.substring(0, 1);
      sep = '';
    } else if (mon.length === 2 || sep.length > 0) {
      sep = ' / ';
    } else if (mon.length === 1 && (mon !== '0' && mon !== '1')) {
      mon = "0" + mon;
      sep = ' / ';
    }
    return mon + sep + year;
  };

}).call(this);

},{}]},{},[1])(1)
});
//# sourceMappingURL=adz-stripe-donations.js.map
