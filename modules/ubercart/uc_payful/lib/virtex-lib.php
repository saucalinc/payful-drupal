<?php

require_once( 'base.php' );
class Virtex_Lib extends Payful_Gateway {	
	static $title = "CaVirtEx";
	static $slug = "virtex";

	public $merchantPageLabel = "Merchant Profile";
	public $merchantPageURL = "https://www.cavirtex.com/merchant_information";

	public function needKeys() { return true; }
	public function supportedCurrencies(){
		return array( 'CAD' );
	}

	public function createTransaction($post = false) {

		$url = 'https://www.cavirtex.com/merchant_purchase/' . $this->merchant_key;

		$response = $this->curl_it($url, $this->parsePostFields($post));

		if(!$response["body"])
			return false;

		$merchant_purchase = json_decode($response["body"], true);

		if( (isset($merchant_purchase['status']) && $merchant_purchase['status'] == 'error') || (isset($merchant_purchase['Status']) && $merchant_purchase['Status'] == 'error') ) { //RRR
			//$this->handle_error( 'Error: '.$merchant_purchase['Message'], 'Error: Could not create virtex invoice. <code>'.json_encode($merchant_purchase).'</code>' );
			return false;
		}
			
		if( empty( $merchant_purchase['order_key'] ) ) {
			//$this->handle_error( __('Something went wrong. Please choose a different payment method.','woocommerce'), 'Error: Empty order key returned. Could not create virtex invoice. <code>'.json_encode($merchant_purchase).'</code>' );
			return false;
		}
		
		return 'https://www.cavirtex.com/merchant_invoice?merchant_key='.$this->merchant_key.'&order_key='.$merchant_purchase['order_key'];

	}

	protected $ignoreParamsDef = array("currency");
	protected $filterParamsDef = array();
	protected $translateParamsDef = array(
		"description" => "name",
		"amount" => "price",
		"client_order_id" => "code",
		"shipping_price" => "shipping",
		"tax_price" => "tax",
		"customer_email" => "email"
	);

	public function parseCustom($params){
		if($params["shipping"] > 0) {
			$params["price"] -= $params["shipping"];
		} else {
			unset($params["shipping"]);
		}

		if($params["tax"] > 0) {
			$params["price"] -= $params["tax"];
		} else {
			unset($params["tax"]);
		}

		$params["custom_1"] = json_encode(array_diff_key($params, array_fill_keys(array("name", "price", "code", "shipping", "tax", "cancel_url", "return_url", "email", "customer_name"), "")));
		$params["format"] = "json"; 
		$params["shipping_required"] = "0"; 
		return $params;
	}

	public function confirmTransaction(){

	}
	public function parseIPN() {
		$rawData = file_get_contents("php://input");

		$retArray = json_decode($rawData, true);
		$retArray = array_merge($retArray, json_decode($retArray['custom_1'], true));

		$retArray = $this->reverseParamsTranslation($retArray);

		$retArray["raw"] = $rawData;

		return $retArray;
	}
	public function configFields(){
		return array(
			'merchant_key' => array(
				'title' => 'Merchant Key',
				'type' => 'text',
				'description' => 'Available on your <a href="'.$this->merchantPageURL.'" target="_blank">'.$this->merchantPageLabel.' page</a>.',
				'default' => '',
			),
		);
	}
}

?>