<?php

class Model_Client extends Model_Base_Table{
	public $table = "client";

	function init(){
		parent::init();

		$this->hasOne('State','state_id');
		$this->hasOne('City','city_id');
		
		$this->addField('name');
		$this->addField('client_code')->caption('Client ID');
		$this->addField('phone_number')->type('number');
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

			$insert_query = "INSERT into transaction (client_id,company_id,sheet_user_id,account_id,exchg_seg,instrument_name,buy_value,sell_value,net_value,buy_avg_price,sell_avg_price,bep,mark_to_market,trading_symbol,client_status,indicator,sell_qty,buy_qty,net_qty,created_at,import_date) VALUES ";
			foreach ($record as $data) {
				$account_id = trim($data['Account Id']);

				if(!isset($company_list[$data['Symbol']])){
					$company_not_found[$data['Symbol']] = $data;
					continue;
				}

				if(!isset($client_list[$account_id])){
					$client_not_found[$account_id] = $data;
					continue;
				}

				$client_id = $client_list[$account_id]['id'];
				$client_name = $client_list[$account_id]['name'];
				$company_id = $company_list[$data['Symbol']]['id'];

				$buy_avg_price = 0;
				if(is_numeric($data['BuyAvgPrice']))
					$buy_avg_price = $data['BuyAvgPrice'];

				$sell_avg_price = 0;
				if(is_numeric($data['SellAvgPrice']))
					$sell_avg_price = $data['SellAvgPrice'];

				$net_value = $data['SellValue'] - $data['BuyValue'];
				$net_qty = (is_numeric($data['BuyQty'])?:0) - (is_numeric($data['SellQty'])?:0);

				$insert_query .= "('".$client_id."','".$company_id."','".$data['UserId']."','".$data['Account Id']."','".$data['Exchg.Seg']."','".$data['Instrument Name']."','".$data['BuyValue']."','".$data['SellValue']."','".$net_value."','".$buy_avg_price."','".$sell_avg_price."','".$data['BEP']."','".$data['MarkToMarket']."','".$data['Trading Symbol']."','".$data['Client Status']."','".$data['Indicator']."','".$data['SellQty']."','".$data['BuyQty']."','".$net_qty."','".$this->app->now."','".$import_date."'),";
				
				$total_record_inserted++;
			}

			if($total_record_inserted){
				$insert_query = trim($insert_query,',');
				$this->app->db->dsql()->expr($insert_query)->execute();
			}

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

				$insert_query .= "('".$client_code."','".$data['Client Name']."','".$data['Email Address']."','".$data['Phone number']."','".$data['Address1']."','".$data['Address2']."','".$city_id."','".$state_id."','".$data['pin_code']."',1,'".$this->app->now."'),";
				
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
			$company_list[$m['isin_code']] = $m;
		}

		$client = $this->add('Model_Client')->getRows();
		$client_list = [];
		foreach ($client as $m) {
			$client_list[$m['client_code']] = $m;
		}

		$client_not_found = [];
		$company_not_found = [];
		$total_record_inserted = 0;

		$fields = ['transaction_master_id','client_id','company_id','created_at','sell_qty','sell_value','net_value','net_qty','import_date'];
		if($type == "Buy"){
			$fields = ['transaction_master_id','client_id','company_id','created_at','buy_qty','buy_value','net_value','net_qty','import_date'];
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

				if(!isset($company_list[$data['SYMBOL']])){
					// echo "company not found ".$data['SYMBOL']."<br/>";
					$company_not_found[$data['SYMBOL']] = $data;
					continue;
				}

				if(!isset($client_list[$client_code])){
					// echo "client not found ".$client_code."<br/>";
					$client_not_found[$client_code] = $data;
					continue;
				}

				$client_id = $client_list[$client_code]['id'];
				$client_name = $client_list[$client_code]['name'];
				$company_id = $company_list[$data['SYMBOL']]['id'];

				if($type == "Buy"){
					$net_qty = $qty = $data['QTY BUY'];
					$net_value = $price = $data['BUY PRICE'];
					$created_at = date('Y-m-d',strtotime(str_replace("/","-",$data['DATE OF PURCHASE'])));
				}else{
					$qty = $data['QTY SOLD'];
					$price = $data['SELLING PRICE'];
					$net_qty = $qty *( -1);
					$net_value = $price *( -1);

					$created_at = date('Y-m-d',strtotime(str_replace("/","-",$data['DATE OF SELLING'])));
				}

				$insert_query .= "('".$tm->id."','".$client_id."','".$company_id."','".$created_at."','".$qty."','".$price."','".$net_value."','".$net_qty."','".$import_date."'),";
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
			
		}
		
		return [
				'total_data_to_import' => count($record),
				'total_data_imported' => $total_record_inserted,
				'total_client_not_found' => count($client_not_found),
				'total_company_not_found' => count($company_not_found)
			];
	}

}