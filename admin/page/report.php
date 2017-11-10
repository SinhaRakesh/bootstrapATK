<?php

class page_report extends Page {

    public $title='Report';

    function init() {
        parent::init();

        $type = $this->app->stickyGET('type');
        $c_id = $this->app->stickyGET('client');
        $f_year = $this->app->stickyGET('financial_year');
        
        $form = $this->add('Form',null,null,['form/horizontal']);
        $fld_client = $form->addField('autocomplete/Basic','client');
        $fld_client->setModel('Client');
        // $fld_client->setEmptyText('All');
        $fld_client->set($c_id);

        $fld_type = $form->addField('DropDown','report_type');
        $fld_type->setValueList([
                'stock_report'=>'Stock Report (till date)',
                'short_term'=>'Short Term Report',
                'long_term'=>'Long Term Report'
            ]);
        $fld_type->set($type);

        $fld_type->js(true)->univ()->bindConditionalShow([
                'stock_report'=>['client'],
                '*'=>['client','financial_year']
            ],'div.atk-form-row');

        $fld_year = $form->addField('DropDown','financial_year');
        $fld_year->setValueList($this->getFinancialYear());       
        $form->addSubmit('Generate Report');

        if($f_year){
            $temp = explode("-", $f_year);
            $this->financial_start_date = date("$temp[0]-04-01");
            $this->financial_end_date = date('Y-03-t',strtotime('+1 year',strtotime($this->financial_start_date)));
            $fld_year->set($f_year);
        }
        
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
            
            $this->js()->reload(['client'=>$form['client'],'type'=>$form['report_type'],'financial_year'=>$form['financial_year']])->execute();
        }

    }


    function addStockReport($client_id){
        $model = $this->add('Model_Transaction');
        $model->addExpression('date')->set('Date_Format(created_at,"%d %M %Y")')->caption('Buy Date');
        $model->getElement('company_id')->caption('Stock');
        $model->getElement('fifo_remaining_qty')->caption('Hold Qty');

        $model->addExpression('fifo_remaining_amount')->set(function($m,$q){
            return $q->expr('([0] * [1])',[$m->getElement('fifo_remaining_qty'),$m->getElement('buy_value')]);
        })->type('money')->caption('Hold Amount');

        $model->addExpression('cmp')->set(function($m,$q){
            $c = $m->add('Model_DailyBhav')
                ->addCondition('company_id',$m->getElement('company_id'))
                ->setOrder('created_at','desc')
                ->setLimit(1)
                ;
            return $q->expr('IFNULL([0],0)',[$c->fieldQuery('last')]);
        })->type('money')->caption('Current Value (CMP)');

        $model->addExpression('cmp_amount')->set(function($m,$q){
            return $q->expr('([0] * [1])',[$m->getElement('fifo_remaining_qty'),$m->getElement('cmp')]);
        })->type('money')->caption('Current Value Amount');

        $model->addExpression('pl')->set(function($m,$q){
            return $q->expr('([0]-[1])',[$m->getElement('cmp_amount'),$m->getElement('fifo_remaining_amount')]);
        })->type('money')->caption('Profit/<span style="color:red;"> Loss</span>');

        $model->addExpression('gain')->set(function($m,$q){
            return $q->expr('(([0]/[1])*100)',[$m->getElement('pl'),$m->getElement('fifo_remaining_amount')]);
        })->type('money')->caption('Gain %');

        $model->addCondition('fifo_remaining_qty','>',0);
        $model->addCondition('client_id',$client_id);
        
        $model->addCondition('created_at','>=',$this->financial_start_date);
        $model->addCondition('created_at','<',$this->financial_end_date);

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
        // $on_date = $this->app->stickyGET('date');
        // if(!$on_date) $on_date = $this->app->today;

        // $strtotime = strtotime($on_date);
        // $fin_start_date = (date('m',$strtotime) < '04') ? date('Y-04-01',strtotime('-1 year',$strtotime)) : date('Y-04-01',$strtotime);
        // $fin_end_date = date('Y-03-t',strtotime('+1 year',strtotime($fin_start_date)));

        $fin_start_date = $this->financial_start_date;
        $fin_end_date = $this->financial_end_date;

        if($client_id){
            $tra = $this->add('Model_FifoSell');
            $tra->addExpression('total_sell_amount')->set('sum(sell_price * sell_qty)')->type('money');
            $tra->addExpression('total_buy_amount')->set('sum(buy_price * sell_qty)')->type('money');
            $tra->addExpression('LTCP')->set(function($m,$q){
                return $q->expr('IFNULL((([total_sell_amount]-[total_buy_amount])/[total_buy_amount])*100,0)',
                        [
                        'total_sell_amount'=>$m->getElement('total_sell_amount'),
                        'total_buy_amount'=>$m->getElement('total_buy_amount')
                    ]);
            })->type('money')->caption('LTGC %');
            $tra->addCondition('client_id',$client_id)
                ->addCondition('sell_date','>=',$fin_start_date)
                ->addCondition('sell_date','<',$this->app->nextDate($fin_end_date))
                ->addCondition('sell_duration','>',365)
                ;
            $tra->_dsql()->group('company_id');

            $grid = $this->add('Grid');
            $grid->setModel($tra,['client','company','total_buy_amount','total_sell_amount','LTCP']);
            // $grid->addPaginator($ipp=50);

            if($tra->count()->getOne()){
                $grid->add('misc/Export');
                $grid->addTotals(['total_sell_amount','total_buy_amount']);
            }

            $grid->add('VirtualPage')
                ->addColumn('detail')
                ->set(function($page)use($client_id,$fin_start_date,$fin_end_date){
                    $id = $_GET[$page->short_name.'_id'];
                    $old_model = $this->add('Model_FifoSell')->load($id);

                    $m = $page->add('Model_FifoSell');
                    $m->addExpression('date')->set(function($m,$q){
                        return $q->expr('Date_Format([0],"%d %M %Y")',[$m->getElement('tran_date')]);
                    });
                    $m->addCondition('client_id',$old_model['client_id']);
                    $m->addCondition('company_id',$old_model['company_id']);
                    $m->addCondition('sell_date','<',$fin_start_date);
                    $m->addCondition('sell_duration','>',365);

                    $g = $page->add('Grid');
                    $g->setModel($m);
                    $g->addTotals(['buy_amount','sell_amount']);
            });
        }else{
            $m = $this->add('Model_ClientData',['fin_start_date'=>$this->financial_start_date,'fin_end_date'=>$this->financial_end_date]);
            $grid = $this->add('Grid');
            $grid->setModel($m,['name','long_term_capital_gain']);
        }

    }

    function addShortTermReport($client_id){
        // $on_date = $this->app->stickyGET('date');
        // if(!$on_date) $on_date = $this->app->today;

        // $strtotime = strtotime($on_date);
        // $fin_start_date = (date('m',$strtotime) < '04') ? date('Y-04-01',strtotime('-1 year',$strtotime)) : date('Y-04-01',$strtotime);
        // $fin_end_date = date('Y-03-t',strtotime('+1 year',strtotime($fin_start_date)));

        $fin_start_date = $this->financial_start_date;
        $fin_end_date = $this->financial_end_date;
        
        if($client_id){

            $tra = $this->add('Model_FifoSell');
            $tra->addExpression('total_sell_amount')->set('sum(sell_price * sell_qty)')->type('money');
            $tra->addExpression('total_buy_amount')->set('sum(buy_price * sell_qty)')->type('money');
            $tra->addExpression('STCP')->set(function($m,$q){
                return $q->expr('((([total_sell_amount]-[total_buy_amount])/[total_buy_amount])*100)',
                        [
                        'total_sell_amount'=>$m->getElement('total_sell_amount'),
                        'total_buy_amount'=>$m->getElement('total_buy_amount')
                    ]);
            })->type('money')->caption('STGC %');
            $tra->addCondition('client_id',$client_id)
                ->addCondition('sell_date','>=',$fin_start_date)
                ->addCondition('sell_date','<',$this->app->nextDate($fin_end_date))
                ->addCondition('sell_duration','<=',365)
                ;
            $tra->_dsql()->group('company_id');

            $grid = $this->add('Grid');
            $grid->setModel($tra,['client','company','total_buy_amount','total_sell_amount','STCP']);
            // $grid->addPaginator($ipp=50);

            if($tra->count()->getOne()){
                $grid->add('misc/Export');
                $grid->addTotals(['total_sell_amount','total_buy_amount']);
            }

            $grid->add('VirtualPage')
                ->addColumn('detail')
                ->set(function($page)use($client_id,$fin_start_date,$fin_end_date){
                    $id = $_GET[$page->short_name.'_id'];
                    $old_model = $this->add('Model_FifoSell')->load($id);

                    $m = $page->add('Model_FifoSell');
                    $m->addExpression('date')->set(function($m,$q){
                        return $q->expr('Date_Format([0],"%d %M %Y")',[$m->getElement('tran_date')]);
                    });
                    $m->addCondition('client_id',$old_model['client_id']);
                    $m->addCondition('company_id',$old_model['company_id']);
                    $m->addCondition('sell_date','>=',$fin_start_date);
                    $m->addCondition('sell_date','<',$this->app->nextDate($fin_end_date));
                    $m->addCondition('sell_duration','<',365);

                    $g = $page->add('Grid');
                    $g->setModel($m);
                    $g->addTotals(['buy_amount','sell_amount']);
            });  
        }else{
            $m = $this->add('Model_ClientData',['fin_start_date'=>$this->financial_start_date,'fin_end_date'=>$this->financial_end_date]);
            // if($client_id)
            //     $m->addCondition('id',$client_id);
            $grid = $this->add('Grid');
            $grid->setModel($m,['name','short_term_capital_gain']);
        }

    }

    function getFinancialYear(){
        $startDate = '1970-04-01';
        $endDate = $this->app->today;

        $prefix = '';
        $ts1 = strtotime($startDate);
        $ts2 = strtotime($endDate);

        $year1 = date('Y', $ts1);
        $year2 = date('Y', $ts2);

        $month1 = date('m', $ts1);
        $month2 = date('m', $ts2);

        //get months
        $diff = (($year2 - $year1) * 12) + ($month2 - $month1);

        /**
         * if end month is greater than april, consider the next FY
         * else dont consider the next FY
         */
        $total_years = ($month2 > 4)?ceil($diff/12):floor($diff/12);

        $fy = array();

        while($total_years >= 0) {

            $prevyear = $year1 - 1;

            //We dont need 20 of 20** (like 2014)
            $fy[$prevyear.'-'.$year1] = $prevyear.'-'.$year1;
            // $fy[] = $prefix.substr($prevyear,-2).'-'.substr($year1,-2);

            $year1 += 1;

            $total_years--;
        }
        /**
         * If start month is greater than or equal to april, 
         * remove the first element
         */
        if($month1 >= 4) {
            unset($fy[0]);
        }
        /* Concatenate the array with ',' */
        return array_reverse( $fy);
        // return implode(',',$fy);
    }

}
