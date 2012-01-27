<?php
namespace EMT\Model;

class OrderItem extends AbstractModel {

    protected static $associations = array(
        'order' => '\EMT\Model\Order',
        'product' => '\EMT\Model\Product',
    );

    protected static $attributes = array(
        'id'                          => AbstractModel::ATTR_RO,
        'orderId'                     => AbstractModel::ATTR_RW,
        'productId'                   => AbstractModel::ATTR_RW,
        'served'                      => AbstractModel::ATTR_RO,
        'dateCreated'                 => AbstractModel::ATTR_RO,
        'dateModified'                => AbstractModel::ATTR_RO,
        'value'                       => AbstractModel::ATTR_RO,
    );

    /**
     * Get the order
     *
     * @return \EMT\Model\Order
     */
    public function getOrder(){
        return $this->_client->getItem('order',$this->orderId);
    }

    /**
     * Get the product
     *
     * @return \EMT\Model\Product
     */
    public function getProduct(){
        return $this->_client->getItem('product',$this->productId);
    }

}