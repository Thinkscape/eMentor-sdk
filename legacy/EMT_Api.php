<?php

class EMT_API
{
	protected $_ver = 1.41;
	protected $_config = array();
	protected $_defaultConfig = array(
		'productKey' => null,
		'apiKey' => null,
		'loginUrl' => 'http://www.ementor.pl/logowanie/:productKey',
		'apiUrl' => 'http://www.ementor.pl/api/:action',
		'apiTimeout' => 10,
		'throwExceptions' => false,
	);
	protected $_disfunct = false;
	protected $_userData = null;
	protected $_lastQuery = '';
	protected $_lastResponse = '';
	
	public function __construct($config = array()){
		$this->_config = $this->_defaultConfig;
		if(is_array($config) || $config instanceof Traversable){
			foreach(array_keys($this->_defaultConfig) as $key){
				if(isset($config[$key])){
					$this->_config[$key] = $config[$key];
				}
			}
		}elseif(is_scalar($config)){
			$this->_config['apiKey'] = (string)$config;
		}
		
		if(
			empty($this->_config['apiKey'])
		){
			$this->_disfunct = true;

			if($this->_config['throwExceptions']){
				throw new EMT_API_Exception('Cannot use EMT Api - no apiKey provided!');
			}
		}
	}
	
	public function login($returnUrl = null){
		if($this->_disfunct)	return false;
		// -- prepare login URL
		$url = str_replace(array(
			':productKey'
		),array(
			$this->_config['productKey'],
		),
		$this->_config['loginUrl']);
		
		header('Location: '.$url);
	}
	
	/**
	 * Check if the user is authorized to access this product
	 *
	 * @param	string	$productKey		The product to check against
	 * @param	string	$authToken		The auth token received after login attempt
	 * @return	bool					True if user is allowed to access and view this product
	 */
	public function userCanView($productKey = null, $authToken = null){
		if($this->_disfunct)	return false;
		if($authToken === null){
			$authToken = $_REQUEST['authToken'];
		}
		
		if($productKey === null){
			if($this->_config['productKey'])
				$productKey = $this->_config['productKey'];
			else
				return false;
		}
		
		if(!$authToken)
			return false;
		
		try{
			$data = $this->_sendApiCall('getusercanview',array(
				'authToken' => $authToken,
				'productKey' => $productKey
			));
		}catch(EMT_API_Exception $e){
			if($this->_config['throwExceptions'])
				throw $e;
			else
				return false;
		}
		
		return (bool)$data;
		
	}
	
	/**
	 * Retrieve the date user has purchased the given product
	 *
	 * @param	string	$productKey		The product to check against
	 * @param	string	$authToken		The auth token received after login attempt
	 * @return	integer					Exact date of purchase of -1 if not ever purchased
	 */
	public function getDatePurchased($productKey = null, $authToken = null){
		if($this->_disfunct)	return false;
		if($authToken === null){
			$authToken = $_REQUEST['authToken'];
		}
		
		if($productKey === null){
			if($this->_config['productKey'])
				$productKey = $this->_config['productKey'];
			else
				return false;
		}
		
		if(!$authToken)
			return false;
		
		try{
			$data = $this->_sendApiCall('getdatepurchased',array(
				'authToken' => $authToken,
				'productKey' => $productKey
			));
		}catch(EMT_API_Exception $e){
			if($this->_config['throwExceptions'])
				throw $e;
			else
				return false;
		}
		
		return (int)$data;
	}
	
	/**
	 * Check if the token is valid
	 *
	 * @param	$authToken		The auth token received after login attempt
	 * @return	bool			True if user is allowed to access this product
	 */
	public function verifyAuthToken($authToken = null){
		if($this->_disfunct)	return false;
		if($authToken === null){
			$authToken = $_REQUEST['authToken'];
		}
		
		if(!$authToken)
			return false;
	
		try{
			$data = $this->_sendApiCall('verifyauthtoken',array(
				'authToken' => $authToken
			));
		}catch(EMT_API_Exception $e){
			if($this->_config['throwExceptions'])
				throw $e;
			else
				return false;
		}
		$this->_userData = $data['user'];
		
		return (bool)$data['valid'];
	}
	
	/**
	 * Get user data retrieve from last verifyAuthToken.
	 *
	 * @param	$authToken		(optional) If auth token has not been verified yet, a call will be made with this authtoken
	 * @return	array|boolean
	 */
	public function getUserData($authToken = null){
		if($this->_disfunct)	return false;
		if($this->_userData === null){
			// -- no user data, call verify auth token
			if($this->verifyAuthToken($authToken)){
				return $this->_userData;
			}else{
				return false;
			}
		}else{
			return $this->_userData;
		}
	}
	
	
	/**
	 * Alias for userCanView
	 */
	public function userPurchased($productKey = null, $authToken = null){
		return $this->userCanView($productKey, $authToken);
	}
	
	/**
	 * Return an url to API for given action
	 */
	protected function _prepareApiUrl($action,$params = array()){
		$url = str_replace(array(
			':apiKey',
			':action'
		),array(
			$this->_config['apiKey'],
			$action,
		),
		$this->_config['apiUrl']);
		
		if(count($params)){
			if(!stristr($url,'?'))
				$url .= '?';
			
			$foo = array();
			foreach($params as $key=>$val){
				$foo[] = urlencode($key).'='.urlencode($val);
			}
			$url .= join('&',$foo);
		}
		
		return $url;
	}
	
	protected function _sendApiCall($action,$data = array()){
		$url = $this->_prepareApiUrl($action,array(
			'format' => 'php',
		));
		
		$this->_lastQuery = serialize($data);
		$data = file_get_contents($url,false,stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header'=> "Content-Type: text/plain\r\nEMT-API-KEY: ".$this->_config['apiKey'],
				'user_agent' => 'EMTApi '.$this->_ver,
				'content' => serialize($data),
				'timeout' => $this->_config['apiTimeout']
			)
		)));
		$this->_lastResponse = $data;
		if(!$data || !($data = @unserialize($data)) || !is_array($data)){
			throw new EMT_API_Exception('Cannot send API call to url "'.$url.'"');
		}elseif(!$data['success']){
			throw new EMT_API_Exception('API call unsuccessful ("'.$url.'"');
		}else{
			return $data['data'];
		}
	}
}


class EMT_API_Exception extends Exception {}