<?php
namespace EMT\Client;

use \EMT\Client\Exception\ConnectionFailed,
    \EMT\Client\Exception\ServerError,
    \EMT\Model\AbstractModel
;

class Client
{
    /**
     * Signing key id
     *
     * @var string
     */
    protected $keyId;

    /**
     * Signing key secret
     *
     * @var string
     */
    protected $keySecret;

    /**
     * Api server URL
     *
     * @var string
     */
    protected $apiEndpoint = 'https://www.ementor.pl/api/v2';

    /**
     * @var int
     */
    protected $timeout = 30;

    /**
     * @var Request
     */
    protected $lastRequest;

    /**
     * @var \EMT\Client\Response
     */
    protected $lastResponse;

    /**
	 * Total number of items from last findAll operation
	 *
	 * @var null|null
	 */
    protected $totalCount = null;

    /**
     * @param string|null $keyId        Your key id
     * @param string|null $keySecret    Your key secret
     * @param string|null $apiEndpoint  (optional) URL for api endpoint. Leave empty to use default url
     */
    public function __construct($keyId = null, $keySecret = null, $apiEndpoint = null)
    {
        if ($keyId !== null) {
            $this->setKeyId( $keyId );
        }

        if ($keySecret !== null) {
            $this->setKeySecret( $keySecret );
        }

        if ($apiEndpoint !== null) {
            $this->setApiEndpoint( $apiEndpoint );
        }
    }

    /**
     * Find all items associated with the specified model
     *
     * @param string      $modelName        Name of the model to query.
     * @param string      $itemId           Item's unique ID
     * @param string      $associationName  The association to retrieve
     * @param array       $searchCriteria   An associative array with search criteria.
     * @param string|null $order            Attribute to sort by.
     * @param string      $orderDir         Sorting direction (default: ascending)
     * @param string|null $limit            Max number of items to return (max 100)
     * @param string|null $offset           Number of items to skip (when used with $limit)
     * @return array
     */
    public function getAssociation(
        $modelName, $itemId, $associationName, $searchCriteria = array(),
        $order = null, $orderDir = 'ASC', $limit = null, $offset = null
    ){
        /**
         * Check model
         */
        $itemClass = $this->getClassForModel($modelName);
        if(!class_exists($itemClass)){
            throw new \BadMethodCallException('Unknown model "'.$modelName.'"');
        }

        /**
         * Check association
         */
        $associationClass = call_user_func(array($itemClass,'getAssociationClass'),$associationName);
        if(!$associationClass || !class_exists($itemClass)){
            throw new \BadMethodCallException('Unknown association "'.$associationName.'"');
        }

        /**
         * Prepare request
         */
        $request = new Request($this->getRESTUrl($modelName,$itemId,$associationName));

        /**
         * Parse limit
         */
        if($limit !== null){
            $limit = (int)$limit;
            if($limit < 1 || $limit > 100){
                throw new \BadMethodCallException('Limit has to be between 1 and 100');
            }
            $request->setParam('limit',$limit);
        }

        /**
         * Parse offset
         */
        if($offset !== null){
            $offset = (int)$offset;
            if($offset < 1){
                throw new \BadMethodCallException('Offset has to be > 0');
            }

            $request->setParam('offset',$offset);
        }

        /**
         * Parse order
         */
        if($order !== null){
            if(!call_user_func(array($associationClass, 'validateAttribute'),$order)){
                throw new \BadMethodCallException('Unknown order attribute "'.$order.'" for model "'.$associationName.'"');
            }
            $request->setParam('order',$order);

            $orderDir = strtoupper($orderDir);
            if(!in_array($orderDir,array('ASC','DESC'))){
                throw new \BadMethodCallException('Unknown order direction "'.$orderDir.'"');
            }

            $request->setParam('orderDir',$orderDir);
        }

        /**
         * Add filtering params
         */
        foreach($searchCriteria as $key=>$val){
            /**
			 * Interpret the criteria
			 */
            if(!is_numeric($key)){
                /**
                 * attribute => $value
                 */
                if(!call_user_func(array($associationClass, 'validateAttribute'),$key)){
                    throw new \BadMethodCallException('Unknown '.$modelName.' attribute "'.$key.'"');
                }

                $value = $val;
                $attribute = $key;
                $operator = is_array($value) ? 'in' : 'eq';
            }else{
                if(is_array($val) && count($val) == 3){
                    /**
                     * array( attribute, operator, value )
                     */
                    list($attribute, $operator, $value) = $val;
                }elseif(is_array($val) && count($val) == 2){
                    /**
                     * array( attribute, value )
                     */
                    list($attribute, $value) = $val;
					$operator = 'eq';
                }else{
                    throw new \BadMethodCallException('Could not understand search criteria '.$key.'=>'.$val);
                }
            }

            /**
			 * Validate attribute
			 */
			if(!call_user_func(array($associationClass, 'validateAttribute'),$attribute)){
				throw new \BadMethodCallException('Unknown '.$modelName.' attribute "'.$attribute.'"');
			}

            if($operator == 'in' || $operator == 'notIn'){
                /**
				 * Multiple options
				 */
                if(!is_array($value)){
                    throw new \BadMethodCallException('Cannot use '.gettype($val).' as multi-value criteria');
                }

                $options = array();
				foreach($value as $k=>$v){
					if(!is_scalar($v)){
						throw new \BadMethodCallException('Cannot use '.gettype($v).' as multi-value option');
					}
					$options[] = (string)$v;
				}
				$request->setParam('filter-'.$attribute.'-'.$operator,json_encode($options));
            }else{
            	/**
            	 * Single value comparison
            	 */
            	if(!is_scalar($value)){
                    throw new \BadMethodCallException('Cannot use '.gettype($val).' in search criteria');
                }

                $request->setParam('filter-'.$attribute.'-'.$operator,$value);
            }

        }

        /**
         * Sign the request
         */
        $this->signRequest($request);

        /**
         * Send request to API server
         */
        $response = $this->send($request);

        /**
         * Create model items from the response
         */
        $result = array();
        foreach($response->getData() as $itemData){
            $result[] = new $associationClass($itemData, $this);
        }

        /**
		 * Store total items count (if available)
		 */
		if($response->hasHeader('X-Emt-Total')){
			$this->totalCount = $response->getHeader('X-Emt-Total');
		}else{
			$this->totalCount = null;
		}

        return $result;
    }

