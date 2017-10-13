<?php

class Model_DailyBhav extends Model_Base_Table{
	public $table = "daily_bhav";
	public $title_field = "last";

	function init(){
		parent::init();

		$this->hasOne('Company','company_id');
		
		$this->addField('open')->type('money');
		$this->addField('high')->type('money');
		$this->addField('low')->type('money');
		$this->addField('close')->type('money');
		$this->addField('last')->type('money');
		$this->addField('prevclose')->type('money');

		$this->addField('trading_date')->type('date');
		$this->addField('created_at')->type('datetime')->set($this->app->now);
		$this->addField('import_date')->type('datetime'); // import date in software system

		$this->add('dynamic_model/Controller_AutoCreator');
	}


}