<?php 
require_once("class/MysqliDb.php");
require_once("class/Model.php");
$items = []; 
$branch = "PH001";
$db = new Model("localhost","root","","cma_back");
$results = $db->customer_search_term($_POST['term'],$branch,$_POST['customer_nos']);
foreach($results as $result):
	$credit = $db->credit_referral($result['CustNo'],$result['StudentID']);
	$item['label'] = "{$result['StudentID']} - {$result['SurName']} {$result['FirstName']}";
	$item['value'] = $result['CustNo'];
	$item['credit'] = $credit;
	$items[] = $item;
endforeach;	
echo json_encode($items);