    /**
     * Create a single item.
     *
     * @param string        $modelName      Item's model name
     * @param array         $createData     Array of attribute => "new value"
     * @throws \EMT\Exception
     * @return \EMT\Model\AbstractModel
     */
    public function create($modelName, $createData = array())
    {
        /**
         * Check model
         */
        $itemClass = $this->getClassForModel($modelName);
        if(!class_exists($itemClass)){
            throw new \BadMethodCallException('Unknown model "'.$modelName.'"');
        }

        /**
         * Validate create data
         */
        foreach($createData as $attr=>$val){
            /**
             * Validate the attribute exists and is writable
             */
            if(!call_user_func(
                array($itemClass, 'validateAttribute'),
                $attr,
                AbstractModel::ATTR_RW
            )){
                throw new \BadMethodCallException('Invalid or read-only '.$modelName.' attribute "'.$attr.'"');
            }
        }

        /**
         * Prepare request
         */
        $request = new Request($this->getRESTUrl($modelName));
        $request->setMethod('POST');
        $request->setData($createData);

        /**
         * Sign it
         */
        $this->signRequest($request);

        /**
         * Send request to API server
         */
        $response = $this->send($request);

        /**
         * Create and return new item from the retrieved data
         */
        return new $itemClass($response->getData(), $this);
    }

    /**
     * Update a single item.
     *
     * @param string        $modelName      Item's model name
     * @param string        $itemId         Item's unique ID
     * @param array         $updateData     Array of attribute => "new value"
     * @throws \EMT\Exception
     * @return \EMT\Model\AbstractModel
     */
    public function update($modelName, $itemId, $updateData = array())
    {
        /**
         * Check model
         */
        $itemClass = $this->getClassForModel($modelName);
        if(!class_exists($itemClass)){
            throw new \BadMethodCallException('Unknown model "'.$modelName.'"');
        }

        /**
         * Validate update data
         */
        foreach($updateData as $attr=>$val){
            /**
             * Validate the attribute exists and is writable
             */
            if(!call_user_func(
                array($itemClass, 'validateAttribute'),
                $attr,
                AbstractModel::ATTR_RW
            )){
                throw new \BadMethodCallException('Invalid or read-only '.$modelName.' attribute "'.$attr.'"');
            }
        }

        /**
         * Prepare request
         */
        $request = new Request($this->getRESTUrl($modelName, $itemId));
        $request->setMethod('POST');
        $request->setData($updateData);

        /**
         * Sign it
         */
        $this->signRequest($request);

        /**
         * Send request to API server
         */
        $response = $this->send($request);

        /**
         * Create and return new item from the retrieved data
         */
        return new $itemClass($response->getData(), $this);
    }

