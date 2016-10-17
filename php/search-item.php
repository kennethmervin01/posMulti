<?php
require_once("class/MysqliDb.php");
require_once("class/Model.php");
$branch = "PH001";

function search_item($term,$branch){
	$db = new Model(array("host" => "localhost", "username" => "root", "password" => "", "charset" => "utf-8" , "db" => "cma_back" ) );
	$results = $db->item_search_term($term,$branch);
	foreach($results as $result):
		$desc = $result['Description'];
		$item['label'] = "{$result['ItemNo']}  $desc";
		$item['value'] = $result['Sku'];
		$item['icon']  = $result['Photo'];
		if($result['Photo'] == NULL || $result['Photo'] == false ){
			$item['icon']  = "upload_inv/no_image.jpg";
		}
		$items[] = $item;
	endforeach;	
	echo json_encode($items);	
}


function fees($sku,$branch,$price){
	$db = new Model(array("host" => "localhost", "username" => "root", "password" => "", "charset" => "utf-8" , "db" => "cma_back" ) );
	$db->where("Code", $sku);
	$db->where("BranchID",$branch);
	$fees = $db->getOne("fees");
	if($db->count >= 1){
		$lessonfee = $fees['lessonfee'];
		$bookfee   = $fees['bookfee'];
		$otherfee  = $fees['otherfee'];
		$vatable   = $lessonfee + $otherfee; 
 	} else {
		$lessonfee = 0;
		$bookfee   = 0;
		$otherfee  = $price;
		$vatable   = $price;	
	} 
	return array("lessonfee" => $lessonfee, "bookfee" => $bookfee, "otherfee" => $otherfee,"vatable" => $vatable);

}

function get_item($sku,$branch){
	$db = new Model(array("host" => "localhost", "username" => "root", "password" => "", "charset" => "utf-8" , "db" => "cma_back" ));
	$results = $db->item_get_det($sku,$branch);
	$fee = fees($sku,$branch,$results[0]['StdCost']);	
	$vatable  = $fee['vatable'];
	if( $results[0]['IsBook'] == "Yes"){
		$vatable = 0;
	}


	echo json_encode(
		array(
			"desc"      => $results[0]['Description'],
			"price"     => $results[0]['StdCost'],
			"itemno"    => $results[0]['ItemNo'],
			"bookfee"   => $fee['bookfee'],
			"lessonfee" => $fee['lessonfee'],
			"otherfee"  => $fee['otherfee'],
			"totprice"  => $results[0]['StdCost'],
			"vatable"   => $vatable,
			"qty"       => $_POST['qty']
		));	
}


function get_bundle($bundle,$branch){
	$db = new Model(array("host" => "localhost", "username" => "root", "password" => "", "charset" => "utf-8" , "db" => "cma_back" ));
	$results = $db->item_get_det($bundle["sku"],$branch);
	$fee = fees($bundle["sku"],$branch,$results[0]['StdCost']);	
	$vatable  = $fee['vatable'];
	if( $results[0]['IsBook'] == "Yes"){
		$vatable = 0;
	}	
	echo json_encode(
		array(
			"desc"      => $results[0]['Description'],
			"price"     => $results[0]['StdCost'],
			"itemno"    => $results[0]['ItemNo'],
			"bookfee"   => $fee['bookfee'],
			"lessonfee" => $fee['lessonfee'],
			"otherfee"  => $fee['otherfee'],
			"totprice"  => $results[0]['StdCost'],
			"vatable"   => $vatable,
			"qty"       => $bundle['qty']
		));	
}


if($_POST['method'] != "get_bundle"):
	$func = $_POST['method'];
	$func($_POST['term'],$_POST['branch']);
else :
	get_bundle($_POST['bundle'],"PH001");
endif;	
?>