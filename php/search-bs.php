<?php 
require_once("class/MysqliDb.php");
require_once("class/Model.php");
$branch = "PH001";


$bs = $_POST['pn'];
search_bs($bs,$branch);

function search_bs($bs,$branch){
	$db = new Model(array("host" => "localhost", "username" => "root", "password" => "", "charset" => "utf-8" , "db" => "cma_back" ) );
	$results = $db->find_bs($bs,$branch);
	$customer = $results['customer'];
	$customer['StudentID'] == NULL || $customer['StudentID'] == ""  ?  $customer_sid = "NEW STUDENT" : $customer_sid = $customer['StudentID'];   
	$customer['StudentID'] == NULL || $customer['StudentID'] == ""  ?  $customer_info['new'] = 1 : $customer_info['new'] = 0;
	$customer_info['label'] = " $customer_sid - {$customer['SurName']}, {$customer['FirstName']}";
	$customer_info['value'] = $customer['CustNo'];	
	$customer_info['next_tier'] = $db->tier_raw($results['head_bs_tier']);
	$customer_info['credit'] = 0;
	echo json_encode(array("customer" => $customer_info, "item" => $results['items']));
}



