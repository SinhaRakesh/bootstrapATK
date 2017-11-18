<?php

class Model_Transaction extends Model_Base_Table{
	public $table = "transaction";

	public $from_date;
	public $to_date;
	function init(){
		parent::init();

		if(!$this->from_date) $this->from_date = $this->app->today;
		if(!$this->to_date) $this->to_date = $this->app->today;

		// $this->addField('name');
		$this->hasOne('TransactionMaster','transaction_master_id');
		$this->hasOne('Client','client_id');
		$this->hasOne('Company','company_id')->caption('Stock');

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
		
		$this->addField('fifo_sell_qty')->type('Number')->defaultValue(0);
		$this->addField('fifo_sell_price')->type('Number')->defaultValue(0);
		$this->addField('fifo_sell_date')->type('datetime');

		$this->addExpression('master_type')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('transaction_master_id')->fieldQuery('name')]);
		});

		$this->addExpression('date_only')->set('Date_Format(created_at,"%d %M %Y")')->caption('Date');

		// fifo sell Qty as on date
		$this->addExpression('fifo_remaining_qty')->set(function($m,$q){
			$s = $m->add('Model_FifoSell');
			$s->addCondition('transaction_id',$m->getElement('id'));
			$s->addCondition('sell_date','<',$this->app->nextDate($this->from_date));

			return $q->expr('(IFNULL([0],0)-IFNULL([1],0))',[$m->getElement('buy_qty'),$s->sum('sell_qty')]);
		})->type('Number');

		$this->addExpression('company_latest_closing_value')->set(function($m,$q){
			$db = $m->add('Model_DailyBhav',['table_alias'=>'dbv']);
			$db->addCondition('company_id',$m->getElement('company_id'))
				->addCondition('created_at','<=',$this->to_date)
				->setOrder('created_at','desc')
				->setLimit(1)
				;
			return $q->expr('IFNULL([0],0)',[$db->fieldQuery('last')]);
		})->type('money');

		$this->addExpression('fifo_sell_amount')->set(function($m,$q){
			$t = $m->add('Model_FifoSell')
				->addCondition('transaction_id',$m->getElement('id'))
				;
			return $q->expr('IFNULL([0],0)',[$t->sum('fifo_sell_amount')]);
		})->type('money');

		$this->addExpression('no_profit_buy_amount')->set(function($m,$q){

		})->type('money');

		// current stock holding by amount
		$this->addExpression('fifo_buy_amount')->set(function($m,$q){
			return $q->expr('IFNULL([0],0) * IFNULL([1],0)',[$m->getElement('fifo_remaining_qty'),$m->getElement('buy_value')]);
		})->type('money');

		$this->addExpression('current_buy_amount')->set(function($m,$q){
			return $q->expr('IFNULL([0],0) * IFNULL([1],0)',[$m->getElement('fifo_remaining_qty'),$m->getElement('company_latest_closing_value')]);
		})->type('money');

		// $this->addExpression('buy_amount_on_date')->set(function($m,$q){
		// 	return $q->expr('IFNULL([0],0) * IFNULL([1],0)',[$m->getElement('fifo_remaining_qty'),$m->getElement('buy_value')]);
		// });

		// $this->addExpression('current_buy_amount_on_date')->set(function($m,$q){
		// 	return $q->expr('(IFNULL([0],0) * IFNULL([1],0))',[$m->getElement('net_qty'),$m->getElement('company_latest_closing_value')]);
		// });


		$this->addExpression('current_pl')->set(function($m,$q){
			$tra = $m->add('Model_FifoSell',['table_alias'=>'ltcgwdsdsds']);
			$tra->addCondition('transaction_id',$m->getElement('id'));

			return $q->expr('IFNULL([0],0)',[$tra->sum('pl')]);
		})->type('money');

		// $this->addExpression('current_pl_on_date')->set(function($m,$q){
		// 	return $q->expr('(IFNULL([0],0)-IFNULL([1],0))',[$m->getElement('current_buy_amount_on_date'),$m->getElement('buy_amount_on_date')]);
		// })->type('money');


		// $this->addExpression('fifo_remaining_qty')->set('IFNULL(buy_qty,0) - IFNULL(fifo_sell_qty,0)')->type('Number');
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