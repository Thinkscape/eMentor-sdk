<?php
require_once __DIR__.'/_init.php';

/**
 * This example works only in a browser
 */
if(PHP_SAPI === 'cli'){
	die("Please run this example in a browser.\n");
}

/**
 * Create client instance
 */
$client = new EMT\Client\Client($keyId, $keySecret, isset($apiEndpoint) ? $apiEndpoint : null);

/**
 * Prepare session
 */
session_start();

/**
 * Determine return url
 */
$returnUrl = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {$returnUrl .= "s";}
$returnUrl .= "://";
if ($_SERVER["SERVER_PORT"] != "80") {
	$returnUrl .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
} else {
	$returnUrl .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
}

/**
 * Step 1 - redirect to login page
 */
if(empty($_GET['authToken']) && empty($_SESSION['user'])){
	// Redirect the user to login page
	$loginUrl = $client->getLoginUrl('',$returnUrl);
	echo '<h3>Not logged in locally</h3>';
	echo '<a href="'.htmlentities($loginUrl,ENT_COMPAT,'utf-8').'">Click here to log</a><hr>';
	echo '<a href="https://www.ementor.pl/wyloguj?returnUrl='.urlencode($returnUrl).'">Log out remotely</a>';
	exit(1);
}

/**
 * Step 2 - validate auth token
 */
elseif(!empty($_GET['authToken'])){
	// Try to get user data for the auth token
	if(!$user = $client->getUserFromAuthToken($_GET['authToken'])){
		echo "<h3>Cannot retrieve user info from auth token - try logging again</h3>";
		echo '<a href="'.htmlentities($_SERVER["PHP_SELF"],ENT_COMPAT,'utf-8').'">Go back</a>';
		exit(1);
	}

	// Login successful! Store in session
	$_SESSION['user'] = $user->toArray();

	// Show main page again
	header('Location: '.$_SERVER["PHP_SELF"]);
}

/**
 * Step 3 - logged in!
 */
elseif(!empty($_SESSION['user']) && empty($_GET['logout'])){
	echo "<h3>Logged in locally as ".$_SESSION['user']['email']."</h3>";
	echo '<pre>';
	print_r($_SESSION['user']);
	echo '</pre><hr>';
	echo '<a href="'.htmlentities($_SERVER["PHP_SELF"].'?logout=1',ENT_COMPAT,'utf-8').'">Log out locally</a><hr>';
	$returnUrl .= '?logout=1';
	echo '<a href="https://www.ementor.pl/wyloguj?returnUrl='.urlencode($returnUrl).'">Log out remotely</a>';
	exit(1);
}

/**
 * Step 4 - log out
 */
else{
	unset($_SESSION['user']);
	header('Location: '.$_SERVER["PHP_SELF"]);
	exit(1);
}