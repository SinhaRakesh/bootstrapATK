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
		$this->addHook('beforeDelete',$this);
		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		$u = $this->add('Model_User');
		$u->addCondition('email','admin');
		$u->addCondition('id','<>',$this->id);
		$u->tryLoadAny();

		if($u->loaded())
			throw $this->exception('Name Already Exists ', 'ValidityCheck')->setField('email');
			
	}

	function beforeDelete(){
		if($this['email'] == "admin"){
			throw new \Exception("Admin User Cannot Delete");
		}
	}

}