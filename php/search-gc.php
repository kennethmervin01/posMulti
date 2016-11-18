<?php
require_once("class/MysqliDb.php");
require_once("class/Model.php");
$db = new Model;
$results = $db->check_valid_gc($_POST['gc']);
$data = array();
if($results['status'] == '1'){
	$data = $db->get_gc_data($results['result']['scholar_type_id']);
} 
echo json_encode(array("result" => $results, "items" => $data));