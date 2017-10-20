<?php

class Model_TransactionMaster extends Model_Base_Table{
	public $table = "transaction_master";

	function init(){
		parent::init();

		$this->addField('name')->setValueList(
						[
							'client_buy_data'=>"Client Buy Data",
							'client_sell_data'=>"Client Sell Data",
							'client_transaction'=>"Client Transaction",
							'daily_bhav'=>"Daily Bhav"
						]
					);
		$this->addExpression('Record')->set(function($m,$q){
			return  $q->expr('[0]',[$m->refSQL('Transaction')->count()]);
		});

		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);

		$this->hasMany('Transaction','transaction_master_id');
		$this->addHook('beforeDelete',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		$this->add('Model_Transaction')
			->addCondition('transaction_master_id',$this->id)
			->deleteAll()
			;
	}

}