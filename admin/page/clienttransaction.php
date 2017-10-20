<?php

class page_clienttransaction extends Page {

    public $title='Client Daily Transaction';

    function init() {
        parent::init();

        $crud = $this->add('CRUD',['allow_add'=>false]);
        $crud->setModel('Model_TransactionMaster')->setOrder('id','desc');
        $crud->grid->addPaginator($ipp=25);

        $crud->grid->add('VirtualPage')
            ->addColumn('transaction')
            ->set(function($page){
               $id = $_GET[$page->short_name.'_id'];
               $t = $this->add('Model_Transaction')
                    ->addCondition('transaction_master_id',$id);
               $crud = $page->add('CRUD');
               $crud->setModel($t);
               $crud->grid->addPaginator(25);
        });

        // $model = $this->add('Model_Transaction');
        // $crud = $this->add('CRUD');
        // $crud->setModel($model,['transaction_master','client','company','created_at','net_value','net_qty']);
        
    }
}
