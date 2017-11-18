<?php

class Model_FifoSellProfit extends Model_FifoSell{
	function init(){
		parent::init();

		$this->addExpression('sell_date_only')->set(function($m,$q){
            return $q->expr('Date_Format([0],"%d %M %Y")',[$m->getElement('sell_date')]);
        })->caption('Sell Date');

		$this->addExpression('pl')->set(function($m,$q){
            return $q->expr('([0]-[1])',[$m->getElement('sell_amount'),$m->getElement('buy_amount')]);
        })->caption('Profit/<span style="color:red;"> Loss</span>')->type('money');
		
		$this->addExpression('gain')->set(function($m,$q){
            return $q->expr('((([sell]-[buy])/[buy])*100)',[
            	'sell'=>$m->getElement('sell_amount'),
            	'buy'=>$m->getElement('buy_amount')
            ]);
        })->caption('Gain (%)')->type('money');

	}
}