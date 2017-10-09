<?php

class Model_Client extends Model_Base_Table{
	public $table = "client";

	function init(){
		parent::init();

		$this->addField('client_code');
		$this->addField('name');
		$this->addField('email');
		$this->addField('contact')->type('number');
		$this->addField('address');
		
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now)->system(true);

		$this->hasMany('Transaction','client_id');
		$this->add('dynamic_model/Controller_AutoCreator');

		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		if(!$this['client_code'])
			$this['client_code'] = "UDR-".$this->nextID();
	}

	function nextID(){
		return $this->add('Model_Client')->count()->getOne() + 1;
	}


	function updateTransaction($data=[],$import_date=null){

		if(!$import_date)
			$import_date = $this->app->now;

		
	}
}