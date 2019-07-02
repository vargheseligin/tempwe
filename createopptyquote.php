<?
if (!defined('DOCROOT')) {
$docroot = get_cfg_var('doc_root');
define('DOCROOT', $docroot);
}
 
// Set up access control and authentication
require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');

use \RightNow\Connect\v1_2 as RNCPHP;


$IncidentId = $_GET ['IncidentId'];	
echo "Incident : ".$IncidentId;
					
//$IncidentId = RNCPHP\Incident::fetch($IncidentID);
if($IncidentId <> '')
{	
 echo "Inside IF code";
	$IncidentObj = RNCPHP\Incident::fetch($IncidentId);
	$IncidentId = $IncidentObj->ID;
	echo("<br>Incident ID is:".$IncidentId);
	
	//$objIncident = RNCPHP\ROQL::queryObject("SELECT Incident FROM Incident WHERE ID = 55;")->next();

	$Oppty = new RNCPHP\Opportunity();
	$OpptyName = "Opportunity for Incident ID::".$IncidentId;
	echo "Opty Name ".$OpptyName;
	$IncConId = $IncidentObj->PrimaryContact->ID;
	echo "Incident Contact Id :".$IncConId;
	echo "IncidentObj : Subject : ".$IncidentObj->Subject;
	//$IncOrgId = $IncidentObj->PrimaryContact->Organization->ID;
	
	$Oppty->Name = $OpptyName;	
	$Oppty->PrimaryContact->Contact = RNCPHP\Contact::fetch($IncConId);
	//$Oppty->Organization = RNCPHP\Organization::fetch($IncOrgId);
	$Oppty->Summary = $IncidentObj->Subject;
	$Oppty->save(RNCPHP\RNObject::SuppressAll); 
	
	echo("<br> Opportunity created::".$Oppty->ID);
	
	if(0 < $Oppty->ID)
	{									
		echo "Inside Quote IF";
		$Oppty->Quotes = new RNCPHP\QuoteArray();
		$Oppty->Quotes[0]= new RNCPHP\Quote();
		//$OppID = $Oppty->ID;

		$Oppty->Quotes[0]->Name = "Quote for Opportunity: ".$Oppty->ID;
	//	echo("<br>Quote Name::".$Oppty->Quotes[0]->Name);
		$Oppty->Quotes[0]->CustomFields->CO->IncidentId = $IncidentId;
		$Oppty->Quotes[0]->DiscountPercent=0;
		$Oppty->save();
		//$Oppty->save(RNCPHP\RNObject::SuppressAll); 
		echo("<br>Quote Created - ".$Oppty->Quotes[0]->ID);
	
	}
}

?>

					