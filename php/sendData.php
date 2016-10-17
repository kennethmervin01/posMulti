<?php
require_once("class/MysqliDb.php");
require_once("class/Model.php");
$real_or   =    $_POST['real_or'];
$client_or =  $_POST['dist_or'];
$willpay   =  $_POST['willpay'];

$items = []; 
$branch = "PH001";
$db = new Model("localhost","root","","cma_back");
$data = array(
	"BranchID" 		 => $branch,
	"real_or_num"    => $real_or['real_or_num'],
	"real_or_amount" => $real_or['real_or_amount'],
	"real_or_date"   => $real_or['real_or_date'],
	"real_or_type"   => $real_or['real_or_type'],
	"real_or_ref"    => '',
	"real_or_remark" => '',
	"date_input"     => date("Y-m-d H:i:s"),
	"balance"        => $real_or['balance'], 
	"credits"        => $real_or['credits']
	);
/***

$real = $db->insert_real_or($data); 
if($real){
	$ok = $real_or;
} else {
	$ok = "no";
}

***/
foreach($client_or as $client){
	$or_head = $client["or_head"];
	$payment = paymentfind($willpay,$or_head['custid']);
	
	$data_head = array(
		"PONumber" => "",
		"BranchID"  => $branch,
		"Date"   => date("Y-m-d"),
		"CustNo" => $or_head['custid'],
		"Name"   =>"" ,
		"SoldBy" => "",
		"PayCode" =>    $real_or['real_or_type'],
		"PayDate" =>    $real_or['real_or_date'],
		"ItemTotal" =>  $or_head['subtotal'],
		"ItemCost"  =>  $or_head['subtotal'],
		"DiscRate"  =>  $or_head['discremark'],
		"DiscAmount" => $or_head['discount'] + $or_head['refdiscount'] ,
		"OrderTotal" => $or_head['fintotal'],
		"RemitAmt"  =>  $payment['remit_amt'],
		"OrderBal" =>   $payment['balance'],
		"BankName" => "",
		"Branch"   => "",
		"CheckNo"  => "",
		"CheckDate" => "",
		"ORNumber" => $real_or['real_or_num'],
		"TierID"   => "",
		"payment_status" => "PAID",
		"bs_number" => "",
		"enroll_status" => "CMA",
		"ref_qty" => 	$or_head['refqty'],
		"special_discount" => $or_head['disckey'], 
		"real_or_num" => $real_or['real_or_num'],
	);		
	$or_detail = $client["or_details"];
} 




function paymentfind($willpay,$custno){
	foreach($willpay as $wpay){
		if($wpay["custno"] == $custno){
			$payment = array("balance" => $wpay["balance"], "remit_amt" => $wpay["remit_amt"]);
			break;
		}
	}
	return $payment;
}

echo json_encode(array("OK" => $data, "OK2" => $data_head));
