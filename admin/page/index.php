<?php

class page_index extends Page {

    public $title='Dashboard';

    function init() {
        parent::init();

        $on_date = $this->app->stickyGET('date');
        if(!$on_date) $on_date = $this->app->today;

        $form = $this->add('Form');
        $form->addField('DatePicker','date')->set($on_date);
        $form->addSubmit('filter');

        $m = $this->add('Model_ClientData',['on_date'=>$on_date]);
        $grid = $this->add('Grid');
        $grid->setModel($m,['name','today_buying_value','today_sell_value','short_term_capital_gain','long_term_capital_gain']);

        // $strtotime = strtotime($on_date);
        // $fin_start_date = (date('m',$strtotime) < '04') ? date('Y-04-01',strtotime('-1 year',$strtotime)) : date('Y-04-01',$strtotime);
        // $end_start_date = date('Y-03-t',strtotime('+1 year',strtotime($fin_start_date)));

        // $grid->add('View',null,'grid_buttons')->set($fin_start_date." end= ".$end_start_date);

        if($form->isSubmitted()){
            $grid->js()->reload(['date'=>$form['date']])->execute();
        }
        
    }
}
