<!DOCTYPE html>
<html>
<head>
	<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
</head>
<body>

<?

require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
require_once('include/init.phph');



$curlResponse = curlCall();

//print_r($curlResponse);

echo "<pre>";
//var_dump(json_decode($curlResponse, true));

$decodedjson = json_decode($curlResponse);
//print_r($decodedjson->dates);
$labelarray=  array();
$Date_timeSlots_array = array();
$Final_Date_timeSlots_array = array();

foreach($decodedjson->timeSlotsDictionary as $timeSlotsvalue)
{
	$labelarray[] = $timeSlotsvalue->label;
}

print_r( $labelarray );

foreach($decodedjson->dates as $Date_value)
{
	$timeSlots = getdateValue( $Date_value );
	$timeSlotsParts = explode('/',$timeSlots);
	$Date_timeSlots_array[$timeSlotsParts[0]] = $timeSlotsParts[1];
}

//print_r( $Date_timeSlots_array );


$current_month = date('m');
$current_year = date('Y');
//echo $current_month . '\\\/// ' .$current_year;

echo '<h2>'. month_header($current_month - 1 , $current_year).'</h2>';
echo draw_calendar($current_month , $current_year, $Date_timeSlots_array);

//echo '<h2>'. month_header($current_month + 0 , $current_year).'</h2>';
//echo draw_calendar($current_month + 1 , $current_year);

//echo '<h2>'. month_header($current_month + 1 , $current_year).'</h2>';
//echo draw_calendar($current_month + 2 , $current_year);

function draw_calendar($month,$year,$Date_timeSlots_array){
	
	/* Get the no of days of a month */
	$no_of_days = $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
	$date_list=array();
	
	for($d=1; $d<= $no_of_days; $d++)
	{
		$time=mktime(12, 0, 0, $month, $d, $year);          
		if (date('m', $time)==$month){      
			$date_list[]=date('Y-m-d', $time);
		}
	}
	echo "<pre>";
	print_r($date_list);
	echo "</pre>";

	/* create table */
	$calendar = '<table name = "tbl1" cellpadding="0" cellspacing="0" class="calendar" >';
	/* table headings */
	$headings = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	
	$calendar.= '<tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>';

	/* days and weeks vars now ... */
	$running_day = date('w',mktime(0,0,0,$month,1,$year));
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();

	/* row for week one */
	$calendar.= '<tr class="calendar-row">';

	/* print "blank" days until the first of the current week */
	for($x = 0; $x < $running_day; $x++):
		$calendar.= '<td class="calendar-day-np"> </td>';
		$days_in_this_week++;
	endfor;

	/* keep going with days.... */
	for($list_day = 1; $list_day <= $days_in_month; $list_day++):
		$calendar.= '<td class="calendar-day" onclick="alert(this)">';
			/* add in the day number */
			
			$date_string = $year."-".$month."-".$list_day;
			$date_show = date_create($date_string);
			$date_now = date("m/d/Y");
			$date_in = date_format($date_show,"m/d/Y");
			
			if($date_in >= $date_now )
			{
				$calendar.= '<div class="day-number">'.$list_day.'</div>';
				$timeSlotsdata = $Date_timeSlots_array[$date_list[$list_day-1]];
				$Cal_timeSlotsParts = explode('|',$timeSlotsdata);
				//print_r($Cal_timeSlotsParts);
				$calendar.= '<p>Available Quota:'.$Cal_timeSlotsParts[0].'</p>';
				$_8_12h = explode(',',$Cal_timeSlotsParts[2]);
				//print_r($_8_12h);
				$calendar.= '<p>08h-12h : '.$_8_12h[1].'</p>';
				$_13_17h = explode(',',$Cal_timeSlotsParts[1]);
				//print_r($_13_17h);
				$calendar.= '<p>13h-17h : '.$_13_17h[1].'</p>';
				$_all_day = explode(',',$Cal_timeSlotsParts[3]);
				//print_r($_all_day);
				$calendar.= '<p>all_day : '.$_all_day[1].'</p>';
				//$calendar.= '<p>08h-12h</p>';
				//$calendar.= '<p>13h-17h</p>';
				//$calendar.= '<p>all_day</p>';
				//$calendar.= '<p>'.$timeSlotsdata.'</p>';
				//$calendar.= '<p>'.$date_list[$list_day-1].'</p>';
			}
			else
			{
				$calendar.= '<div class="day-number-closed">'.$list_day.'</div>';
				$calendar.= '<p>all_slots_closed</p>';
				$calendar.= '<p>.</p>';
				$calendar.= '<p>.</p>';
			}
			
			/** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
			//$calendar.= str_repeat('<p> </p>',2);
			
			
		$calendar.= '</td>';
		if($running_day == 6):
			$calendar.= '</tr>';
			if(($day_counter+1) != $days_in_month):
				$calendar.= '<tr class="calendar-row">';
			endif;
			$running_day = -1;
			$days_in_this_week = 0;
		endif;
		$days_in_this_week++; $running_day++; $day_counter++;
	endfor;

	/* finish the rest of the days in the week */
	if($days_in_this_week < 8):
		for($x = 1; $x <= (8 - $days_in_this_week); $x++):
			$calendar.= '<td class="calendar-day-np"> </td>';
		endfor;
	endif;

	/* final row */
	$calendar.= '</tr>';

	/* end the table */
	$calendar.= '</table>';
	
	/* all done, return result */
	return $calendar;
}

