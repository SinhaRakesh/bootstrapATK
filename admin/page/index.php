<?php

class page_index extends Page {

    public $title='Dashboard';
    public $on_date;
    function init() {
        parent::init();

        $on_date = $this->app->stickyGET('date');
        if(!$on_date) $on_date = $this->app->today;

        $this->on_date = $on_date;

        $form = $this->add('Form');
        $form->addField('DatePicker','date')->set($on_date);
        $form->addSubmit('filter');

        $m = $this->add('Model_ClientData',['on_date'=>$on_date]);
        $grid = $this->add('Grid');
        $grid->setModel($m,['name','today_buying_value','today_sell_value']);
        $grid->addPaginator($ipp=50);
        $grid->addQuickSearch(['name']);
        // $grid->addSno();

        $grid->add('VirtualPage')
        ->addColumn('detail')
        ->set(function($page){
            $id = $_GET[$page->short_name.'_id'];
            $m = $page->add('Model_Transaction',['table_alias'=>'dt'])
                ->addCondition('client_id',$id)
                ->addCondition('created_at','>=',$this->on_date)
                ->addCondition('created_at','<',$this->app->nextDate($this->on_date))
                ;
            $g = $page->add('Grid');
            $g->setModel($m,['company','buy_qty','buy_value','buy_amount','sell_qty','sell_value','sell_amount','created_at']);
            
            if($m->count()->getOne()){
                $g->addTotals(['buy_amount','sell_amount']);
                $g->add('misc/Export');
            }
        });    
        // $strtotime = strtotime($on_date);
        // $fin_start_date = (date('m',$strtotime) < '04') ? date('Y-04-01',strtotime('-1 year',$strtotime)) : date('Y-04-01',$strtotime);
        // $end_start_date = date('Y-03-t',strtotime('+1 year',strtotime($fin_start_date)));

        // $grid->add('View',null,'grid_buttons')->set($fin_start_date." end= ".$end_start_date);

        if($form->isSubmitted()){
            $grid->js()->reload(['date'=>$form['date']])->execute();
        }
        
    }
}
