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
$media = $client->findAll('media',array(),'dateCreated','desc');

/**
 * Show response
 */
echo "Received ".count($media)." media items from API server\n";
$x = 0;
foreach($media as $m){
    echo ' '.$x++.'. '.$m->type.' '.$m->id."\n";
}
echo "\n";

if(!count($media)){
    die("Cannot continue - there are no media associated with this account...\n");
}else{
    /**
     * Pick one random media and try to fetch its data
     */
    $m = $media[mt_rand(0,count($media)-1)];
    echo "Trying to fetch details for a single media item \"".$m->id."\"...\n";

    /**
     * Send query to API server
     */
    $m2 = $client->get('media',$m->id);

    /**
     * Check if the product has been returned
     */
    if($m2 === false){
        die("ERROR! Server reported that this media does not exist\n");
    }

    echo "Media data: \n";
    foreach($m2 as $k=>$v){
        echo " - $k = ".($v === null ? 'NULL' : (is_string($v) ? '"'.$v.'"' : $v) )."\n";
    }
}

echo "\nFinished\n";
