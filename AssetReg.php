<?php
namespace Custom\Models;
use RightNow\Connect\v1_2 as RNCPHP;

class AssetReg extends \RightNow\Models\Base
{
    function __construct()
    {
        parent::__construct();
    }

	function AssetWarrantyItemCreation($assetId)
	{
		
				
		try{
				
				// Get Asset Object 
				echo "assetId : ".$assetId."<br/>";
				$AssetObj= RNCPHP\Asset::Fetch($assetId);
				//$AssetName = $AssetObj->Name;
				
				
				// Create of AssetWarrantyItems : Asset object Search		
				$MasterWarrantyName = $AssetObj->WarrantyName->value;
				$PurchasedDate = $AssetObj->PurchasedDate;					
				$ProdId = $AssetObj->Product->ID;
				$ProdName = $AssetObj->Product->LookupName;
				
				echo "Prod ID: ".$ProdId."Prod Name: ".$ProdName;
				$ServiceProdID = $AssetObj->Product->ServiceProduct->ID;
				$ServiceProdName = $AssetObj->Product->ServiceProduct->LookupName;
				
				if($AssetObj->Product->ServiceProduct->Parent->ID <> ''){
					$ServiceProdParentID = $AssetObj->Product->ServiceProduct->Parent->ID;
				}
				
				if($ServiceProdParentID <> ''){
					$GrandParentID = $AssetObj->Product->ServiceProduct->Parent->Parent->ID;
				}
				
				
				echo "ServiceProdID : ".$ServiceProdID;
				echo "ServiceProdName : ".$ServiceProdName;
				echo "ServiceProdParentID : ".$ServiceProdParentID;
				echo "GrandParentID :".$GrandParentID;
				
				
				// Query for Warranty Object search with Product
				$ResSet = RNCPHP\ROQL::queryObject("SELECT CO.Warranty FROM CO.Warranty
				where CO.Warranty.ProductID = '".$ServiceProdID."' 
				OR CO.Warranty.ProductID = '".$ServiceProdParentID."'
				OR CO.Warranty.ProductID = '".$GrandParentID."'
				AND CO.Warranty.MasterWarrantyName = '".$MasterWarrantyName."' ;")->next();
				
				while($WarrantyObj = $ResSet->next()){
					
					/*echo "Warranty ID  :".$WarrantyObj->ID;
					echo "Warranty ProductID : ".$WarrantyObj->ProductID;
					echo "Warranty ProductName: ".$WarrantyObj->Product_Name;
					*/
					
					
					$AssetWarrantyItem = new RNCPHP\CO\AssetWarrantyItem();
					//$AssetWarrantyItem->Name = "Warranty for Asset: ".$AssetName;
					$AssetWarrantyItem->AssetId = RNCPHP\Asset::Fetch($assetId);
					$SalesProductName = $WarrantyObj->Name;
					$SalesProdObj = RNCPHP\SalesProduct::Fetch($SalesProductName);
					
					
					$AssetWarrantyItem->ProductId = $SalesProdObj->ID;
					$AssetWarrantyItem->ProductName = $WarrantyObj->Name;
					$AssetWarrantyItem->Discount = $WarrantyObj->Discount;
					
					$Startdate = $WarrantyObj->ValidityStart;
					$EndDate = $WarrantyObj->ValidityEnd;
					$Startdate = $PurchasedDate + ($Startdate * 86400);
					$EndDate = $PurchasedDate + ($EndDate * 86400);
														
					$AssetWarrantyItem->WarrantyStart = $Startdate;
					$AssetWarrantyItem->WarrantyExpiry = $EndDate;
					$AssetWarrantyItem->WarrantyType = $WarrantyObj->WarrantyType;
							
								
					$AssetWarrantyItem->save(RNCPHP\RNObject::SuppressAll);
					//echo "Price :".$SumofPrice;
					//echo "AgreementItem ID:  ".$SAgreeItem->ID;
								
				}
					
			}
			catch(Exception $err){
				echo  $err->getMessage();
			}
			
		return;
	
	} //end of function AssetWarrantyItems
	
	
	function AssetEntitlementsCreation($assetId)
	{
		
				
		try{
				
				// Get Asset Object 
				//echo "assetId : ".$assetId."<br/>";
				$AssetObj= RNCPHP\Asset::Fetch($assetId);
				$AssetName = $AssetObj->Name;
				
				$PurchasedDate = $AssetObj->PurchasedDate;
				
				// Create of AssetEntitlementss : Asset object Search		
				$MasterAgreementName = $AssetObj->AgreementName->value;		
				
				
				// Query for Warranty Object search with Product
				$ResSet = RNCPHP\ROQL::queryObject("SELECT CO.ServiceAgreement FROM CO.ServiceAgreement
				 CO.ServiceAgreement.MasterAgreementName = '".$MasterAgreementName."' ;")->next();
				
				while($AgreeObj = $ResSet->next()){
					
									
					$AssetEntitlements = new RNCPHP\CO\AssetEntitlements();
					//$AssetEntitlements->Name = "Warranty for Asset: ".$AssetName;
					$AssetEntitlements->AssetId = RNCPHP\Asset::Fetch($assetId);
					$AssetEntitlements->ProdId = $AgreeObj->ProdID;
					$AssetEntitlements->ProductName = $AgreeObj->ProductName;
					$AssetEntitlements->Discount = $AgreeObj->Discount;
					
					$Startdate = $AgreeObj->ValidityStart;
					$EndDate = $AgreeObj->ValidityEnd;
					$Startdate = $PurchasedDate + ($Startdate * 86400);
					$EndDate = $PurchasedDate + ($EndDate * 86400);
														
					$AssetEntitlements->EntitlementStart = $Startdate;
					$AssetEntitlements->EntitlementExpiry = $EndDate;
					
					$AssetEntitlements->save(RNCPHP\RNObject::SuppressAll);
					
								
				}
				
				
			}
			catch(Exception $err){
				echo  $err->getMessage();
			}
		 
		return;
	} //end of function AssetEntitlements
	
	

} // end of Class