    /**
     * Get a single item with a given id.
     *
     * @param string        $modelName      Item's model name
     * @param string        $itemId         Item's unique ID
     * @return \EMT\Model\AbstractModel|false
     */
    public function get($modelName, $itemId)
    {
        /**
         * Check model
         */
        $itemClass = $this->getClassForModel($modelName);
        if(!class_exists($itemClass)){
            throw new \BadMethodCallException('Unknown model "'.$modelName.'"');
        }

        /**
         * Check item id
         */
        if(!$itemId){
            throw new \BadMethodCallException('Item id cannot be empty');
        }

        /**
         * Prepare request
         */
        $request = new Request($this->getRESTUrl($modelName, $itemId));

        /**
         * Sign it
         */
        $this->signRequest($request);

        /**
         * Send request to API server
         */
        try{
            $response = $this->send($request);
        }catch(Exception\NotFound $e){
            /**
             * Return false if this item could not be found
             */
            return false;
        }

        /**
         * Create and return new item from the retrieved data
         */
        return new $itemClass($response->getData(), $this);
    }

    /**
     * Alias for get()
     *
     * @param string        $modelName      Item's model name
     * @param string        $itemId         Item's unique ID
     * @return \EMT\Model\AbstractModel|false
     */
    public function getItem($modelName, $itemId)
    {
        return $this->get($modelName,$itemId);
    }

    /**
     * Find all items with specified attribute matching given value.
     *
     * @param string      $modelName        Name of the model to query
     * @param string      $searchAttr       Attribute to search on.
     * @param mixed       $attrValue        Items matching this value will be retrieved
     * @param string|null $order            Attribute to sort by.
     * @param string      $orderDir         Sorting direction (default: ascending)
     * @param string|null $limit            Max number of items to return (max 100)
     * @param string|null $offset           Number of items to skip (when used with $limit)
     * @return array
     */
    public function findBy(
        $modelName, $searchAttr, $attrValue,
        $order = null, $orderDir = 'ASC', $limit = null, $offset = null
    ){
        return $this->findAll(
            $modelName,
            array(
                $searchAttr => $attrValue
            ),
            $order,
            $orderDir,
            $limit,
            $offset
        );
    }

