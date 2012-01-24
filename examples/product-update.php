<?php
require_once __DIR__.'/_init.php';

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, $apiEndpoint);

/**
 * Try to retrieve a list of products from API server
 */
echo "Connecting to ".$client->getApiEndpoint()."\n";
$products = $client->findAll('product',array(),null, null, 5);
echo "Received ".count($products)." products from API server\n";

if(!count($products)){
    die("Cannot continue - there are no products we could update...\n");
}else{
    /**
     * Pick one random product
     */
    $product = $products[mt_rand(0,count($products)-1)];
    echo "Trying to fetch details for a single product \"".$product->id."\"...\n";

    /**
     * We are changing its name
     */
    $oldName = $product->name;
    $newName = $oldName . ".";

    /**
     * Send query to API server
     */
    echo "Updating product ".$product->id." name to \"$newName\"...\n";
    $product2 = $client->update(
        'product',
        $product->id,
        array(
            'name' => $newName
        )
    );

    /**
     * Check if the product has been updated
     */
    if($product2->name !== $newName){
        die("Cannot update product name!\n");
    }else{
        echo "Success!\n";
    }

    /**
     * Try to retrieve the product directly from the server
     */
    echo "Loading product data from API server...\n";
    $product3 = $client->get('product',$product->id);
    if($product3->name !== $newName){
        die("Cannot update product name!\n");
    }

    /**
     * Revert product name
     */
    echo "Reverting changes to product ".$product->id."...\n";
    $product4 = $client->update(
        'product',
        $product->id,
        array(
            'name' => $oldName
        )
    );

    /**
     * Check if the product has been updated
     */
    if($product4->name !== $oldName){
        die("Cannot revert product name!\n");
    }else{
        echo "Success!\n";
    }
}

echo "\nFinished\n";
