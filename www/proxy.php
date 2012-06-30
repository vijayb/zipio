<?php
// Get the url of to be proxied
// Is it a POST or a GET?
$url = $_GET['url'];
//$headers = $_GET['headers'];
$mimeType = $_GET['mimeType'];


//Start the Curl session
$session = curl_init($url);


// Don't return HTTP headers. Do return the contents of the call
//curl_setopt($session, CURLOPT_HEADER, ($headers == "true") ? true : false);

curl_setopt($session, CURLOPT_FOLLOWLOCATION, true); 
//curl_setopt($ch, CURLOPT_TIMEOUT, 4); 
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

// Make the call
$response = curl_exec($session);


if ($mimeType != "")
{
	// The web service returns XML. Set the Content-Type appropriately
	header("Content-Type: ".$mimeType);
    //echo("Content-Type: ".$mimeType);
}

echo $response;

curl_close($session);

?>