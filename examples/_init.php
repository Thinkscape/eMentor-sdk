<?php
/**
 * Check config
 */
if(!file_exists(__DIR__.'/_config.php')){
    die(
        '_config.php not found! Please edit the file "_config.php.dist",'.
        'insert you API key info and save the file as "_config.php" in the same directory.'."\n"
    );
}
require_once __DIR__.'/_config.php';

/**
 * Check API key info
 */
if(!$keyId || !$keySecret){
    die("Please configure your access key inside ".basename(__FILE__)." before running examples\n");
}

/**
 * Check PHP version
 */
if(!version_compare(PHP_VERSION,'5.3.0', '>=')){
    die("eMentor SDK requires PHP version 5.3.0 or higher\n");
}


/**
 * Turn on verbose error reporting
 */
ini_set('display_errors',1);
error_reporting(E_ALL);

/**
 * Init autoload
 */
require_once __DIR__.'/../autoload_register.php';

/**
 * Register exception handler
 */
set_exception_handler(function(\Exception $e) use (&$client){
    $class = get_class($e);
    if(substr($class,0,1) == '\\'){
        $foo = preg_split('#^.*\\\\#',$class);
        $class = $foo[1];
    }

    echo "=========================================================================\n";
    echo "  Error! $class: ".$e->getMessage()."\n";
    echo "=========================================================================\n";
    echo $e->getTraceAsString()."\n";
    echo "-------------------------------------------------------------------------\n";

    /** @var \EMT\Client\Client $client */
    if($client){
        if($response = $client->getLastResponse()){
            echo "Last HTTP response: ".$response->getStatusCode()." ".$response->getReasonPhrase()."\n";
            echo "-------------------------------------------------------------------------\n";
            echo $response->getBody()."\n";
            echo "-------------------------------------------------------------------------\n";
        }
    }

    exit(1);
});
