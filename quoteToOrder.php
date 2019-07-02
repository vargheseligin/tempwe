<?
 /*
* CPMObjectEventHandler: quoteToOrder
* Package: RN
* Objects: Opportunity
* Actions: Create, Update
* Version: 1.2
*/

use \RightNow\Connect\v1_2 as RNCPHP;
use \RightNow\CPM\v1 as RNCPM;

const DEV_MODE = true;

define('APPPATH',
        DEV_MODE ?
                __DIR__ . "/scripts/cp/customer/development/" :
                __DIR__ . "/scripts/cp/generated/production/optimized/");

//require_once APPPATH . "libraries/simple_html_dom_v2.php";

class quoteToOrder implements RNCPM\ObjectEventHandler
{

    public static function apply( $run_mode, $action, $object, $n_cycles )
    {
      if($n_cycles!==0){ return;}//To avoid recursive call

	  static $optyOne = null;
		
	  if (RNCPM\ActionCreate == $action)// If the Action is Create
        {
			$logfile = @fopen('/cgi-bin/evergegroup.cfg/scripts/cp/customer/development/views/pages/Log/quoteToOrderCPM_'.date("d.m.Y.H.i.s").'.log', 'a'); //Definelogfile  
			try{
				
				//echo  'Apply Opportunity ID: '.$object->ID. '<br/>';
				$OptyID = $object->ID;
				$sQuoteAdjustment = $object->CustomFields->CO->TriggerQuoteAdjustment; //get flag value
				$today_dt = strtotime(date("d-m-Y"));

				$log = "OptyID=".$OptyID." AND today=".$today_dt.PHP_EOL;
				$fwrite = @fputs($logfile, $log);
							
				$OpptyResult = RNCPHP\ROQL::queryObject("SELECT Opportunity FROM Opportunity WHERE ID ='".$OptyID."';")->next();
                                                
				//numProds = 0;
				$Opp = $OpptyResult->next();
				$q_count =  count($Opp->Quotes);
				//echo "q_count : ".$q_count;
				for($i=0;$i<$q_count;$i++)
				{
					//echo ("Inside For Loop-------- ");	
					$enableQuoteToOrder = $Opp->Quotes[$i]->CustomFields->CO->EnableQuoteToOrder;
					$orderId = $Opp->Quotes[$i]->CustomFields->CO->OrderId->ID;
					//echo "Order Id Is:" .$orderId.' enableQuoteToOrder'.$enableQuoteToOrder;
					$log = "enableQuoteToOrder=".$enableQuoteToOrder." AND orderId=".$orderId. PHP_EOL ;
					$fwrite = @fputs($logfile, $log);
					
					if(($enableQuoteToOrder == 1) AND ($orderId ==''))
					{
					//	echo ("Creating Orders-------- Inside IF");
						$QuoteId = $Opp->Quotes[$i]->ID;
					//	echo "$QuoteId :".$QuoteId;
						$new_Order = new RNCPHP\CO\ServiceOrder();
						$ONum = 'OrderToQuote-'.$QuoteId;
						
					//	echo "ONum ".$ONum;
						$new_Order->OrderNumber = $ONum;
						
						//$new_Order->OrderType->LookupName = "ServiceOrder";
						$new_Order->OrderType = RNCPHP\CO\OrderType::fetch("ServiceOrder"); 
						$new_Order->QuoteId = $QuoteId;
						
						$IncidentId = $Opp->Quotes[$i]->CustomFields->CO->IncidentId->ID; // Retrives Incident ID
						
						If ($IncidentId <> ''){
						
							$new_Order->IncidentId = RNCPHP\Incident::fetch($IncidentId); //Incident Association
							$IncObj = $Opp->Quotes[$i]->CustomFields->CO->IncidentId;  // Incident Object 
							
							$Inc_Prim_Con = $IncObj->PrimaryContact->LookupName;
							$new_Order->ContactId = RNCPHP\Contact::fetch($Inc_Prim_Con); //Contact Association
						
							$inc_org = $object->Organization->ID;
							$new_Order->OrganizationId = RNCPHP\Organization::fetch($inc_org); // Organization Association
						}
						$new_Order->OrderAmount = $Opp->Quotes[$i]->Total->Value;
						$new_Order->QuoteTotal = $Opp->Quotes[$i]->Total->Value;
						$new_Order->QuoteGrandTotal = $Opp->Quotes[$i]->AdjustedTotal->Value;
						$new_Order->DiscountPercentage = $Opp->Quotes[$i]->DiscountPercent;
						$new_Order->save();
						//echo ("Order Id Is"+ $new_Order->ID);
						$log = "Order Id Is=".$new_Order->ID. PHP_EOL ;
						$fwrite = @fputs($logfile, $log);
								
						
										
						if(($new_Order->ID)<> '')
						{

							$Opp->Quotes[$i]->CustomFields->CO->OrderId = $new_Order->ID;
							$Opp->save();
							$ql_count = count($Opp->Quotes[$i]->LineItems);
							////echo  "Quote LineItems Count :".$ql_count;
							for($j=0;$j<$ql_count;$j++)
							{
								$new_Order_Item = new RNCPHP\CO\ServiceOrderItem();
							//	$new_Order_Item->ID = $Opp->Quotes[$i]->LineItems[$j]->ID;
								$new_Order_Item->Quantity = $Opp->Quotes[$i]->LineItems[$j]->Quantity;
								$new_Order_Item->Total = $Opp->Quotes[$i]->LineItems[$j]->AdjustedTotal->Value;
								
								$new_Order_Item->PartNumber = $Opp->Quotes[$i]->LineItems[$j]->Product->LookupName; // $Opp->Quotes[$i]->LineItems[$j]->OriginalPartNumber;
								$new_Order_Item->ProductId = $Opp->Quotes[$i]->LineItems[$j]->Product->ID;
								$new_Order_Item->OrderId = $new_Order->ID;
								$new_Order_Item->Discount =  $Opp->Quotes[$i]->LineItems[$j]->DiscountPercent;
								$new_Order_Item->AdjustedDescription  = $Opp->Quotes[$i]->LineItems[$j]->AdjustedDescription;
								$new_Order_Item->AdjustedPrice = $Opp->Quotes[$i]->LineItems[$j]->AdjustedPrice->Value;
								
								$new_Order_Item->OriginalName = $Opp->Quotes[$i]->LineItems[$j]->OriginalName;
								$new_Order_Item->OriginalDescription = $Opp->Quotes[$i]->LineItems[$j]->OriginalDescription;
								$new_Order_Item->OriginalPrice = $Opp->Quotes[$i]->LineItems[$j]->OriginalPrice->Value;
								
								$DisValue = ($Opp->Quotes[$i]->LineItems[$j]->OriginalPrice->Value * $Opp->Quotes[$i]->LineItems[$j]->DiscountPercent)/100;
								//echo "DisValue :".$DisValue;
								$new_Order_Item->DiscountAmount = $DisValue;//$Opp->Quotes[$i]->LineItems[$j]->DiscountAmount;
								$new_Order_Item->save();
								//echo  "Order Item Created::".$new_Order_Item->ID;
								$log = "Order Item Created:: ".$new_Order_Item->ID. PHP_EOL ;
								$fwrite = @fputs($logfile, $log);
							}
						}
						////echo ("Order Id Is"+ $Opp->Quotes[$i]->CustomFields->CO->OrderId);
						
						
					}
					
					
				}

			}
			catch(Exception $err){
				////echo  $err->getMessage();
			}
        }
	  	return;
    }
}


class quoteToOrder_TestHarness implements RNCPM\ObjectEventHandler_TestHarness
{

    static $opty_id = NULL;
    static $optyOne = NULL;

    public static function setup()
    {

		try{

			//Incident Create
			$optyOne = RNCPHP\Opportunity::fetch(127);
			//$incidentOne->PrimaryContact = RNCPHP\Contact::fetch(2);
			$optyOne->save(RNCPHP\RNObject::SuppressAll);
			self::$opty_id = $optyOne->ID;

			////echo  "TestingPhase_Stage1";
		}
		catch (Exception $err ){
			////echo  $err->getMessage();
		}
      	return;
    }


    public static function fetchObject( $action, $object_type)
    {
      $optOne = $object_type::fetch(self::$opty_id);
      return($optOne);
    }


    public static function validate( $action, $object )
    {
		//$pass = false;
      	return true;
    }


    public static function cleanup()
    {

		self::$opty_id = NULL;
		self::$optyOne = NULL;
		return;
    }
}