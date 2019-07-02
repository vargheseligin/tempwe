<?
 /*
* CPMObjectEventHandler: bookAppointment
* Package: RN
* Objects: Incident
* Actions: Create,Update
* Version: 1.2
*/

use \RightNow\Connect\v1_2 as RNCPHP;
use \RightNow\CPM\v1 as RNCPM;

const DEV_MODE = true;

define('APPPATH',
        DEV_MODE ?
                __DIR__ . "/scripts/cp/customer/development/" :
                __DIR__ . "/scripts/cp/generated/production/optimized/");

require_once APPPATH . "libraries/simple_html_dom_v2.php";

class bookAppointment implements RNCPM\ObjectEventHandler
{

    public static function apply( $run_mode, $action, $object, $n_cycles )
    {
      if($n_cycles!==0){ return;}//To avoid recursive call

		//static $contact = null;
		//static $con_id = null;

		static $incidentOne = null;
		static $inc_title = null;
		static $inc_thread = null;
		$i = 0;

	  if (RNCPM\ActionCreate == $action)// If the Action is Create
        {
			try{
				//echo 'Inside ActionCreate';
				//echo 'Apply Incident ID: '.$object->ID. '<br/>';
				$inc_title = $object->Subject;//Store the Title of the Incident
				$inc_thread = $object->Threads[0]->Text;//Store the Description of the Incident
				$inc_ref = $object->ReferenceNumber;
				$inc_prod_name = $object->Product->Name;
				$inc_org = $object->Organization->LookupName;
				$inc_con_fst = $object->PrimaryContact->Name->First;
				$inc_con_lst = $object->PrimaryContact->Name->Last;
				$inc_con_ph = $object->PrimaryContact->Phones[0]->Number;
				$inc_con_email = $object->PrimaryContact->Emails[0]->Address;

				$inc_con_addr = $object->PrimaryContact->Address->Street;
			    $inc_con_city = $object->PrimaryContact->Address->City;
			    $inc_con_state = $object->PrimaryContact->Address->StateOrProvince->LookupName;
			    $inc_con_country = $object->PrimaryContact->Address->Country->LookupName;
			    $inc_con_zipcode = $object->PrimaryContact->Address->PostalCode;
			    $today_dt = date("Y-m-d") ;

			    $inc_appoint_flag = $object->c->AppointmentFlg1;

			   // $inc_appoint_flag = "No";
			    $inc_pref_date = date('Y-m-d', $object->CustomFields->CO->PreferredDate);
				$inc_pref_slot = $object->CustomFields->CO->PreferredSlot->LookupName;
				$inc_type = $object->CustomFields->CO->IncidentType->LookupName;
				
				if($inc_pref_date=="")
					$inc_pref_date=$today_dt;
				if($inc_pref_slot=="")
					$inc_pref_slot="08-10";

				//echo($inc_pref_date);
				//echo($inc_pref_slot);

				//echo 'TestingPhase_Stage2';
				load_curl();

				//build TOA user authentication section - now & auth_str //print_r('now - '.$now.'<br/>'); //print_r('auth_str - '.$auth_str.'<br/>')
				$password = "cheilig";
				$str1 = md5($password);
				$zone = date_default_timezone_set('Asia/Kolkata'); 				//Auth_Sting = md5(now+md5(password))
				$now = date('c');
				$str2 = $now.$str1;
				$auth_str = md5($str2);


				if($inc_appoint_flag=="Yes")
				{	
					$urls = "https://demo.etadirect.com/130477902502/soap/inbound/";
					$action = "InboundInterfaceService/inbound_interface";
					$envelop = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:toatech:InboundInterface:1.0">
	   							<soapenv:Header/>
	   							<soapenv:Body>
	      							<urn:inbound_interface_request>
										<user><now>'.$now.'</now><company>sunrise1135.demo</company><login>cheilig</login><auth_string>'.$auth_str.'</auth_string></user>
										<head>
											<upload_type>incremental</upload_type>
											<id>IB2014.02.03</id>
								            <properties_mode>update</properties_mode>
								            <appointment>
								               <keys>
								                  <field>appt_number</field>
								               </keys>
								            </appointment>
								            <inventory>
								               <keys>
								                  <field>invtype_label</field>
								               </keys>
								               <upload_type>incremental</upload_type>
								            </inventory>
								        </head>
								        <data>
								            <commands>
								                <command>
								                  <!--scheduled, ordered activity-->
								                  <date>'.$inc_pref_date.'</date>
								                  <type>update_activity</type>
								                  <external_id>800500</external_id>
								                  <appointment>
								                     <appt_number>'.$inc_ref.'</appt_number>
								                     <customer_number>'.$inc_org.'</customer_number>
								                     <worktype_label>'.$inc_prod_name.'</worktype_label>
								                     <name>'.$inc_con_fst.' '.$inc_con_lst.'</name>
								                     <phone>'.$inc_con_ph.'</phone>
								                     <email>'.$inc_con_email.'</email>
								                     <address>'.$inc_con_addr.'</address>
								                     <city>'.$inc_con_city.'</city>
								                     <zip>'.$inc_con_zipcode.'</zip>
								                     <state>'.$inc_con_state.'</state> 
								                     <language>1</language>
								                     <daybefore_flag>0</daybefore_flag>
								                     <reminder_time>0</reminder_time>
								                     <time_slot>'.$inc_pref_slot.'</time_slot>
													 <property>
													   <label>WO_COMMENTS</label>
													   <value>'.$inc_type.'</value>
													</property>
								                     <properties>
								                        <property>
								                           <label>WO_TYPE</label>
								                           <value>10</value>
								                        </property>
								                     </properties>
								                  </appointment>
								               </command>
								            </commands>
								        </data>
								    </urn:inbound_interface_request>
								</soapenv:Body>
							</soapenv:Envelope>';
							//work on customer number, slot, WO type, 

					$headers = array(
					    'Content-Type: text/xml;charset=UTF-8',
					    'Content-Length: '.strlen($envelop),
					    'SOAPAction: '.$action
					    );

					$soap_do = curl_init();

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
					
					// Send the request and check the response
					if (($result = curl_exec($soap_do)) === FALSE) {
					    die('cURL error: '.curl_error($soap_do) ."<br />\n");
					} else {
					   // echo "Success!<br />\n";
					}

					var_dump($envelop);
					var_dump($result);
					curl_close($soap_do);

					$fireflyDOM = str_get_html($result);
					$activity_id = $fireflyDOM->find("aid", 0)->innertext;
					
					//echo "TestingPhase_Stage2.1";
					//echo "Activty ID -> ".$activity_id;

					// fetch the Incident object for the Appointment
					$IncidentID = $object->ID;
					$Apt = new RNCPHP\CO\Appointment();
					$Apt->IncidentId = RNCPHP\Incident::fetch($IncidentID);  
					$Apt->ContactId = RNCPHP\Contact::fetch($object->PrimaryContact->ID);
					$Apt->OrgId = RNCPHP\Organization::fetch($object->Organization->ID);
					$Apt->AppointmentStatus = RNCPHP\CO\AppointmentStatus::fetch('Not Done'); 
					//$Apt->PreferredSlots->LookupName = $inc_pref_slot;
					$Apt->AppointmentDate = strtotime($inc_pref_date); 
					//$Apt->FieldAgentId = "33";
					$Apt->Summary = $object->Subject;
					$Apt->TOAWorkOrderId=$activity_id;
					$Apt->save(RNCPHP\RNObject::SuppressAll);
				}//if appointFlag = YES


				if($inc_appoint_flag=="No")
				{
					$IncidentID = $object->ID;
					$queryStr = "SELECT CO.Appointment FROM CO.Appointment WHERE CO.Appointment.AppointmentStatus.ID=9 AND CO.Appointment.IncidentId=".$IncidentID.";";

					//echo($queryStr);
					//echo("In NO Block");

					$appointmentResultSet = RNCPHP\ROQL::queryObject($queryStr);
					while($appointmentResult = $appointmentResultSet->next())
					{
						while($row = $appointmentResult->next())
						{
							$activity_id = $row->TOAWorkOrderId ;
							//echo("activity_id=".$activity_id) ;
							//echo("id=".$row->ID) ;
							//calling for Activity details fetch
							$urls = "https://demo.etadirect.com/soap/activity/";
							$action = "activity/get_activity" ;
							$envelop = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:toa:activity">
										<soapenv:Header/>
										<soapenv:Body>
										<urn:get_activity>
										<user>
										<now>'.$now.'</now>						
										<company>sunrise1135.demo</company>
										<login>cheilig</login>
										<auth_string>'.$auth_str.'</auth_string>
										</user>
										<activity_id>'.$activity_id.'</activity_id>
										</urn:get_activity>
										</soapenv:Body>
										</soapenv:Envelope>' ;

							$headers = array(
							    'Content-Type: text/xml;charset=UTF-8',
							    'Content-Length: '.strlen($envelop),
							    'SOAPAction: '.$action
							    );

							$soap_do = curl_init();

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
							
							// Send the request and check the response
							if (($result = curl_exec($soap_do)) === FALSE) {
							    die('cURL error: '.curl_error($soap_do) ."<br />\n");
							} else {
							 //   echo "Success!<br />\n";
							}
							//var_dump($envelop);
							//var_dump($result);
							
							curl_close($soap_do);

							$fireflyDOM = str_get_html($result);
							//$createdBy1 = $fireflyDOM->find("property", 0)->first_child()->next_sibling();

							$propArr = $fireflyDOM->find("activity", 0)->children();
							$propCount = count($propArr);
							//echo("propCount".$propCount);

							for($i=0;$i<$propCount;$i++)
							{
								if($propArr[$i]->children(0)->innertext == "time_slot")
									$time_slot = $propArr[$i]->children(0)->next_sibling()->innertext;
								if($propArr[$i]->children(0)->innertext == "time_zone")
									$time_zone = $propArr[$i]->children(0)->next_sibling()->innertext;
								if($propArr[$i]->children(0)->innertext == "worktype")
									$worktype = $propArr[$i]->children(0)->next_sibling()->innertext;
								if($propArr[$i]->children(0)->innertext == "service_window_start")
									$service_window_start = $propArr[$i]->children(0)->next_sibling()->innertext;
								if($propArr[$i]->children(0)->innertext == "service_window_end")
									$service_window_end = $propArr[$i]->children(0)->next_sibling()->innertext;
								if($propArr[$i]->children(0)->innertext == "status")
									$status = $propArr[$i]->children(0)->next_sibling()->innertext;
								if($propArr[$i]->children(0)->innertext == "delivery_window_start")
									$delivery_window_start = $propArr[$i]->children(0)->next_sibling()->innertext;
								if($propArr[$i]->children(0)->innertext == "delivery_window_end")
									$delivery_window_end = $propArr[$i]->children(0)->next_sibling()->innertext;
								if($propArr[$i]->children(0)->innertext == "date")
									$date = $propArr[$i]->children(0)->next_sibling()->innertext;
								if($propArr[$i]->children(0)->innertext == "resource_id")
									$resource_id = $propArr[$i]->children(0)->next_sibling()->innertext;
								if($propArr[$i]->children(0)->innertext == "start_time")
									$start_time = $propArr[$i]->children(0)->next_sibling()->innertext;
								if($propArr[$i]->children(0)->innertext == "end_time")
									$end_time = $propArr[$i]->children(0)->next_sibling()->innertext;
								//var_dump($propArr[$i]);

							}
							
							//$row->SelectedSlot->LookupName = 
							$row->SelectedSlot = RNCPHP\CO\AppointmentSlots::fetch($time_slot);
							$row->AppointmentStatus = RNCPHP\CO\AppointmentStatus::fetch("Pending");  // till TOA fixes LOV - $status
							$row->TimeZone = $time_zone;
							$row->FieldAgentId = RNCPHP\Account::fetch($resource_id); 
							$rnDate = date_format(date_create($date), 'd-m-Y');
							$row->AppointmentDate = strtotime($rnDate);
							$row->AppointmentStartDT = strtotime(date_format(date_create($start_time), 'd-m-Y H:i'));
							$row->AppointmentEndDT = strtotime(date_format(date_create($end_time), 'd-m-Y H:i'));
							$row->ServiceStartDT = strtotime(date_format(date_create($rnDate." ".$service_window_start)));
							$row->ServiceEndDT = strtotime(date_format(date_create($rnDate." ".$service_window_end)));
							$row->DeliveryStartDT = strtotime(date_format(date_create($rnDate." ".$delivery_window_start))) ;
							$row->DeliveryEndDT = strtotime(date_format(date_create($rnDate." ".$delivery_window_end))) ; 
							$row->save(RNCPHP\RNObject::SuppressAll) ; 
						}

					}//end-for
				}//if-appont-flag = NO
				
			}
			catch(Exception $err){
				echo $err->getMessage();
			}
        }
	  	return;
    }
}


class bookAppointment_TestHarness implements RNCPM\ObjectEventHandler_TestHarness
{

    static $incident_id = NULL;
    //static $contact_id = NULL;
	//static $contactOne = NULL;
	static $incidentOne = NULL;
   // static $count = 0;
	//static $client = NULL;
	//static $wsdl = NULL;


    public static function setup()
    {

		try{

			//Incident Update
			$incidentOne = RNCPHP\Incident::fetch(273);
			$incidentOne->Subject = "Product Info";
			$incidentOne->save(RNCPHP\RNObject::SuppressAll);
			self::$incident_id = $incidentOne->ID;
		}
		catch (Exception $err ){
			echo $err->getMessage();
		}


      return;
    }


    public static function fetchObject( $action, $object_type)
    {
    		
      $incOne = $object_type::fetch(self::$incident_id);
      return($incOne);
    }


    public static function validate( $action, $object )
    {
		$pass = false;
      return true;
    }


    public static function cleanup()
    {

		self::$incident_id = NULL;
		self::$incidentOne = NULL;

		return;
    }
}