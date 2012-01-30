<?php
require_once __DIR__.'/_init.php';

/**
 * Max number of items to retrieve
 */
$limit = 10;

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, isset($apiEndpoint) ? $apiEndpoint : null);
echo "Will use API server at ".$client->getApiEndpoint()."\n";

/**
 * Try to load products
 */
echo "Loading $limit products ... ";
$search = $client->findAll('product',array(),null,null,$limit);
if(!count($search)) die("Error! Cannot load data from api server\n");
echo " Found ".count($search)." items:\n"; $x=1;
foreach($search as $item){
   echo $x++.".Product \"".$item->id."\"\n";
    foreach($item as $k=>$v){
        echo "  - $k = ".(($v === null)?'NULL':(is_string($v)?'"'.$v.'"':(is_array($v)? print_r($v,true) : $v)) )."\n";
    }
}
echo "---~---~---~---~---~---~---~---~\n";

/**
 * Try to load orders
 */
echo "Loading $limit orders ... ";
$search = $client->findAll('order',array(),null,null,$limit);
if(!count($search)) die("Error! Cannot load data from api server\n");
echo " Found ".count($search)." items:\n"; $x=1;
foreach($search as $item){
   echo $x++.".Order \"".$item->id."\"\n";
    foreach($item as $k=>$v){
        echo "  - $k = ".(($v === null)?'NULL':(is_string($v)?'"'.$v.'"':(is_array($v)? print_r($v,true) : $v)) )."\n";
    }
}
echo "---~---~---~---~---~---~---~---~\n";


/**
 * Try to load orders items
 */
echo "Loading $limit order items ... ";
$search = $client->findAll('OrderItem',array(),null,null,$limit);
if(!count($search)) die("Error! Cannot load data from api server\n");
echo " Found ".count($search)." items:\n"; $x=1;
foreach($search as $item){
   echo $x++.".Order item \"".$item->id."\"\n";
    foreach($item as $k=>$v){
        echo "  - $k = ".(($v === null)?'NULL':(is_string($v)?'"'.$v.'"':(is_array($v)? print_r($v,true) : $v)) )."\n";
    }
}
echo "---~---~---~---~---~---~---~---~\n";

/**
 * Try to load users
 */
echo "Loading $limit users ... ";
$search = $client->findAll('user',array(),null,null,$limit);
if(!count($search)) die("Error! Cannot load data from api server\n");
echo " Found ".count($search)." items:\n"; $x=1;
foreach($search as $item){
   echo $x++.".User \"".$item->id."\"\n";
    foreach($item as $k=>$v){
        echo "  - $k = ".(($v === null)?'NULL':(is_string($v)?'"'.$v.'"':(is_array($v)? print_r($v,true) : $v)) )."\n";
    }
}
echo "---~---~---~---~---~---~---~---~\n";


/**
 * Try to load media
 */
echo "Loading $limit media... ";
$search = $client->findAll('media',array(),null,null,$limit);
if(!count($search)) die("Error! Cannot load data from api server\n");
echo " Found ".count($search)." items:\n"; $x=1;
foreach($search as $item){
   echo $x++.".Media \"".$item->id."\"\n";
    foreach($item as $k=>$v){
        echo "  - $k = ".(($v === null)?'NULL':(is_string($v)?'"'.$v.'"':(is_array($v)? print_r($v,true) : $v)) )."\n";
    }
}
echo "---~---~---~---~---~---~---~---~\n";




echo "\nFinished\n";
