<?php
namespace EMT\Model;

class Media extends AbstractModel {

    protected static $associations = array(
        'product'   => '\EMT\Model\Product',
        'embed'     => '\EMT\Model\MediaEmbed',
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

    /**
     * Get media embeds for the specified user
     *
     * @param User      $user       The user to generate embeds for.
     * @return \EMT\Model\MediaEmbed[]
     */
    public function getEmbeds(User $user){
        if(!$this->isPreview){
            // get a signed embed
            return $this->_client->getAssociation(
                'media',
                $this->id,
                'embed',
                array(
                    'userId' => $user->id,
                )
            );
        }else{
            // get a preview (free) media embed
            return $this->_client->getAssociation(
                'media',
                $this->id,
                'embed'
            );
        }
    }

    /**
     * Get a single media embed for the specified user
     *
     * @param User      $user       The user to generate embeds for.
     * @param string    $template   Embed template name
     * @return \EMT\Model\MediaEmbed
     */
    public function getEmbed(User $user, $template = 'basic'){
        return @current($this->_client->getAssociation(
            'media',
            $this->id,
            'embed',
            array(
                'userId' => $user->id,
                'template' => $template
            )
        ));
    }


}