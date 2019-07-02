<?
if (!defined('DOCROOT')) 
{
	$docroot = get_cfg_var('doc_root');
	define('DOCROOT', $docroot);
}
 
// Set up access control and authentication
require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');

use RightNow\Connect\v1_2 as RNCPHP;

$AssetId = $_GET['asset_id'];



try{
		// Get Asset Object 
		echo "AssetId : ".$AssetId."<br/>";
		$AssetObj= RNCPHP\Asset::Fetch($AssetId);
		$AssetName = $AssetObj->Name;
		
		//Service Agreement Creation : START           
		
		$SerAgrement = new RNCPHP\CO\ServiceAgreement();
		$SerAgrement->Name = "Agreement for Asset: ".$AssetName;
		$SerAgrement->Asset = RNCPHP\Asset::Fetch($AssetId);  
		$SerAgrement->Agreement_Number = $AssetId;
	
		$SerAgrement->save(RNCPHP\RNObject::SuppressAll);
		//echo "save stage";
		
		echo "Agreement is Created for:  ".$AssetId."<br/>";
		echo "Agrement ID: ".$SerAgrement->ID;
	
		//Service Agreement Creation : END

		// Create of Service AgreementItems : Warranty object Search		
					
		$ProdId = $AssetObj->Product->ID;
		$ProdName = $AssetObj->Product->LookupName;
		
		echo "Prod ID: ".$ProdId."Prod Name: ".$ProdName;
		$ServiceProdID = $AssetObj->Product->ServiceProduct->ID;
		$ServiceProdName = $AssetObj->Product->ServiceProduct->LookupName;
		
		$ServiceProdParentID = $AssetObj->Product->ServiceProduct->Parent->ID;
		
		if($ServiceProdParentID <> ''){
			$GrandParentID = $AssetObj->Product->ServiceProduct->Parent->Parent->ID;
		}
		
		/* 
		echo "ServiceProdID : ".$ServiceProdID;
		echo "ServiceProdName : ".$ServiceProdName;
		echo "ServiceProdParentID : ".$ServiceProdParentID;
		echo "GrandParentID :".$GrandParentID;
		*/
		
		// Query for Warranty Object search with Product
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
			$SAgreeItem->ServiceAgreementId = $SerAgrement->ID;
			
			$SAgreeItem->WarrantyType = $WarrantyObj->WarrantyType;
			
			if($WarrantyObj->WarrantyType == "Standard"){
			
				$SAgreeItem->WarrantyStatus = 'Active';
			}
			else if($WarrantyObj->WarrantyType == 'Extended'){
				
				$SAgreeItem->WarrantyStatus = 'Not Applied';
				
			}
						
			$SAgreeItem->save(RNCPHP\RNObject::SuppressAll);
			
			echo "AgreementItem ID:  ".$SAgreeItem->ID;
						
		}

		$object->save(RNCPHP\RNObject::SuppressAll);
		
	}
	catch(Exception $err){
		////echo  $err->getMessage();
	}


	
?>
