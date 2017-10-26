<?php

class Model_FifoSell extends Model_Base_Table{
	public $table = "fifo_sell";

	function init(){
		parent::init();

		$this->hasOne('Transaction','transaction_id');
		
		$this->addField('sell_qty')->type('number');
		$this->addField('sell_price')->type('number');
		$this->addField('sell_date')->type('datetime');

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}