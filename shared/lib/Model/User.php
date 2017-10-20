<?php

class Model_User extends Model_Base_Table{
	public $table = "user";
	public $title_field = "email";
	function init(){
		parent::init();
		
		$this->addField('email')->caption('username');
		$this->addField('password')->type('password');
		$this->addField('is_active')->type('boolean');
		
		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}