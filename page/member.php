<?php

class page_member extends basePage{

    function init(){
        parent::init();
 
        $member = $this->add('Model_Member');
        $crud = $this->add('CRUD');
        $crud->setModel($member);
    }
}