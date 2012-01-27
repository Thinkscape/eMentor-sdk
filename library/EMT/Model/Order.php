<?php
namespace EMT\Model;

class Order extends AbstractModel {

    protected static $associations = array(
        'item' => '\EMT\Model\OrderItem',
    );

    protected static $attributes = array(
        'id'                           => AbstractModel::ATTR_RO,
        'userId'                       => AbstractModel::ATTR_RW,
        'status'                       => AbstractModel::ATTR_RW,
        'statusName'                   => AbstractModel::ATTR_RO,
        'statusProgress'               => AbstractModel::ATTR_RO,
        'dateCreated'                  => AbstractModel::ATTR_RO,
        'dateCreatedIso'               => AbstractModel::ATTR_RO,
        'served'                       => AbstractModel::ATTR_RO,
        'value'                        => AbstractModel::ATTR_RO,
        'campaignName'                 => AbstractModel::ATTR_RW,
        'referrerUrl'                  => AbstractModel::ATTR_RW,
    );

    /**
     * Get all items in this order
     *
     * @param array       $searchCriteria
     * @param null|string $order
     * @param string      $orderDir
     * @param null        $limit
     * @param null        $offset
     * @return array
     */
    public function getItems(
        $searchCriteria = array(), $order = 'dateCreated', $orderDir = 'ASC', $limit = null,
        $offset = null
    ){
        return $this->_client->getAssociation(
            'order',
            $this->id,
            'item',
            $searchCriteria,
            $order,
            $orderDir,
            $limit,
            $offset
        );
    }

}