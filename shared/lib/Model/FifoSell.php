<?php

class Model_FifoSell extends Model_Base_Table{
	public $table = "fifo_sell";

	function init(){
		parent::init();

		$this->hasOne('Transaction','transaction_id');
		$this->hasOne('Company','company_id');
		$this->hasOne('Client','client_id');
		
		$this->addField('sell_qty')->type('number');
		$this->addField('sell_price')->type('number');
		$this->addField('sell_date')->type('datetime');
		$this->addField('buy_price')->type('number');

		$this->addExpression('fifo_sell_amount')->set('sell_price * sell_qty')->type('money');

		// $this->addExpression('company_id')->set($this->refSQL('transaction_id')->fieldQuery('company_id'));
		// $this->addExpression('company_name')->set($this->refSQL('transaction_id')->fieldQuery('company'))->caption('company');

		// $this->addExpression('client_name')->set(function($m,$q){
		// 	return $q->expr('[0]',[$m->refSQL('transaction_id')->fieldQuery('client')]);
		// })->caption('client');
		// $this->addExpression('client_id')->set(function($m,$q){
		// 	return $q->expr('[0]',[$m->refSQL('transaction_id')->fieldQuery('client_id')]);
		// });

		$this->addExpression('tran_date')->set($this->refSQL('transaction_id')->fieldQuery('created_at'));
		$this->addExpression('fifo_buy_amount')->set(function($m,$q){
			return $q->expr('[0] * [1]',[$m->getElement('sell_qty'), $m->getElement('buy_price')]);
		});

		$this->addExpression('sell_duration')->set(function($m,$q){
			return $q->expr('DATEDIFF([0],[1])',[$m->getElement('sell_date'),$m->getElement('tran_date')]);
		});

		$this->add('dynamic_model/Controller_AutoCreator');
	}

}