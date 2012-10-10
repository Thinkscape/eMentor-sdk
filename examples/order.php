<?php
require_once __DIR__.'/_init.php';

use EMT\Model\User;
use EMT\Model\Product;
use EMT\Model\Order;
use EMT\Model\OrderItem;

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, isset($apiEndpoint) ? $apiEndpoint : null);

/**
 * Try to retrieve all products from API server
 */
echo "Connecting to ".$client->getApiEndpoint()."\n";
$products = $client->findAll('product');

/**
 * Show response
 */
echo "Received ".count($products)." products from API server\n";
$x = 1;

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
}

/**
 * Try to create new client record
 */
$user = $client->create('user',array(
    'email' => md5(mt_rand()).'@'.md5(mt_rand()).'.com'
));

if(!$user || !($user instanceof User)){
    die("User was not created successfully!\n");
}
echo "Created new client record ".$user->id."\n";

/**
 * Attempt to create new order
 */
/* @var $order \EMT\Model\Order */
$order = $client->create('order',array(
    'userId' => $user->id
));

if(!$order || !($order instanceof Order)){
    die("Order was not created successfully!\n");
}
echo "Created new order ".$order->id."\n";
echo "Order data: \n";
foreach($order as $k=>$v){
    echo " - $k = ".(($v === null)?'NULL':(is_string($v)?'"'.$v.'"':(is_array($v)? print_r($v,true) : $v)) )."\n";
}

/**
 * Add a product to the order
 */
$item = $order->addItem(array(
    'productId' => $product->id
));

if(!$item || !($item instanceof OrderItem)){
    die("Order was not created successfully!\n");
}
echo "Created new order item ".$item->id."\n";
echo "Order item data: \n";
foreach($item as $k=>$v){
    echo " - $k = ".(($v === null)?'NULL':(is_string($v)?'"'.$v.'"':(is_array($v)? print_r($v,true) : $v)) )."\n";
}

/**
 * Attempt to retrieve order items and validate them
 */
echo "Validating order items ...\n";
$items = $order->getItems();
if(!$items || !is_array($items)){
    die("Cannot retrieve order items\n");
}elseif(!count($items)){
    die("Order does not have any items, even though we've added one.\n");
}elseif(count($items) !== 1){
    die("Order has invalid number of items.\n");
}elseif($items[0]->id != $item->id){
    die("Order does not contain our newly created order item.\n");
}

/**
 * Attempt to change order status to PENDING
 */
echo "Changing order status to PENDING";
$order->status = Order::STATUS_PENDING;
$order->save();

$order2 = $client->get('order',$order->id);
if($order2->status !== Order::STATUS_PENDING){
    die("The status change has not been saved on the server!\n");
}

/**
 * Delete the order from server
 */
echo "Changing order status to DELETED";
$order->status = Order::STATUS_DELETED;
$order->save();


echo "\nFinished\n";
