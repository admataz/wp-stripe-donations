 <div class="wrap">

        <?php if(!$start_time):?>
        <p>Couldn't recognise "<?php echo $start?>" as a start date, can you try again with a more date-like phrase?</p>
        <?php endif;?>

        <?php if(!$end_time):?>
        <p>Couldn't recognise "<?php echo $end?>" as an end date, can you try again with a more date-like phrase?</p>
        <?php endif;?>


        <form action="<?php echo admin_url('admin.php?page=adz_stripe_donor_reports')?>" method="GET" >
            <input type="hidden" name="page" value="adz_stripe_donor_reports">
            <div>From: <input type="text" name="start" placeholder="enter start date" value="<?php echo $start?>"> to <input type="text" name="end" placeholder="enter end date" value="<?php echo $end?>" /> <input type="submit" name="go" value="go"></div>
            <div style="font-size:x-small">Tip: You can enter human friendly date formats, like "today" or "1 July" and it should work out what you mean</div> 
        </form>


            <h2>Donors</h2>

            <p><a href="<?php echo admin_url('admin.php?donation_report_export=csv&start='.$start_time.'&end='.$end_time)?>">Download CSV</a></p>

            <table class="wp-list-table widefat fixed posts">
            




  <thead>
    <tr>

    <th>name on card</th>
    <th>plan</th>
    <th>amount</th>
    <th>giftaid</th>
    <th>payments</th>
    <th></th>
</tr>
</thead>



  <tbody>
  <?php foreach($items as $item): ?>
    <tr>
        <td><?php echo $item['name'] ?></td>
        <td><?php echo $item['plan'] ?></td>
        <td><?php echo $item['amount'] ?></td>
        <td><?php echo $item['giftaid'] ?></td>
        <td ><a href="#" class="show-detail" ><?php echo count($item['payments']) ?></a>
            <div class="payment-detail detail-cell" style="display:none;position:absolute; background:#fff; padding: 2px; font-size:smaller; border: 1px solid #999">
            <ul>
                <?php foreach($item['payments'] as $p):?>
                    <li>£<?php echo $p['amount']/100 ?> on <?php echo date("Y-m-d", $p['date']) ?>. Stripe id: <?php echo $p['charge_id']?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        </td>
        
        <td >
        <a href="#" class="show-detail">detail</a>
        <div class="donor-detail detail-cell"  style="display:none;position:absolute; background:#fff; padding: 2px; font-size:smaller; border: 1px solid #999">
            <div><?php echo $item['email'] ?></div>
            <div><?php echo $item['title'] ?></div>
            <div><?php echo $item['first_name'] ?></div>
            <div><?php echo $item['last_name'] ?></div>
            <div><?php echo $item['address_1'] ?></div>
            <div><?php echo $item['address_2'] ?></div>
            <div><?php echo $item['address_3'] ?></div>
            <div><?php echo $item['postcode'] ?></div>
        </div>
        </td>
        </tr>
    </tr>
  <?php endforeach ?>
  </tbody>

</table>

        </div>

        <script>
        jQuery(
            function(){
                jQuery('.show-detail').click(function(evt){
                    evt.preventDefault();
                    // jQuery('.detail-cell').hide();
                    jQuery(evt.currentTarget).next('.detail-cell').toggle();

                });

            }
            )
        </script>