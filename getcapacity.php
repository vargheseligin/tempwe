<?
if (!defined('DOCROOT')) {
$docroot = get_cfg_var('doc_root');
define('DOCROOT', $docroot);
}
 
// Set up access control and authentication
require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');
 
use RightNow\Connect\v1_2 as RNCPHP;


include_once('/cgi-bin/evergegroup.cfg/scripts/cp/customer/development/libraries/simple_html_dom_v2.php');
include_once('/cgi-bin/evergegroup.cfg/scripts/cp/customer/development/libraries/html_table.class1.php');
include_once('/cgi-bin/evergegroup.cfg/scripts/cp/customer/development/libraries/string_extract.php');
include_once('/cgi-bin/evergegroup.cfg/scripts/cp/customer/assets/themes/standard/site.php');

//https://evergegroup--tst.custhelp.com/cgi-bin/evergegroup.cfg/php/custom/getcapacity.php?date=2015-06-14
//"https://evergegroup--tst.custhelp.com/cgi-bin/evergegroup.cfg/php/custom/set_timeslot_osvc.php?date="selected_date"&slot=



$username='sdas';
$password='Welcome1234';
$account = AgentAuthenticator::authenticateCredentials($username,$password);
 
//$date = $_GET['date'];	
$inc_id = $_GET['id'];

//echo("date :".$date." Incident ID : ".$inc_id);

//echo "<html><body onload='resizeTo(400,400);'></body></html>"
//echo "<script>window.resizeTo(250,250)</script>";
//echo "<script> alert('this is a javascript code')</script>";
//echo "<script> window.opener.close()</script>";
//echo ("<html><body onload=\"window.opener.close();\"></body>");
//echo ("<BUTTON onclick=\"window.close();\">Close Window</BUTTON>") ; 
//echo ("<h3>Choose a Slot from below available Slots</h3>");
//echo ("</body>");
					

$incident = RNCPHP\Incident::fetch($inc_id);				
		 

$rn_date = $incident->CustomFields->CO->PreferredDate;		                              


$format = 'Y-m-d';
$PreferredDate = date('Y-m-d', $rn_date);
$date=$PreferredDate;
//echo("PreferredDate :".$PreferredDate);strtotime(date_format(date_create($date), 'd-m-Y H:i'));
//date($format, strtotime($date));

//echo($date." date :".$PreferredDate." Incident ID : ".$inc_id);

$date1 = date($format, strtotime ( '-3 day' , strtotime ( $date ) ) );
$date2 = date($format, strtotime ( '-2 day' , strtotime ( $date ) ) );
$date3 = date($format, strtotime ( '-1 day' , strtotime ( $date ) ) );
$date4 = date($format, strtotime($date));
$date5 = date($format, strtotime ( '+1 day' , strtotime ( $date ) ) );
$date6 = date($format, strtotime ( '+2 day' , strtotime ( $date ) ) );
$date7 = date($format, strtotime ( '+3 day' , strtotime ( $date ) ) );

//echo nl2br("Date1 : ".$date1."; Date2 : ".$date2."; Date3 : ".$date3."; Date4 : ".$date4."; Date5 : ".$date5."; Date6 : ".$date6."; Date7 : ".$date7);



/*$capacity = array(
					'08-09' =>array('08-09','','','','','','',''),
					'09-10' =>array('09-10','','','','','','',''),
					'10-11' =>array('10-11','','','','','','',''),
					'11-12' =>array('11-12','','','','','','',''),
					'12-13' =>array('12-13','','','','','','',''),
					'13-14' =>array('13-14','','','','','','',''),
					'14-15' =>array('14-15','','','','','','',''),
					'15-16' =>array('15-16','','','','','','',''),
					'16-17' =>array('16-17','','','','','','','')
				 );*/
$capacity = array(
					'08-09' =>array('08-09','NA','NA','NA','NA','NA','NA','NA'),
					'09-10' =>array('09-10','NA','NA','NA','NA','NA','NA','NA'),
					'10-11' =>array('10-11','NA','NA','NA','NA','NA','NA','NA'),
					'11-12' =>array('11-12','NA','NA','NA','NA','NA','NA','NA'),
					'12-13' =>array('12-13','NA','NA','NA','NA','NA','NA','NA'),
					'13-14' =>array('13-14','NA','NA','NA','NA','NA','NA','NA'),
					'14-15' =>array('14-15','NA','NA','NA','NA','NA','NA','NA'),
					'15-16' =>array('15-16','NA','NA','NA','NA','NA','NA','NA'),
					'16-17' =>array('16-17','NA','NA','NA','NA','NA','NA','NA')
				 );
