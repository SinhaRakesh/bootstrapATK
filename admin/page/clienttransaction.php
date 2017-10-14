<?php

class page_clienttransaction extends Page {

    public $title='Client Daily Transaction';

    function init() {
        parent::init();

        $model = $this->add('Model_Transaction');
        $crud = $this->add('CRUD');
        $crud->setModel($model);
        
    }
}
