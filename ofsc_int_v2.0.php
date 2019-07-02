<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/xml;charset=UTF-8');

require_once(get_cfg_var('doc_root'). '/include/ConnectPHP/Connect_init.phph');
require_once(get_cfg_var('doc_root').'/custom/src/xtree.php');
initConnectAPI('oe.integration','Objectedge01');
use RightNow\Connect\v1_3 as RNCPHP;

$playload = file_get_contents('php://input');
$payload = htmlspecialchars_decode($playload);
if(trim($payload)==""){
	echo '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
			<SOAP-ENV:Header/>
				<SOAP-ENV:Body>
				<SOAP-ENV:Fault>
					<faultcode>SOAP-ENV:Server</faultcode>
					<faultstring>Internal Server Error</faultstring>
					<detail>Data not found</detail>
				</SOAP-ENV:Fault>
				</SOAP-ENV:Body>
			</SOAP-ENV:Envelope>';
			
	// $Teste = new RNCPHP\TOA\Test();
	// $Teste->dados = "NO DATA";
	// $Teste->save();
}else{

	// $Teste = new RNCPHP\TOA\Test();
	// $Teste->dados = "Test: ".$payload;
	// $Teste->save();
	
	echo "
	<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'>
			<soapenv:Header/><soapenv:Body><urn:send_message_response>";
			
		$payloadTree = new xtree(array('xmlRaw' => $payload, 'stripNamespaces' => true));
		$messages = $payloadTree->xtree->Envelope->Body->send_message->messages->message;
		if (count($messages) == 1) {
			
			if($messages->message_id->_['value']!=""){
				echo '<urn:message_response><urn:message_id>'.$messages->message_id->_['value'].'</urn:message_id><urn:status>sent</urn:status><urn:description>Activity updated</urn:description></urn:message_response>';			
			}
			
			if($messages->body->activityId->_['value']!=""){
				
				$Incident = RNCPHP\Incident::fetch((int)$messages->body->apptNumber->_['value']);
				
				if($messages->body->type->_['value'] == 'COMPLETE' ||
					$messages->body->type->_['value'] == 'CANCEL' ||
						$messages->body->type->_['value'] == 'NOTDONE'){
					$Incident->StatusWithType->Status->Id = 2;//Solved
				}
				if($messages->body->type->_['value'] == 'REMINDER'){
					$messages->body->status->_['value'] = 'In Route';
				}
				if(trim($messages->body->Disposition->_['value'])!=""){					
					$Incident->Disposition = RNCPHP\ServiceDisposition::fetch(trim($messages->body->Disposition->_['value']));
				}
								
				$Activity = new RNCPHP\TOA\Activities();
				$Activity->activityId = $messages->body->activityId->_['value'];
				$Activity->incident_id = (int) $messages->body->apptNumber->_['value'];
				$Activity->apptNumber = $messages->body->apptNumber->_['value'];
				$Activity->activityType = $messages->body->activityType->_['value'];
				$Activity->status = $messages->body->status->_['value'];
				$Activity->activityDate = $messages->body->eta->_['value'];
				$Activity->resourceId = $messages->body->resourceId->_['value'];
				$Activity->resourceName = $messages->body->resourceName->_['value'];
				$Activity->save();
				
				
				$Incident->CustomFields->c->activity_status = $messages->body->status->_['value'];
				$Incident->save();
			}
			
		} else {
			foreach ($messages as $val) {
				if($val->message_id->_['value']!=""){
					echo '<urn:message_response><urn:message_id>'.$val->message_id->_['value'].'</urn:message_id><urn:status>sent</urn:status><urn:description>Activity updated</urn:description></urn:message_response>';			
				}
				if($val->body->activityId->_['value']!=""){
					if($val->body->type->_['value'] == 'COMPLETE' ||
						$val->body->type->_['value'] == 'CANCEL' ||
							$val->body->type->_['value'] == 'NOTDONE'){
						
						$Incident = RNCPHP\Incident::fetch((int)$val->body->apptNumber->_['value']);
						$Incident->StatusWithType->Status->Id = 2;//Solved
						
						if($val->body->type->_['value'] == 'COMPLETE'){
							$Contact = RNCPHP\Contact::fetch($Incident->PrimaryContact->ID);
							
							if($Incident->Product->ID == 384) //Install - Residential
								$Contact->ContactType->ID = 2;//Residential
							if($Incident->Product->ID == 381) //Install - Business	
								$Contact->ContactType->ID = 3;//Business
							if($Incident->Product->ID == 382) //Install - Commercial
								$Contact->ContactType->ID = 4;//Commercial
								
							$Contact->save();
						}
					}
					if($val->body->type->_['value'] == 'REMINDER'){
						$val->body->status->_['value'] = 'In Route';
					}
					
					$Activity = new RNCPHP\TOA\Activities();
					$Activity->activityId = $val->body->activityId->_['value'];
					$Activity->incident_id = (int) $val->body->apptNumber->_['value'];
					$Activity->apptNumber = $val->body->apptNumber->_['value'];
					$Activity->activityType = $val->body->activityType->_['value'];
					$Activity->status = $val->body->status->_['value'];
					$Activity->activityDate = $val->body->eta->_['value'];
					$Activity->resourceId = $val->body->resourceId->_['value'];
					$Activity->resourceName = $val->body->resourceName->_['value'];
					if($val->body->cancelReasontc->_['value'])
						$Activity->cancelReason = $val->body->cancelReasontc->_['value'] . " ".$val->body->cancelReasonvp->_['value'];
					if($val->body->rescheduleReason->_['value'])
						$Activity->rescheduleReason = $val->body->rescheduleReason->_['value'];
					$Activity->save();
					
					
					$Incident->CustomFields->c->activity_status = $val->body->status->_['value'];
					$Incident->save();
				}
				
		
			}
		}
		
	echo "</urn:send_message_response></soapenv:Body></soapenv:Envelope>";
}
?>