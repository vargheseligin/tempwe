<?
 /*
* CPMObjectEventHandler: AssetReg
* Package: CO
* Objects: CO\ServiceAgreement
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

class AssetReg implements RNCPM\ObjectEventHandler
{

    public static function apply( $run_mode, $action, $object, $n_cycles )
    {
      if($n_cycles!==0){ return;}//To avoid recursive call

	  static $sAgree = null;
		
	  if (RNCPM\ActionCreate == $action)// If the Action is Create
        {
			try{
				echo  'Apply ServiceAgreement ID: '.$object->ID."<br/>";
				$sAgree = $object->ID;
				$AssetID = $object->Agreement_Number;
				echo 'Asset ID: '.$AssetID."<br/>";
				$AssetObj = RNCPHP\Asset::Fetch($AssetID);
				
				$ProdId = $AssetObj->Product->ID;
				$ProdName = $AssetObj->Product->LookupName;
				
				echo "Prod ID: ".$ProdId."Prod Name: ".$ProdName;
				$ServiceProdID = $AssetObj->Product->ServiceProduct->ID;
				$ServiceProdName = $AssetObj->Product->ServiceProduct->LookupName;
				
				$ServiceProdParentID = $AssetObj->Product->ServiceProduct->Parent->ID;
				
				if($ServiceProdParentID <> ''){
					$GrandParentID = $AssetObj->Product->ServiceProduct->Parent->Parent->ID;
				}
				
				echo "ServiceProdID : ".$ServiceProdID;
				echo "ServiceProdName : ".$ServiceProdName;
				echo "ServiceProdParentID : ".$ServiceProdParentID;
				echo "GrandParentID :".$GrandParentID;
				
				$ResSet = RNCPHP\ROQL::queryObject("SELECT CO.Warranty FROM CO.Warranty
				where CO.Warranty.ProductID = '".$ServiceProdID."' 
				OR CO.Warranty.ProductID = '".$ServiceProdParentID."'
				OR CO.Warranty.ProductID = '".$GrandParentID."';")->next();
				
				while($WarrantyObj = $ResSet->next()){
					
					/*echo "Warranty ID  :".$WarrantyObj->ID;
					echo "Warranty ProductID : ".$WarrantyObj->ProductID;
					echo "Warranty ProductName: ".$WarrantyObj->Product_Name;
					*/
					
					$SAgreeItem = new RNCPHP\CO\ServiceAgreementItem();
					$SAgreeItem->ProductId = $WarrantyObj->ProductID;
					$SAgreeItem->PartNumber = $WarrantyObj->Product_Name;
					$SAgreeItem->Price = $WarrantyObj->Price;
					$SAgreeItem->ServiceAgreementId = $object->ID;
					
					$SAgreeItem->save(RNCPHP\RNObject::SuppressAll);
					
					echo "AgreementItem ID:  ".$SAgreeItem->ID;
					
					
				}

				$object->save(RNCPHP\RNObject::SuppressAll);
				
			}
			catch(Exception $err){
				////echo  $err->getMessage();
			}
        }
	  	return;
    }
}


class AssetReg_TestHarness implements RNCPM\ObjectEventHandler_TestHarness
{

    static $sAgree_id = NULL;
    static $sAgreeOne = NULL;

    public static function setup()
    {

		try{

			//Incident Create
			$sAgreeOne = RNCPHP\CO\ServiceAgreement::fetch(4);
			//$incidentOne->PrimaryContact = RNCPHP\Contact::fetch(2);
			$sAgreeOne->save(RNCPHP\RNObject::SuppressAll);
			self::$sAgree_id = $sAgreeOne->ID;

			////echo  "TestingPhase_Stage1";
		}
		catch (Exception $err ){
			////echo  $err->getMessage();
		}
      	return;
    }


    public static function fetchObject( $action, $object_type)
    {
      $optOne = $object_type::fetch(self::$sAgree_id);
      return($optOne);
    }


    public static function validate( $action, $object )
    {
		//$pass = false;
      	return true;
    }


    public static function cleanup()
    {

		self::$sAgree_id = NULL;
		self::$sAgreeOne = NULL;
		return;
    }
}