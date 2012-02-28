<?php
namespace EMT\Model;

class Affsitelink extends AbstractModel {

    protected static $associations = array(
    );

    protected static $attributes = array(
		'id' => AbstractModel::ATTR_RO,
		'domain' => AbstractModel::ATTR_RO,
	);

}