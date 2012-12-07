<?php
require_once __DIR__.'/_init.php';

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, isset($apiEndpoint) ? $apiEndpoint : null);

/**
 * Try to retrieve all users from API server
 */
echo "Retrieving users from ".$client->getApiEndpoint()."\n";
$users = $client->findAll('user',array(),'dateCreated','desc',10);

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

	/**
	 * Try to get user's products
	 */
	/* @var $user2 \EMT\Model\User */
	echo "Retrieving purchased products for user ".$user->id."\n";
	$products = $user2->getProducts(array(), null,'ASC',100);

	if(!is_array($products)){
		die("Cannot retrieve purchased products for user ".$user->id);
	}

	if(!count($products)){
		echo "This user does not have any purchased products.\n";
	}else{
		echo "List of ".count($products)." product(s) purchased by the user:\n";
		$x=1;
		foreach($products as $product){
			/* @var $product \EMT\Model\Product */
			echo ' '.$x++.'. '.$product->id.': "'.substr($product->name,0,20).'..."'.' ('.(float)$product->price." zł)\n";
		}
	}
}

echo "\nFinished\n";
