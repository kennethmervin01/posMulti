<?php
Class Sync extends Model {

	function __construct($username,$password){
		parent::__construct();
		$this->ic_username = $username;
		$this->ic_password = $password;
	}

	
	public function init_sync($curl){
		$this->curl_english($curl);
		$this->curl_login($curl);
	}

	public function  curl_english($curl){
		$en = "http://www.cma.com.tw/gourl.php?lang=en";
		curl_setopt($curl, CURLOPT_URL, $en);
		curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, 'callback');
		curl_setopt($curl, CURLOPT_BUFFERSIZE,64000);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_exec($curl);
		if(curl_errno($curl)){
			echo "Something went wrong in your Connection. Please Refresh the page to continue. Initialize Failed";
			exit(); 
		}
	}

	public function curl_login($curl){
		$loginUrl ="http://www.cma.com.tw/jobstatus/enterCheck.php";
		curl_setopt($curl, CURLOPT_URL, $loginUrl);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'account='.$this->ic_username.'&password='.$this->ic_password.'&login=1&SubmitLogout=Login&lang=en');
		curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_exec($curl);
		if(curl_errno($curl)){
			echo "Something went wrong in your Connection. Please Refresh the page to continue. Login Failed";
			exit(); 
		}
	}

	public function curl_url($url,$curl){
		curl_setopt($curl,CURLOPT_URL,$url); //The URL to fetch. This can also be set when initializing a session with curl_init().
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE); //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
		curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,0); //The number of seconds to wait while trying to connect.	
		curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1'); //The contents of the "User-Agent: " header to be used in a HTTP request.
		curl_setopt($curl, CURLOPT_FAILONERROR, TRUE); //To fail silently if the HTTP code returned is greater than or equal to 400.
		curl_setopt($curl, CURLOPT_TIMEOUT, 4000); //The maximum number of seconds to allow cURL functions to execute.	
		$contents = curl_exec($curl);
		if(curl_errno($curl)){
			echo "Something went wrong in your Connection. Please Refresh the page to continue";
			exit(); 
		} else{
			return $contents;	
		}
	}


	public function curl_url_post($url,$post,$curl) {
		$Url = $url;
		$test  = $post;

		$variables ="";
		foreach ($test as $key => $tests){
			$variables .= $key."=".$tests."&";
		}
		curl_setopt($curl, CURLOPT_URL,$Url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS,rtrim($variables,"&")); 
		curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		return curl_exec($curl);
	}

	public function scrape_id(){
		$curl  = curl_init();
		$this->init_sync($curl);
		$contents = $this->curl_url('http://www.cma.com.tw/jobstatus/student/add.php',$curl);
		$file_content = str_get_html($contents);
		//echo $file_content."<br /> <br />";
		foreach($file_content->find('#stuid2') as $element){
			$id =  $element->value;
		}
		foreach($file_content->find('#bycheck11') as $element){
			$olp =  $element->value;
		}
		return array('stud_id' => $id, 'olp' => $olp);
	}

	
	public function init_sync_pay_data($datas){
		foreach ($datas as $data) {
		    $student_info = $this->student_data_raw($data['CustNo']);	
		    $student_id = $this->check_new_student($student_info,$data); //check if real student or new student
		    if($student_id){
		    	$insert_log = array(
		    		"branch_id"    => "PH002",
 		    		"date_input" => date("Y-m-d"),
		    		"or_number"  => $data['ORNumber'], 
		    		"success_func" => "check new student",
		    	 );
		    	$this->custom_insert("sync_log", $insert_log);
		    	$check = $this->insert_sync_payment($student_info,$data,$student_id);
		    	if($check){
					$insert_log2 = array(
						"branch_id"       => "PH002",
						"date_input"   => date("Y-m-d"),
						"or_number"    => $data['ORNumber'], 
						"success_func" => "sync payment to internal"
					);
					$this->custom_insert("sync_log", $insert_log2);
		    	}

		    }	
		}
		return $check;
	}

	public function check_new_student($student_info,$data){
		if($student_info['StudentID'] == "" || $student_info['StudentID'] == NULL){
			$id = $this->scrape_id();
			$insert_student = array(
		      'act'    => 'insert', 
		      'query_string' => '',
		      'id'     =>  '',
		      'stuid1' => "PH002",
		      'stuid2' => $id['stud_id'],
		      'name'   => $student_info['FirstName']." ".$student_info['MiddleName']." ".$student_info['SurName'],
		      'ename'  => $student_info['FirstName']." ".$student_info['MiddleName']." ".$student_info['SurName'],
		      'sex'    => $student_info['Gender'] == "Male" ? 1 : 2,
		      'joinday' => date('Y-m-d'),
		      'birthday'=> $student_info['Birthday'], 
		      'stucard' =>  'A', //Tier
		      'classnum' => 8, //Lesson
		      'stucharge'=>  2600, //FEE
		      'stubook'  => 1000, //Book Fee
		      'stuother' => '', //Other 
		      'sturemark'=> '', //Remark
		      'weeknum'  => 1, //timesperweek
		      'asroom'   => '', //COoperation
		      'tel'    	 => $student_info['Phone'], // Telophone
		      'mobile'   => $student_info['Mobile'],
		      'email'    => $student_info['Email'],
		      'postid'	 => $student_info['Zip'],
		      'address'  => $student_info['Address'],
		      'eid'  	 => "978", //teacher id
		      'classtype'=>  $this->check_student_cat($student_info['LevelID']),
		      'bystop'   => 'no', //if stopped no yes
		      'stopday'  => '',//stopped date
		      'status'   => '1', //Current 1 or Graduate 2
		      'content'  => '', //Description
		      'stop_reason' => '', // stop reason
		      'er'          => '', //Benefactor
		      'card_no'     => '', // Tier Receipt No
		      'file1'       => '', 
		      'bycheck1'    => $id['olp'], // Online Practice
		    ); 
			$this->insert_sync(1,$insert_student);
			$new_id = $insert_student['stuid1'].$insert_student['stuid2'];
			$this->custom_update("tcustomer",array('StudentID' => $new_id),"CustNo",$student_info['CustNo']);
			$id = $new_id;
		} else {
			$insert_student ="no";	
			$id = $student_info['StudentID'];
		}
		return $id;
	}

	public function insert_sync_payment($student_info,$data,$student_id){
		$internal_id = $this->get_internal_id($student_id);
		$tier = $this->tier_raw($data["TierID"]);
		$end_date = date('Y-m-d',strtotime($data['PayDate']. "+30 days"));	
		$to_insert  = array(
		  'act'      => 'insert', 
		  'query_string' => '',
		  'sid1'     => $internal_id, // make a function 
		  'stuid1'  =>  $student_id,//check
		  'stucard1' => $tier,//check
		  'cname1'   => $student_info['FirstName']." ".$student_info['MiddleName']." ".$student_info['SurName'], // check
		  'ename1'   => $student_info['FirstName']." ".$student_info['MiddleName']." ".$student_info['SurName'],//check
		  'beginday' => $data['PayDate'],
		  'endday'   => $end_date,
		  'num'      => 10, //$num, 
		  'nextpayday'=> '',
		  'next2payday'=> '',
		  'week'     => '',
		  'time'     => '',
		  'days'     => '',
		  'stucharge'=> $data['lessonfee'], // fee check  
		  'teacharge'=> $data['bookfee'], // bookfee check 
		  'other'    => $data['otherfee'], //other  check 
		  'total'    => $data['OrderTotal'],
		  'teaid1'   => "PH002001",
		  'tid1'    =>  "978",
		  'teacname1'=> "Anthony Esguerra", // add setup  for this.
		  'info'     => "", // remark
		  'payday'   =>  $data['PayDate'],
		  'receiptnum'=> $data['ORNumber'],
		);
		$this->insert_sync(2,$to_insert);
		
		return $to_insert;
	}

	public function insert_sync($case,$data){
		$url = $this->sync_url($case);
		$variables = $this->sync_variable($data);
		if($url){
			$curl  = curl_init();
			$this->init_sync($curl);
			curl_setopt($curl, CURLOPT_URL,$url);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS,rtrim($variables,"&")); 
			curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_exec($curl);
			if(curl_errno($curl))
			{
				echo 'error:' . curl_error($curl);
				exit();
			}	
		}
	}
	
	public function get_internal_id($student_id){
		$curl  = curl_init();
		$this->init_sync($curl);
		$contents = $this->curl_url('www.cma.com.tw/jobstatus/student/list.php?action_type=search&status=all&classtype=all&searchtype=stuid&keyword='.$student_id.'&Submit=Search',$curl);
		$file_content = str_get_html($contents);
		foreach($file_content->find('table#g_table_list') as $element){
				$x = 0;
				foreach($element->find('tr') as $elem){
					if($x >= 1){
						$url = $elem->children(1)->children(0)->children(0)->href;
						$length = strpos($url,'&query') - strrpos($url,'sid=')-4;
						$id = substr($url,strrpos($url,'sid=')+4,$length);
						if($id != false ){
							$student_id=  $id;
						}
					}
					$x = 1;	
				}
		}
		curl_close($curl);
		return $student_id;
	}

	// helper function begin		
	function sync_url($case){
		switch($case) {
		    case 1:
		        $url ="http://www.cma.com.tw/jobstatus/student/act.php"; // insert new student to internal
		     	break;
		    case 2:
		     	$url ="http://www.cma.com.tw/jobstatus/charge/act.php"; // insert  payment to internal
		     	break;
		    case 3: 
		    	$url="http://www.cma.com.tw/jobstatus/studentid/act.php"; // update online practice
		}
		return $url;
	}

	function sync_variable($data){
		$items  = $data;
		$variables ="";
		foreach ($items as $key => $item){
			$variables .= $key."=".$item."&";
		}
		return $variables;	
	}

	function check_student_cat($level_id){
	  $level = $this->level_cat($level_id);
	  if($level['category'] == 0){
	    $class = 1;
	  } elseif($level['category'] == 1){
	    $class = 3;
	  }
	  return $class;
	}

	/****  For Online Practice  ****/


	public function init_sync_online_practice($datas){
		foreach($datas as $data){
			$student_info = $this->student_data_raw($data['CustNo']);
			$this->online_practice($student_info);
		}
	}

	public function get_olid($id){
		$curl  = curl_init();
		$this->init_sync($curl);
		$contents = $this->curl_url('www.cma.com.tw/jobstatus/studentid/list.php?action_type=search&ison=all&searchtype=stuid&keyword='.$id.'&Submit=Search',$curl);
		$file_content = str_get_html($contents);
		$dummy = false;
		foreach($file_content->find('table#g_table_list') as $element){
				$listme = "<h3>Import Student Log</h3><ul>"; 
				foreach($element->find('tr') as $elem){
					if($dummy == true){
						$linkid = $elem->children(11)->children(1)->href;
						$name = trim(strip_tags($elem->children(3)));
						$ename = trim(strip_tags($elem->children(4)));
					
					}else{
							$dummy = true;
					}
				}
		}

	 	$test = explode('&', $linkid);
	 	$olid = str_replace('edit.php?id=','',$test[0]);
	 	curl_close($curl);
	 	return array('name' => $name, 'ename' => $ename, 'olid' => $olid);
	}

	public function online_practice($student){
		$custno = $student['CustNo'];
		$count  = $this->custom_count_where('eonline',array('CustNo' => $custno)); // check if student already exist in online practice
		$customer = $student;
		if($count == 0){
			$check_exist = 0;
			do{
				$online = $this->get_olid($customer['StudentID']);
				$password_online = substr($customer['Mobile'], -4);
			    $password_online .= substr($customer['FirstName'],0,1); 
			    $password_online .= substr($customer['SurName'],0,1);
			    $password_online .= $this->generateRandomString();
			    $check_exist= $this->custom_count_where('eonline',array('Password' => $password_online, 'BranchID' => "PH002"));
			} while($check_exist > 0);
			$sex   =  $customer['Gender'] == "Male" ?  1 :  2 ;
			$array = array(
			  'act'      => 'update', 
			  'id'       => $online['olid'], 
			  'query_string' => '', 
			  'logid'    =>   'PH002',
			  'logpass'  => $password_online,  
			  'stuid1'   =>  'PH002',
			  'stuid2'   =>  str_replace("PH002", "", $customer['StudentID']),
			  'name'     =>  $online['name'],
			  'ename'    =>   $online['ename'],
			  'sex'		 => $sex,
			  'birthday' => $customer['Birthday'],
			  'mobile'   => $customer['Mobile'],
			  'email'    => $customer['Email'],
			  'ison'     => 1,
			  'status'   => 1,   
			 );

			$this->insert_sync(3,$array);
			$this->custom_insert("eonline",array('CountryID' => "PH",'BranchID' => "PH002", 'Password' => $password_online, 'CustNo' => $custno));
	  	
			/*****	

		    if($customer->fields['Email'] != "" || $customer->fields['Email'] != NULL ){
		      $to = $customer->fields['Email'];
		      $subject = "CMA Online Practice New Password";
		      $txt = "Hello ". $online['name'] .". Your  new password in your Online Practice is ".$password_online.". Visit <a href='http://cma.ph/#keywords'>CMA Website to login to your Online Practice</a> ";
		      $headers = "From: webmaster@cma.ph" . "\r\n";
		      mail($to,$subject,$txt,$headers);
		    }****/
		    return $array;
		} else {
			return array('ol_status'=> "already have");
		}	
	}


	function generateRandomString($length = 2){
	    $characters = 'abcdefghijklmnopqrstuvwxyz';
	    $randomString ='';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	    }
	    return $randomString;
	}

	/****END ONLINE PRACTICE****/
}