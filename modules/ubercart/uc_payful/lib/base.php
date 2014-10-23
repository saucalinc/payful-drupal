<?php

abstract class Payful_Gateway
{
	protected $merchant_key;
	protected $merchant_secret;
	static $title;
	static $slug;

	public $merchantPageLabel = false;
	public $merchantPageURL = false;

	public function __construct($merchant_key, $merchant_secret = NULL){
		$this->changeKeys($merchant_key, $merchant_secret);
	}

	public function changeKeys($merchant_key, $merchant_secret = NULL){
		$this->merchant_key    = $merchant_key;
		$this->merchant_secret = $merchant_secret;
	}

    abstract public function createTransaction();
    abstract public function confirmTransaction();
    abstract public function parseIPN();
    public function configFields(){
    	return array();
    }
	abstract public function supportedCurrencies();

	public function supportedCurrenciesString() {
		$array = $this->supportedCurrencies();
		$last = array_pop($array);

		$ret = "";
		if(count($array) > 0)
			$ret .= implode(", ", $array)." or ";
		$ret .= $last;
		
		return $ret;
	}

	public function has_merchant_page(){
		if(empty($this->merchantPageURL) || empty($this->merchantPageLabel)){
			return false;
		}
		return true;
	}
	public function getTitle() {
		$className = get_class($this);
		return $className::$title;
	}
	public function getSlug() {
		$className = get_class($this);
		return $className::$slug;
	}

	public function isCurrencySupported($curr) {
		$supported = $this->supportedCurrencies();
		if($supported === true)
			return true;

		if(!is_array($curr))
			$curr = array($curr);

		$intersect = array_intersect ( $curr , $supported );
		return !empty($intersect);
	}

	public function needKeys() {
		return false;
	}

	public function hasKeys(){
		if(!empty($this->merchant_key) || !$this->needKeys()){
			return true;
		} else {
			return false;
		}
	}

	public function get_payful_hash( $order_id, $amount ) {
		return md5($this->merchant_key . $order_id . $amount);
	}

	protected $ignoreParamsDef = array();
	protected $filterParamsDef = array();
	protected $translateParamsDef = array();

	public function parseCustom($params){
		return $params;
	}

	public function parsePostFields($params){
		$ignoreParams = $this->ignoreParamsDef;
		foreach($ignoreParams as $ignore){
			if(isset($params[$ignore]))
				unset($params[$ignore]);
		}

		$filterParams = $this->filterParamsDef;
		foreach($filterParams as $param => $filter) {
			if(isset($params[$param])){
				if(!is_array($filter))
					$filter = array($filter);

				foreach($filter as $func){
					$params[$param] = call_user_func($func, $params[$param]);
				}
			}
		}

		$translateParams = $this->translateParamsDef;
		foreach ($translateParams as $key => $newKey) {
			if(isset($params[$key])){
				$params[$newKey] = $params[$key];
				//unset($params[$key]);
			}
		}

		$params["payful_hash"] = $this->get_payful_hash($params["client_order_id"], $params["amount"]);

		$params = $this->parseCustom($params);

		return $params;
	}

	public function reverseParamsTranslation($params) {
		$translateParams = $this->translateParamsDef;
		foreach ($translateParams as $newKey => $key) {
			if(isset($params[$key])){
				$params[$newKey] = $params[$key];
				//unset($params[$key]);
			}
		}
		return $params;
	}

    protected function curl_it($url, $post = false){
    	$curl = curl_init($url);    
		
		$ch = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($curl, CURLOPT_VERBOSE, 1);
		//curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, 1.0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		if (is_array($post)) {     
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		}

		$responseString = curl_exec($curl);       


		// Then, after your curl_exec call:
		/*$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$header = substr($responseString, 0, $header_size);
		$body = substr($responseString, $header_size);   */

		$body = $responseString;
		$redir = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
		$error = curl_error($curl);
		curl_close($curl);

		return array(
			"body" => $body,
			"redir" => $redir,
			"error" => $error,
		);
    }
}

class Null_Lib extends Payful_Gateway {	
	static $title = "Void";
	static $slug = "null";
	public function createTransaction($post = false) {

	}
	public function confirmTransaction(){

	}
	public function supportedCurrencies(){
		return true;
	}
	public function configFields(){
		return array();
	}
	public function parseIPN() {
		return array();
	}
}

?>