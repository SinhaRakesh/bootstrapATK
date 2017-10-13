<?php

class Model_State extends Model_Base_Table{
	public $table = "state";

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		
		$this->hasMany('City','state_id');
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}