    /**
     * Find all items matching the specified criteria.
     *
     * @param string      $modelName        Name of the model to query.
     * @param array       $searchCriteria   An associative array with search criteria.
     * @param string|null $order            Attribute to sort by.
     * @param string      $orderDir         Sorting direction (default: ascending)
     * @param string|null $limit            Max number of items to return (max 100)
     * @param string|null $offset           Number of items to skip (when used with $limit)
     * @return array
     */
    public function findAll(
        $modelName, $searchCriteria = array(),
        $order = null, $orderDir = 'ASC', $limit = null, $offset = null
    ){
        /**
         * Check model
         */
        $itemClass = $this->getClassForModel($modelName);
        if(!class_exists($itemClass)){
            throw new \BadMethodCallException('Unknown model "'.$modelName.'"');
        }

        /**
         * Prepare request
         */
        $request = new Request($this->getRESTUrl($modelName));

        /**
         * Parse limit
         */
        if($limit !== null){
            $limit = (int)$limit;
            if($limit < 1 || $limit > 100){
                throw new \BadMethodCallException('Limit has to be between 1 and 100');
            }
            $request->setParam('limit',$limit);
        }

        /**
         * Parse offset
         */
        if($offset !== null){
            $offset = (int)$offset;
            if($offset < 1){
                throw new \BadMethodCallException('Offset has to be > 0');
            }

            $request->setParam('offset',$offset);
        }


        /**
         * Parse order
         */
        if($order !== null){
            if(!call_user_func(array($itemClass, 'validateAttribute'),$order)){
                throw new \BadMethodCallException('Unknown order attribute "'.$order.'" for model "'.$modelName.'"');
            }
            $request->setParam('order',$order);

            $orderDir = strtoupper($orderDir);
            if(!in_array($orderDir,array('ASC','DESC'))){
                throw new \BadMethodCallException('Unknown order direction "'.$orderDir.'"');
            }

            $request->setParam('orderDir',$orderDir);
        }

        /**
         * Add filtering params
         */
        foreach($searchCriteria as $key=>$val){
            /**
			 * Interpret the criteria
			 */
            if(!is_numeric($key)){
                /**
                 * attribute => $value
                 */
                if(!call_user_func(array($itemClass, 'validateAttribute'),$key)){
                    throw new \BadMethodCallException('Unknown '.$modelName.' attribute "'.$key.'"');
                }

                $value = $val;
                $attribute = $key;
                $operator = is_array($value) ? 'in' : 'eq';
            }else{
                if(is_array($val) && count($val) == 3){
                    /**
                     * array( attribute, operator, value )
                     */
                    list($attribute, $operator, $value) = $val;
                }elseif(is_array($val) && count($val) == 2){
                    /**
                     * array( attribute, value )
                     */
                    list($attribute, $value) = $val;
					$operator = 'eq';
                }else{
                    throw new \BadMethodCallException('Could not understand search criteria '.$key.'=>'.$val);
                }
            }

            /**
			 * Validate attribute
			 */
			if(!call_user_func(array($itemClass, 'validateAttribute'),$attribute)){
				throw new \BadMethodCallException('Unknown '.$modelName.' attribute "'.$attribute.'"');
			}

            if($operator == 'in' || $operator == 'notIn'){
                /**
				 * Multiple options
				 */
                if(!is_array($value)){
                    throw new \BadMethodCallException('Cannot use '.gettype($val).' as multi-value criteria');
                }

                $options = array();
				foreach($value as $k=>$v){
					if(!is_scalar($v)){
						throw new \BadMethodCallException('Cannot use '.gettype($v).' as multi-value option');
					}
					$options[] = (string)$v;
				}
				$request->setParam('filter-'.$attribute.'-'.$operator,json_encode($options));
            }else{
            	/**
            	 * Single value comparison
            	 */
            	if(!is_scalar($value)){
                    throw new \BadMethodCallException('Cannot use '.gettype($val).' in search criteria');
                }

                $request->setParam('filter-'.$attribute.'-'.$operator,$value);
            }

        }

        /**
         * Sign the request
         */
        $this->signRequest($request);

        /**
         * Send request to API server
         */
        $response = $this->send($request);

        /**
         * Create model items from the response
         */
        $result = array();
        foreach($response->getData() as $itemData){
            $result[] = new $itemClass($itemData, $this);
        }

        /**
		 * Store total items count (if available)
		 */
		if($response->hasHeader('X-Emt-Total')){
			$this->totalCount = $response->getHeader('X-Emt-Total');
		}else{
			$this->totalCount = null;
		}

        return $result;
    }

    /**
     * Send the request and return response body.
     *
     * @param Request $request
     * @return Response
     */
    public function send(Request $request)
    {
        /**
         * Store this request for reference
         */
        $this->lastRequest = &$request;

        /**
         * Compile headers
         */
        $headers = array();
        foreach ($request->getHeaders() as $name=> $val) {
            $headers[] = $name . ': ' . $val;
        }
        $headers = join( "\r\n", $headers );

        /**
         * Prepare stream context
         */
        $context = stream_context_create( array(
            'http' => array(
                'method'        => $request->getMethod(),
                'header'        => $headers,
                'content'       => $request->getBody(),
                'max_redirects' => 1,
                'timeout'       => $this->timeout,
                'ignore_errors' => true,
            )
        ) );

        /**
         * Prepare response
         */
        $response = new Response();
        $this->lastResponse = &$response;

        /**
         * Connect, send query to server and fetch result
         */
        $stream = fopen( $request->getFullUrl(), 'r', null, $context );
        if (!$stream) {
            throw new ConnectionFailed('Cannot connect to ' . $request->getUrl());
        }

        $body = '';
        while (!feof( $stream )) {
            $body .= fread( $stream, 8192 );
        }

        /**
         * Get stream meta
         */
        $meta = stream_get_meta_data( $stream );

        /**
         * Close stream
         */
        fclose( $stream );

        /**
         * Check if timed out
         */
        if ($meta['timed_out']) {
            throw new ConnectionFailed('Timeout connecting to server');
        }

        /**
         * Check body
         */
        if (!$body) {
            throw new ServerError('Server returned empty response');
        }
        $response->setBody( $body );

        /**
         * Collect headers
         */
        foreach ($meta['wrapper_data'] as $header) {
            if (preg_match( '/^HTTP\/(?P<version>1\.[01]) (?P<status>\d{3}) (?P<reason>.*)$/', $header, $matches )) {
                //                $response->version = $matches['version'];
                $response->setStatusCode( $matches['status'] );
                $response->setReasonPhrase( $matches['reason'] );
            } else {
                // other http header
                list($name, $val) = explode( ':', $header );
                $response->addHeader( trim($name), trim($val) );
            }
        }

        /**
         * Process response data
         */
        $response->parseData();

        /**
         * Return response
         */
        return $response;
    }

