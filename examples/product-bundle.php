<?php
require_once __DIR__.'/_init.php';

use EMT\Model\Product;

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, isset($apiEndpoint) ? $apiEndpoint : null);

/**
 * Try to retrieve all bundles from API server
 */
echo "Connecting to ".$client->getApiEndpoint()."\n";
$bundles = $client->findAll('product',array(
    'type' => Product::TYPE_BUNDLE
),'dateCreated','desc',5);

/**
 * Show response
 */
echo "Received ".count($bundles)." bundles from API server\n";

if(!count($bundles)){
    die("Cannot continue - there are no bundles associated with this account...\n");
}else{
    /**
     * Pick one random bundle
     */
    $bundle = $bundles[mt_rand(0,count($bundles)-1)];

    /**
     * Retrieve products contained inside
     */
    echo "Trying to fetch all products inside a bundle \"".$bundle->id."\"...";
    $products = $bundle->getBundleProducts();

    /**
     * Check if the product has been returned
     */
    if(!$bundles || !count($bundles)){
        die("ERROR! Server did not return any products for this bundle.\n");
    }else{
        echo "Success! \n\n";
    }

    echo "Bundle \"" . $bundle->name ."\" contents:\n";
    foreach($products as $product){
        echo " - ".$product['name']."\n";
    }
}

echo "\nFinished\n";
