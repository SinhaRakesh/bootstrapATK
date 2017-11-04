<?php

class page_report extends Page {

    public $title='Report';

    function init() {
        parent::init();

        $type = $this->app->stickyGET('type');
        $c_id = $this->app->stickyGET('client');

        $form = $this->add('Form');
        $fld_client = $form->addField('autocomplete/Basic','client');
        $fld_client->setModel('Client');
        // $fld_client->setEmptyText('All');
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
        
        $model->addExpression('fifo_remaining_amount')->set(function($m,$q){
            return $q->expr('([0] * [1])',[$m->getElement('fifo_remaining_qty'),$m->getElement('buy_value')]);
        })->type('money');

        $model->addExpression('cmp')->set(function($m,$q){
            $c = $m->add('Model_DailyBhav')
                ->addCondition('company_id',$m->getElement('company_id'))
                ->setOrder('created_at','desc')
                ->setLimit(1)
                ;
            return $q->expr('IFNULL([0],0)',[$c->fieldQuery('last')]);
        })->type('money');

        $model->addExpression('cmp_amount')->set(function($m,$q){
            return $q->expr('([0] * [1])',[$m->getElement('fifo_remaining_qty'),$m->getElement('cmp')]);
        })->type('money');

        $model->addExpression('pl')->set(function($m,$q){
            return $q->expr('([0]-[1])',[$m->getElement('cmp_amount'),$m->getElement('fifo_remaining_amount')]);
        })->type('money')->caption('P/L');

        $model->addExpression('gain')->set(function($m,$q){
            return $q->expr('(([0]/[1])*100)',[$m->getElement('pl'),$m->getElement('fifo_remaining_amount')]);
        })->type('money');

        $model->addCondition('fifo_remaining_qty','>',0);
        $model->addCondition('client_id',$client_id);
        
        $grid = $this->add('Grid');
        $grid->setModel($model,['date','company','buy_qty','buy_value','buy_amount','fifo_remaining_qty','fifo_remaining_amount','cmp','cmp_amount','pl','gain']);
        $grid->add('misc/Export');
        $grid->addPaginator($ipp=30);

        $grid->addHook('formatRow',function($g){
            if($g->model['pl'] < 0 ){
                $g->current_row_html['pl'] = abs($g->model['pl']);
                $g->current_row_html['gain'] = round(abs($g->model['gain']),2);
            }
        });
    }

    function addLongTermReport($client_id){
        $on_date = $this->app->stickyGET('date');
        if(!$on_date) $on_date = $this->app->today;

        $strtotime = strtotime($on_date);
        $fin_start_date = (date('m',$strtotime) < '04') ? date('Y-04-01',strtotime('-1 year',$strtotime)) : date('Y-04-01',$strtotime);
        $fin_end_date = date('Y-03-t',strtotime('+1 year',strtotime($fin_start_date)));

        if($client_id){
            $tra = $this->add('Model_FifoSell');
            $tra->addExpression('total_sell_amount')->set('sum(sell_price * sell_qty)')->type('money');
            $tra->addExpression('total_buy_amount')->set('sum(buy_price * sell_qty)')->type('money');
            $tra->addExpression('LTCP')->set(function($m,$q){
                return $q->expr('IFNULL(([total_sell_amount]/[total_buy_amount])*100,0)',
                        [
                        'total_sell_amount'=>$m->getElement('total_sell_amount'),
                        'total_buy_amount'=>$m->getElement('total_buy_amount')
                    ]);
            })->type('money');
            $tra->addCondition('client_id',$client_id)
                ->addCondition('sell_date','<',$fin_start_date)
                // ->addCondition('sell_date','<',$this->app->nextDate($fin_end_date))
                // ->addCondition('sell_duration','<',365)
                ;
            $tra->_dsql()->group('company_id');

            $grid = $this->add('Grid');
            $grid->setModel($tra,['client','company','total_sell_amount','total_buy_amount','LTCP']);
            $grid->addPaginator($ipp=50);
            $grid->add('misc/Export');
        }else{
            $m = $this->add('Model_ClientData',['on_date'=>$on_date]);
            $grid = $this->add('Grid');
            $grid->setModel($m,['name','long_term_capital_gain']);
        }

    }

    function addShortTermReport($client_id){
        $on_date = $this->app->stickyGET('date');
        if(!$on_date) $on_date = $this->app->today;

        $strtotime = strtotime($on_date);
        $fin_start_date = (date('m',$strtotime) < '04') ? date('Y-04-01',strtotime('-1 year',$strtotime)) : date('Y-04-01',$strtotime);
        $fin_end_date = date('Y-03-t',strtotime('+1 year',strtotime($fin_start_date)));

        if($client_id){

            // $tra = $this->add('Model_Transaction');
            // $tra->addExpression('total_sell_amount')->set('IFNULL(sum(sell_amount),0)');
            // $tra->addExpression('total_buy_amount')->set('IFNULL(sum(buy_amount),0)');

            // $tra->addExpression('STCP')->set(function($m,$q){
            //     return $q->expr('IFNULL(([total_sell_amount]/[total_buy_amount])*100,0)',
            //         [
            //         'total_sell_amount'=>$m->getElement('total_sell_amount'),
            //         'total_buy_amount'=>$m->getElement('total_buy_amount')
            //     ]);
            // })->type('money');

            // $tra->addCondition('client_id',$client_id);
            // $tra->addCondition('created_at','>=',$fin_start_date);
            // $tra->addCondition('created_at','<',$this->app->nextDate($fin_end_date));
            // $tra->_dsql()->group('company_id');

            $tra = $this->add('Model_FifoSell');
            $tra->addExpression('total_sell_amount')->set('sum(sell_price * sell_qty)')->type('money');
            $tra->addExpression('total_buy_amount')->set('sum(buy_price * sell_qty)')->type('money');
            $tra->addExpression('STCP')->set(function($m,$q){
                return $q->expr('IFNULL(([total_sell_amount]/[total_buy_amount])*100,0)',
                        [
                        'total_sell_amount'=>$m->getElement('total_sell_amount'),
                        'total_buy_amount'=>$m->getElement('total_buy_amount')
                    ]);
            })->type('money');
            $tra->addCondition('client_id',$client_id)
                ->addCondition('sell_date','>=',$fin_start_date)
                ->addCondition('sell_date','<',$this->app->nextDate($fin_end_date))
                ->addCondition('sell_duration','<',365)
                ;
            $tra->_dsql()->group('company_id');

            $grid = $this->add('Grid');
            $grid->setModel($tra,['client','company','total_sell_amount','total_buy_amount','STCP']);
            $grid->addPaginator($ipp=50);
            $grid->add('misc/Export');
        }else{
            $m = $this->add('Model_ClientData',['on_date'=>$on_date]);
            // if($client_id)
            //     $m->addCondition('id',$client_id);
            $grid = $this->add('Grid');
            $grid->setModel($m,['name','short_term_capital_gain']);
        }


    }

}
