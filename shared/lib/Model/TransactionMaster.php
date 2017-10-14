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
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}