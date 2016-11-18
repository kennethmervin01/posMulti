<?php
require_once("class/MysqliDb.php");
require_once("class/Model.php");
require_once("class/StrHtml.php");
require_once("class/Sync.php");

$real_or   =  $_POST['real_or'];
$client_or =  $_POST['dist_or'];
$willpay   =  $_POST['willpay'];
$client_id = "";
$items = []; 
$branch = "PH002";
$credits_save = 0;
$data_heads = array();
$db = new Model;
$sync = new Sync("PH002","Dz73ZtTAu1");

// Setting Variables For eorderhdr
$count_client = count($client_or); 
$letter_str = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N");
$x = 0;
$ok = $db->save_data_realOR($branch,$real_or); // save real or
 
foreach($client_or as $client){
	$or_head = $client["or_head"];
	$client_id .="{$or_head['custid']},";
	$student_data = $db->student_data_raw($or_head['custid']);
	$payment = $db->payment_find($willpay,$or_head['custid']);
	$new_po = $db->generate_po($branch); // generate  new po
	$re_or  = $count_client == 1 ? 	$real_or['real_or_num'] : $real_or['real_or_num']."-".$letter_str[$x];
	$data_head = $db->init_data_head($branch,$new_po,$student_data,$real_or,$or_head,$payment,$client,$re_or); // create data variable for table eorderhdr 
	$data_sync_remit = $payment['remit_amt']; // amount payment
	$cfee = $db->compute_data_fees($data_sync_remit,$or_head); // calcute balance and  payment to bookfee -> otherfee -> lessonfee
	// creating variables for syncing	
	$data_raw_heads[$x] = $db->init_data_for_sync($branch,$or_head,$real_or,$payment,$client,$cfee['bfee'],$cfee['lfee'],$cfee['ofee'],$re_or);  
	$x++;
	
	// save to eorderhdr,eorderdtl and balance table
	$db->save_to_eorderhdr($data_head);	 
	$db->save_to_eorderdtl($client["or_details"],$new_po);
	$db->save_balance($cfee,$or_head,$client,$re_or); 
}

$credits_save = $db->save_credits($real_or,$client_id); // save credits

if($ok == "yes"){
	$stest = $sync->init_sync_pay_data($data_raw_heads);
	$sonline = $sync->init_sync_online_practice($data_raw_heads);
	echo json_encode(array("real_or" => $ok, "tcredits" => $credits_save, "cc" => $stest, "for_sync" => $data_raw_heads));
}



