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
}