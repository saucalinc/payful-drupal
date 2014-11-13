<?php

require_once( 'base.php' );
class Quadriga_Lib extends Payful_Gateway {	
	static $title = "QuadrigaCX";
	static $slug = "quadriga";

	public $merchantPageLabel = "Merchant Setup";
	public $merchantPageURL = "https://www.quadrigacx.com/merchant_setup";

	public function needKeys() { return true; }
	public function supportedCurrencies(){
		return array( 'USD', 'CAD', 'BTC' );
	}

	public function createTransaction($post = false) {
		$url  ="https://www.quadrigacx.com/merchant";

		$response = $this->curl_it($url, array_merge(
			array("key" => $this->merchant_key),
			$this->parsePostFields($post)
		));

		$redir = $response["redir"];
		if($redir == $url) {
			$response = false;
		} else {
			$response = $redir;
		}

		return $response;		
	}
	public function confirmTransaction(){
		echo "ack";
	}
	public function parseIPN() {
		$rawData = file_get_contents("php://input");

		$retArray = $_REQUEST;

		$retArray["raw"] = $rawData;

		return $retArray;
	}

	protected $translateParamsDef = array();
	protected $ignoreParamsDef = array('cancel_url', 'return_url', 'customer_email', 'customer_name', "description", "shipping_price", "tax_price", "callback_url");
	protected $filterParamsDef = array(
		"currency" => "strtolower"
	);

	public function parseCustom($params){
		$params["custom"] = implode(",", array_diff(array_keys($params), array("description", "currency", "amount", "custom")));
		return $params;
	}
	
	public function configFields(){
		return array(
			'merchant_key' => array(
				'title' => 'Store Key',
				'type' => 'text',
				'description' => 'Available on your <a href="'.$this->merchantPageURL.'" target="_blank">'.$this->merchantPageLabel.' page</a>.',
				'default' => '',
			),
		);
	}
}

?>