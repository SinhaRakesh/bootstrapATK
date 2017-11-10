<?php

class page_company extends Page {

    public $title='Stock Management';

    function init() {
        parent::init();

        $model = $this->add('Model_Stock');
        $crud = $this->add('CRUD');
        $crud->setModel($model,
                ['sc_name','isin_code','sc_code','sc_group','sc_type','is_active'],
                ['sc_name','isin_code','last_update','closing_value','is_active']
            );

        $crud->grid->addQuickSearch(['isin_code','sc_name']);
        $crud->grid->addPaginator($ipp=50);
        
        $crud->grid->add('VirtualPage')
        ->addColumn('closing_values','Closing Value',['title'=>'Update Closing Value'])
        ->set(function($page){
            $id = $_GET[$page->short_name.'_id'];

            $m = $page->add('Model_DailyBhav',['table_alias'=>'dt'])
                ->addCondition('company_id',$id)
                ;
            $m->setOrder('created_at','desc');
            $c = $page->add('CRUD');
            $c->setModel($m);
            $c->grid->addPaginator($ipp=50);
            
        });    
    }
}
