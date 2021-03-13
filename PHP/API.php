<?php 

        function get_fastag_details(){
            $client_id = XXXXX;
			$key = XXXXX;
			$content_type = XXXXX;
			
			if($start_date=='') $start_date = date('Y-m-d',strtotime('-1 days'));
			$end_date = $start_date;
			
			$url = "https://fastag-issuer-reporting.paytmbank.com/ext/reporting/transaction/corporateSearch";

			// SET INPUT DATA ARRAY
			$data = array(
				"withdraw_at_from_date" => $start_date,
				"withdraw_at_to_date" 	=> $end_date,
				"offset"				=> 0,
				"limit"					=> 1
			);
			$data = json_encode($data);	

			// INPUT
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	        $url = sprintf("%s?%s", $url, http_build_query($data));

	        
	        $current=date("Y-m-d H:i:s");
	    	$current30=date("Y-m-d H:i:s",strtotime($current." + 30 minute"));
			
			$token_data['iss']=$client_id ;
			$token_data['exp']=strtotime($current30);

			$jwt_token=$this->objJwt->GenerateToken($token_data,$key);


	        // SET HEADER
	        $header_api_id = "client_id:".$client_id;
	        $header_api_key = "jwt-encoded-token:".$jwt_token;
	        $hader_content_type = "content-type:application/json";
	        $header = array($header_api_id,$header_api_key,$hader_content_type);

	        //echo $client_id.'<br>'.$jwt_token.'<br>'.$content_type.'<br>'.$url;
	        // OPTIONS:
		    curl_setopt($curl, CURLOPT_URL, $url);
		    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

		    // EXECUTE:
		    $result = curl_exec($curl);
		    if(!$result){die("Connection Failure");}
		    curl_close($curl);
		    $result = json_decode($result);

		    //echo '<pre>';	print_r($result); die; 


		    //OUTPUT
		    if($result->count)
		    {
			    $per_page=100;
			    $total_page = ceil($result->count/$per_page);
			    
			    for($a = 0; $a<$total_page; $a++)
			    {
			    	if($a){
			    		$offset=($a*$per_page); //+1;
			    	}
			    	else {
			    		$offset=$a*$per_page;
			    	}
			    	if($a==($total_page-1)){
			    		$limit=$result->count%$per_page;
			    	}
			    	else{
			    		$limit=$per_page;
			    	}

			    	$data = array(
						"withdraw_at_from_date" => $start_date,
						"withdraw_at_to_date" 	=> $end_date,
						"offset"				=> $offset,
						"limit"					=> $limit
					);
					$this->get_page_entry($url,$data,$header,$customer_details);
			    }
			}
		}
	public function get_page_entry($url,$input_data=array(),$header_data=array(),$customer_details=array())
	{
		$url = $url;

		// SET INPUT DATA ARRAY
		$data = $input_data;
		$data = json_encode($data);

		// INPUT
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $url = sprintf("%s?%s", $url, http_build_query($data));

        // SET HEADER
        $header = $header_data;


	    // OPTIONS:
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

	    // EXECUTE:
	    $result = curl_exec($curl);
	    if(!$result){die("Connection Failure");}
	    curl_close($curl);
	    $result = json_decode($result);

	    //echo '<pre>'; print_r($result); die; 

	    // OUTPUT
	    $j = 1;
	    for($i = 0; $i<count($result->transactions); $i++)
	    {
	    	//GET FROM API TOLL TRANSACTION DETAILS
	    	$toll_data = array();
	    	$toll_data['transaction_type'] = ($result->transactions[$i]->transaction_type)?$result->transactions[$i]->transaction_type:'';
	    	$toll_data['transaction_amount'] = ($result->transactions[$i]->transaction_amount)?$result->transactions[$i]->transaction_amount:'';
	    	$toll_data['TransactionReferenceNumber'] = ($result->transactions[$i]->tsn)?$result->transactions[$i]->tsn:'';
	    	$toll_data['VehicleNumber'] = ($result->transactions[$i]->vrn)?$result->transactions[$i]->vrn:'';
	    	$toll_data['vin'] = ($result->transactions[$i]->vin)?$result->transactions[$i]->vin:'';
	    	$toll_data['TransactionId'] = ($result->transactions[$i]->wallet_transaction_id)?$result->transactions[$i]->wallet_transaction_id:'';
	    	$toll_data['tag_id'] = ($result->transactions[$i]->tag_id)?$result->transactions[$i]->tag_id:'';
	    	$toll_data['tag_vehicle_class'] = ($result->transactions[$i]->tag_vehicle_class)?$result->transactions[$i]->tag_vehicle_class:'';
	    	$toll_data['vehicle_description'] = ($result->transactions[$i]->vehicle_description)?$result->transactions[$i]->vehicle_description:'';
	    	//$toll_data['tag_state_last_update_time'] = $result->transactions[$i]->tag_state_last_update_time;
	    	$toll_data['PlazaCode'] = ($result->transactions[$i]->plaza_id)?$result->transactions[$i]->plaza_id:'';
	    	$toll_data['PlazaName'] = ($result->transactions[$i]->plaza_name)?$result->transactions[$i]->plaza_name:'';
	    	$toll_data['lat_long'] = ($result->transactions[$i]->plaza_geo_code)?$result->transactions[$i]->plaza_geo_code:'';

	    	if($result->transactions[$i]->plaza_geo_code){
		    	$arr=explode(',', $result->transactions[$i]->plaza_geo_code);
		    	$toll_data['latitude'] = $arr[0];
		    	$toll_data['longitude'] = $arr[1];
		    }

	    	$toll_data['LaneCode'] = ($result->transactions[$i]->lane_id)?$result->transactions[$i]->lane_id:'';
	    	$toll_data['lane_direction'] = ($result->transactions[$i]->lane_direction)?$result->transactions[$i]->lane_direction:'';
	    	//$toll_data['reader_time'] = $result->transactions[$i]->reader_time;
	    	$toll_data['transaction_receive_time'] = ($result->transactions[$i]->transaction_receive_time)?$result->transactions[$i]->transaction_receive_time:'';
	    	$toll_data['transaction_date_read'] = ($result->transactions[$i]->reader_time)?$result->transactions[$i]->reader_time:'';
	    	$toll_data['create_date_settle'] = ($result->transactions[$i]->payment_settlement_time)?$result->transactions[$i]->payment_settlement_time:'';
	    }
	}
?>
