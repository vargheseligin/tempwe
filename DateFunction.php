<?
if (!defined('DOCROOT')) 
{
	$docroot = get_cfg_var('doc_root');
	define('DOCROOT', $docroot);
}
 
// Set up access control and authentication
require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');
use RightNow\Connect\v1_2 as RNCPHP;


$date=date("Y-m-d");
date_add($date,date_interval_create_from_date_string(" days"));
echo date_format($date,"Y-m-d");

	
?>
