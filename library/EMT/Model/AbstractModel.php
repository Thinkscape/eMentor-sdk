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

    protected $_dirtyFields = array();

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

    /**
     * Store changes on the API server
     */
    public function save(){
        $updateData = array();
        foreach(static::$attributes as $attr =>$access){
            if($access === self::ATTR_RW && isset($this->_dirtyFields[$attr])){
                $updateData[$attr] = $this[$attr];
            }
        }

        $this->_dirtyFields = array();

        $modelName = strtolower(substr(get_called_class(),strripos(get_called_class(),'\\')+1));

        return $this->_client->update($modelName,$this->id,$updateData);
    }

    public function offsetSet($attr, $val){
        $this->_dirtyFields[$attr] = 1;
        parent::offsetSet($attr, $val);
    }
}