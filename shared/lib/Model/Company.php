<?php

class Model_Company extends Model_Base_Table{
	public $table = "company";
	public $title_field = 'sc_name';

	function init(){
		parent::init();

		$this->addField('sc_name');
		$this->addField('isin_code')->caption('ISIN_CODE');
		$this->addField('sc_code');
		$this->addField('sc_group');
		$this->addField('sc_type');

		$this->addField('is_active')->type('boolean')->defaultValue(true)->sortable(true);
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now)->system(true);
		
		$this->hasMany('DailyBhav','company_id');

		$this->addExpression('date_updated')->set("'todo'");
		$this->addExpression('closing_value')->set("'todo'");

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}