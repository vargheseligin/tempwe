<?php 
require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
require_once('include/init.phph');
$curlResponse = curlCall();

//
//echo "<pre>";
//$start_date = '2019-04-14';
//$end_date = '2019-04-20';
//$action = 'monthView';
//$action = 'weekView';
//$action = 'dateView';

$start_date = $_POST["startDate"];
$end_date = $_POST["endDate"];
$action = $_POST["action"];

$date = getDatesFromRange($start_date,$end_date); 
$all_dates = join(', ', $date);
//print_r( $date );
//print_r( $all_dates );
if( $action == "dateView")
{
	$all_dates = $start_date;
}

$curl_response = curlCall($all_dates);
$decodedjson_curl_response = json_decode($curl_response);
$labelarray=  array();
$Date_timeSlots_array = array();

//print_r( $decodedjson_curl_response );

foreach($decodedjson_curl_response->timeSlotsDictionary as $timeSlotsvalue)
{
	$labelarray[] = $timeSlotsvalue->label;
}

//print_r( $labelarray );

if( $action == "monthView")
{
	foreach($decodedjson_curl_response->dates as $Date_value)
	{
		//$timeSlots = getdateValue( $Date_value );
		$Date_timeSlots_array[] = getdateValueMonth( $Date_value );
		//$timeSlotsParts = explode('/',$timeSlots);
		//$Date_timeSlots_array[$timeSlotsParts[0]] = $timeSlotsParts[1];
	}
	if(count($Date_timeSlots_array) != count($date))
	{
		for ($i = count($Date_timeSlots_array); $i < count($date); $i++) {
			$Array_data_timeslot = "Not Avaliable";
			$Date_timeSlots_array[]=$date[$i]."|".$Array_data_timeslot;
		}
	}
	$all_timeSlots = join('/', $Date_timeSlots_array);
	print_r($all_timeSlots);
}
if( $action == "weekView")
{
	foreach($decodedjson_curl_response->dates as $Date_value)
	{
		//$timeSlots = getdateValue( $Date_value );
		$Date_timeSlots_array[] = getdateValue( $Date_value );
		//$timeSlotsParts = explode('/',$timeSlots);
		//$Date_timeSlots_array[$timeSlotsParts[0]] = $timeSlotsParts[1];
	}
	if( count($Date_timeSlots_array) != 7)
	{
		for ($i = count($Date_timeSlots_array); $i < 7; $i++) {
			$Array_data_timeslot = "13h-17h,0|08h-12h,0|all_day,0";
			$Date_timeSlots_array[]=$date[$i]."|".$Array_data_timeslot;
		}
	}
	$all_timeSlots = join('/', $Date_timeSlots_array);
	print_r($all_timeSlots);
}

if( $action == "dateView")
{
	foreach($decodedjson_curl_response->dates as $Date_value)
	{
		//$timeSlots = getdateValue( $Date_value );
		$Date_timeSlots_array[] = getdateValue( $Date_value );
		//$timeSlotsParts = explode('/',$timeSlots);
		//$Date_timeSlots_array[$timeSlotsParts[0]] = $timeSlotsParts[1];
	}
	$all_timeSlots = join('/', $Date_timeSlots_array);
	print_r($all_timeSlots);
}

//print_r( $Date_timeSlots_array );
//var_dump($decodedjson_curl_response);


function week_between_two_dates($date1, $date2)
{
    $first = DateTime::createFromFormat('m/d/Y', $date1);
    $second = DateTime::createFromFormat('m/d/Y', $date2);
    if($date1 > $date2) return week_between_two_dates($date2, $date1);
    return floor($first->diff($second)->days/7);
}

function getDatesFromRange($start, $end)
{	
	$dates = array();
    $startDate = strtotime($start);
    $endDate = strtotime($end);
	$date_format = 'Y-m-d';
	$step = '+1 day';
    while( $startDate <= $endDate ) {

        $dates[] = date($date_format, $startDate);
        $startDate = strtotime($step, $startDate);
    }

    return $dates;
	
}

function curlCall($date_range)
{
	$ip_dbreq = true ;
	load_curl();
	$url ="https://api.etadirect.com/rest/ofscCapacity/v1/activityBookingOptions/?dates=".$date_range."&activityType=Service Call&postalCode=75110&determineAreaByWorkZone=true&determineCategory=true&estimateDuration=true&estimateTravelTime=true";
	$soapUser = "soap@nxlink2.test";  //  username
	$soapPassword = "qwerty123"; // password
	$cURL	=	curl_init();
	curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($cURL, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($cURL, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
	curl_setopt($cURL, CURLOPT_URL,$url);
	curl_setopt($cURL, CURLOPT_USERPWD, $soapUser.":".$soapPassword);
	curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($cURL);
	if($response)
	{
		//print_r($response);
	}
	
	return $response;
}

function getdateValue( $Date_value )
{
	$Array_data_date = $Date_value->date;
	
	foreach($Date_value->areas as $areas)
	{
		$Array_data_remainingQuota = $areas->remainingQuota ;
		foreach($areas->timeSlots as $timeslot)
		{
			if( $timeslot->label == "13h-17h")
			{
				$Array_timeslot['1PM - 5PM'] = "1PM - 5PM,".$timeslot->remainingQuota;
			}
			if( $timeslot->label == "08h-12h")
			{
				$Array_timeslot['8AM - 12PM'] = "8AM - 12PM,".$timeslot->remainingQuota;
			}
			if( $timeslot->label == "all_day")
			{
				$Array_timeslot['All Day'] = "All Day,".$timeslot->remainingQuota;
			}
		}
	}
	//print_r($Array_timeslot);
	if($Array_timeslot)
	{
		$Array_data_timeslot = $Array_timeslot['8AM - 12PM']."|".$Array_timeslot['1PM - 5PM']."|".$Array_timeslot['All Day'];
	}
	if($Array_data_timeslot=="")
	{
		$Array_data_timeslot = "8AM - 12PM,0|1PM - 5PM,0|All Day,0";
	}
	
	$return_data =  $Array_data_date."|".$Array_data_timeslot;
	return $return_data;
}

function getdateValueMonth( $Date_value )
{
	$Array_data_date = $Date_value->date;
	foreach($Date_value->areas as $areas)
	{
		if($areas->reason){
			$Array_data_status = "Not Avaliable" ;
		}
		else {
			$Array_data_status = "Avaliable" ;
		}
	}
	$return_data =  $Array_data_date."|".$Array_data_status;
	return $return_data;
}

?>