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
$products = $client->findAll('product',array(),null,null,5);
echo "Received ".count($products)." products from API server\n";

if(!count($products)){
    die("Cannot continue - there are no products associated with this account...\n");
}else{
    /**
     * Pick one random product and try to fetch its data
     */
    $product = $products[mt_rand(0,count($products)-1)];
    echo "Selected a single product to search for:\n";
    foreach($product as $k=>$v){
        echo " - $k = ".(($v === null)?'NULL':(is_string($v)?'"'.$v.'"':(is_array($v)? print_r($v,true) : $v)) )."\n";
    }
    echo "\n";

    /**
     * Try to query for products by id
     */
    echo "Trying to find product by its id... ";
    $search1 = $client->findAll('product',array(
        'id' => $product->id
    ));

    if(count($search1) < 1){
        die("Search failed! Could not find any matching items\n");
    }else{
        // verify that collection contains our product
        $found = false;
        foreach($search1 as $p){
            if($p->id == $product->id){
                echo "Success!\n";
                $found = true;
                break;
            }
        }
        if(!$found){
            die("Search failed! The result does not contain our product\n");
        }
    }

    /**
     * Try to query for products by price
     */
    echo "Trying to find product by its price \"".$product->price."\"... ";
    $search2 = $client->findAll('product',array(
        'price' => $product->price
    ));

    if(count($search2) < 1){
        die("Search failed! Could not find any matching items\n");
    }else{
        // verify that collection contains our product
        $found = false;
        foreach($search2 as $p){
            if($p->id == $product->id){
                echo "Success!\n";
                $found = true;
                break;
            }
        }
        if(!$found){
            die("Search failed! The result does not contain our product\n");
        }
    }

    /**
     * Try to query for products by price with less-than comparison
     */
    echo "Trying to find product by matching price < ".($product->price+10)."... ";
    $search3 = $client->findAll('product',array(
        array('price', 'lt', $product->price + 10)
    ));

    if(count($search3) < 1){
        die("Search failed! Could not find any matching items\n");
    }else{
        // verify that collection contains our product
        $found = false;
        foreach($search3 as $p){
            if($p->id == $product->id){
                echo "Success!\n";
                $found = true;
                break;
            }
        }
        if(!$found){
            die("Search failed! The result does not contain our product\n");
        }
    }

    /**
     * Try to query for products by name substring match
     */
    mb_internal_encoding('UTF8');
    $substr = mb_substr($product->name,0,ceil(mb_strlen($product->name)/2));
    echo "Trying to find product by matching name like  \"$substr\"... ";
    $search4 = $client->findAll('product',array(
        array('name', 'like', $substr)
    ));

    if(count($search4) < 1){
        die("Search failed! Could not find any matching items\n");
    }else{
        // verify that collection contains our product
        $found = false;
        foreach($search4 as $p){
            if($p->id == $product->id){
                echo "Success!\n";
                $found = true;
                break;
            }
        }
        if(!$found){
            die("Search failed! The result does not contain our product\n");
        }
    }
}

echo "\nFinished\n";
