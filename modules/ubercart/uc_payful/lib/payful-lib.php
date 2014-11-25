<?php

require_once( 'base.php' );
class Payful_Lib extends Payful_Gateway {	
	static $title = "Payful";
	static $slug = "payful";

	//public $merchantPageLabel = "Merchant Profile";
	//public $merchantPageURL = "https://www.cavirtex.com/merchant_information";

	public function needKeys() { return true; }
	public function supportedCurrencies(){
		return array( 'USD','EUR','GBP','JPY','CAD','AUD','CNY','CHF','SEK','NZD','KRW','AED','AFN','ALL','AMD','ANG','AOA','ARS','AWG','AZN','BAM','BBD','BDT','BGN','BHD','BIF','BMD','BND','BOB','BRL','BSD','BTN','BWP','BYR','BZD','CDF','CLF','CLP','COP','CRC','CVE','CZK','DJF','DKK','DOP','DZD','EEK','EGP','ETB','FJD','FKP','GEL','GHS','GIP','GMD','GNF','GTQ','GYD','HKD','HNL','HRK','HTG','HUF','IDR','ILS','INR','IQD','ISK','JEP','JMD','JOD','KES','KGS','KHR','KMF','KWD','KYD','KZT','LAK','LBP','LKR','LRD','LSL','LTL','LVL','LYD','MAD','MDL','MGA','MKD','MMK','MNT','MOP','MRO','MUR','MVR','MWK','MXN','MYR','MZN','NAD','NGN','NIO','NOK','NPR','OMR','PAB','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','RWF','SAR','SBD','SCR','SDG','SGD','SHP','SLL','SOS','SRD','STD','SVC','SYP','SZL','THB','TJS','TMT','TND','TOP','TRY','TTD','TWD','TZS','UAH','UGX','UYU','UZS','VEF','VND','VUV','WST','XAF','XAG','XAU','XCD','XOF','XPF','YER','ZAR','ZMW','ZWL', );
	}

	public function createTransaction($post = false) {

		$url  ="http://merchants.payful.co/api/transaction/create";

		$response = $this->curl_it($url, array_merge(
			array("address" => $this->merchant_key),
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

	protected $ignoreParamsDef = array("shipping_price", "tax_price", "customer_email", "customer_name");
	protected $filterParamsDef = array(
		"currency" => "strtoupper"
	);
	protected $translateParamsDef = array(
		"return_url" => "success_url",
	);

	public function parseCustom($params){
		$params["custom"] = implode(",", array_diff(array_keys($params), array("address", "amount_btc", "amount", "currency", "callback_url", "custom", "success_url", "cancel_url", "return")));
		return $params;
	}
	public function confirmTransaction(){

	}
	public function parseIPN() {
		$rawData = file_get_contents("php://input");

		$retArray = json_decode($rawData, true);

		$retArray = $this->reverseParamsTranslation($retArray);

		$retArray["raw"] = $rawData;

		return $retArray;
	}
	public function configFields(){
		return array(
			'merchant_key' => array(
				'title' => 'Wallet Address',
				'type' => 'text',
				'description' => "Use your own wallet to receive funds, no API key required. Plain and easy",
				'default' => '',
			),
		);
	}
}

?>