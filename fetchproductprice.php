<head>
<title>Connect PHP API test page</title>
</head>
<?
if (!defined('DOCROOT')) 
{
	$docroot = get_cfg_var('doc_root');
	define('DOCROOT', $docroot);
}
 
// Set up access control and authentication
require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');

use RightNow\Connect\v1_2 as RNCPHP;

$AgreeId = $_GET ['Object_Id'];

//echo "AgreeId :".$AgreeId;
$SerAgrement= RNCPHP\CO\ServiceAgreement::Fetch($AgreeId);


//echo "Hello".$SerAgrement->ID;
$ResSet = RNCPHP\ROQL::queryObject("SELECT CO.ServiceAgreementItem FROM CO.ServiceAgreementItem where CO.ServiceAgreementItem.ServiceAgreementId = '".$AgreeId."';")->next();
                                                
while($Opp = $ResSet->next()) //ServiceAgreementItem Object
{

	 $ProdId = $Opp->ProductId;
	 $PartNumber = $Opp->PartNumber;
	 echo "PartNumber : ".$PartNumber."</br>";
		
	$sales_prod= RNCPHP\SalesProduct::Fetch($PartNumber);
	echo "Product ".$sales_prod->ID;
	
	 $sCount = count($sales_prod->Schedules);
	//echo "Count :".$sCount;
	 for($i=0; $i< $sCount ; $i++){                  // Price Schedules
	 
		if($SerAgrement->WarrantyStatus == 'Active'){
			if($sales_prod->Schedules[$i]->Price->Currency->ID ==1){
				
				$price = $sales_prod->Schedules[$i]->Price->Value;
				$Qty = $Opp->Quantity;
				$ItemSum = ($Qty * $price);
				$SumOfPrice += $ItemSum;
				
				$Opp->Price = $price;
				$Opp->Total = $ItemSum;
					echo "SumofPrice Inside Loop:".$SumOfPrice."</br>";
				
			}
		}
	 }
	
		
	//	echo "Quantity :".$Qty."</br>";
		
	//	echo "Price :".$price."</br>";
		
		//echo "Total :".$Opp->Total."</br>";
	//	echo "$SumOfPrice OutOfLoop:"$SumOfPrice."</br>";
		$SerAgrement->TotalAmount = $SumOfPrice;
		
		
		$Opp->save(RNCPHP\RNObject::SuppressAll);
		$SerAgrement->save(RNCPHP\RNObject::SuppressAll);
		//echo "save stage";
		
	
	//echo($Opp->PartNumber."---");
}

echo "Product Price Updated.";
		
?>
