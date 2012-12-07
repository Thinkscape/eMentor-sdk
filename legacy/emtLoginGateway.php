<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);



require_once('EMTApi.php');
$config = array(
	'storeRef' => 'store',
	'productKey' => 'someproduct',
	'apiKey' => 'ABCXYZ',
);
$api = new EMT_Api($config);



session_start();


// 		$api->login();
// 		$api->verifyAuthToken()
//		$api->userCanView()
//		$api->logout()


if($_GET['doLogin']){
	
	// -- redirect to login page
	$api->login();
	exit();

}elseif($_GET['doLogout']){
	
	$api->logout();
	exit();

}else{

	if(!empty($_GET['authToken'])){
		// -- auth token is here! User came from login page ?
		// verify!
		if($api->verifyAuthToken()){
			echo '<h2>Logged in!</h2>';
			echo '<h2>Verified authentication token (user is indeed logged in!)</h2>';
			
			// -- check if user has purchased this training
			if($api->userCanView()){
				echo '<h2>User can access this training!</h2>';
			}else{
				echo '<h2>User can NOT access this training (not purchased?)</h2>';
			}
			
			echo '<p><a href="'.$_SERVER['SCRIPT_NAME'].'?doLogout=1">Click here to LOGOUT</a></p>';
		
		}else{
			// cannot verify token. User logged out? Maybe we should try again?
			echo '<h2>Error! Could not verify authentication token! (logged out?)</h2>';
			echo '<p><a href="'.$_SERVER['SCRIPT_NAME'].'?doLogin=1">Click here to try log in again</a></p>';
		}
	}else{
		// -- we are not logged in and not yet attempted to do so
		echo '<h2>Not logged in</h2>';
		echo '<p><a href="'.$_SERVER['SCRIPT_NAME'].'?doLogin=1">Click here to log in</a></p>';
	}


}

