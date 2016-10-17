<?php 
require_once("class/MysqliDb.php");
require_once("class/Model.php");
$items = []; 
$branch = "PH001";
$db = new Model("localhost","root","","cma_back");
$results = $db->customer_search_term($_POST['term'],$branch,$_POST['customer_nos']);
foreach($results as $result):
	$credit = $db->credit_referral($result['CustNo'],$result['StudentID']);
	$result['StudentID'] ? $res_id = $result['StudentID'] : $res_id = "NEW STUDENT"; 

	$item['label'] = "$res_id - {$result['SurName']},  {$result['FirstName']}";
	$item['value'] = $result['CustNo'];
	$item['credit'] = $credit;
	$item['next_tier'] = $db->next_tier($result['CustNo']);
	$res_id == "NEW STUDENT" ? $item["new"]	 = 1 : $item["new"] = 0; 

	$items[] = $item;
endforeach;	
echo json_encode($items);