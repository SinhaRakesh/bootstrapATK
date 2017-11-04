<?php

class Model_Client extends Model_Base_Table{
	public $table = "client";

	function init(){
		parent::init();

		$this->hasOne('State','state_id');
		$this->hasOne('City','city_id');
		
		$this->addField('name');
		$this->addField('client_code')->caption('Client ID');
		$this->addField('phone_number');
		$this->addField('email_id');
		$this->addField('address1');
		$this->addField('address2');
		$this->addField('pin_code');

		$this->addField('is_active')->type('boolean')->defaultValue(true);
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now)->system(true);

		$this->hasMany('Transaction','client_id');

		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		$transaction = $this->add('Model_Transaction');
		$transaction->addCondition('client_id',$this->id);
		if($transaction->count()->getOne()){
			throw new \Exception("Cannot Delete, its has transaction");
		}
	}

	function beforeSave(){
		if(!$this['client_code'])
			throw $this->exception('Client ID Must not be empty', 'ValidityCheck')->setField('client_code');
		
		$old = $this->add('Model_Client');
		$old->addCondition('client_code',$this['client_code']);
		$old->addCondition('id','<>',$this->id);
		$old->tryLoadAny();
		if($old->loaded())
			throw $this->exception('Client ID is already in use', 'ValidityCheck')->setField('client_code');
	}

	function nextID(){
		return $this->add('Model_Client')->count()->getOne() + 1;
	}


	function updateTransaction($record=[],$import_date=null){

		if(!$import_date)
			$import_date = $this->app->now;

		$company = $this->add('Model_Company')->getRows();
		$company_list = [];
		foreach ($company as $m) {
			$company_list[$m['isin_code']] = $m;
			$company_list[$m['sc_name']] = $m;
		}

		$client = $this->add('Model_Client')->getRows();
		$client_list = [];
		foreach ($client as $m) {
			$client_list[$m['client_code']] = $m;
		}

		$client_not_found = [];
		$company_not_found = [];
		$total_record_inserted = 0;

		try{
			$this->app->db->beginTransaction();


			// $insert_query = "INSERT into transaction (client_id,company_id,sheet_user_id,account_id,exchg_seg,instrument_name,buy_value,sell_value,net_value,buy_avg_price,sell_avg_price,bep,mark_to_market,trading_symbol,client_status,indicator,sell_qty,buy_qty,net_qty,created_at,import_date) VALUES ";
			foreach ($record as $data) {
				$account_id = trim($data['ACCOUNT ID']);
				$symbol = trim($data['SYMBOL']);

				if(!isset($company_list[$symbol])){
					$company_not_found[$symbol] = $data;
					continue;
				}

				if(!isset($client_list[$account_id])){
					$client_not_found[$account_id] = $data;
					continue;
				}

				$client_id = $client_list[$account_id]['id'];
				$client_name = $client_list[$account_id]['name'];
				$company_id = $company_list[$symbol]['id'];

				$buy_avg_price = 0;
				if(is_numeric($data['BUY AVG PRICE']))
					$buy_avg_price = $data['BUY AVG PRICE'];

				$sell_avg_price = 0;
				if(is_numeric($data['SELL AVG PRICE']))
					$sell_avg_price = $data['SELL AVG PRICE'];

				$net_value = $data['SELL VALUE'] - $data['BUY VALUE'];
				$net_qty = (is_numeric($data['BUY QTY'])?:0) - (is_numeric($data['SELL QTY'])?:0);

				$tra = $this->add('Model_Transaction');
				$tra['client_id'] = $client_id;
				$tra['company_id'] = $company_id;
				$tra['sheet_user_id'] = $data['USER ID'];
				$tra['account_id'] = $data['ACCOUNT ID'];
				$tra['exchg_seg'] = $data['EXCHG SEG'];
				$tra['instrument_name'] = $data['INSTRUMENT NAME'];
				$tra['buy_value'] = $data['BUY VALUE'];
				$tra['sell_value'] = $data['SELL VALUE'];
				$tra['net_value'] = $net_value;
				$tra['buy_avg_price'] = $buy_avg_price;
				$tra['sell_avg_price'] = $sell_avg_price;
				$tra['bep'] = $data['BEP'];
				$tra['mark_to_market'] = $data['MARK TO MARKET'];
				$tra['trading_symbol'] = $data['TRADING SYMBOL'];
				$tra['client_status'] = $data['CLIENT STATUS'];
				$tra['indicator'] = $data['INDICATOR'];
				$tra['sell_qty'] = $data['SELL QTY'];
				$tra['buy_qty'] = $data['BUY QTY'];
				$tra['net_qty'] = $net_qty;
				$tra['created_at'] = $this->app->now;
				$tra['import_date'] = $import_date;
				$tra->save();

				$this->updateFIFO($data['SELL QTY'],$data['SELL VALUE'],$client_id,$company_id,$date=null);
				// $insert_query .= "('".$client_id."','".$company_id."','".$data['USER ID']."','".$data['ACCOUNT ID']."','".$data['EXCHG SEG']."','".$data['INSTRUMENT NAME']."','".$data['BUY VALUE']."','".$data['SELL VALUE']."','".$net_value."','".$buy_avg_price."','".$sell_avg_price."','".$data['BEP']."','".$data['MARK TO MARKET']."','".$data['TRADING SYMBOL']."','".$data['CLIENT STATUS']."','".$data['INDICATOR']."','".$data['SELL QTY']."','".$data['BUY QTY']."','".$net_qty."','".$this->app->now."','".$import_date."'),";
				
				$total_record_inserted++;
			}

			// if($total_record_inserted){
			// 	$insert_query = trim($insert_query,',');
			// 	$this->app->db->dsql()->expr($insert_query)->execute();
			// }

			$this->app->db->commit();
		}catch(\Exception $e){
			$this->app->rollback();
			throw new \Exception($e->getMessage());
		}
		
		return [
				'total_data_to_import' => count($record),
				'total_data_imported' => $total_record_inserted,
				'total_client_not_found' => count($client_not_found),
				'total_company_not_found' => count($company_not_found)
			];
	}


	function importClient($record){

		$client = $this->add('Model_Client')->getRows();
		$client_list = [];
		foreach ($client as $m) {
			$client_list[$m['client_code']] = $m;
		}

		$state = $this->add('Model_State')->getRows();
		$state_list = [];
		foreach ($state as $m) {
			$state_list[strtolower(trim($m['name']))] = $m['id'];
		}

		$city = $this->add('Model_City')->getRows();
		$city_list = [];
		foreach ($city as $m) {
			$city_list[strtolower(trim($m['name']))] = $m['id'];
		}

		$old_client = [];

		$total_record_inserted = 0;

		try{
			$this->app->db->beginTransaction();

			$insert_query = "INSERT into client (client_code,name,email_id,phone_number,address1,address2,city_id,state_id,pin_code,is_active,created_at) VALUES ";
			foreach ($record as $data) {

				$client_code = trim($data['Client ID']);
				if(isset($client_list[$client_code])){
					if(!isset($client_list[$client_code]['is_new'])){
						$old_client[$client_code] = $data;
					}
					continue;
				}

				$city_id = 0;
				$state_id = 0;
				if(isset($city_list[strtolower(trim($data['City']))]))
					$city_id = $city_list[strtolower(trim($data['City']))];

				if(isset($state_list[strtolower(trim($data['State']))]))
					$state_id = $state_list[strtolower(trim($data['State']))];

				$insert_query .= "('".$client_code."','".$data['Client Name']."','".$data['Email Address']."','".$data['Phone Number']."','".$data['Address1']."','".$data['Address2']."','".$city_id."','".$state_id."','".$data['Pincode']."',1,'".$this->app->now."'),";
				
				$total_record_inserted++;
				$client_list[$data['Client Code']] = [
													'name'=>$data['Name'],
													'email'=>$data['Email Address'],
													'contact'=>$data['Phone number'],
													'is_new'=>1
												];
			}

			if($total_record_inserted){
				$insert_query = trim($insert_query,',');
				$this->app->db->dsql()->expr($insert_query)->execute();
			}
			$this->app->db->commit();
		}catch(\Exception $e){
			$this->app->db->rollback();
			throw new \Exception($e->getMessage());
		}

		return [
			'total_data_to_import' => count($record),
			'total_data_imported' => $total_record_inserted,
			'total_old_client' => count($old_client)
		];
	}


	function updateClientWiseData($record,$type){
		$import_date = $this->app->now;

		$company = $this->add('Model_Company')->getRows();
		$company_list = [];
		foreach ($company as $m) {
			$company_list[trim($m['isin_code'])] = $m;
			$company_list[trim($m['sc_name'])] = $m;
		}

		$client = $this->add('Model_Client')->getRows();
		$client_list = [];
		foreach ($client as $m) {
			$client_list[$m['client_code']] = $m;
		}

		$client_not_found = [];
		$company_not_found = [];
		$total_record_inserted = 0;

		// default value for sell
		$fields = ['transaction_master_id','client_id','company_id','created_at','sell_qty','sell_value','net_value','net_qty','sell_amount','import_date','fifo_sell_qty','fifo_sell_price','fifo_sell_date'];
		if($type == "Buy"){
			$fields = ['transaction_master_id','client_id','company_id','created_at','buy_qty','buy_value','net_value','net_qty','buy_amount','import_date','fifo_sell_qty','fifo_sell_price','fifo_sell_date'];
		}

		try{
			$this->app->db->beginTransaction();

			$tm = $this->add('Model_TransactionMaster');
			if($type == "Buy")
				$tm['name'] = 'client_buy_data';
			else
				$tm['name'] = 'client_sell_data';
			$tm->save();

			$insert_query = "INSERT into transaction (".trim(implode(",", $fields),',').") VALUES ";
			foreach ($record as $data) {

				$client_code = trim($data['CLIENT ID']);
				$symbol = trim($data['SYMBOL']);

				if(!isset($company_list[$symbol])){
					$company_not_found[$symbol] = $data;
					continue;
				}

				if(!isset($client_list[$client_code])){
					// echo "client not found ".$client_code."<br/>";
					$client_not_found[$client_code] = $data;
					continue;
				}

				$client_id = $client_list[$client_code]['id'];
				$client_name = $client_list[$client_code]['name'];
				$company_id = $company_list[$symbol]['id'];

				$fifo_sell_date = null;
				if($type == "Buy"){
					$net_qty = $qty = trim($data['QTY BUY']);
					$net_value = $price = trim($data['BUY PRICE']);
					$created_at = date('Y-m-d',strtotime(str_replace("/","-",$data['DATE OF PURCHASE'])));
					$fifo_sell_qty = 0;
					$fifo_sell_price = 0;
				}else{
					$qty = $data['QTY SOLD'];
					$price = $data['SELLING PRICE'];
					$net_qty = $qty *( -1);
					$net_value = $price *( -1);
					$created_at = date('Y-m-d',strtotime(str_replace("/","-",$data['DATE OF SELLING'])));
					
					// update fifo value
					$fifo_sell_qty = $this->updateFIFO($qty,$price,$client_id,$company_id,$created_at);
					$fifo_sell_price = 0;
					if($fifo_sell_qty > 0){
						$fifo_sell_price = $price;
						$fifo_sell_date = $created_at;
					}
				}

				$insert_query .= "('".$tm->id."','".$client_id."','".$company_id."','".$created_at."','".$qty."','".$price."','".$net_value."','".$net_qty."','".($qty * $price)."','".$import_date."','".$fifo_sell_qty."','".$fifo_sell_price."','".$fifo_sell_date."'),";
				$total_record_inserted++;
			}

			if($total_record_inserted){
				$insert_query = trim($insert_query,',');
				$this->app->db->dsql()->expr($insert_query)->execute();
			}else{
				throw new \Exception("no one record inserted");
			}
			$this->app->db->commit();
		}catch(\Exception $e){
			$this->app->db->rollback();
			throw new \Exception($e->getMessage());
			
		}
		
		return [
				'total_data_to_import' => count($record),
				'total_data_imported' => $total_record_inserted,
				'total_client_not_found' => count($client_not_found),
				'total_company_not_found' => count($company_not_found)
			];
	}


	function updateFIFO($sell_qty,$sell_price,$client_id,$company_id,$date=null){

		if(!$date) $date = $this->app->now;

		$transaction = $this->add('Model_Transaction');
		$transaction->addCondition('client_id',$client_id);
		$transaction->addCondition('company_id',$company_id);
		$transaction->addCondition('fifo_remaining_qty','>',0);
		$transaction->setOrder('created_at','asc');

		// throw new \Exception("Error Processing Request", 1);
		foreach ($transaction as $tra) {
			if(!$sell_qty) continue;

			$tra_id = $tra->id;

			$fill_qty = $tra['fifo_remaining_qty'];
			if($fill_qty > $sell_qty){
				$fill_qty = $sell_qty;
				$sell_qty = 0;
			}else{
				$sell_qty = $sell_qty - $fill_qty;
			}
			$tra['fifo_sell_qty'] += $fill_qty;
			$tra['fifo_sell_price'] = $sell_price;
			$tra['fifo_sell_date'] = $date;
			$tra->saveAndUnload();

			$sell = $this->add('Model_FifoSell');
			$sell['client_id'] = $client_id;
			$sell['company_id'] = $company_id;
			$sell['transaction_id'] = $tra_id;
			$sell['sell_qty'] = $fill_qty;
			$sell['sell_price'] = $sell_price;
			$sell['sell_date'] = $date;
			$sell['buy_price'] = $transaction['buy_value'];
			$sell->save();

			// echo "buy_qty = ".$tra['buy_qty']." = "."<br/>";
		}
		return $sell_qty;

	}

}