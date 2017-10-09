<?php

class page_company extends Page {

    public $title='Company Management';

    function init() {
        parent::init();

        $model = $this->add('Model_Company');
        $crud = $this->add('CRUD');
        $crud->setModel($model,
                ['sc_name','isin_code','sc_code','sc_group','sc_type','is_active'],
                ['sc_name','isin_code','last_update','closing_value','is_active']
            );

        $crud->grid->addQuickSearch(['isin_code','sc_name']);
        $crud->grid->addPaginator($ipp=50);
        
    }
}
