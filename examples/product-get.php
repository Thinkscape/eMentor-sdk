<?php
require_once __DIR__.'/_init.php';

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, $apiEndpoint);

/**
 * Try to retrieve all products from API server
 */
echo "Connecting to ".$client->getApiEndpoint()."\n";
$products = $client->findAll('product');

/**
 * Show response
 */
echo "Received ".count($products)." products from API server\n";
$x = 0;
foreach($products as $product){
    echo ' '.$x++.'. '.$product->id.': "'.substr($product->name,0,20).'..."'.' ('.(float)$product->price." zł)\n";
}
echo "\n";

if(!count($products)){
    die("Cannot continue - there are no products associated with this account...\n");
}else{
    /**
     * Pick one random product and try to fetch its data
     */
    $product = $products[mt_rand(0,count($products)-1)];
    echo "Trying to fetch details for a single product \"".$product->id."\"...\n";

    /**
     * Send query to API server
     */
    $product2 = $client->get('product',$product->id);

    /**
     * Check if the product has been returned
     */
    if($product2 === false){
        die("ERROR! Server reported that this product does not exist\n");
    }

    echo "Product data: \n";
    foreach($product2 as $k=>$v){
        echo " - $k = ".($v === null ? 'NULL' : (is_string($v) ? '"'.$v.'"' : $v) )."\n";
    }
}

echo "\nFinished\n";
