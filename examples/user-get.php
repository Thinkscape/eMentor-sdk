<?php
require_once __DIR__.'/_init.php';

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, $apiEndpoint);

/**
 * Try to retrieve all users from API server
 */
echo "Retrieving users from ".$client->getApiEndpoint()."\n";
$users = $client->findAll('user',array(),'dateCreated','desc');

/**
 * Show response
 */
echo "Received ".count($users)." users from API server\n";
$x = 1;
foreach($users as $user){
    echo ' '.$x++.'. '.$user->id.' created '.date('r',$user->dateCreated)."\n";
}
echo "\n";

if(!count($users)){
    die("Cannot continue - there are no users registered with your brand...\n");
}else{
    /**
     * Pick one random user and display his/her data
     */
    $user = $users[mt_rand(0,count($users)-1)];
    echo "Trying to fetch details for a single user item \"".$user->id."\"...\n";

    /**
     * Send query to API server
     */
    $user2 = $client->get('user',$user->id);

    /**
     * Check if the user item has been returned
     */
    if($user2 === false){
        die("ERROR! Server reported that this user does not exist\n");
    }

    echo "User data: \n";
    foreach($user2 as $k=>$v){
        echo " - $k = ".($v === null ? 'NULL' : (is_string($v) ? '"'.$v.'"' : $v) )."\n";
    }
}

echo "\nFinished\n";
