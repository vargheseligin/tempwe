<?
 /*
* CPMObjectEventHandler: adjustOpportunityQuote
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

class adjustOpportunityQuote implements RNCPM\ObjectEventHandler
{

    public static function apply( $run_mode, $action, $object, $n_cycles )
    {
      if($n_cycles!==0){ return;}//To avoid recursive call

	  static $optyOne = null;
	  //Put this section where you want to write
	  
		
		if (RNCPM\ActionCreate == $action)// If the Action is Create
        {
			$logfile = @fopen('/cgi-bin/evergegroup.cfg/scripts/cp/customer/development/views/pages/Log/QuoteAdjustmentCPM_'.date("d.m.Y.H.i.s").'.log', 'a'); //Definelogfile          

			try{
				
				//echo  'Apply Opportunity ID: '.$object->ID. '<br/>';
				//$object->Summary = "Tested - Screen Cracked and need a replacement";
				//$object->save() ; 
				
				$OptyID = $object->ID;
				$sQuoteAdjustment = $object->CustomFields->CO->TriggerQuoteAdjustment; //get flag value
				$today_dt = strtotime(date("d-m-Y"));

				$log = "OptyID=".$OptyID." AND today=".$today_dt." AND sQuoteAdjustment=".$sQuoteAdjustment.PHP_EOL;
				$fwrite = @fputs($logfile, $log);
							
				if($sQuoteAdjustment==1)
				{
					$OpptyResult = RNCPHP\ROQL::queryObject("SELECT Opportunity FROM Opportunity WHERE ID ='".$OptyID."';")->next();
	            	$object = $OpptyResult->next();
					$q_count =  count($object->Quotes);
					//echo("q_count : ".$q_count);
					
					for($i=0;$i<$q_count;$i++)
					{
						//echo ("Inside Quote For Loop-------- ");	
						$enableQuoteAdjustment = $object->Quotes[$i]->CustomFields->CO->EnableQuoteAdjustment;
						$quoteAdjustedFlag = $object->Quotes[$i]->CustomFields->CO->QuoteAdjustedFlag;
						$log = "enableQuoteAdjustment=".$enableQuoteAdjustment." AND quoteAdjustedFlag=".$quoteAdjustedFlag. PHP_EOL ;
						$fwrite = @fputs($logfile, $log);
				
						if($enableQuoteAdjustment == 1 AND $quoteAdjustedFlag <> 1)
						{
							//echo ("Creating Orders-------- Inside IF");
							$QuoteId = $object->Quotes[$i]->ID;
							$IncidentId = $object->Quotes[$i]->CustomFields->CO->IncidentId->ID; // Retrives Incident ID
							$IncidentPreferredDt = $object->Quotes[$i]->CustomFields->CO->IncidentId->CustomFields->CO->PreferredDate; // Retrives Incident Pref Dt
							$AssetId = $object->Quotes[$i]->CustomFields->CO->IncidentId->Asset->ID; // Retrives Asset ID
							$log = "QuoteId=".$QuoteId." AND IncidentId=".$IncidentId." AND IncidentPreferredDt=".$IncidentPreferredDt." AND AssetId=".$AssetId. PHP_EOL;
							$fwrite = @fputs($logfile, $log);

							if ($AssetId <> '')
							{
								//Adjust Quote based on Asset Warranty Items && Asset Entitlements
								$quoteItemCount = count($object->Quotes[$i]->LineItems);
								$quoteTotal = 0 ; 
								for($j=0;$j<$quoteItemCount;$j++)
								{
									//echo "inside for Loop ";
									$itemProductID = $object->Quotes[$i]->LineItems[$j]->Product->ID ;
									$WarrantyItemResult = RNCPHP\ROQL::queryObject("SELECT CO.AssetWarrantyItem FROM CO.AssetWarrantyItem WHERE AssetId ='".$AssetId."' AND ProdId='".$itemProductID."';")->next();
	            					$WarrantyItem = $WarrantyItemResult->next();

	            					$log = "itemProductID=".$itemProductID." AND WarrantyItemID=".$WarrantyItem->ID. PHP_EOL;
									$fwrite = @fputs($logfile, $log);
									
				
	            					if($WarrantyItem->ID <> '')
	            					{
	            						$WarrantyItemStartDt = $WarrantyItem->WarrantyStart;
	            						$WarrantyItemEndDt = $WarrantyItem->WarrantyExpiry;
	            						$WarrantyItemDiscount = $WarrantyItem->Discount;
										
										$log = "WarrantyItemDiscount=".$WarrantyItemDiscount." AND WarrantyItemStartDt=".$WarrantyItemStartDt." AND Today=".$today_dt." AND $WarrantyItemEndDt=".$WarrantyItemEndDt. PHP_EOL;
	            						$fwrite = @fputs($logfile, $log);
				
	            						if($WarrantyItemStartDt <= $today_dt && $WarrantyItemEndDt >= $today_dt)
	            						{
	            							$object->Quotes[$i]->LineItems[$j]->DiscountPercent = $WarrantyItem->Discount;
											//adjusted total
	            							$object->Quotes[$i]->LineItems[$j]->AdjustedPrice->Value = ($object->Quotes[$i]->LineItems[$j]->OriginalPrice->Value * (100-$WarrantyItem->Discount))/100 ; 
	            							$object->Quotes[$i]->LineItems[$j]->AdjustedTotal->Value = ($object->Quotes[$i]->LineItems[$j]->OriginalPrice->Value * (100-$WarrantyItem->Discount) * $object->Quotes[$i]->LineItems[$j]->Quantity)/100 ;
	            							$object->save(RNCPHP\RNObject::SuppressAll) ; 
	            							$quoteTotal = $quoteTotal + $object->Quotes[$i]->LineItems[$j]->AdjustedTotal->Value ; 
											
	            							$log = "save done 1" . PHP_EOL. $object->Quotes[$i]->LineItems[$j]->DiscountPercent. PHP_EOL. $object->Quotes[$i]->LineItems[$j]->AdjustedPrice->Value . PHP_EOL . $object->Quotes[$i]->LineItems[$j]->AdjustedTotal->Value . PHP_EOL . $quoteTotal ;
	            							$fwrite = @fputs($logfile, $log);
	            						}
	            					}

	            					$EntitlementResult = RNCPHP\ROQL::queryObject("SELECT CO.AssetEntitlements FROM CO.AssetEntitlements WHERE AssetId ='".$AssetId."' AND ProdId='".$itemProductID."';")->next();
	            					$EntitlementItem = $EntitlementResult->next();
									$log = "itemProductID=".$itemProductID." AND EntitlementItemID=".$EntitlementItem->ID. PHP_EOL;
									$fwrite = @fputs($logfile, $log);
				
	            					if($EntitlementItem->ID <> '')
	            					{
	            						$EntitlementItemStartDt = $EntitlementItem->EntitlementStart;
	            						$EntitlementItemEndDt = $EntitlementItem->EntitlementExpiry;
	            						$EntitlementItemDiscount = $EntitlementItem->Discount;
	            						$log = "EntitlementItemDiscount=".$EntitlementItemDiscount." AND EntitlementItemStartDt=".$EntitlementItemStartDt." AND Today=".$today_dt." AND $EntitlementItemEndDt=".$EntitlementItemEndDt. PHP_EOL;
	            						$fwrite = @fputs($logfile, $log);
				
	            						if($EntitlementItemStartDt <= $today_dt && $EntitlementItemEndDt >= $today_dt)
	            						{
	            							$object->Quotes[$i]->LineItems[$j]->DiscountPercent = $EntitlementItem->Discount;
	            							//adjusted total
	            							$object->Quotes[$i]->LineItems[$j]->AdjustedPrice->Value = ($object->Quotes[$i]->LineItems[$j]->OriginalPrice->Value * (100-$EntitlementItem->Discount))/100 ; 
	            							$object->Quotes[$i]->LineItems[$j]->AdjustedTotal->Value = ($object->Quotes[$i]->LineItems[$j]->OriginalPrice->Value * (100-$EntitlementItem->Discount) * $object->Quotes[$i]->LineItems[$j]->Quantity)/100 ;
	            							$object->save(RNCPHP\RNObject::SuppressAll) ;
	            							$quoteTotal = $quoteTotal + $object->Quotes[$i]->LineItems[$j]->AdjustedTotal->Value ; 
	            							$log = "save done 2" . PHP_EOL. $object->Quotes[$i]->LineItems[$j]->DiscountPercent. PHP_EOL. $object->Quotes[$i]->LineItems[$j]->AdjustedPrice->Value . PHP_EOL . $object->Quotes[$i]->LineItems[$j]->AdjustedTotal->Value . PHP_EOL . $quoteTotal ;
	            							$fwrite = @fputs($logfile, $log);
	            						}
	            					}
									//echo "Quote Total ".$quoteTotal;
	            				}//end-for($j=0;$j<$quoteItemCount;$j++)
									
							}//end-if ($AssetId <> '')
						
							
							$object->Quotes[$i]->CustomFields->CO->QuoteAdjustedFlag=1;
							$object->Quotes[$i]->AdjustedTotal->Value = $quoteTotal ; 
							$object->Quotes[$i]->Total->Value = $quoteTotal ; 
							
							$object->save(RNCPHP\RNObject::SuppressAll) ; 
						}//end-if(($enableQuoteAdjustment == 1) AND ($quoteAdjustedFlag == '0'))	
					}//end-for($i=0;$i<$q_count;$i++)
					$object->CustomFields->CO->TriggerQuoteAdjustment=0;
					$object->save(RNCPHP\RNObject::SuppressAll) ; 
				}//end-if($sQuoteAdjustment=="Yes")
				
			}//end-try
			catch(Exception $err){
				$log = $err->getMessage();
				$fwrite = @fputs($logfile, $log);
			}
			@fclose($logfile);
        }
		//return;
    }
}


class adjustOpportunityQuote_TestHarness implements RNCPM\ObjectEventHandler_TestHarness
{

    static $opty_id = NULL;
    static $optyOne = NULL;

    public static function setup()
    {

		try{

			//Incident Create
			$optyOne = RNCPHP\Opportunity::fetch(127);
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