    /**
     * Send a ping REST request
     *
     * @return array|string
     */
    public function ping()
    {
        /**
         * Create request
         */
        $url = $this->getRESTUrl( 'ping' );
        $request = new Request($url);

        /**
         * Sign the request using API Access Key
         */
        $this->signRequest($request);

        $response = $this->send( $request );
        return $response->getData();
    }

    /**
     * Build a valid REST URL for API server
     *
     * @param string      $model                Model name
     * @param string|null $itemId               (optional) item id to read
     * @param string|null $association          (optional) item's association to fetch
     * @return string
     */
    protected function getRESTUrl($model, $itemId = null, $association = null)
    {
        $url = $this->getApiEndpoint();

        /**
         * Parse the url
         */
        $parse = parse_url($url);

        /**
         * Strip trailing slash off of path
         */
        $parse['path'] = rtrim( $parse['path'], '/' );

        /**
         * Assemble back the url
         */
        $url = $parse['scheme'].'://'.$parse['host'].(isset($parse['port']) ? ':'.$parse['port'] : '').$parse['path'];

        /**
         * Add model suffix
         */
        $url .= '/rest/' . $model;

        /**
         * Add item ID
         */
        if ($itemId !== null) {
            $url .= '/' . $itemId;
        }

        /**
         * Add association suffix
         */
        if ($itemId !== null && $association !== null) {
            $url .= '/' . $association;
        }

        /**
         * Add query if it was present before
         */
        if(isset($parse['query'])){
            $url .= '?'.$parse['query'];
        }

        return $url;
    }

    /**
     * Sign the request with API Access Key
     *
     * @param Request $request
     * @return Request
     */
    protected function signRequest(Request $request)
    {
        /**
         * Calculate checksum
         */
        $parts = array();

        /**
         * Get HTTP verb (method)
         */
        $parts[] = $request->getMethod();

        /**
         * Get full request URI
         */
        $parts[] = $request->getFullUri();

        /**
         * Get content type
         */
        $parts[] = $request->getHeader('Content-Type');


        /**
         * Calculate md5 body and add header
         */
        $md5 = md5($request->getBody());
        $request->addHeader('Content-Md5',$md5);
        $parts[] = $md5;

        /**
         * Add date header
         */
        $date = gmdate("D, d M Y H:i:s", time())." GMT"; // RFC1123
        $request->addHeader('Date',$date);
        $parts[] = $date;

        /**
         * Add key secret to the checksum
         */
        $parts[] = $this->getKeySecret();

        /**
         * Calculate and encode the checksum
         */
        $checksum = base64_encode( sha1( join('',$parts),true ) );

        /**
         * Build authorization header
         */
        $authHeader = 'EMT '.$this->getKeyId().' '.$checksum;

        /**
         * Add authorization header to the request
         */
        $request->addHeader('Authorization',$authHeader);

        return $request;

    }

    /**
     * @param string $apiEndpoint
     */
    public function setApiEndpoint($apiEndpoint)
    {
        $this->apiEndpoint = $apiEndpoint;
    }

    /**
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->apiEndpoint;
    }

    /**
     * @param string $keyId
     */
    public function setKeyId($keyId)
    {
        $this->keyId = $keyId;
    }

    /**
     * @return string
     */
    public function getKeyId()
    {
        return $this->keyId;
    }

    /**
     * @param string $keySecret
     */
    public function setKeySecret($keySecret)
    {
        $this->keySecret = $keySecret;
    }

    /**
     * @return string
     */
    public function getKeySecret()
    {
        return $this->keySecret;
    }

    /**
     * @return null|Request
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
    * @return \EMT\Client\Response
    */
    public function getLastResponse()
    {
       return $this->lastResponse;
    }


    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Get PHP class name representing given model
     *
     * @param $modelName
     * @return string
     */
    protected function getClassForModel($modelName)
    {
        return '\\EMT\\Model\\'.ucfirst($modelName);
    }

	/**
	 * Return total number of matching items from last fetchAll() or getAssociation() query.
	 * The number is total available items, regardless of $limit and $offset.
	 *
	 * @return int|null
	 */
	public function getTotalCount() {
		return $this->totalCount;
	}

}