$fireflyDOM = null;
$response_cap = null;
$result = null;


				//build TOA user authentication section - now & auth_str //print_r('now - '.$now.'<br/>'); //print_r('auth_str - '.$auth_str.'<br/>')
				$password = "cheilig";
				$str1 = md5($password);
				$zone = date_default_timezone_set('Asia/Kolkata'); 				//Auth_Sting = md5(now+md5(password))
				$now = date('c');
				$str2 = $now.$str1;
				$auth_str = md5($str2);
				
				
				$urls = "https://demo.etadirect.com/130477902502/soap/capacity/";
				$action = "CapacityBinding/get_capacity";
				
				$envelop = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:toa:capacity">
							   <soapenv:Header/>
							   <soapenv:Body>
								  <urn:get_capacity>
										<user><now>'.$now.'</now><company>sunrise1135.demo</company><login>cheilig</login><auth_string>'.$auth_str.'</auth_string></user>
										 <date>'.$date1.'</date>
										 <date>'.$date2.'</date>
										 <date>'.$date3.'</date>
										 <date>'.$date4.'</date>
										 <date>'.$date5.'</date>
										 <date>'.$date6.'</date>
										 <date>'.$date7.'</date>
										 <location>800500</location>
										 <calculate_duration>true</calculate_duration>
										 <calculate_travel_time>true</calculate_travel_time>
										 <calculate_work_skill>true</calculate_work_skill>
										 <return_time_slot_info>true</return_time_slot_info>
										 <determine_location_by_work_zone></determine_location_by_work_zone>
										 <dont_aggregate_results>true</dont_aggregate_results>
										 <min_time_to_end_of_time_slot>125</min_time_to_end_of_time_slot>
										 <default_duration>60</default_duration>
										 <time_slot></time_slot>
										 <work_skill>CRMInstall</work_skill>     
										 <activity_field>
											<name>WO_TYPE</name>
											<value>1</value>
										 </activity_field>
										 <activity_field>
											<name>worktype_label</name>
											<value>Siebel CRM</value>
										 </activity_field> 
									  </urn:get_capacity>
								   </soapenv:Body>
								</soapenv:Envelope>';
						
							
					//echo ("Envelop :".$envelop);

					$headers = array(
					    'Content-Type: text/xml;charset=UTF-8',
					    'Content-Length: '.strlen($envelop),
					    'SOAPAction: '.$action
					    );

					load_curl();
					$soap_do = curl_init();
					
					//echo ("....................".$headers."........................");

					curl_setopt($soap_do, CURLOPT_URL,            $urls );
					curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
					curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
					curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
					curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($soap_do, CURLOPT_POST,           true );
					curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $envelop);
					curl_setopt($soap_do, CURLOPT_VERBOSE, TRUE);
					curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
					
					try{
							// Send the request and check the response
							if (($result = curl_exec($soap_do)) === FALSE) {
								die('cURL error: '.curl_error($soap_do) ."<br/>\n");
							} else {
							   // echo "Success!<br/>\n";
							}
						
						curl_close($soap_do);						
						
						$fireflyDOM = str_get_html($result);
						
					  
					}
					catch(Exception $err)
					{
						echo 'Error with the soap response ';
					}
					
					//Put this section where you want to write
					$logfile = @fopen('/cgi-bin/evergegroup.cfg/scripts/cp/customer/development/views/pages/Log/AppointmentSlotCPM_getcapacity'.date("d.m.Y.H.i.s").'.log', 'a'); //Definelogfile      
					
					
					$log= PHP_EOL."PREFERRED DATE  :" .$PreferredDate. PHP_EOL;
					$fwrite = @fputs($logfile, $log);
					
					$log= "Request time :" .date("F j, Y, g:i a"). PHP_EOL."Request XML  :" .$envelop. PHP_EOL;
					$fwrite = @fputs($logfile, $log);

					$log= "Response time :" .date("F j, Y, g:i a"). PHP_EOL."Response XML  :" .$result. PHP_EOL;
					$fwrite = @fputs($logfile, $log);
					
					$response_cap = $fireflyDOM->find('capacity');
					
						// Find all links, and their text						
						$a=count($response_cap);
						for($i==0;$i<$a;$i++) {
								
								//echo "TIMESLOT : {".extractString($response_cap[$i], '<time_slot>', '</time_slot>')."}";
								$elm_time_slot = null;
								$elm_work_skill = null;
								$elm_date = null;
								$elm_available = null;
								
								$elm_time_slot = extractString($response_cap[$i], '<time_slot>', '</time_slot>');
								$elm_work_skill = extractString($response_cap[$i], '<work_skill>', '</work_skill>');
								
								 $log= PHP_EOL."Response CAPACITY ELEMENT  :" .$response_cap[$i]."TIMESLOT : {".$elm_time_slot."}". PHP_EOL;
								 
								 $fwrite = @fputs($logfile, $log);						 
								
								
							if(isset($elm_time_slot) && $elm_work_skill=='CRMInstall')
							{
								$elm_date = extractString($response_cap[$i], '<date>', '</date>');
								$elm_available = extractString($response_cap[$i], '<available>', '</available>');	

								$log= PHP_EOL."{ WORKSKILL : ".$elm_work_skill.", DATE: ".$elm_date.", AVAILABLE : ".$elm_available."}". PHP_EOL;
								$fwrite = @fputs($logfile, $log);
								
								switch ($elm_date) {
												case $date1:
													$capacity[$elm_time_slot][1] = $elm_available;
													break;
												case $date2:
													$capacity[$elm_time_slot][2] = $elm_available;
													break;
												case $date3:
													$capacity[$elm_time_slot][3] = $elm_available;
													break;
												case $date4:
													$capacity[$elm_time_slot][4] = $elm_available;
													break;
												case $date5:
													$capacity[$elm_time_slot][5] = $elm_available;
													break;
												case $date6:
													$capacity[$elm_time_slot][6] = $elm_available;
													break;
												case $date7:
													$capacity[$elm_time_slot][7] = $elm_available;
													break;
											}
							}
						}
						
										
						//var_dump($capacity);				

