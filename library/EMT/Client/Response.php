<?php
namespace EMT\Client;

use \EMT\Client\Exception;


class Response
{
    /**
     * Response headers
     *
     * @var array
     */
    protected $headers = array();

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array|string
     */
    protected $data;

    /**
     * @var integer
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $reasonPhrase;

    public function __construct()
    {

    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get a single response header
     *
     * @param string    $name             Header name (case-sensitive)
     * @return string|null
     */
    public function getHeader($name)
    {
        return $this->headers[$name];
    }

    public function addHeader($name, $val)
    {
        $this->headers[$name] = $val;
    }

    /**
	 * Check if given header exists in the response. Header names are case-sensitive.
	 *
	 * @param $name
	 * @return bool
	 */
    public function hasHeader($name)
    {
    	return array_key_exists($name, $this->headers);
    }

    /**
     * @param array|string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array|string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $reasonPhrase
     */
    public function setReasonPhrase($reasonPhrase)
    {
        $this->reasonPhrase = $reasonPhrase;
    }

    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Process response body and headers, determine response format and
     * read encapsulated data.
     *
     * @throws \EMT\Client\Exception
     */
    public function parseData(){
        /**
         * Get response format from headers
         */
        $contentType = $this->getHeader('Content-Type');
        if(!$contentType)
            throw new Exception\ServerError('API server returned a response without Content-Type header');

        $this->data = null;

        /**
         * Convert response to array
         */
        switch($contentType){
            case 'text/xml':
                $xml = simplexml_load_string($this->body);
                if($xml !== false && is_object($xml)){
                    // try to convert to array
                    $this->data = $this->simpleXMLToArray($xml);
                }
                break;

            case 'text/x-json':
            case 'application/json':
                $this->data = json_decode($this->body,true);
                break;

            case 'application/x-www-form-urlencoded':   // RFC1738
                $this->data = parse_str($this->body);
                break;

            case 'text/html':   // possibly an error
                throw new Exception\ServerError('API server returned unexpected "'.$contentType.'" data');

            default:
                throw new Exception\NotImplemented('Unknown result Content-Type "'.$contentType.'"');
                break;
        }

        /**
         * Check if any data has been loaded and throw an exception
         * (will not throw an exception for "204 No Content" http status)
         */
        if(!is_array($this->data)){
            $this->data = array();
            if($this->getStatusCode() != 204){
                throw new Exception\ServerError('Cannot parse response from API Server - type "'.$contentType.'"');
            }
        }

        /**
         * Check for existence of "error" response
         */
        $errMsg = null;
        if(isset($this->data) && isset($this->data['error'])){
            $errMsg = $this->data['error'];
        }

        /**
         * Check http header and thrown an exception if non-200 response
         */
        switch((int)$this->getStatusCode()){
            case 200:
            case 201:
            case 202:
            case 204:
                return;

            case 400:
                throw new Exception\BadQuery($errMsg);

            case 401:
            case 403:
                throw new Exception\Unauthorized($errMsg);

            case 404:
                throw new Exception\NotFound($errMsg);

            case 409:
                throw new Exception\Conflict($errMsg);


            case 429:
                throw new Exception\TooManyRequests($errMsg);

            case 501:
                throw new Exception\NotImplemented($errMsg);

            default:
            case 500:
            case 503:
            case 504:
                throw new Exception\ServerError($errMsg);
        }
    }

    /**
     * Process SimpleXMLElement and convert it to array
     *
     * @param \SimpleXMLElement $xml
     * @return array|null
     */
    protected function simpleXMLToArray(\SimpleXMLElement $xml){
   	    $return = array();
   	    $name = $xml->getName();
   	    $_value = trim((string)$xml);
   	    if(!strlen($_value)){$_value = null;};

   	    if($_value!==null){
   	        $return = $_value;
   	    }

   	    $children = array();
   	    $first = true;
   	    foreach($xml->children() as $elementName => $child){
   	        $value = $this->simpleXMLToArray($child);
   	        if(isset($children[$elementName])){
   	            if(is_array($children[$elementName])){
   	                if($first){
   	                    $temp = $children[$elementName];
   	                    unset($children[$elementName]);
   	                    $children[$elementName][] = $temp;
   	                    $first=false;
   	                }
   	                $children[$elementName][] = $value;
   	            }else{
   	                $children[$elementName] = array($children[$elementName],$value);
   	            }
   	        }
   	        else{
   	            $children[$elementName] = $value;
   	        }
   	    }
   	    if($children){
   	        $return = array_merge($return,$children);
   	    }

   	    return $return;
   	}


}