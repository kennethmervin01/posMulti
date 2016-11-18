<?php 
class Model extends MysqliDb{

	function __construct(){
		parent::__construct("localhost","root","","cma_back",null,"utf-8");
	}	


	public function customer_search_term($term,$branch,$customers){
		$cust_query = "";
		if($customers != ""){
			$customers = rtrim($customers,',');
			$customer = explode(",", $customers);
			$custq = "";
			foreach ($customer as $custno) {
				$custq .= "CustNo != $custno AND ";
			}
			$custq = rtrim($custq," AND ");
			$cust_query = "($custq) AND "; 
		}
		$results = $this->rawQuery("SELECT  * FROM tcustomer WHERE BranchID='$branch'  AND $cust_query  CONCAT(`StudentID`,' ',`FirstName`,' ',`SurName`) LIKE '%$term%' LIMIT 10");
		return $results;
	}


	public function credit_referral($custno,$studentID){
		$all = $this->rawQuery("SELECT COUNT(*) as mycount FROM referral_request WHERE givento = '$studentID' AND `status`=  1  AND status_credit = 0");
		$used = $this->rawQuery("SELECT SUM(`ref_qty`) as sum_used FROM eorderhdr WHERE CustNo = '$custno' ");	
		$credit_available = $all[0]['mycount'] - $used[0]['sum_used']; 
		return $credit_available;
	}



	public function gen_po_num($branch,$year,$month){
		$results  = $this->rawQuery("SELECT COUNT(*) as mycount FROM eorderhdr WHERE BranchID = '$branch' AND YEAR(`Date`) = '$year' AND MONTH(`Date`) = '$month'");
		$max = $results[0]['mycount'];
		$total  = $max + 1;
		return  str_pad($total,'4','0',STR_PAD_LEFT);
	}

	public function item_search_term($term,$branch){
		$results = $this->rawQuery("SELECT thitems.Sku,thitems.ItemNo,titems.Description,titems.Photo,titems.IsBook FROM thitems INNER JOIN titems ON thitems.ItemNo = titems.ItemNo WHERE thitems.BranchID = '$branch' AND  CONCAT(thitems.`ItemNo` ,' ', titems.`Description`) LIKE '%$term%'  ORDER BY  thitems.ItemNo LIMIT 5 ");
		return $results;
	}
	
	public function item_get_det($term,$branch){
		$results = $this->rawQuery("SELECT thitems.Sku,thitems.ItemNo,titems.Description,thitems.StdCost,titems.IsBook FROM thitems INNER JOIN titems ON thitems.ItemNo = titems.ItemNo WHERE thitems.BranchID = '$branch' AND thitems.Sku= '$term'");
		return $results;
	}

	public function custom_insert($table,$data){
		$check  = $this->insert($table,$data);
		return $check;
	}

	public function custom_update($table,$data,$column,$value){
		$this->where($column,$value);
		$this->update ($table,$data);
	}

	public function custom_count_where($table,$array){
		foreach ($array as $key => $value) {
			$this->where($key,$value);
		}	
		$results = $this->withTotalCount()->get($table);
		return $this->totalCount;
	}

	public function custom_multiple_where($table,$array){
		foreach ($array as $key => $value) {
			$this->where($key,$value);
		}	
		$results = $this->get($table);
		return $results;	
	}

	public function insert_real_or($data){
		$check  = $this->insert("real_or",$data);
		return $check;
	}


	public function insert_eorderhdr($data){
		$check  = $this->insert("eorderhdr",$data);
		return $check;
	}


	public function student_data($custno){
		$customer = $this->rawQuery("SELECT SurName as sname,MiddleName as mname,FirstName as fname FROM tcustomer WHERE CustNo= $custno");
		return  $name= " {$customer[0]["sname"]},  {$customer[0]["fname"]} {$customer[0]["MiddleName"]} ";
	}

