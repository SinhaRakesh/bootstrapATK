<?php

class page_clienttransaction extends Page {

    public $title='Client Daily Transaction';

    function init() {
        parent::init();

        $tab = $this->add('Tabs');
        $tra = $tab->addTab('Transaction');
        $fifo = $tab->addTab('FIFO');
        $sell = $tab->addTab('sell');

        $crud = $tra->add('CRUD',['allow_add'=>false]);
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

        $model = $this->add('Model_Transaction');
        $model->addExpression('isin')->set($model->refSQL('company_id')->fieldQuery('isin_code'));
        $model->setOrder('created_at','asc');
        $model->addCondition('buy_qty','>',0);

        $grid = $fifo->add('Grid');
        $grid->setModel($model,['client','company','isin','created_at','buy_qty','fifo_sell_qty','fifo_remaining_qty','fifo_sell_date','sell_qty']);
        $grid->addPaginator($ipp=50);
        $grid->addQuickSearch(['company']);

        $model = $this->add('Model_Transaction');
        $model->addExpression('isin')->set($model->refSQL('company_id')->fieldQuery('isin_code'));
        $model->addCondition('sell_qty','>',0);
        $model->setOrder('created_at','asc');
        // $model->setOrder();

        $grid = $sell->add('Grid');
        $grid->setModel($model,['client','company','isin','created_at','sell_qty']);
        $grid->addPaginator($ipp=50);
        $grid->addQuickSearch(['company']);

    }
}
