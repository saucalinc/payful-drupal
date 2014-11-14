<?php

function uc_payful_complete($cart_id = 0) {
	global $payfulGateway;

	$_IPNDATA = $payfulGateway->service->parseIPN();

	$rawData = $_IPNDATA["raw"];

	if(!isset($_IPNDATA["client_order_id"])){
		uc_payful_debug( 'Empty IPN received. <code>'.$rawData.'</code>' );
		die("empty_ipn");
	}

	$order = uc_order_load( $_IPNDATA["client_order_id"] );
	$order_hash = $payfulGateway->service->get_payful_hash($order->order_id, $order->order_total);

	if(!isset($_IPNDATA["payful_hash"]) || (isset($_IPNDATA["payful_hash"]) && $_IPNDATA["payful_hash"] != $order_hash)){
		uc_payful_debug( 'Authentication failed. Bad hash. <code>'.$rawData.'</code>' );
		die("bad_hash");
	}

	uc_payful_debug('Payment approved for order #'.$_IPNDATA["client_order_id"].'.');
	$comment = t('Paid with Bitcoin.');
	uc_payment_enter($order->order_id, 'payful', $order->order_total, 0, NULL, $comment);
	uc_cart_complete_sale($order);

	$payfulGateway->service->confirmTransaction();
	die();
}

function uc_payful_failed($cart_id = 0) {
	uc_payful_debug('Payment cancelled by gateway or buyer');
	$order = uc_order_load($_GET['PAYMENT_ID']);
	if (uc_order_status_data($order->order_status, 'state') == 'in_checkout') {
		drupal_set_message(t('Your payment was cancelled. Please try again or change the payment method.'));
		uc_order_comment_save($order->order_id, 0, t('Payment was cancelled at gateway.'), 'admin');
	}
	drupal_goto('cart/checkout');
	return $output;
}

function uc_payful_start($cart_id = 0){
	global $payfulGateway;

	if(!isset($_REQUEST["order_id"]))
		return;

	$order = uc_order_load( $_REQUEST["order_id"] );

	$params = array(
		"description" => variable_get("uc_store_name", "Ubercart") . " Order",
		"currency" => variable_get("uc_currency_code", "USD"),
		"amount" => $order->order_total,
		"shipping_price" => 0,
		"tax_price" => 0,
		"client_order_id" => $order->order_id,
		'cancel_url' => url('cart/checkout', array('absolute' => TRUE)),
		'return_url' => url('cart/checkout/complete', array('absolute' => TRUE)),
		'callback_url' => payful_notify_url(),
		'customer_email' => $order->primary_email,
		'customer_name' => $order->billing_first_name . ' ' . $order->billing_last_name,
	);

	$url = $payfulGateway->service->createTransaction($params);
	if($url !== false){
		uc_cart_empty(uc_cart_get_id());
		drupal_goto($url);
	} else
		drupal_goto(url("<front>"));
	return $output;
}



