<?php
namespace EMT\Model;


class Product extends AbstractModel {

    protected static $associations = array(
        'media' => '\EMT\Model\Media',
    );

    protected static $attributes = array(
        'id'              => AbstractModel::ATTR_RO,
        'type'            => AbstractModel::ATTR_RW,
        'typeName'        => AbstractModel::ATTR_RO,
        'name'            => AbstractModel::ATTR_RW,
        'descr'           => AbstractModel::ATTR_RW,
        'price'           => AbstractModel::ATTR_RW,
        'status'          => AbstractModel::ATTR_RW,
        'statusName'      => AbstractModel::ATTR_RO,
        'statusProgress'  => AbstractModel::ATTR_RO,
        'dateCreated'     => AbstractModel::ATTR_RO,
        'dateCreatedIso'  => AbstractModel::ATTR_RO,
        'dateModified'    => AbstractModel::ATTR_RO,
        'dateModifiedIso' => AbstractModel::ATTR_RO,
        'visibleCatalog'  => AbstractModel::ATTR_RO,
        'visibleAff'      => AbstractModel::ATTR_RW,
        'allowDownload'   => AbstractModel::ATTR_RO,
        'terms'           => AbstractModel::ATTR_RW,
        'affDescr'        => AbstractModel::ATTR_RW,
    );

    /**
     * Get all media associated with this product.
     *
     * @param array  $searchCriteria
     * @param null   $order
     * @param string $orderDir
     * @param null   $limit
     * @param null   $offset
     * @return array
     */
    public function getMedia($searchCriteria = array(), $order = null, $orderDir = 'ASC', $limit = null, $offset = null)
    {
        return $this->_client->getAssociation(
            'product',
            $this->id,
            'media',
            $searchCriteria,
            $order,
            $orderDir,
            $limit,
            $offset
        );
    }

}