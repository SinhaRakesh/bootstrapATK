<?php

class Model_City extends Model_Base_Table{
	public $table = "city";

	function init(){
		parent::init();

		$this->hasOne('State','state_id');
		
		$this->addField('name');
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}