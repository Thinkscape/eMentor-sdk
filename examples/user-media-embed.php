<?php
use EMT\Model\User;

require_once __DIR__.'/_init.php';

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, $apiEndpoint);

/**
 * Define user email
 */
$userEmail = 'acmeclient@ementor.pl';
if(!$userEmail){
    die("Please define \$userEmail before running this example\n");
}

/**
 * Try to retrieve user data
 */
echo "Loading data for user $userEmail from API server ".$client->getApiEndpoint()."\n";
$user = $client->get('user',$userEmail);

/**
 * Show response
 */
if(!$user || !($user instanceof User)){
    die("Could not find user with email $userEmail!\n");
}else{
    /**
     *
     * Load user's orders
     *
     */
    /** @var $user \EMT\Model\User */
    echo "Trying to fetch user's finished orders... ";
    $orders = $user->getOrders(array(
        'status' => 100
    ));
    if(count($orders)){
        echo "Success! ".count($orders)." orders.\n";
    }else{
        die("Warning. This user does not have any orders.\n");
    }

    /**
     *
     * Pick one order and get its items
     *
     */
    /** @var $order \EMT\Model\Order */
    $order = $orders[mt_rand(0,count($orders)-1)];
    echo "Trying to fetch items for order ".$order->id."...";
    $items = $order->getItems();
    if(count($items)){
        echo "Success! Order has ".count($items)." item(s)\n";
    }else{
        die("Error! Order does not have any items?\n");
    }

    /**
     *
     * Get product for the first order item
     *
     */
    /** @var $item \EMT\Model\OrderItem */
    $item = $items[0];
    echo "Fetching product for the first order item \"".$item->id."\"...";
    /** @var $product \EMT\Model\Product */
    $product = $item->getProduct();
    if($product){
        echo "Success! Loaded product \"".$product->id."\"\n";
    }else{
        die("Error! Cannot load product data\n");
    }

    echo "Product data: \n";
    foreach($product as $k=>$v){
        echo " - $k = ".($v === null ? 'NULL' : (is_string($v) ? '"'.$v.'"' : $v) )."\n";
    }

    /**
     *
     * Get all product media
     *
     */
    echo "Loading product \"".$product->id."\" media items...";
    $media = $product->getMedia();
    if(count($media)){
        echo "Success! Product contains ".count($media)." media.\n";
    }else{
        die("Error! Product does not contain any media?\n");
    }


    /**
     *
     * Generate embed codes for all media items
     *
     */
    echo "Attempting to generate media embeds:\n";

    foreach($media as $m){
        /** @var $m \EMT\Model\Media */
        echo " - ".($m->isPreview?'PREVIEW ':'').$m->type." ".$m->id.", user: ".$user->id."\n";
        $embeds = $m->getEmbeds($user);
        if(!$embeds) die("Error! Cannot load embeds\n");
        foreach($embeds as $embed){
            echo "    - ".$embed->template.": \"".$embed->html."\"\n";
        }
    }


}

echo "\nFinished\n";
