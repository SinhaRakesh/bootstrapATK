<?php

class Model_Client extends Model_Base_Table{
	public $table = "client";

	function init(){
		parent::init();

		$this->hasOne('State','state_id');
		$this->hasOne('City','city_id');
		
		$this->addField('name');
		$this->addField('client_code');
		$this->addField('contact')->type('number');
		$this->addField('email');
		$this->addField('address');

		$this->addField('is_active')->type('boolean')->defaultValue(true);
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now)->system(true);

		$this->hasMany('Transaction','client_id');

		$this->add('dynamic_model/Controller_AutoCreator');

		// $this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		if(!$this['client_code'])
			$this['client_code'] = "UDR-".$this->nextID();
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
			$client_list['client_code'] = $m;
		}

		$client_not_found = [];
		$company_not_found = [];
		$total_record_inserted = 0;

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

		$old_client = [];

		$total_record_inserted = 0;
		$insert_query = "INSERT into client (client_code,name,email,contact,address,is_active,created_at) VALUES ";
		foreach ($record as $data) {
			$client_code = trim($data['Client Code']);

			if(isset($client_list[$client_code])){
				$old_client[$client_code] = $data;
				continue;
			}
			$insert_query .= "('".$client_code."','".$data['Name']."','".$data['Email']."','".$data['Contact']."','".$data['Address']."',1,'".$this->app->now."'),";
			
			$total_record_inserted++;
			$client_list[$data['Client Code']] = [
												'name'=>$data['Name'],
												'email'=>$data['Email'],
												'contact'=>$data['Contact']
											];
		}

		if($total_record_inserted){
			$insert_query = trim($insert_query,',');
			$this->app->db->dsql()->expr($insert_query)->execute();
		}

		return [
			'total_data_to_import' => count($data),
			'total_data_imported' => $total_record_inserted,
			'total_old_client' => count($old_client)
		];
	}
}