<?php
$accountId = $_GET['p_acct_id'];

$url = "http://103.241.136.20/callcenter_enu/start.swe?SWECmd=ExecuteLogin&SWEUserName=%s&SWEPassword=%s&SWEAC=SWECmd=GotoView&SWEView=All+Opportunity+List+View&SWEReloadFrames=1";

// Set up access control and authentication
require_once (get_cfg_var('doc_root') . '/include/services/AgentAuthenticator.phph');
 
// On failure, this includes the Access Denied page and then exits,
// preventing the rest of the page from running.
//$account = AgentAuthenticator::authenticateSessionID();
 
$agentObj = \RightNow\Connect\v1_2\Account::fetch($accountId);

//echo $accountId." , ".$agentObj->CustomFields->c->siebel_password siebel_username;

$url = sprintf($url, $agentObj->CustomFields->c->siebel_username, $agentObj->CustomFields->c->siebel_password);
				
//Perform Redirect
header("Location: $url");
		
exit();


?>