function month_header($month,$year){
	$months = array('January','February','March','April','May','June',
'July ','August','September','October','November','December');
	return $months[$month]." ".$year;
}

function validate_date($inputDate){
	$date_now = date("m/d/Y");
	$date_in = date_format($inputDate,"m/d/Y");
	if($date_in > $date_now ){
		echo "true";
	}
	else{
		echo "false";
	}
}

function curlCall()
{
	$ip_dbreq = true ;
	load_curl();
	$url ="https://api.etadirect.com/rest/ofscCapacity/v1/activityBookingOptions/?dates=2019-04-01,2019-04-02,2019-04-03,2019-04-04,2019-04-05,2019-04-06,2019-04-07,2019-04-08,2019-04-09,2019-04-10,2019-04-11,2019-04-12,2019-04-13,2019-04-14,2019-04-15,2019-04-16,2019-04-17,2019-04-18,2019-04-19,2019-04-20,2019-04-21,2019-04-22,2019-04-23,2019-04-24,2019-04-25,2019-04-26,2019-04-27,2019-04-28,2019-04-29,2019-04-30&activityType=Service Call&postalCode=75110&determineAreaByWorkZone=true&determineCategory=true&estimateDuration=true&estimateTravelTime=true";
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
			if($Array_data_timeslot)
			{
				$Array_data_timeslot = $Array_data_timeslot ."|".$timeslot->label.",".$timeslot->remainingQuota;
			}
			else{
				$Array_data_timeslot = $timeslot->label.",".$timeslot->remainingQuota;
			}
		}
	}
	$return_data =  $Array_data_date."/".$Array_data_remainingQuota."|".$Array_data_timeslot;
	return $return_data;
}
?>
</body>
</html>

<style>
/* calendar */
table.calendar		{ border-left:1px solid #999; }
tr.calendar-row	{  }
td.calendar-day	{ min-height:80px; font-size:11px; position:relative; } * html div.calendar-day { height:80px; }
td.calendar-day:hover	{ background:#eceff5; }
td.calendar-day-np	{ background:#eee; min-height:80px; } * html div.calendar-day-np { height:80px; }
td.calendar-day-head { background:#ccc; font-weight:bold; text-align:center; width:120px; padding:5px; border-bottom:1px solid #999; border-top:1px solid #999; border-right:1px solid #999; }
div.day-number		{ background:#0ADA18; padding:5px; color:#fff; font-weight:bold; float:right; margin:-5px -5px 0 0; width:20px; text-align:center; }
div.day-number-closed		{ background:#F30000; padding:5px; color:#fff; font-weight:bold; float:right; margin:-5px -5px 0 0; width:20px; text-align:center; }
/* shared */
td.calendar-day, td.calendar-day-np { width:120px; padding:5px; border-bottom:1px solid #999; border-right:1px solid #999; }
</style>

<script>

function alert(data)
{
	console.log(data);
	//var html = $.parseHTML( data );
	//console.log(html);
	var $log = $( "#log" ),
	  str = data,
	  html = $.parseHTML( str ),
	  nodeNames = [];
	console.log($log);
	var searchThis = data.textContent || data.innerText;
	
	console.log(searchThis);
	//console.log(searchThis.includes("all_slots_closed"));
	var cell_status = searchThis.includes("all_slots_closed");
	//console.log(cell_status);
	if( cell_status )
	{
		console.log("[ CONSOLE ] all_slots_closed");
		
	}
	else
	{
		console.log("[ CONSOLE ] all or few slots open");
	}
}

//window.onload = function(){
//    document.getElementById('tbl1').onclick = function(e){
        //var e = e || window.event;
        //var target = e.target || e.srcElement;
        //if(target.tagName.toLowerCase() ==  "td") {
            //alert('Test');
        //}
//    };
//};

</script>