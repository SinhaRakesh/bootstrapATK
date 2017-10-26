<?php

class page_report extends Page {

    public $title='Report';

    function init() {
        parent::init();

        $type = $this->app->stickyGET('type');
        $c_id = $this->app->stickyGET('client');

        $form = $this->add('Form');
        $fld_client = $form->addField('DropDown','client');
        $fld_client->setModel('Client');
        $fld_client->setEmptyText('All');
        $fld_client->set($c_id);

        $fld_type = $form->addField('DropDown','report_type');
        $fld_type->setValueList([
                'stock_report'=>'Stock Report (till date)',
                'short_term'=>'Short Term Gain Report',
                'long_term'=>'Long Term Gain Report'
            ]);
        $fld_type->set($type);

        $form->addSubmit('Generate Report');

        if($type == "stock_report"){
            $this->addStockReport($c_id);

        }elseif($type == "short_term"){
            $this->addShortTermReport($c_id);
            // $field_to_show = ['name','client_code','short_total_buy_amount','short_total_sell_amount','short_term_capital_gain'];
        }elseif($type == "long_term"){
            $this->addLongTermReport($c_id);
            // $field_to_show = ['name','client_code','long_total_buy_amount','long_total_sell_amount','long_term_capital_gain'];
        }

        // $client_data = $this->add('Model_ClientData');
        // if($c_id = $_GET['client'])
        //     $client_data->addCondition('id',$c_id);
        // $field_to_show = ['name','client_code'];
        // $grid = $this->add('Grid');
        // $grid->setModel($client_data,$field_to_show);
        // $grid->addPaginator($ipp=25);

        if($form->isSubmitted()){
            if($form['report_type'] == "stock_report" && !$form['client']) $form->error('client','must not be empty');

            $this->js()->reload(['client'=>$form['client'],'type'=>$form['report_type']])->execute();
        }
    }


    function addStockReport($client_id){
        $model = $this->add('Model_Transaction');
        $model->addExpression('date')->set('Date_Format(created_at,"%d %M %Y")');

        $model->addExpression('cmp')->set(function($m,$q){
            $c = $m->add('Model_DailyBhav')
                ->addCondition('company_id',$m->getElement('id'))
                ->setOrder('created_at','desc')
                ->setLimit(1)
                ;
            return $q->expr('IFNULL([0],0)',[$c->fieldQuery('last')]);
        })->type('money');

        $model->addExpression('cmp_amount')->set(function($m,$q){
            return $q->expr('(Abs([0]) * Abs([1]))',[$m->getElement('fifo_remaining_qty'),$m->getElement('cmp')]);
        });

        $model->addExpression('fifo_remaining_amount')->set(function($m,$q){
            return $q->expr('(Abs([0]) * Abs([1]))',[$m->getElement('fifo_remaining_qty'),$m->getElement('buy_value')]);
        })->type('money');


        $model->addExpression('pl')->set(function($m,$q){
            return $q->expr('[0]-[1]',[$m->getElement('cmp_amount'),$m->getElement('fifo_remaining_amount')]);
        })->type('money')->caption('P/L');

        $model->addExpression('gain')->set(function($m,$q){
            return $q->expr('(Abs([0])/Abs([1]))*100',[$m->getElement('pl'),$m->getElement('fifo_remaining_amount')]);
        })->type('money');

        $model->addCondition('fifo_remaining_qty','>',0);
        $model->addCondition('client_id',$client_id);
        
        $grid = $this->add('Grid');
        $grid->setModel($model,['date','company','buy_qty','buy_amount','fifo_remaining_qty','buy_value','fifo_remaining_amount','cmp','cmp_amount','pl','gain']);

    }

    function addLongTermReport($client_id){
        $this->add('View')->set($client_id);
    }

    function addShortTermReport($client_id){
        $this->add('View')->set($client_id);
    }

}
