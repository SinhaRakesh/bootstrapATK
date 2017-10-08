<?php

class Model_DailyBhav extends Model_Base_Table{
	public $table = "daily_bhav";

	function init(){
		parent::init();

		$this->hasOne('Company','company_id');
		
		$this->addField('name');
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}