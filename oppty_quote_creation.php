<?
if (!defined('DOCROOT')) {
$docroot = get_cfg_var('doc_root');
define('DOCROOT', $docroot);
}
 
// Set up access control and authentication
require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');

use \RightNow\Connect\v1_2 as RNCPHP;


$IncidentId = $_GET ['Incident_Id'];	

					
//$IncidentId = RNCPHP\Incident::fetch($IncidentId);
if($IncidentId > 0)
{	
	//echo "Inside IF code";
	$IncidentObj = RNCPHP\Incident::fetch($IncidentId);
	//$IncidentId = $IncidentObj->ID;
	echo "<br>Incident : ".$IncidentId;
	//$objIncident = RNCPHP\ROQL::queryObject("SELECT Incident FROM Incident WHERE ID = 55;")->next();

	$Oppty = new RNCPHP\Opportunity();					
	$Oppty->Name = "RMA for Incident ID::".$IncidentId;
	$Oppty->PrimaryContact->Contact = RNCPHP\Contact::fetch($IncidentObj->PrimaryContact->ID);
	$Oppty->Organization = $Oppty->PrimaryContact->Contact->Organization->ID;
	$Oppty->Summary = $IncidentObj->Subject;
	$Oppty->StatusWithType->Status->ID =14; 
	
	//$Oppty->save(RNCPHP\RNObject::SuppressAll); 
	$Oppty->save();
	echo("<br> Opportunity created::".$Oppty->ID);	
	//echo 'Opty Status'.$Oppty->StatusWithType->Status;
	if(0 < $Oppty->ID)
	{									
		//echo "<br>Inside Quote IF";
		$IncidentObj->CustomFields->CO->Oppty_ID = $Oppty->ID;
		//echo "<br> Setting Oppty ID".$Oppty->ID;
		$IncidentObj->save();
		//echo "<br>Record saved";
		
		$Oppty->Quotes = new RNCPHP\QuoteArray();
		$Oppty->Quotes[0]= new RNCPHP\Quote();
		
		$Oppty->Quotes[0]->Name = "Quote for RMA: ".$Oppty->ID;
		
		$Oppty->Quotes[0]->CustomFields->CO->IncidentId = RNCPHP\Incident::Fetch($IncidentId);
		
		//echo("<br>Quote Name::".$Oppty->Quotes[0]->Name);
		$Oppty->Quotes[0]->DiscountPercent=0;
		//$Oppty->save(RNCPHP\RNObject::SuppressAll);
		$Oppty->save(); 
		echo("<br>Quote Created - ".$Oppty->Quotes[0]->ID);
	
	}
}

?>

					