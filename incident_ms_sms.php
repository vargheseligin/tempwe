<?
 /*
* CPMObjectEventHandler: incident_ms_sms
* Package: RN
* Objects: Incident
* Actions: Create
* Version: 1.2
*/

use \RightNow\Connect\v1_2 as RNCPHP;
use \RightNow\CPM\v1 as RNCPM;

/**
 * When set to 'true', the CPM will run the CP handler library in development
 * mode. This should be set to 'false' once the handler logic has been tested
 * and the CP library has been deployed.
 */
const DEV_MODE = true;

define('APPPATH',
        DEV_MODE ?
                __DIR__ . "/scripts/cp/customer/development/" :
                __DIR__ . "/scripts/cp/generated/production/optimized/");

require_once APPPATH . "libraries/cpm/v1/simple_html_dom_v2.php";

class incident_ms_sms implements RNCPM\ObjectEventHandler
{

    public static function apply( $run_mode, $action, $object, $n_cycles )
    {
      if($n_cycles!==0){ return;}//To avoid recursive call

		//static $contact = null;
		//static $con_id = null;

		static $incidentOne = null;
		static $number = '919742756492';
		static $inc_title = null;
		static $inc_thread = null;
		static $message = null;
		$i = 0;
		$url='http://smslane.com/vendorsms/pushsms.aspx';
		$delivery_report = null;


	  if (RNCPM\ActionCreate == $action)// If the Action is Create
        {
			try{
			//	echo 'Apply Incident ID: '.$object->ID. '<br/>';

			//	$inc_title = $object->Subject;//Store the Title of the Incident

			//	$inc_thread = $object->Threads[0]->Text;//Store the Description of the Incident

			//	echo '<br/>inc_title:'.$inc_title.'<br/>inc_thread:'.$inc_thread ;
				echo 'Testing Begins ----';
				//$date->setISODate(2015, 3, 25);
				//echo $date->format('Y-m-d H:i:s') . "\n";
				$date = date_create();
				$daateform = date_format($date, 'Y-m-d H:i:s');
				echo $daateform;

				load_curl();
				$urls = "https://demo.etadirect.com/soap/activity/";
				$action = "https://demo.etadirect.com/soap/activity/get_activity" ;
				$envelop = '<soapenv:Envelope xmlns:soapenv=http://schemas.xmlsoap.org/soap/envelope/ xmlns:urn=urn:toa:activity>
							<soapenv:Header/>
							<soapenv:Body>
							<urn:get_activity>    
							<user>
							<now>2015-03-25T23:08:36+0530</now>
							<company>sunrise1135.demo</company>
							<login>cheilig</login>
							<auth_string>1fd9203267c20f25e28aa86f4d8ed84b</auth_string>
							</user>
							<activity_id>4224092</activity_id>
							</urn:get_activity>
							</soapenv:Body>
							</soapenv:Envelope>';
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
				    echo "Success!<br />\n";
				  }

				  echo 'Raw WS Response ----';
				  var_dump($result);
				  curl_close($soap_do);

			//	echo 'Fields Retrieved as ---->';


				/*$fireflyDOM2 = str_get_html($result);
								$createdBy1 = $fireflyDOM2->find("StockSymbol", 0)->innertext;
								$createdBy2 = $fireflyDOM2->find("CompanyName", 0)->innertext;
				        		echo sprintf("<h2>StockSymbol: %s</h2>", $createdBy1);
				        		echo '<br/>';
				        		echo sprintf("<h2>CompanyName: %s</h2>", $createdBy2);
				        		echo '<br/>';*/


			}
			catch(Exception $err){
				echo $err->getMessage();
			}
        }
     /*  $message = 'Title:'.$inc_title.'.\Detail:'.$inc_thread;//Format the message to add the Title & Description of the Incident.

	   //echo '<br/> SMS :'.$message.'<br/>';
	   //echo '<br> Phone Number:'.$number.'<br/>';
	   $querydata= array(
			'user'=> 'Pritish',
			'password'=>'Pritish123',
			'msisdn'=>'919742756492',
			'msg'=>$message,
			'sid'=>'919742756492',
			'fl'=>'0'
		);

		$data=http_build_query($querydata);

		$params = array('http' => array(
				  'method' => 'POST',
				  'content' => $data
				));




		  $ctx = stream_context_create($params);
		  $fp = @fopen($url, 'rb', false, $ctx);

		  //$result = file_get_contents('http://example.com/submit.php', false, $context);

		  if (!$fp) {
			throw new Exception("Problem with $url, $php_errormsg");
		  }

		  $response = @stream_get_contents($fp);
		  if ($response === false) {
			throw new Exception("Problem reading data from $url, $php_errormsg");
		  }

		  fclose($fp);


	   //self::send_sms($number, $msg);*/


	  return;

    }


}


class incident_ms_sms_TestHarness implements RNCPM\ObjectEventHandler_TestHarness
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

			/*Incident Create
			$incidentOne = new RNCPHP\Incident();

			$incidentOne->Subject = "Product Info";

			$incidentOne->Threads = new RNCPHP\ThreadArray();
			$incidentOne->Threads[0] = new RNCPHP\Thread();
			$incidentOne->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
			$incidentOne->Threads[0]->EntryType->ID = 3;
			$incidentOne->Threads[0]->Text = "I need the info about the broadband services.";

			$incidentOne->StatusWithType = new RNCPHP\StatusWithType() ;
			$incidentOne->StatusWithType->Status = new RNCPHP\NamedIDOptList() ;
			$incidentOne->StatusWithType->Status->ID  = 2 ;

			//$incidentOne->PrimaryContact = RNCPHP\Contact::fetch(self::$contact_id);
			$incidentOne->PrimaryContact = RNCPHP\Contact::fetch(5);

			$incidentOne->save(RNCPHP\RNObject::SuppressAll);
			self::$incident_id = $incidentOne->ID;

			echo '<br/> Incident Id:'.$incidentOne->ID.'<br/>';
			echo 'Subject:'.$incidentOne->Subject.'  Desc:'.$incidentOne->Threads[0]->Text;

			echo "<br/><br/><br/>";
			//print_r($incidentOne);

			echo "TestingPhase_Stage1";*/
			// Object Creation

		}
		catch (Exception $err ){
			echo $err->getMessage();
		}


      return;
    }


    public static function fetchObject( $action, $object_type)
    {
     // $incOne = $object_type::fetch(self::$incident_id);
    //  return($incOne);
		return;
    }


    public static function validate( $action, $object )
    {
		/*$pass = false;

	  	$telno = null;
		$sms = null;
		if (RNCPM\ActionCreate == $action)
		{
			$pass = true;
			if ($object->ID == $incident_id)
			echo 'Validate function.'. $object->ID.'<br/> Test Passed';
			else
			echo 'Test Failed';


		}
		else
		$pass = false;

		echo $pass;
		echo ':Pass#';

		return($pass);*/

      return;
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