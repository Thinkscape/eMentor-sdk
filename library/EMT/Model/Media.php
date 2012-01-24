<?php
namespace EMT\Model;


class Media extends AbstractModel {

    protected static $associations = array(
        'product' => '\EMT\Model\Product',
    );

    protected static $attributes = array(
        'id'              => AbstractModel::ATTR_RO,
        'type'            => AbstractModel::ATTR_RW,
        'productId'       => AbstractModel::ATTR_RW,
        'name'            => AbstractModel::ATTR_RW,
        'status'          => AbstractModel::ATTR_RW,
        'statusName'      => AbstractModel::ATTR_RO,
        'statusProgress'  => AbstractModel::ATTR_RO,
        'dateCreated'     => AbstractModel::ATTR_RO,
        'dateCreatedIso'  => AbstractModel::ATTR_RO,
        'dateModified'    => AbstractModel::ATTR_RO,
        'dateModifiedIso' => AbstractModel::ATTR_RO,
        'isPreview'       => AbstractModel::ATTR_RW,
        'rawName'         => AbstractModel::ATTR_RO,
        'rawLength'       => AbstractModel::ATTR_RO,
    );

    /**
     * Get the product associated with this media
     *
     * @return \EMT\Model\Product
     */
    public function getProduct(){
        return current($this->_client->getAssociation(
            'media',
            $this->id,
            'product',
            array(),
            null,
            null,
            1
        ));
    }

}