	public function student_data_raw($custno){
		$customer = $this->rawQuery("SELECT * FROM tcustomer WHERE CustNo= $custno");
		return  $result= $customer[0];
	}

	//next tier payment of student
	public function next_tier($custno){
		$tier = $this->rawQuery("SELECT MAX(TierID + 1) AS next_tier FROM eorderhdr WHERE CustNo='$custno' AND enroll_status ='CMA' AND payment_status  != 'VOID'");
		if($tier[0]['next_tier']){
			$desc = $this->rawQuery("SELECT `Description` AS next_tier_string FROM ttier WHERE ID='{$tier[0]['next_tier']}'");
			return $desc[0]['next_tier_string'];
		} else {
			return "A";
		}
	}

	public function next_tier_id($custno){
		$tier = $this->rawQuery("SELECT MAX(TierID + 1) AS next_tier FROM eorderhdr WHERE CustNo='$custno' AND enroll_status ='CMA' AND payment_status  != 'VOID'");
		if($tier[0]['next_tier']){
			return $tier[0]['next_tier'];
		} else {
			return "1";
		}
	}

	public function tier_raw($tier){
		if($tier != "" || $tier != NULL ){
			$desc = $this->rawQuery("SELECT `Description` AS next_tier_string FROM ttier WHERE ID='$tier'");	
			return $desc[0]['next_tier_string'];
		} else {
			return "A";
		}
	}


	public function find_bs($bs,$branch){
		$result = $this->rawQuery("SELECT * FROM billing_statement as bs INNER JOIN billing_statement_details as bsd ON bs.bs_num = bsd.bs_num AND bs.BranchID = bsd.branch_id WHERE bs.bs_num = '$bs' AND bs.BranchID='$branch' ");
		$item = array();
		$x = 0;
		$student_data = array();
		foreach($result as $rs) {
			array_push($item,array("item_name" => $rs['ItemNo'],"item_qty" => $rs['Qnty']));
			if($x == 0){
				$custno = $rs['CustNo'];
				$student_data =  $this->student_data_raw($custno);	
				$head_bs_tier =  $rs['tier_id'];	
				$x++;
			}	
		}
		return array("head_bs_tier" => $head_bs_tier, "items" => $item, "customer" => $student_data );
	}


	public function check_valid_gc($gc){
		$this->where("CustNo",0);
		$this->where("gc_code",$gc);
		$result = $this->getOne("scholar_student");
		if($this->count <= 0){
			return array("status" => "0", "result" => $result);
		} else {
			return array("status" => "1", "result" => $result);
		}

	}

	public function get_gc_data($type){
		$this->where("scholar_type_id",$type);
		return $this->getOne("scholar_type");
	}

	public function level_cat($level_id){
		$this->where("ID",$level_id);
		return $this->getOne("tlevel");
	}	



	// Send Data Function	
	public function save_data_realOR($branch,$real_or){
		$data = array(
			"BranchID" 		 => $branch,
			"real_or_num"    => $real_or['real_or_num'],
			"real_or_amount_paid"    => $real_or['real_or_amount_paid'],
			"real_or_amount_due" => $real_or['real_or_amount_due'],
			"real_or_date"   => $real_or['real_or_date'],
			"real_or_type"   => $real_or['real_or_type'],
			"real_or_ref"    => '',
			"real_or_remark" => '',
			"date_input"     => date("Y-m-d H:i:s"),
			"balance"        => $real_or['balance'], 
			"credits"        => $real_or['credits'],
			"scholar_code"   => $real_or['scholar_code'],
			"real_or_status" => $real_or['real_or_status']
		);
		//insert real_or table
		$real = $this->insert_real_or($data); 
		return $ok = $real ? "yes" : "no";
	}

