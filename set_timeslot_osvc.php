<?
if (!defined('DOCROOT')) {
$docroot = get_cfg_var('doc_root');
define('DOCROOT', $docroot);
}
 
// Set up access control and authentication
require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');
 
use RightNow\Connect\v1_2 as RNCPHP;


/*
include_once('/cgi-bin/evergegroup.cfg/scripts/cp/customer/development/libraries/simple_html_dom_v2.php');
include_once('/cgi-bin/evergegroup.cfg/scripts/cp/customer/development/libraries/html_table.class.php');
include_once('/cgi-bin/evergegroup.cfg/scripts/cp/customer/development/libraries/string_extract.php');
*/

//https://evergegroup--tst.custhelp.com/cgi-bin/evergegroup.cfg/php/custom/getcapacity.php?date=2015-06-14
//"https://evergegroup--tst.custhelp.com/cgi-bin/evergegroup.cfg/php/custom/set_timeslot_osvc.php?date=2015-06-14&slot=10-11&id=283



$username='sdas';
$password='Welcome1234';
$account = AgentAuthenticator::authenticateCredentials($username,$password);
 
$date = $_GET['date'];	
$inc_id = $_GET['id'];
$time_slot = $_GET['slot'];

echo "date :".$date." Incident ID : ".$inc_id ." Time Slot : ".$time_slot."<br/>";

try{
			$incident = RNCPHP\Incident::fetch($inc_id);			
			//$inc_md = $incident::getMetadata();
			//$cf_type_name = $incident->CustomFields->type_name;
			//$md2 = $cf_type_name::getMetadata();
			//print_r($md2);
			//$incident->CustomFields->c = new $md2->c->type_name;
		 
			$incident->CustomFields->CO->Preferred_Slot = $time_slot;
			$incident->CustomFields->CO->PreferredDate = strtotime(date_format(date_create($date), 'd-m-Y H:i'));
			//$incident->CustomFields->CO->PreferredDate = date('Y-m-d',$date);		                                  

			$incident->save();
			echo "Incident Updated!";
			
			
	} 
	catch (Exception $err ){
		echo $err->getMessage();
	}
?>
