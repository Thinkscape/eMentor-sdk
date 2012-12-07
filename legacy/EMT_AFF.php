<?php

if(!function_exists('EMT_AFF')){
	global $EMTAFFconfig;
	$EMTAFFconfig = array(
		'linkId' => null,
		'noAffImg' => '<img src="http://pp.ementor.pl/track/:linkId/img?ref=:ref" alt="" />',
		'noAffJs' => '<script type="text/javascript" src="http://pp.ementor.pl/track/:linkId/js?ref=:ref"></script>',
		'affImg' => '<img src="http://pp.ementor.pl/track/:linkId/:affId/img?ref=:ref" alt="" />',
		'affJs' => '<script type="text/javascript" src="http://pp.ementor.pl/track/:linkId/:affId/js?ref=:ref"></script>',
		'params' => array('EMTAFF','emtaff','aff','a'),
	);

	// store local cookie
	
	foreach($EMTAFFconfig['params'] as $p){
		if(isset($_GET[$p]) && strlen($_GET[$p]) > 0){
			@setcookie(
				'emtaff',
				substr(preg_replace('/[^a-zA-Z0-9\-\_\=]/','',$_GET[$p]),0,25),
				time()+15552000,
				'/'
			);
			break;
		}
	}

	
	function EMT_AFF($linkId = null, $method = 'img'){
		global $EMTAFFconfig;
		
		if($linkId === null)
			$linkId = $EMTAFFconfig['linkId'];
			
		$linkId = (int)$linkId;
		if(!$linkId)
			return;
		
		$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
		
		// -- try to determine affId
		foreach($EMTAFFconfig['params'] as $p){
			if(isset($_GET[$p]) && strlen($_GET[$p]) > 0){
				$frag = str_replace(
					array(
						':affId',
						':linkId',
						':ref'
					),array(
						substr(preg_replace('/[^a-zA-Z0-9\-\_\=]/','',$_GET[$p]),0,25),
						$linkId,
						$ref ? htmlentities(urlencode($ref)) : '' 
					),
					$method === 'js' ? $EMTAFFconfig['affJs'] : $EMTAFFconfig['affImg']
				);
				
				echo $frag;
				return;
			}
		}
		
		// -- no affId could be found
		$frag = str_replace(
			array(
				':linkId',
				':ref'
			),array(
				$linkId,
				$ref ? htmlentities(urlencode($ref)) : '' 
			),
			$method === 'js' ? $EMTAFFconfig['noAffJs'] : $EMTAFFconfig['noAffImg']
		);
		
		echo $frag;
	}
}
