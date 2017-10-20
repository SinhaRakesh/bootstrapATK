<?php

class Model_Transaction extends Model_Base_Table{
	public $table = "transaction";

	function init(){
		parent::init();

		// $this->addField('name');
		$this->hasOne('TransactionMaster','transaction_master_id');
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
		
		$this->addField('buy_amount')->type('money');
		$this->addField('sell_amount')->type('money');

		$this->addField('created_at')->type('datetime')->set($this->app->now);
		$this->addField('import_date')->type('datetime'); // import date in software system
		
		$this->addExpression('master_type')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->ref('transaction_master_id')->fieldQuery('name')]);
		});

		// $this->addExpression('buy_amount')->set('IFNULL(buy_value,0) * IFNULL(buy_qty,0)');
		// $this->addExpression('sell_amount')->set('IFNULL(sell_value,0) * IFNULL(sell_qty,0)');

		$this->addHook('beforeSave',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		$this['buy_amount'] = $this['buy_qty'] * $this['buy_value'];
		$this['sell_amount'] = $this['sell_qty'] * $this['sell_value'];
	}
}