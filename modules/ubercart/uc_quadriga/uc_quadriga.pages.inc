<?php

function uc_quadriga_complete($cart_id = 0) {
  $rawData = file_get_contents("php://input");

  if(!isset($_REQUEST["uc_order"])){
    uc_quadriga_debug( 'Empty IPN received. <code>'.$rawData.'</code>' );
    return "empty_ipn";
  }

  $order = uc_order_load( $_REQUEST["uc_order"] );
  $order_hash = uc_get_hash_for_order($order);

  if(!isset($_REQUEST["hash_quadriga"]) || (isset($_REQUEST["hash_quadriga"]) && $_REQUEST["hash_quadriga"] != $order_hash)){
    uc_quadriga_debug( 'Authentication failed. Bad hash. <code>'.$rawData.'</code>' );
    return "bad_hash";
  }
  
  watchdog('QuadrigaCX', 'Payment approved for order #!order_id.', array('!order_id' => check_plain($_REQUEST["uc_order"])));
  $comment = t('Paid with Bitcoin.');
  uc_payment_enter($order->order_id, 'quadriga', $_REQUEST["amount"], 0, NULL, $comment);
  uc_cart_complete_sale($order);

  echo "ack";
}

function uc_quadriga_failed($cart_id = 0) {
  watchdog('QuadrigaCX', 'Payment cancelled by QuadrigaCX or buyer');
  $order = uc_order_load($_GET['PAYMENT_ID']);
  if (uc_order_status_data($order->order_status, 'state') == 'in_checkout') {
    drupal_set_message(t('Your payment was cancelled. Please try again or change the payment method.'));
    uc_order_comment_save($order->order_id, 0, t('Payment was cancelled at QuadrigaCX.'), 'admin');
  }
  drupal_goto('cart/checkout');
  return $output;
}



