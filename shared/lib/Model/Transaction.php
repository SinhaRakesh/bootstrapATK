<?php

class Model_Transaction extends Model_Base_Table{
	public $table = "transaction";

	function init(){
		parent::init();

		// $this->addField('name');
		$this->hasOne('Client','client_id');
		$this->hasOne('Company','company_id');

		$this->addField('sheet_user_id'); // just saviing values
		$this->addField('account_id'); // just saviing values

		$this->addField('exchg_seg');
		$this->addField('instrument_name');
		$this->addField('buy_value')->type('money');
		$this->addField('sell_value')->type('money');
		$this->addField('net_value')->type('money');
		$this->addField('buy_avg_price')->type('money');
		$this->addField('sell_avg_price')->type('money');
		$this->addField('bep');
		$this->addField('mark_to_market');
		$this->addField('trading_symbol');
		$this->addField('client_status');
		$this->addField('indicator');
		$this->addField('sell_qty')->type('Number');
		$this->addField('buy_qty')->type('Number');
		$this->addField('net_qty')->type('Number');
		
		$this->addField('created_at')->type('datetime')->set($this->app->now);
		$this->addField('import_date')->type('datetime'); // import date in software system
				
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}