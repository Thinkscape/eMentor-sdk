<?php
use EMT\Model\User;

require_once __DIR__.'/_init.php';

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, isset($apiEndpoint) ? $apiEndpoint : null);

/**
 * Define user email
 */
$userEmail = '';
if(!$userEmail){
    die("Please define \$userEmail before running this example\n");
}

/**
 * Try to create new user with the given email address
 */
echo "Creating new user with email $userEmail at API server ".$client->getApiEndpoint()."\n";
$user = $client->create('user',array(
    'email' => $userEmail
));


/**
 * Show response
 */
if(!$user || !($user instanceof User)){
    die("User was not created succefully!\n");
}else{

    echo "New user data: \n";
    foreach($user as $k=>$v){
        echo " - $k = ".($v === null ? 'NULL' : (is_string($v) ? '"'.$v.'"' : $v) )."\n";
    }
}

echo "\nFinished\n";
