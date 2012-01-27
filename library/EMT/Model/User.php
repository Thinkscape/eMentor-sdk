<?php
namespace EMT\Model;

class User extends AbstractModel {

    protected static $associations = array(
        'order'   => '\EMT\Model\Order',
        'product' => '\EMT\Model\Product',
    );

    protected static $attributes = array(
        'id'               => AbstractModel::ATTR_RO,
        'email'            => AbstractModel::ATTR_RW,
        'type'             => AbstractModel::ATTR_RO,
        'typeName'         => AbstractModel::ATTR_RO,
        'status'           => AbstractModel::ATTR_RO,
        'statusName'       => AbstractModel::ATTR_RO,
        'statusProgress'   => AbstractModel::ATTR_RO,
        'dateCreated'      => AbstractModel::ATTR_RO,
        'dateCreatedIso'   => AbstractModel::ATTR_RO,
        'dateLogged'       => AbstractModel::ATTR_RO,
        'dateLoggedIso'    => AbstractModel::ATTR_RO,
        'visibleCatalog'   => AbstractModel::ATTR_RO,
        'registerIp'       => AbstractModel::ATTR_RW,
    );

    /**
     * Get all orders for this user.
     *
     * @param array  $searchCriteria
     * @param null   $order
     * @param string $orderDir
     * @param null   $limit
     * @param null   $offset
     * @return array
     */
    public function getOrders(
        $searchCriteria = array(), $order = null, $orderDir = 'ASC', $limit = null,
        $offset = null
    ){
        return $this->_client->getAssociation(
            'user',
            $this->id,
            'order',
            $searchCriteria,
            $order,
            $orderDir,
            $limit,
            $offset
        );
    }

    /**
     * Get all purchased products by this user
     *
     * @param array  $searchCriteria
     * @param null   $order
     * @param string $orderDir
     * @param null   $limit
     * @param null   $offset
     * @return array
     */
    public function getProducts(
        $searchCriteria = array(), $order = null, $orderDir = 'ASC', $limit = null,
        $offset = null
    ){
        return $this->_client->getAssociation(
            'user',
            $this->id,
            'product',
            $searchCriteria,
            $order,
            $orderDir,
            $limit,
            $offset
        );
    }

}