//echo '<div id="rn_Container" >';
//echo "<body style='background-color:blue'>";	
//$format_month = 'Y-M-d';
//$date7 = date($format_month, strtotime ($date)) 
					$format_month = 'd-M';
					//echo ("<b><font face=\"arial\" size="7">Book an available slot</font></b>");
					$tbl = new HTML_Table('', 'demoTbl');
					$tbl->addCaption('Choose a available Slot', 'cap', array('id'=> 'tblCap') );
					
					$tbl->addRow();
						$tbl->addCell('Date Vs <br/> TimeSlot ', 'first', 'header');
						$tbl->addCell(' '.date($format_month, strtotime ($date1)).' ', '', 'header');
						$tbl->addCell(' '.date($format_month, strtotime ($date2)).' ', '', 'header');
						$tbl->addCell(' '.date($format_month, strtotime ($date3)).' ', '', 'header');
						$tbl->addCell(' '.date($format_month, strtotime ($date4)).' ', '', 'header');
						$tbl->addCell(' '.date($format_month, strtotime ($date5)).' ', '', 'header');
						$tbl->addCell(' '.date($format_month, strtotime ($date6)).' ', '', 'header');
						$tbl->addCell(' '.date($format_month, strtotime ($date7)).' ', '', 'header');
											
					$url = 'https://evergegroup--tst.custhelp.com/cgi-bin/evergegroup.cfg/php/custom/set_timeslot_osvc.php?date=';
					//"https://evergegroup--tst.custhelp.com/cgi-bin/evergegroup.cfg/php/custom/set_timeslot_osvc.php?date=2015-06-14&slot=10-11&id=283
					//$tbl->addCell('<a href="'.$url.$date2.'&'.'slot='.$slot_day2.'&'.'id='.$inc_id.'">'.$slot_day2.'</a>');
					//$tbl->addCell('<a href="'.$url.$date2.'&'.'slot='.$slot_day2.'&'.'id=283">'.$slot_day2.'</a>');
					//($slot_day1=='N.A')? '<a>'.$slot_day1.'</a>':'<a href="'.$url.$date1.'&'.'slot='.$slot.'&'.'id='.$inc_id.'">'.$slot_day1.'</a>'
					
					foreach($capacity as $slots) {
						list($slot, $slot_day1, $slot_day2, $slot_day3, $slot_day4, $slot_day5, $slot_day6, $slot_day7 ) = $slots;
						$tbl->addRow();
							$tbl->addCell($slot,'','dates');
							$tbl->addCell(($slot_day1=='NA')? '  ':'<a href="'.$url.$date1.'&'.'slot='.$slot.'&'.'id='.$inc_id.'">PICK</a>');
							$tbl->addCell(($slot_day2=='NA')? '  ':'<a href="'.$url.$date2.'&'.'slot='.$slot.'&'.'id='.$inc_id.'">PICK</a>');
							$tbl->addCell(($slot_day3=='NA')? '  ':'<a href="'.$url.$date3.'&'.'slot='.$slot.'&'.'id='.$inc_id.'">PICK</a>');
							$tbl->addCell(($slot_day4=='NA')? '  ':'<a href="'.$url.$date4.'&'.'slot='.$slot.'&'.'id='.$inc_id.'">PICK</a>');
							$tbl->addCell(($slot_day5=='NA')? '  ':'<a href="'.$url.$date5.'&'.'slot='.$slot.'&'.'id='.$inc_id.'">PICK</a>');
							$tbl->addCell(($slot_day6=='NA')? '  ':'<a href="'.$url.$date6.'&'.'slot='.$slot.'&'.'id='.$inc_id.'">PICK</a>');
							$tbl->addCell(($slot_day7=='NA')? '  ':'<a href="'.$url.$date7.'&'.'slot='.$slot.'&'.'id='.$inc_id.'">PICK</a>');
							
												
					}
					
					echo $tbl->display();	

//echo '</div>';					
					
					@fclose($logfile);
?>