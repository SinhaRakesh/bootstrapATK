<?php

class Model_Company extends Model_Base_Table{
	public $table = "company";
	public $title_field = 'sc_name';

	function init(){
		parent::init();

		$this->addField('sc_name');
		$this->addField('isin_code')->caption('ISIN_CODE');
		$this->addField('sc_code');
		$this->addField('sc_group');
		$this->addField('sc_type');

		$this->addField('is_active')->type('boolean')->defaultValue(true)->sortable(true);
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now)->system(true);
		
		$this->hasMany('DailyBhav','company_id');

		$this->addExpression('closing_value')->set(function($m,$q){
			$db = $m->add('Model_DailyBhav');
			$db->addCondition('company_id',$m->getElement('id'))
				->setOrder('created_at','desc')
				->setLimit(1)
				;
			return $q->expr('[0]',[$db->fieldQuery('last')]);
		})->type('money');

		$this->addExpression('last_update')->set(function($m,$q){
			$db = $m->add('Model_DailyBhav');
			$db->addCondition('company_id',$m->getElement('id'))
				->setOrder('created_at','desc')
				->setLimit(1)
				;
			return $q->expr('[0]',[$db->fieldQuery('created_at')]);
		});

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function updateDailyBhav($record,$import_date=null){
		//get all company record
		// ['isin_number'=>['id'=>,'name'=>]]
		$company = $this->add('Model_Company')->getRows();
		$company_list = [];
		foreach ($company as $m) {
			$company_list[$m['sc_name']] = $m['id'];
		}

		if(!$import_date)
			$import_date = $this->app->now;

		try{

			$this->app->db->beginTransaction();
			$insert_query = "INSERT into daily_bhav (company_id,open,high,low,close,last,prevclose,trading_date,created_at,import_date) VALUES ";
			// record
			foreach ($record as $data) {
				// if isin number is not in company list
				$sc_name = trim($data['SC_NAME']);
				if(!isset($company_list[$sc_name])){
					$cmp = $this->add('Model_Company');
					$cmp->addCondition('sc_name',$sc_name);
					$cmp->tryLoadAny();
					// $cmp['sc_name'] = $data['SC_NAME'];
					$cmp['sc_code'] = trim($data['SC_CODE']);
					$cmp['sc_group'] = trim($data['SC_GROUP']);
					$cmp['sc_type'] = trim($data['SC_TYPE']);
					$cmp['isin_code'] = trim($data['ISIN_CODE']);
					$cmp['is_active'] = true;
					$cmp->save();

					$company_list[$sc_name] = $cmp->id;
				}

				$company_id = $company_list[$sc_name];

				$insert_query .= "('".$company_id."','".$data['OPEN']."','".$data['HIGH']."','".$data['LOW']."','".$data['CLOSE']."','".$data['LAST']."','".$data['PREVCLOSE']."','".date('Y-m-d', strtotime($data['TRADING_DATE']))."','".date('Y-m-d', strtotime($data['TRADING_DATE']))."','".$import_date."'),";
			}
			$insert_query = trim($insert_query,',');

			$this->app->db->dsql()->expr($insert_query)->execute();
			$this->api->db->commit();
		}catch(\Exception $e){
			$this->api->db->rollback();
			throw new \Exception($e->getMessage());
		}
	}

}