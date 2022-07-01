<?php
$is_live = 0;
$prod_url = 'https://api-m.paypal.com';    
$dev_url = 'https://api-m.sandbox.paypal.com';
if(!$is_live){
    $plan_id = "P-6YY78253K71192828MBRSBXQ"; // Testing Cred
    $client_id = "AVKF6X6nrNCZxtnDLKlHTK7JTomJUgCauYODop8f3U1-JkcIH0YYhHeYAyAHrNwfkWNP8-AJWire7lFU";  // Testing Cred
    $client_secret = "ELw3D7MXB_Ve5CvpU87J6x_pu1S24zdxuOg-7R_43ZlNpx99bPHsHjSC8KGMMz2yeYOe2EpulaPikRI5"; // Testing Cred
}else{
    $plan_id = "P-0BA059821D9378533MC3CZEI"; // Live Cred
    $client_id = "ASl-BM2_8nCmZogfXxvbDzkUVXoOLyUZjUnCLs40eQ9MzF3LmUuLkWnVa_WiSc37vs-qOizNFzV226Ms"; // Live Cred
    $client_secret = "EF_3hNn2NsZ6KEQunkb9HhlUJeRNU1izZwPbRkRfAUOdprTa_yeaAtsatrE-9LjB5hNmK7OeJihhCvjD"; // Live Cred
}

function simple_curl($end_point, $method='GET', $data=null, $curl_headers=array(), $curl_options=array()) {
	// defaults
	global $prod_url,$dev_url,$is_live;
	if($is_live){
        $uri = $prod_url.$end_point;
    }else{
        $uri = $dev_url.$end_point;
    }
    $curl_headers[] = "Accept: application/json";
    $curl_headers[] = "Accept-Language: en_US";
    $curl_headers[] = "Content-Type: application/json";
    
	$default_curl_options = array(
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_HEADER => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 30,
	);
	$default_headers = array();

	// validate input
	$method = strtoupper(trim($method));
	$allowed_methods = array('GET', 'POST', 'PUT', 'DELETE');

	if(!in_array($method, $allowed_methods))
		throw new \Exception("'$method' is not valid cURL HTTP method.");

	if(!empty($data) && !is_string($data))
		throw new \Exception("Invalid data for cURL request '$method $uri'");

	// init
	$curl = curl_init($uri);

	// apply default options
	curl_setopt_array($curl, $default_curl_options);
    
	// apply method specific options
	switch($method) {
		case 'GET':            
			break;
		case 'POST':
			//if(!is_string($data))
				//throw new \Exception("Invalid data for cURL request '$method $uri'");
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		case 'PUT':
			//if(!is_string($data))
				//throw new \Exception("Invalid data for cURL request '$method $uri'");
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		case 'DELETE':
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			break;
	}

	// apply user options
	curl_setopt_array($curl, $curl_options);

	// add headers
	curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge($default_headers, $curl_headers));
    
	// parse result
	$raw = rtrim(curl_exec($curl));
	$lines = explode("\r\n", $raw);
	$headers = array();
	$content = '';
	$write_content = false;
	if(count($lines) > 3) {
		foreach($lines as $h) {
			if($h == '')
				$write_content = true;
			else {
				if($write_content)
					$content .= $h."\n";
				else
					$headers[] = $h;
			}
		}
	}
	$error = curl_error($curl);

	curl_close($curl);
    
    
    
    $content = json_decode($content);
    if(!empty($content)){
        return $content;
    }else{
        return '';
    }
	
} 
function getToken(){
    global $client_id ,$client_secret,$token_url;
    
    $authorize = $client_id.":".$client_secret;
    $authorization = base64_encode($authorize);
    $headers = array();
    
    $headers[] = "Authorization: Basic ".$authorization."";
    $data = "grant_type=client_credentials";
    $result = simple_curl("/v1/oauth2/token", 'POST', $data , $headers);   
    $content = json_decode(json_encode($result));    
    if(!empty($content->access_token)){
        return $content->access_token;
    }else{
        return '';
    }
}

?>
