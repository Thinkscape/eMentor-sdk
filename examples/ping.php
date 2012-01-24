<?php
if(!file_exists(__DIR__.'_config.php'))die('_config.php not found! Please edit the file "_config.php.dist", insert you API key info and save the file as "_config.php" in the same directory.'."\n");
require_once __DIR__.'/_config.php';

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, $apiEndpoint);

/**
 * Try to send ping to API server
 */
echo "Connecting to ".$client->getApiEndpoint()."\n";
$response = $client->ping();

/**
 * Show response
 */
echo "Ping result: ";
var_dump($response);

echo "\nFinished\n";
