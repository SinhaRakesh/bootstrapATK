<?php

class Model_ClientData extends Model_Client{
	// public $table = "client";
	public $on_date = "";
	function init(){
		parent::init();

		if(!$this->on_date) $this->on_date = $this->app->today;

		$this->getElement('name')->caption('Client');

		$this->addExpression('today_buying_value')->set(function($m,$q){
			$t = $m->add('Model_Transaction')
				->addCondition('client_id',$m->fieldQuery('id'))
				->addCondition('created_at','>=',$this->on_date)
				->addCondition('created_at','<',$this->app->nextDate($this->on_date))
				;
			if($t->count()->getOne() > 1){
				$t->addCondition('master_type','client_buy_data');
			}
			return $q->expr('IFNULL([0],0)',[$t->fieldQuery('buy_value')]);
		});
		
		$this->addExpression('today_sell_value')->set(function($m,$q){

			$t = $m->add('Model_Transaction')
				->addCondition('client_id',$m->fieldQuery('id'))
				->addCondition('created_at','>=',$this->on_date)
				->addCondition('created_at','<',$this->app->nextDate($this->on_date))
				;
			if($t->count()->getOne() > 1){
				$t->addCondition('master_type','client_sell_data');
			}
			return $q->expr('IFNULL([0],0)',[$t->fieldQuery('sell_value')]);
		});

		$this->addExpression('short_term_capital_gain')->set(function($m,$q){
			return "'0'";
			// return $q->expr();
		});
		
		$this->addExpression('long_term_capital_gain')->set("'0'");

	}
}