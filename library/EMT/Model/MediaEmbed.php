<?php
namespace EMT\Model;

class MediaEmbed extends AbstractModel {

    protected static $associations = array(
        'user' => '\EMT\Model\User',
        'media' => '\EMT\Model\Media',
    );

    protected static $attributes = array(
        'template'              => AbstractModel::ATTR_RO,
        'mediaId'               => AbstractModel::ATTR_RO,
        'userId'                => AbstractModel::ATTR_RO,
        'html'                  => AbstractModel::ATTR_RW,
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
        return $this->_client->getAssociation(
            'media',
            $this->id,
            'embed',
            array(
                'userId' => $user->id,
            )
        );
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