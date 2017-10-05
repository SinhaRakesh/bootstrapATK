<?php
class page_test extends basePage{

    function init(){

        parent::init();
    	
        $crud = $this->add('CRUD');
        $crud->setModel("User");

    }
}