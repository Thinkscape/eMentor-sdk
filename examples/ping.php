<?php
require_once __DIR__.'/_init.php';

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, isset($apiEndpoint) ? $apiEndpoint : null);

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
