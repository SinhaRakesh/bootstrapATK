<?php

class Model_Template extends Model_Base_Table{
	public $table = "template";

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('subject');
		$this->addField('body')->type('text');
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}