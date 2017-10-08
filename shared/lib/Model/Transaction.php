<?php

class Model_Transaction extends Model_Base_Table{
	public $table = "transaction";

	function init(){
		parent::init();

		$this->addField('name');
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}