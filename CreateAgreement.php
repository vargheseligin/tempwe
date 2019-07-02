<rn:meta title="#rn:msg:SHP_TITLE_HDG#" template="standard.php" clickstream="home"/>
<h1>Import Products</h1>
<p>Please name the file "ImportProducts.csv". It must be a CSV file in order for this to work.</p>
<ol>
    <li>Import csv file into an array</li>
    <li>Foreach answer in Array:
        <ol>
            <li>Import Products</li>
            <li>get the ID for the answer corresponding to the Siebel ID</li>
            <li>Delete them one by one</li>
        </ol>
    </li>
</ol>
<?
//use \RightNow\Connect\v1_2 as RNCPHP;

$AssetId = $_GET ['asset_id'];

echo "asset id: ".$AssetId;
 //$CI = get_instance();
 
 //$CI->model('custom/ImportProducts_Code')->ImportProducts();
?>