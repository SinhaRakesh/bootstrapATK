<?php

class page_client extends Page {

    public $title='Client Management';

    function init() {
        parent::init();

        $model = $this->add('Model_Client');
        $crud = $this->add('CRUD');
        $crud->setModel($model,
                ['name','client_code','phone_number','email_id','state_id','city_id','address1','address2','pin_code','is_active'],
                ['name','client_code','phone_number','email_id','state','city','is_active']
            );
        $crud->grid->addPaginator(50);
        $crud->grid->addQuickSearch(['name','client_code']);
    }
}
