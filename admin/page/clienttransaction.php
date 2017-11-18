<?php

class page_clienttransaction extends Page {

    public $title='Client Transaction Records';

    function init() {
        parent::init();

        $this->app->stickyGET('a_company_id');
        $this->app->stickyGET('a_from_date');
        $this->app->stickyGET('a_to_date');
        $this->app->stickyGET('a_client_id');

        $tab = $this->add('Tabs')->addClass('rtabs');
        $tra = $tab->addTab('Transaction Record');
        // $fifo = $tab->addTab('FIFO');
        // $sell = $tab->addTab('sell');
        $all = $tab->addTab('Transaction Filter');
        // $only_transaction = $tab->addTab('All Transaction');

        $crud = $tra->add('CRUD',['allow_add'=>false]);
        $crud->setModel('Model_TransactionMaster')->setOrder('id','desc');
        $crud->grid->addPaginator($ipp=25);

        $crud->grid->add('VirtualPage')
            ->addColumn('transaction','Transaction',['title'=>'Transaction'])
            ->set(function($page){
               $id = $_GET[$page->short_name.'_id'];
               $t = $this->add('Model_Transaction')
                    ->addCondition('transaction_master_id',$id);
               $crud = $page->add('CRUD',['allow_add'=>false]);
               $crud->setModel($t,['client','client_id','company_id','company','buy_value','sell_value','net_value','sell_qty','buy_qty','net_qty','buy_amount','sell_amount'],['client','company','buy_value','sell_value','net_value','sell_qty','buy_qty','net_qty','buy_amount','sell_amount']);
               $crud->grid->addPaginator(25);

               $crud->grid->addHook('formatRow',function($g){
                    $g->current_row_html['net_value'] = round(abs($g->model['net_value']),2);
                });
        });

        // $model = $this->add('Model_Transaction');
        // $model->addExpression('isin')->set($model->refSQL('company_id')->fieldQuery('isin_code'));
        // $model->setOrder('created_at','asc');
        // $model->addCondition('buy_qty','>',0);

        // $grid = $fifo->add('Grid');
        // $grid->setModel($model,['client','company','isin','created_at','buy_qty','fifo_sell_qty','fifo_remaining_qty','fifo_sell_date','sell_duration','sell_qty']);
        // $grid->addPaginator($ipp=50);
        // $grid->addQuickSearch(['company','client','isin']);

        // $model = $this->add('Model_Transaction');
        // $model->addExpression('isin')->set($model->refSQL('company_id')->fieldQuery('isin_code'));
        // $model->addCondition('sell_qty','>',0);
        // $model->setOrder('created_at','asc');
        // $model->setOrder();

        // $grid = $sell->add('Grid');
        // $grid->setModel($model,['client','company','isin','created_at','sell_qty']);
        // $grid->addPaginator($ipp=50);
        // $grid->addQuickSearch(['company','client','isin']);

        $form = $all->add('Form',null,null,['form/horizontal']);
        $fld_client = $form->addField('autocomplete/Basic','client');
        $fld_client->other_field->setAttr('placeholder', "Search By Name");

        $fld_client->setModel('Client');

        $fld_company = $form->addField('autocomplete/Basic','company','Stock');
        $fld_company->other_field->setAttr('placeholder', "Search By Name");
        $fld_company->setModel('Company');

        $form->addField('DatePicker','from_date');
        $form->addField('DatePicker','to_date');
        $form->addSubmit('Filter');

        $tras = $this->add('Model_Transaction');
        $tras->addCondition('client_id',$_GET['a_client_id']);

        if($_GET['a_company_id'] > 0)
            $tras->addCondition('company_id',$_GET['a_company_id']);

        if(strtotime($_GET['a_from_date']) > 0){
            $tras->addCondition('created_at','>=',$_GET['a_from_date']);
        }
        if(strtotime($_GET['a_to_date']) > 0){
            $tras->addCondition('created_at','<',$this->app->nextDate($_GET['a_to_date']));
        }
        $tras->setOrder('created_at','desc');

        $a_grid = $all->add('Grid');
        $a_grid->setModel($tras,['date_only','company','buy_qty','buy_value','buy_amount','sell_qty','sell_value','sell_amount','fifo_remaining_qty']);

        if($tras->count()->getOne()){
            $a_grid->addTotals(['buy_amount','sell_amount','buy_qty','sell_qty']);
            $a_grid->addPaginator($ipp=100);
        }    

        if($form->isSubmitted()){
            $a_grid->js()->reload([
                        'a_client_id'=>$form['client'],
                        'a_company_id'=>$form['company'],
                        'a_from_date'=>$form['from_date'],
                        'a_to_date'=>$form['to_date'],
                    ])->execute();
        }

        // $all_crud = $only_transaction->add('CRUD',['allow_add'=>false]);
        // $all_crud->setModel("Model_Transaction")->setOrder('id','desc');
        // $all_crud->grid->addPaginator($ipp=30);
    }
}
