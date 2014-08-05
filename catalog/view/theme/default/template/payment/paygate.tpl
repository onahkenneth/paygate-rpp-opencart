<form action="<?php echo $action ?>" method="POST">
  <input type="hidden" name="cmd" value="_cart" />
  <?php $i = 1; ?>
  <?php $orderTotal=0;?>
  <?php foreach ($products as $product) { ?>
  	<?php
	$thisTotal=null;
	$thisTotal=$product['price']*$product['quantity'];
	$orderTotal+=$thisTotal;
  	?>
  <input type="hidden" name="item_name_<?php echo $i; ?>" value="<?php echo $product['name']; ?>" />
  <input type="hidden" name="item_number_<?php echo $i; ?>" value="<?php echo $product['model']; ?>" />
  <input type="hidden" name="amount_<?php echo $i; ?>" value="<?php echo $product['price']; ?>" />
  <input type="hidden" name="quantity_<?php echo $i; ?>" value="<?php echo $product['quantity']; ?>" />
  <input type="hidden" name="weight_<?php echo $i; ?>" value="<?php echo $product['weight']; ?>" />
  <?php $j = 0; ?>
  <?php foreach ($product['option'] as $option) { ?>
  <input type="hidden" name="on<?php echo $j; ?>_<?php echo $i; ?>" value="<?php echo $option['name']; ?>" />
  <input type="hidden" name="os<?php echo $j; ?>_<?php echo $i; ?>" value="<?php echo $option['value']; ?>" />
  <?php $j++; ?>
  <?php } ?>
  <?php $i++; ?>
  <?php } ?>
  <?php if ($discount_amount_cart) { ?>
  <input type="hidden" name="discount_amount_cart" value="<?php echo $discount_amount_cart; ?>" />
  <?php } ?>
  <?php 
	$order_total = $orderTotal*100;
	
	$checksum_source = $paygate_merchant_id.'|';
	$checksum_source.= $custom.'|';
	$checksum_source.= $order_total.'|';
	$checksum_source.= $currency_code.'|';
	$checksum_source.= $notify_url.'|';
	$checksum_source.= $transaction_date.'|';
	$checksum_source.= $email.'|';
	$checksum_source.= $encryption_key;
	
	$checksum = md5($checksum_source);
  ?>
  <input type="hidden" name="PAYGATE_ID" value="<?php echo $paygate_merchant_id; ?>" />
  <input type="hidden" name="REFERENCE" value="<?php echo $custom; ?>" />
  <input type="hidden" name="AMOUNT" value="<?php echo $order_total; ?>" />
  <input type="hidden" name="CURRENCY" value="<?php echo $currency_code; ?>" />
  <input type="hidden" name="RETURN_URL" value="<?php echo $notify_url; ?>" />
  <input type="hidden" name="TRANSACTION_DATE" value="<?php echo $transaction_date; ?>" />
  <input type="hidden" name="EMAIL" value="<?php echo $email; ?>" />
  <input type="hidden" name="CHECKSUM" value="<?php echo $checksum; ?>" />
  <div class="buttons">
    <div class="right">
      <input type="submit" value="<?php echo $button_confirm; ?>" class="button" />
    </div>
  </div>
</form>
