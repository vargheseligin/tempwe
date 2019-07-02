<?
 /*
* CPMObjectEventHandler: bookAppointment
* Package: CO
* Objects: CO\Appointment
* Actions: Create
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
				
				
				$Apt = new RNCPHP\CO\Appointment();
				// fetch the Incident object for the RMA
				//$Apt->IncidentID = RNCPHP\Incident::fetch(2);  
				$Apt->TOAResourceId = "123456";
				$Apt->AppointmentStatus = "Testing";
				$Apt->FieldAgentId = "33";
				$Apt->FieldAgentName = "FieldAgentName";
				$Apt->Summary = "Summary";
				//$Apt->save();
				$Apt->save(RNCPHP\RNObject::SuppressAll);
				RNCPHP\ConnectAPI::commit(); 
				
				echo "TestingPhase_Stage2.3";
				echo "Appointment ID : ".$Apt->ID;
				
				
				
				//write code to create new record in custom object - Appointment
				//stamp values - appointment date/time, TOA WorkOrder Id, FieldAgent Name and Incident ID
				//reset value for hidden field in incident

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

			//Incident Create
		//	$incidentOne = RNCPHP\Incident::fetch(2);
			$incidentOne = new RNCPHP\CO\Appointment();
			$incidentOne->FieldAgentId = "33";
			$incidentOne->FieldAgentName = "Test_FieldAgentName";
			$incidentOne->Summary = "Test_Summary";
			//$incident_id = $incidentOne->ID;
			
			
			/*$incidentOne->Subject = "Product Info";

			$incidentOne->Threads = new RNCPHP\ThreadArray();
			$incidentOne->Threads[0] = new RNCPHP\Thread();
			$incidentOne->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
			$incidentOne->Threads[0]->EntryType->ID = 3;
			$incidentOne->Threads[0]->Text = "I need the info about the broadband services.";

			$incidentOne->StatusWithType = new RNCPHP\StatusWithType() ;
			$incidentOne->StatusWithType->Status = new RNCPHP\NamedIDOptList() ;
			$incidentOne->StatusWithType->Status->ID  = 2 ;

			//$incidentOne->PrimaryContact = RNCPHP\Contact::fetch(self::$contact_id); */
			//$incidentOne->PrimaryContact = RNCPHP\Contact::fetch(2);
			$incidentOne->save(RNCPHP\RNObject::SuppressAll);
			self::$incident_id = $incidentOne->ID;
			//echo $incident_id;
			echo '<br/> Incident Id:'.$incidentOne->ID.'<br/>';
			//echo 'Subject:'.$incidentOne->Subject.'  Desc:'.$incidentOne->Threads[0]->Text;

			//echo "<br/><br/><br/>";
			//print_r($incidentOne);

			echo "TestingPhase_Stage1";
			// Object Creation

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
		//return($pass);

      return ;
    }


    public static function cleanup()
    {

		//self::$incident_id = NULL;
		//self::$contact_id = NULL;
		//self::$contactOne = NULL;
		//self::$incidentOne = NULL;

		return;
    }
}