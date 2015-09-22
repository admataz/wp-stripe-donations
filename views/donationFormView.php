  <form action="" method="POST" id="adz-stripe-donation-form" autocomplete="on">
    <?php print $wp_nonce_field ?>
 


  <fieldset class="once-off">
  <legend>Once-off donation</legend>
    <div class="form-row">
      <label>
        <input type="radio" value="once_off"  name="plan" <?php echo empty($donate['defaults']['plan']) || $donate['defaults']['plan'] =='once_off' ? 'checked="checked"' : '' ?> /> 
        <span>Once-off donation</span>
      </label>
    </div>
    <div class="form-row">
      <label>
        <span>Amount</span>
        <input type="text" size="25"  name="amount" class="input-amount"  value="<?php echo !empty($donate['defaults']['amount']) ? $donate['defaults']['amount'] : '' ?>" />
      </label>
    </div>
    <div class="form-row">
      <select name="currency">
      <?php foreach( $currencies as $currency): ?>
        <option value="<?php echo $currency?>" <?php echo !empty($donate['defaults']['currency']) && $donate['defaults']['currency'] == $currency ? 'selected="selected"' : '' ?> >
          <?php echo \Symfony\Component\Intl\Intl::getCurrencyBundle()->getCurrencyName(strtoupper($currency))?>
           ( <?php echo \Symfony\Component\Intl\Intl::getCurrencyBundle()->getCurrencySymbol(strtoupper($currency))?> )
        </option>
      <?php endforeach; ?>
      </select>
    </div>

</fieldset>

  <fieldset class="plans">
  <legend>Repeating Plans</legend>
  <?php foreach($plans->data as $plan): ?>
    <div class="form-row">
      <label>
        <input type="radio" value="<?php echo $plan->id?>"  name="plan" <?php echo !empty($donate['defaults']['plan']) && $donate['defaults']['plan'] == $plan->id ? 'checked="checked"' : '' ?> /> 
        <span>
        <?php echo $plan->name ?>
         <?php if($plan->id != 'monthly_custom'):?>
          (<?php echo \Symfony\Component\Intl\Intl::getCurrencyBundle()->getCurrencySymbol(strtoupper($plan->currency))?><?php echo $plan->amount/100 ?> - <?php echo strtoupper($plan->currency) ?>)
        <?php endif ?>
        </span>
      </label>
    </div>

  <?php endforeach; ?>
   <div class="form-row">
      <label>
        <span>Custom Amount (<?php echo $default_currency_symbol?>)</span>
        <input type="text" size="25"  name="custom_amount" class="input-custom_amount"  value="<?php echo !empty($donate['defaults']['custom_amount']) ? $donate['defaults']['custom_amount'] : '' ?>" />
      </label>
    </div>
  </fieldset>



  

    <div class="form-row">
      <label>
        <span>Email*</span>
        <input type="email" size="25" data-stripe="email" name="email" class="input-email" required placeholder="e.g. you@example.org" value="<?php echo !empty($donate['defaults']['email']) ? $donate['defaults']['email'] : '' ?>" />
      </label>
    </div>


    <fieldset class="payment-details">
    
    <div class="form-row">
      <label>
        <span>Card Number*</span>
        <input type="text" size="25" data-stripe="number" class="cc-number"  name="cc-number" placeholder="Card number" required  value="<?php echo !empty($donate['defaults']['cc-number']) ? $donate['defaults']['cc-number'] : '' ?>" />
      </label>
    </div>

    <div class="form-row">
      <label>
        <span>CVC*</span>
        <input type="text" size="4" data-stripe="cvc"  autocomplete="off" class="cc-cvc" name="cc-cvc" required />
      </label>
      
    </div>

    <div class="form-row">
      <div class="cc-exp">
        <label>
          <span>Expiration (MM/YYYY)*</span>
          <input type="text" size="3" data-stripe="exp-month" name="exp-month" id="exp-month" class="exp-month" placeholder="MM" required  value="<?php echo !empty($donate['defaults']['exp-month']) ? $donate['defaults']['exp-month'] : '' ?>" />
        </label>
        <span> / </span>
        <input type="text" size="4" data-stripe="exp-year" name="exp-year" id="exp-month"  class="exp-year" placeholder="YYYY" required  value="<?php echo !empty($donate['defaults']['exp-year']) ? $donate['defaults']['exp-year'] : '' ?>"  />
      </div>
    </div>

    <div class="form-row">
      <label>
        <span>Cardholder's full name</span>
        <input type="text" size="25" class="cc-name"  data-stripe="name"  name="cc-name" placeholder="Your name"   value="<?php echo !empty($donate['defaults']['cc-name']) ? $donate['defaults']['cc-name'] : '' ?>" />
      </label>
    </div>

    </fieldset>



    <div class="form-row">
      <label><span>UK taxpayer?</span>
        <input type="checkbox" id="giftaidcheckbox" name="giftaid" value="1" <?php echo !empty($donate['defaults']['giftaid']) ? 'checked' : '' ?> /> Yes - please Gift Aid my donation
      </label>
    </div>

    <fieldset class="giftaid-fields" >
      <div class="form-row">
        <label>
          <span>Title*</span>
          <input type="text" size="25" data-stripe="title"  name="customer[title]" class="input-title required" placeholder="Dr, Mr, Mrs, Miss, Ms" value="<?php echo !empty($donate['defaults']['customer']['title']) ? $donate['defaults']['customer']['title'] : '' ?>" />
        </label>
      </div>
      <div class="form-row">
        <label>
          <span>First name*</span>
          <input type="text" size="25" data-stripe="first-name"  name="customer[first_name]" class="input-firstname required" value="<?php echo !empty($donate['defaults']['customer']['first_name']) ? $donate['defaults']['customer']['first_name'] : '' ?>" />
        </label>
      </div>              
      <div class="form-row">
        <label>
          <span>Last name*</span>
          <input type="text" size="25" data-stripe="last-name"  name="customer[last_name]" class="input-lastname required" value="<?php echo !empty($donate['defaults']['customer']['last_name']) ? $donate['defaults']['customer']['last_name'] : '' ?>" />
        </label>
      </div>
      <div class="form-row">
        <label>
          <span>Address 1*</span>
          <input type="text" size="25" data-stripe="address-1"  name="customer[address_1]" class="input-address1 required" value="<?php echo !empty($donate['defaults']['customer']['address_1']) ? $donate['defaults']['customer']['address_1'] : '' ?>" />
        </label>
      </div>
      <div class="form-row">
        <label>
          <span>Address 2</span>
          <input type="text" size="25" data-stripe="address-2"  name="customer[address_2]" class="input-address2" value="<?php echo !empty($donate['defaults']['customer']['address_2']) ? $donate['defaults']['customer']['address_2'] : '' ?>" />
        </label>
      </div>
      <div class="form-row">
        <label>
          <span>City*</span>
          <input type="text" size="25" data-stripe="city" name="customer[city]" class="input-city required" value="<?php echo !empty($donate['defaults']['customer']['city']) ? $donate['defaults']['customer']['city'] : '' ?>" />
        </label>
      </div>                            
      <div class="form-row">
        <label>
          <span>Postcode*</span>
          <input type="text" size="25" data-stripe="postcode" name="customer[postcode]" class="input-postcode required" value="<?php echo !empty($donate['defaults']['customer']['postcode']) ? $donate['defaults']['customer']['postcode'] : '' ?>" />
        </label>
      </div>

    </fieldset>




    <button type="submit" id="donationform-submit-button">Donate</button>
  </form> 