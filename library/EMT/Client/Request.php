<?php
namespace EMT\Client;


class Request
{
    /**
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
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var string
     */
    protected $format = 'json';

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @var array
     */
    protected $data = array();


    public function __construct($url, $format = 'json', $method = null, $params = null, $data = null)
    {
        $this->setUrl($url);

        if($method !== null){
            $this->setMethod($method);
        }

        if($format !== null){
            $this->setFormat($format);
        }

        if($params !== null){
            $this->setParams($params);
        }

        if($data !== null){
            $this->setData($data);
        }
    }

    /**
     * Add a single header
     *
     * @param string $name
     * @param string $val
     */
    public function addHeader($name, $val)
    {
        $this->headers[$name] = $val;
    }

    /**
     * Return a single header
     *
     * @param string $name
     * @return string
     */
    public function getHeader($name){
        return $this->headers[$name];
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
        if($this->body === null){
            $this->encodeBody();
        }

        return $this->body;
    }

    /**
     * Prepare request body from data in selected format
     */
    protected function encodeBody()
    {
        if($this->format == 'json'){
            $this->body = json_encode( $this->getData() );
        }elseif($this->format == 'xml'){
            $this->body = $this->arrayToXml($this->getData());
        }else{
            $this->body = http_build_query( $this->getData() );
        }
    }

    /**
     * @param array $bodyParams
     */
    public function setData($bodyParams)
    {
        $this->data = $bodyParams;
        $this->encodeBody();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {

        /**
         * Validate format
         */
        $format = strtolower($format);

        if (!in_array( $format, array('json', 'xml', 'post') ))
            throw new \BadMethodCallException('Unknown request format "' . $format . '"');

        if($format !== $this->format){
            $this->encodeBody();
        }

        /**
         * Add http header
         */
        if($format == 'json'){
            $this->addHeader( 'Content-Type', 'application/json' );
        }elseif($format == 'xml'){
            $this->addHeader( 'Content-Type', 'text/xml' );
        }else{
            $this->addHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
        }

        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param array $queryParams
     */
    public function setParams($queryParams)
    {
        $this->params = $queryParams;
    }

    /**
     * Set a single param value
     *
     * @param string $name
     * @param string $value
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * Get a single param
     *
     * @param string   $name
     * @return string
     */
    public function getParam($name)
    {
        return $this->params[$name];
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
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
     * Get URI containing all parameters but no host, scheme and port.
     *
     * @return string
     */
    public function getFullUri()
    {
        /**
         * Parse original url
         */
        $parse = parse_url($this->url);

        /**
         * Process params inside the url
         */
        if(isset($parse['query']))
            parse_str($parse['query'],$params);
        else
            $params = array();

        /**
         * Merge the params from url with params explicitly set on request object
         */
        $params = array_merge($this->params, $params);

        return
            $parse['path'].(count($params) ? '?'.http_build_query($params) : '')
        ;
    }

    /**
     * Get full URL with all parameters
     *
     * @return string
     */
    public function getFullUrl()
    {
        /**
         * Parse original url
         */
        $parse = parse_url($this->url);

        /**
         * Return full url
         */
        return
            $parse['scheme'].'://'.
            $parse['host'].
            (isset($parse['port'])?':'.$parse['port']:'').
            $this->getFullUri()
        ;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Convert an array to XML document
     *
     * @param $array
     * @param string $rootElement
     * @return string
     */
    protected function arrayToXML($array, $rootElement = 'data'){
        $xml = new \SimpleXMLElement(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".
            "<{$rootElement} generator=\"eMentor Client\"></{$rootElement}>"
        );

        $f = function($f,$c,$a){
            foreach($a as $k=>$v) {
                if(is_array($v)) {
                    $ch=$c->addChild($k);
                    $f($f,$ch,$v);
                } else {
                    $c->addChild($k,$v);
                }
            }
        };
        $f($f,$xml,$array);
        return $xml->asXML();
    }

}