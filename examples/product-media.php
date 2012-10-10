<?php
require_once __DIR__.'/_init.php';

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, isset($apiEndpoint) ? $apiEndpoint : null);

/**
 * Try to retrieve all products from API server
 */
echo "Connecting to ".$client->getApiEndpoint()."\n";
$products = $client->findAll('product',array(),'dateCreated','desc',5);

/**
 * Show response
 */
echo "Received ".count($products)." products from API server\n";

if(!count($products)){
    die("Cannot continue - there are no products associated with this account...\n");
}else{
    /**
     * Pick one random product
     */
    $product = $products[mt_rand(0,count($products)-1)];

    /**
     * Try to fetch media for that product
     */
    echo "Trying to fetch all media for a single product \"".$product->id."\"...";
    $media = $product->getMedia();

    /**
     * Check if the product has been returned
     */
    if(!$media || !count($media)){
        die("ERROR! Server did not return any media for this product?\n");
    }else{
        echo "Success! \n\n";
    }

    /**
     * Display results
     */
    foreach($media as $m){
        echo "Media ".$m->id."\n";
        foreach($m as $k=>$v){
            echo " - $k = ".(($v === null)?'NULL':(is_string($v)?'"'.$v.'"':(is_array($v)? print_r($v,true) : $v)) )."\n";
        }
        echo "----~----~----~----~\n";
    }

    /**
     * Try to get product association for each media item
     */
    echo "Checking if media items have a valid \"product\" association ";
    foreach($media as $m){
        $product2 = $m->getProduct();
        if(!$product2 || $product2->id != $product->id){
            die("ERROR! Product associated with media \"".$media->id."\" is different than \"".$product->id."\"\n");
        }else{
            echo ".";
        }
    }
}

echo "\nFinished\n";
