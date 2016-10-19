<?php 
class Model extends MysqliDb{
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

	public function item_search_term($term,$branch){
		$results = $this->rawQuery("SELECT thitems.Sku,thitems.ItemNo,titems.Description,titems.Photo,titems.IsBook FROM thitems INNER JOIN titems ON thitems.ItemNo = titems.ItemNo WHERE thitems.BranchID = '$branch' AND  CONCAT(thitems.`ItemNo` ,' ', titems.`Description`) LIKE '%$term%'  ORDER BY  thitems.ItemNo LIMIT 5 ");
		return $results;
	}
	
	public function item_get_det($term,$branch){
		$results = $this->rawQuery("SELECT thitems.Sku,thitems.ItemNo,titems.Description,thitems.StdCost,titems.IsBook FROM thitems INNER JOIN titems ON thitems.ItemNo = titems.ItemNo WHERE thitems.BranchID = '$branch' AND thitems.Sku= '$term'");
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

}