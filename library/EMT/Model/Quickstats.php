<?php
namespace EMT\Model;

class Quickstats extends AbstractModel {

    protected static $associations = array(
    );

    protected static $attributes = array(
		'd' => AbstractModel::ATTR_RO,
		'wk' => AbstractModel::ATTR_RO,
		'mo' => AbstractModel::ATTR_RO,
		'earnings' => AbstractModel::ATTR_RO,
	);

}