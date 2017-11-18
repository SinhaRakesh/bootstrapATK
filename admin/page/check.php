<?php

class page_check extends Page {

    public $title='';

    function init() {
        parent::init();

        $client = $this->app->stickyGET('client');
        $date = $this->app->stickyGET('date');

        $tab = $this->add('Tabs');
        $bvaod = $tab->addTab('BuyValueAsOnDate');
        $sell = $tab->addTab('sell');

        $f = $bvaod->add('Form');
        $f->addField('autocomplete\Basic','client')->setModel('Client');
        $f->addField('DatePicker','date');
        $f->addSubmit();
        
        $tra = $this->add('Model_Transaction',['table_alias'=>'tstcgbb','to_date'=>$date,'from_date'=>$date]);
        if($client && $date){
            $tra->addCondition('client_id',$client);
            $tra->addCondition('created_at','<=',$date);
            $tra->addCondition('fifo_remaining_qty','>',0);
        }else{
            $tra->addCondition('id',-1);
        }

        $c = $bvaod->add('CRUD');
        $c->setModel($tra,['company','buy_value','buy_qty','sell_value','sell_qty','net_value','net_qty','buy_amount','created_at','fifo_sell_qty','fifo_sell_amount','fifo_remaining_qty','company_latest_closing_value','fifo_buy_amount','current_buy_amount','current_pl']);
        $c->grid->addTotals(['fifo_buy_amount','current_buy_amount','current_pl']);

        $c->grid->add('VirtualPage')
            ->addColumn('sell_detail')
            ->set(function($page){

                $id = $_GET[$page->short_name.'_id'];
                $page->add('View')->set($id);
                $tra = $page->add('Model_Transaction',['table_alias'=>'tra123io']);
                $tra->addCondition('id',$id);
                if($tra->count()->getOne()){
                    $c = $page->add('CRUD');
                    $m = $page->add('Model_FifoSell')
                            ->addCondition('transaction_id',$id)
                            // ->addCondition('company_id',$tra['company_id'])
                            // ->addCondition('client_id',$tra['client_id'])
                            ;
                    $c->setModel($m);
                }else{
                    $page->add('View')->set('no record found');
                }
            });
         $c->grid->add('VirtualPage')
            ->addColumn('bhav')
            ->set(function($page){

                $id = $_GET[$page->short_name.'_id'];
                $tra = $page->add('Model_Transaction',['table_alias'=>'tra123io']);
                $tra->addCondition('id',$id);
                if($tra->loaded()){
                    $page->add('View')->set('Company: '.$tra['company_id']);
                    $c = $page->add('CRUD');
                    $m = $page->add('Model_DailyBhav')
                            ->addCondition('company_id',$tra['company_id'])
                            ;
                    $m->setOrder('created_at','desc');
                    $c->setModel($m);
                }else{
                    $page->add('View')->set('no record found');
                }
            });
        if($f->isSubmitted()){
            $c->js()->reload(['date'=>$f['date'],'client'=>$f['client']])->execute();
        }


        $crud = $sell->add('CRUD');
        $crud->setModel('FifoSell');
        $crud->grid->addPaginator($ipp=50);


    }
}
