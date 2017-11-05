<?php

class Model_ClientData extends Model_Client{
	// public $table = "client";
	public $on_date = "";
	public $short_date;
	public $fin_start_date;
	public $fin_end_date;

	function init(){
		parent::init();

		if(!$this->on_date) $this->on_date = $this->app->today;

		if(!$this->short_date){
			$strtotime = strtotime($this->on_date);
			$this->fin_start_date = (date('m',$strtotime) < '04') ? date('Y-04-01',strtotime('-1 year',$strtotime)) : date('Y-04-01',$strtotime);
			$this->fin_end_date = date('Y-03-t',strtotime('+1 year',strtotime($this->fin_start_date)));

			$this->short_date = date('Y-m-d',strtotime("-1 year",strtotime($this->on_date)));
		}

		$this->getElement('name')->caption('Client');

		$this->addExpression('today_buying_value')->set(function($m,$q){
						
			$t = $m->add('Model_Transaction',['table_alias'=>'tbv'])
				->addCondition('client_id',$m->getElement('id'))
				->addCondition('created_at','>=',$this->on_date)
				->addCondition('created_at','<',$this->app->nextDate($this->on_date))
				;
			return $q->expr('IFNULL([0],0)',[$t->sum('buy_amount')]);
		});
		
		$this->addExpression('today_sell_value')->set(function($m,$q){
			
			$t = $m->add('Model_Transaction',['table_alias'=>'tsv'])
				->addCondition('client_id',$m->getElement('id'))
				->addCondition('created_at','>=',$this->on_date)
				->addCondition('created_at','<',$this->app->nextDate($this->on_date))
				;
			return $q->expr('IFNULL([0],0)',[$t->sum('sell_amount')]);
		});


		$this->addExpression('short_term_capital_gain')->set(function($m,$q){
			$tra = $m->add('Model_FifoSell',['table_alias'=>'stcg']);
			$tra->addCondition('client_id',$m->getElement('id'))
				->addCondition('sell_date','>=',$this->fin_start_date)
				->addCondition('sell_date','<',$this->app->nextDate($this->fin_end_date))
				->addCondition('sell_duration','<',365)
				;
			return $q->expr('IFNULL(([total_sell_amount]/[total_buy_amount])*100,0)',
					[
					'total_sell_amount'=>$tra->sum('fifo_sell_amount'),
					'total_buy_amount'=>$tra->sum('fifo_buy_amount')
				]);
		})->type('money');
		
		$this->addExpression('long_term_capital_gain')->set(function($m,$q){
			$tra = $m->add('Model_FifoSell',['table_alias'=>'ltcg']);
			$tra->addCondition('client_id',$m->getElement('id'))
				->addCondition('sell_date','<',$this->fin_start_date)
				;
			
			return $q->expr('IFNULL(([total_sell_amount]/[total_buy_amount])*100,0)',
					[
					'total_sell_amount'=>$tra->sum('fifo_sell_amount'),
					'total_buy_amount'=>$tra->sum('fifo_buy_amount')
				]);
		})->type('money');

		// $this->addExpression('short_total_sell_amount')->set(function($m,$q){
		// 	$tra = $m->add('Model_Transaction',['table_alias'=>'tltcgs']);
		// 	$tra->addCondition('client_id',$m->getElement('id'))
		// 		->addCondition('created_at','>=',$this->short_date);
		// 	return $q->expr('[0]',[$tra->sum('sell_amount')]);
		// });

		// $this->addExpression('short_total_buy_amount')->set(function($m,$q){
		// 	$tra = $m->add('Model_Transaction',['table_alias'=>'tstcgb']);
		// 	$tra->addCondition('client_id',$m->getElement('id'))
		// 		->addCondition('created_at','>=',$this->short_date);
		// 	return $q->expr('[0]',[$tra->sum('buy_amount')]);
		// });

		// $this->addExpression('long_total_sell_amount')->set(function($m,$q){
		// 	$tra = $m->add('Model_Transaction',['table_alias'=>'ltcgss']);
		// 	$tra->addCondition('client_id',$m->getElement('id'))
		// 		->addCondition('created_at','<',$this->short_date);
		// 	return $q->expr('[0]',[$tra->sum('sell_amount')]);
		// });

		// $this->addExpression('long_total_buy_amount')->set(function($m,$q){
		// 	$tra = $m->add('Model_Transaction',['table_alias'=>'tstcgbb']);
		// 	$tra->addCondition('client_id',$m->getElement('id'))
		// 		->addCondition('created_at','<',$this->short_date);
		// 	return $q->expr('[0]',[$tra->sum('buy_amount')]);
		// });

		$this->addExpression('buy_value')->set(function($m,$q){
			$tra = $m->add('Model_Transaction',['table_alias'=>'tstcgbb']);
			$tra->addCondition('client_id',$m->getElement('id'))
				->addCondition('created_at','<=',$this->on_date)
				->addCondition('fifo_remaining_qty','>',0)
				;
			return $q->expr('[0]',[$tra->sum('fifo_buy_amount')]);
		})->type('money');

		// $this->addExpression('sell_value')->set(function($m,$q){
		// 	$tra = $m->add('Model_Transaction',['table_alias'=>'tstcgbbs']);
		// 	$tra->addCondition('client_id',$m->getElement('id'))
		// 		->addCondition('created_at','<=',$this->on_date);
		// 	return $q->expr('[0]',[$tra->sum('sell_amount')]);
		// })->type('money');

		$this->addExpression('buy_current_value')->set(function($m,$q){
			$tra = $m->add('Model_Transaction',['table_alias'=>'tstcgbbss','to_date'=>$this->on_date]);
			$tra->addCondition('client_id',$m->getElement('id'))
				->addCondition('created_at','<=',$this->on_date)
				->addCondition('fifo_remaining_qty','>',0)
				;
			return $q->expr('[0]',[$tra->sum('current_buy_amount')]);
		})->type('money')->caption('Current Value');

		$this->addExpression('current_pl')->set(function($m,$q){
			$tra = $m->add('Model_Transaction',['table_alias'=>'pltra','to_date'=>$this->on_date]);
			$tra->addCondition('client_id',$m->getElement('id'))
				->addCondition('created_at','<=',$this->on_date)
				->addCondition('fifo_remaining_qty','>',0)
				;
			return $q->expr('[0]',[$tra->sum('current_pl')]);
		})->type('money');

		$this->addExpression('net_investment')->set(function($m,$q){
            return $q->expr('([buy_value]-[sum_of_pl])',['buy_value'=>$m->getElement('buy_value'),'sum_of_pl'=>$m->getElement('current_pl')]);
        })->type('money');

		$this->addExpression('profit')->set(function($m,$q){
            return $q->expr('([buy_current_value] - [net_investment])',['buy_current_value'=>$m->getElement('buy_current_value'),'net_investment'=>$m->getElement('net_investment')]);
        })->type('money');

		$this->addExpression('ror')->set(function($m,$q){
            return $q->expr('(([profit]/[net_investment])*100)',['profit'=>$m->getElement('profit'),'net_investment'=>$m->getElement('net_investment')]);
        })->caption('R.O.R(%)')->type('money');
	}
}