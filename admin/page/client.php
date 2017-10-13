<?php

class page_client extends Page {

    public $title='Client Management';

    function init() {
        parent::init();

        $model = $this->add('Model_Client');
        $crud = $this->add('CRUD');
        $crud->setModel($model,
                ['name','contact','email','address','state_id','city_id','is_active'],
                ['client_code','name','contact','email','address','state','city','is_active']
            );
        
    }
}
