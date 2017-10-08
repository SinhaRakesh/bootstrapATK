<?php

class page_client extends Page {

    public $title='Client Management';

    function init() {
        parent::init();

        $model = $this->add('Model_Client');
        $crud = $this->add('CRUD');
        $crud->setModel($model,
                ['name','email','contact','address','is_active'],
                ['client_code','name','email','address','is_active']
            );
    }
}
