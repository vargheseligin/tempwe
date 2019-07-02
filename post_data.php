<?php 
if (!defined('DOCROOT')) {
	$docroot = get_cfg_var('doc_root');
	define('DOCROOT', $docroot);
}
require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');
	$sessionID = $_POST["sessionID"];
	if( $sessionID ) {	
		$account = AgentAuthenticator::authenticateSessionID( $sessionID );
	} else {
		$account = AgentAuthenticator::authenticateSessionID();
	}

use RightNow\Connect\v1_4 as RNCPHP;


$timeSlot = $_POST["timeSlot"];
$IncidentID = $_POST["IncidentID"];
$action = $_POST["action"];
$date = $_POST["date"];

$timeSlot = "all_day";
$IncidentID = "12312";
$date = "2019-04-12";
$action = $_POST["action"];
echo "<pre>";

$data = '{"activityType":"Service Call","apptNumber":"'.$IncidentID.'","resourceId":"TechOps","date": "'.$date.'","timeSlot":"'.$timeSlot.'","postalCode": "75110"}';
$curl_response = curlCall($data);
$decodedjson_curl_response = json_decode($curl_response);
//print_r( $decodedjson_curl_response );
print_r( $decodedjson_curl_response->activityId );




function curlCall($data)
{
	$ip_dbreq = true ;
	load_curl();
	$url ="https://api.etadirect.com/rest/ofscCore/v1/activities";
	$soapUser = "soap@nxlink2.test";
	$soapPassword = "qwerty123";
	$cURL	=	curl_init();
	curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($cURL, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($cURL, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
	curl_setopt($cURL, CURLOPT_URL,$url);
	curl_setopt($cURL, CURLOPT_USERPWD, $soapUser.":".$soapPassword);
	curl_setopt($cURL, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($cURL, CURLOPT_POST,true);
	curl_setopt($cURL, CURLOPT_POSTFIELDS,$data);
	
	$response = curl_exec($cURL);
	if($response)
	{
		//print_r($response);
	}
	
	return $response;
}

?>