	public function save_to_eorderdtl($details,$new_po){
		foreach ($details as $ods){
			$data_detail = array(
				"PONumber" => $new_po,
				"ItemNo"   => $ods['sku'],
				"Qnty"     => $ods['qty'],
				"Price"    => $ods['price'],
				"Amount"   => $ods['totprice']
			);
			//INSERT  in eorderdtl
			$status_detail = $this->custom_insert("eorderdtl",$data_detail);
		}
	}

	public function save_to_eorderhdr($data_head){
		$status_header = $this->custom_insert("eorderhdr",$data_head);	 
		return $ok2 = $status_header ? "yes" : "no";	
	}	

	public function init_data_head($branch,$new_po,$student_data,$real_or,$or_head,$payment,$client,$re_or){
		$data_head = array(
			"PONumber" => $new_po,
			"BranchID" => $branch,
			"Date"   =>  date("Y-m-d"),
			"CustNo" =>  $or_head['custid'],
			"Name"   =>  $student_data['SurName'].", ".$student_data['FirstName'],
			"SoldBy" => "Anthony Esguerra",
			"PayCode" =>    $real_or['real_or_type'],
			"PayDate" =>    $real_or['real_or_date'],
			"ItemTotal" =>  $or_head['subtotal'],
			"ItemCost"  =>  $or_head['subtotal'],
			"DiscRate"  =>  $or_head['discremark'],
			"DiscAmount" => $or_head['discount'] + $or_head['refdiscount'],
			"OrderTotal" => $or_head['fintotal'],
			"RemitAmt"  =>  $payment['remit_amt'],
			"OrderBal" =>   $payment['balance'],
			"BankName" => 	$real_or['bank_name'],
			"Branch"   => 	$real_or['bank_branch'],
			"CheckNo"  => 	$real_or['cheque_num'],
			"CheckDate" => 	$real_or['cheque_date'],
			"ORNumber" => 	$re_or,
			"TierID"   =>   $client["or_tier"],
			"payment_status" => "PAID",
			"bs_number" => "",
			"enroll_status" => "CMA",
			"ref_qty" => 	$or_head['refqty'],
			"special_discount" => $or_head['disckey'], 
			"real_or_num" => $real_or['real_or_num'],
		);	

		return $data_head;	

	}

	public function init_data_for_sync($branch,$or_head,$real_or,$payment,$client,$bfee,$lfee,$ofee,$re_or){
		$data_for_sync = array(
			"BranchID" => $branch,
			"Date"   =>  date("Y-m-d"),
			"CustNo" =>  $or_head['custid'],
			"PayCode" =>    $real_or['real_or_type'],  // 
			"PayDate" =>    $real_or['real_or_date'],   //
			"ItemTotal" =>  $or_head['subtotal'], //
			"ItemCost"  =>  $or_head['subtotal'], //
			"DiscRate"  =>  $or_head['discremark'], //
			"DiscAmount" => $or_head['discount'] + $or_head['refdiscount'], //
			"OrderTotal" => $or_head['fintotal'], //
			"RemitAmt"  =>  $payment['remit_amt'], //
			"OrderBal" =>   $payment['balance'],
			"BankName" => 	$real_or['bank_name'], //
			"Branch"   => 	$real_or['bank_branch'], //
			"CheckNo"  => 	$real_or['cheque_num'], //
			"CheckDate" => 	$real_or['cheque_date'], //
			"ORNumber" => 	$re_or, // 
			"TierID"   =>   $client["or_tier"], // 
			"ref_qty" => 	$or_head['refqty'], // 
			"special_discount" => $or_head['disckey'], // 
			"bookfee"   =>  $bfee,
			"lessonfee" => $lfee,
			"otherfee"  => $ofee
		); 
		return  $data_for_sync;
	}

