<?php
namespace EMT\Model;

use EMT\Client\Client;

abstract class AbstractModel extends \ArrayObject
{
    const ATTR_RO = 1;
    const ATTR_RW = 2;
    const ATTR_REQ = 4;
    const ATTR_OPT = 8;

    protected static $attributes = array();
    protected static $associations = array();

    /**
     * And instance of EMT Client used for updating item
     *
     * @var \EMT\Client\Client|null
     */
    protected $_client;

    public function __construct($data, Client &$clientInstance = null){
        if($clientInstance !== null){
            $this->_client = &$clientInstance;
        }
        return parent::__construct($data,\ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Validate that an attribute exists within this model
     *
     * @abstract
     * @param string    $attribute
     * @param int       $mode
     * @return bool
     */
    public static function validateAttribute($attribute, $mode = 0){
        if($mode === 0){
            return array_key_exists($attribute,static::$attributes);
        }else{
            return
                array_key_exists($attribute,static::$attributes) &&
                static::$attributes[$attribute] & $mode
             ;
        }
    }

    /**
     * Return all item data as an array
     *
     * @return array
     */
    public function toArray(){
        return $this->getArrayCopy();
    }

    /**
     * Get php class name for an association.
     * @static
     * @param $association
     * @return string|null
     */
    public static function getAssociationClass($association){
        return static::$associations[$association];
    }
}