	public function compute_data_fees($data_sync_remit,$or_head){
		$bal_bookfee   = 0; // balance book fee  var
		$bal_otherfee  = 0; // balance other fee var
		$bal_lessonfee = 0; // balance lesson fee var	
		if($data_sync_remit >= $or_head['bookfee']){
			$bfee =  $or_head['bookfee'];
			$data_sync_remit2 = $data_sync_remit - $or_head['bookfee'];
			if($data_sync_remit2 >= $or_head['otherfee']){
				$ofee = $or_head['otherfee'];	
				$data_sync_remit3 = $data_sync_remit2 - $or_head['otherfee'];
				if($data_sync_remit3 >= $or_head['lessonfee']){
					$lfee = $or_head['lessonfee'];
				} else {
					$lfee = $data_sync_remit3;
					$bal_lessonfee = $or_head['lessonfee'] -  $data_sync_remit3;
				}
			} else {
				$ofee = $data_sync_remit2;
				$lfee  = 0;
				$bal_otherfee  = $or_head['otherfee'] - $data_sync_remit2;
				$bal_lessonfee = $or_head['lessonfee']; 
			}
		} else {
			 $bfee = $data_sync_remit;
			 $ofee = 0;
			 $lfee = 0;
			 $bal_bookfee   =  $or_head['bookfee'] - $data_sync_remit;
			 $bal_otherfee  =  $or_head['otherfee'];
			 $bal_lessonfee =  $or_head['lessonfee']; 
		}
		$balance_true = $bal_bookfee > 0 || $bal_otherfee > 0 || $bal_lessonfee > 0 ? 1 : 0 ;
		return array(
			"balance"    => $balance_true,  
			"bbookfee"   => $bal_bookfee,
			"botherfee"  => $bal_otherfee, 
			"blessonfee" => $bal_lessonfee, 
			"bfee"       => $bfee,
			"ofee"       => $ofee,
			"lfee"       => $lfee
			);
	}


	public function save_balance($cfee,$or_head,$client,$re_or){
		if($cfee['balance'] == 1){
			$total_balance  = $cfee['bbookfee'] + $cfee['blessonfee'] + $cfee['botherfee']; 
			$to_insert = array(
				"bl_desc"   =>  "Remaining Balance from OR Number -".$re_or,
				"bl_cost"   =>  $total_balance,
				"bl_status" =>  "BALANCE",
				"cust_no"   =>  $or_head['custid'],
				"lesson_fee" => $cfee['blessonfee'] ,
				"book_fee"  =>  $cfee['bbookfee'],
				"other_fee"  => $cfee['botherfee'],
				"tier_id"    => $client["or_tier"],
				"from_or"    => $re_or
			);
			$this->custom_insert("balance_tbl",$to_insert);
		}
	}


	public function save_credits($real_or,$client_id){
		if($real_or['credits'] > 0 ){
			$for_credits = array(
				'cust_nos' => $client_id,
				'from_or'  => $real_or['real_or_num'],
				'credit_amount' =>  $real_or['credits']
			);
			$credits_status = $this->custom_insert("tcredits",$for_credits);
			$credits_save   =  $credits_status ? "yes" : "no" ;
		} else {
			$credits_save  = "none-yes";
		}

		return $credits_save;
	}

	public function payment_find($willpay,$custno){
		foreach($willpay as $wpay){
			if($wpay["custno"] == $custno){
				$payment = array("balance" => $wpay["balance"], "remit_amt" => $wpay["remit_amt"]);
				break;
			}
		}
		return $payment;	
	}

	public function generate_po($branch){
		$branch = $branch;
		$year   = date("Y");
		$month  = date("m");
		sleep(3);
		$max    = $this->gen_po_num($branch,$year,$month);
		return  "$branch-$year-$month-$max";
	}


	public function unsync_data($branch,$month,$year){
		$query = "SELECT *
				FROM   eorderhdr
				WHERE BranchID ='$branch' AND MONTH(`Date`) = $month  AND YEAR(`Date`) = $year  AND NOT EXISTS (SELECT ORNumber
				FROM   sync_log 
				WHERE  sync_log.or_number = eorderhdr.ORNumber AND sync_log.branch_id = eorderhdr.BranchID)";
	}


} // end MOdel class 