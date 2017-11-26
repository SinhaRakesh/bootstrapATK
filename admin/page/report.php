<?php

class page_report extends Page {

    public $title='Report';
    public $allStockData = [];

    function init() {
        parent::init();

        $type = $this->app->stickyGET('type');
        $c_id = $this->app->stickyGET('client');
        $f_year = $this->app->stickyGET('financial_year');
        // $type = $_GET['type'];
        // $c_id = $_GET['client'];
        // $f_year = $_GET['financial_year'];

        $form = $this->add('Form',null,null,['form/horizontal']);
        $fld_client = $form->addField('autocomplete/Basic','client');
        $fld_client->setModel('Client');

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
            if($c_id){
                $this->addStockReport($c_id,$is_single = 1);
            }else{

                $this->all_export = $this->add('Button')->set('Export All Client CSV File')->setStyle('margin-top','10px');
                $this->all_export->js("click")->univ()->location($this->api->url(null, array($this->all_export->name => "1")));

                $client = $this->add('Model_Client');
                $client->addCondition('is_active',true);
                foreach ($client as $c) {
                    $this->addStockReport($c->id,0);
                }
            }

        }elseif($type == "short_term"){
            $this->addShortTermReport($c_id);
            // $field_to_show = ['name','client_code','short_total_buy_amount','short_total_sell_amount','short_term_capital_gain'];
        }elseif($type == "long_term"){
            $this->addLongTermReport($c_id);
            // $field_to_show = ['name','client_code','long_total_buy_amount','long_total_sell_amount','long_term_capital_gain'];
        }

        if(isset($this->all_export) && $_GET[$this->all_export->name]){
            $this->exportAllStock();
        }

        if($form->isSubmitted()){
            $this->js()->reload(['client'=>$form['client'],'type'=>$form['report_type'],'financial_year'=>$form['financial_year']])->execute();
        }

    }


    function addStockReport($client_id,$is_single=1,$return_data=0){
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
        if(!$model->count()->getOne()) return;

        $grid = $this->add('Grid');
        $grid->setModel($model,['client','date','company','buy_qty','buy_value','buy_amount','fifo_remaining_qty','fifo_remaining_amount','cmp','cmp_amount','pl','gain']);

        if($is_single)
            $grid->add('misc/Export');

        $grid->addTotals(['buy_amount','fifo_remaining_amount','cmp_amount']);
        // $grid->addPaginator($ipp=30);
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

        // if($client_id){
            $tra = $this->add('Model_FifoSell');
            $tra->addExpression('total_sell_amount')->set('sum(sell_price * sell_qty)')->type('money');
            $tra->addExpression('total_buy_amount')->set('sum(buy_price * sell_qty)')->type('money');
            
            $tra->addExpression('long_term_amount')->set(function($m,$q){
                return $q->expr('([total_sell_amount]-[total_buy_amount])',
                        [
                        'total_sell_amount'=>$m->getElement('total_sell_amount'),
                        'total_buy_amount'=>$m->getElement('total_buy_amount')
                    ]);
            })->type('money')->caption('Long Term Capital Gain');

            $tra->addExpression('LTCP')->set(function($m,$q){
                return $q->expr('IFNULL((([total_sell_amount]-[total_buy_amount])/[total_buy_amount])*100,0)',
                        [
                        'total_sell_amount'=>$m->getElement('total_sell_amount'),
                        'total_buy_amount'=>$m->getElement('total_buy_amount')
                    ]);
            })->type('money')->caption('LTCG %');

            if($client_id)
                $tra->addCondition('client_id',$client_id);

            $tra->addCondition('sell_date','>=',$fin_start_date)
                ->addCondition('sell_date','<',$this->app->nextDate($fin_end_date))
                ->addCondition('sell_duration','>',365)
                ;
            if(!$client_id)
                $tra->_dsql()->group('client_id');
            else
                $tra->_dsql()->group('company_id');

            $grid = $this->add('Grid');
            $grid->setModel($tra,['client','company','total_buy_amount','total_sell_amount','long_term_amount','LTCP']);
            // $grid->addPaginator($ipp=50);

            if($tra->count()->getOne()){
                // $ex_btn = $grid->addButton('Export CSV');
                // $ex_btn->js("click")->univ()->location($this->api->url(null, array($ex_btn->name => "1")));

                $grid->addTotals(['total_sell_amount','total_buy_amount','long_term_amount','LTCP']);

                // if($_GET[$ex_btn->name] == "1"){
                //     $this->app->stickyForget($ex_btn->name);
                //     $this->export('long');
                // }    
                $grid->add('misc/Export');
            }

            $grid->addHook('formatRow',function($g){
                $g->current_row_html['LTCP'] = round(abs($g->model['LTCP']),2);
                $g->current_row_html['long_term_amount'] = round(abs($g->model['long_term_amount']),2);
            });

            $grid->addHook('formatTotalsRow',function($g){
               $g->current_row_html['LTCP'] = "<strong style='color:red;'>".abs(round((($g->totals['total_sell_amount'] - $g->totals['total_buy_amount'])/$g->totals['total_buy_amount'])*100,2))."</strong>";
               $g->current_row_html['long_term_amount'] = abs(round((($g->totals['total_sell_amount'] - $g->totals['total_buy_amount'])),2));
            });

            $grid->add('VirtualPage')
                ->addColumn('detail','Detail')
                ->set(function($page)use($fin_start_date,$fin_end_date){
                    $id = $_GET[$page->short_name.'_id'];
                    $old_model = $this->add('Model_FifoSell')->load($id);
                        
                    $m = $page->add('Model_FifoSellProfit');
                    $m->addExpression('date')->set(function($m,$q){
                        return $q->expr('Date_Format([0],"%d %M %Y")',[$m->getElement('tran_date')]);
                    });
                    $m->addCondition('client_id',$old_model['client_id']);
                    $m->addCondition('company_id',$old_model['company_id']);
                    $m->addCondition('sell_date','>=',$fin_start_date);
                    $m->addCondition('sell_date','<',$this->app->nextDate($fin_end_date));
                    $m->addCondition('sell_duration','>',365);

                    $g = $page->add('Grid');
                    $g->add('View',null,'quick_search')->setHtml('<strong>Client:</strong> '.$old_model['client']);

                    $g->setModel($m,['company','date','buy_qty','buy_price','buy_amount','sell_date_only','sell_qty','sell_price','sell_amount','pl','gain']);
                    $g->addTotals(['buy_amount','sell_amount','pl','gain']);

                    $g->addHook('formatRow',function($g){
                        if($g->model['pl'] < 0 ){
                            $g->current_row_html['pl'] = abs($g->model['pl']);
                            $g->current_row_html['gain'] = round(abs($g->model['gain']),2);
                        }
                    });

                    $g->addHook('formatTotalsRow',function($g){
                        $g->current_row_html['pl'] = abs($g->totals['pl']);
                        $g->current_row['gain'] = abs(round((($g->totals['sell_amount'] - $g->totals['buy_amount'])/$g->totals['buy_amount']*100),2));
                    });
            });
        // }
        // else{
        //     $m = $this->add('Model_ClientData',['fin_start_date'=>$this->financial_start_date,'fin_end_date'=>$this->financial_end_date]);
        //     $m->addExpression('total_buy_amount')->set(function($m,$q){

        //         $tra = $m->add('Model_FifoSell',['table_alias'=>'stcgtotalbuy']);
        //         $tra->addCondition('client_id',$m->getElement('id'))
        //             ->addCondition('sell_date','<',$this->financial_start_date)
        //             ;
        //         return $q->expr('[0]',[$tra->sum('fifo_buy_amount')]);
        //     })->type('money');

        //     $m->addExpression('total_sell_amount')->set(function($m,$q){
        //         $tra = $m->add('Model_FifoSell',['table_alias'=>'stcgtotalsell']);
        //         $tra->addCondition('client_id',$m->getElement('id'))
        //             ->addCondition('sell_date','<',$this->financial_start_date)
        //             ;
        //         return $q->expr('[0]',[$tra->sum('fifo_sell_amount')]);
        //     })->type('money');

        //     $m->setOrder('long_term_capital_gain','desc');
        //     $grid = $this->add('Grid');
        //     $grid->setModel($m,['name','total_buy_amount','total_sell_amount','long_term_capital_gain']);
        // }

    }

    function addShortTermReport($client_id){

        $fin_start_date = $this->financial_start_date;
        $fin_end_date = $this->financial_end_date;
        
            $tra = $this->add('Model_FifoSell');
            $tra->addExpression('total_sell_amount')->set('sum(sell_price * sell_qty)')->type('money');
            $tra->addExpression('total_buy_amount')->set('sum(buy_price * sell_qty)')->type('money');
            $tra->addExpression('short_term_amount')->set(function($m,$q){
                return $q->expr('([total_sell_amount]-[total_buy_amount])',
                        [
                        'total_sell_amount'=>$m->getElement('total_sell_amount'),
                        'total_buy_amount'=>$m->getElement('total_buy_amount')
                    ]);
            })->type('Money')->caption('Short Term Capital Gain');

            $tra->addExpression('STCP')->set(function($m,$q){
                return $q->expr('((([total_sell_amount]-[total_buy_amount])/[total_buy_amount])*100)',
                        [
                        'total_sell_amount'=>$m->getElement('total_sell_amount'),
                        'total_buy_amount'=>$m->getElement('total_buy_amount')
                    ]);
            })->type('money')->caption('STCG %');

            if($client_id)
                $tra->addCondition('client_id',$client_id);

            $tra
                ->addCondition('sell_date','>=',$fin_start_date)
                ->addCondition('sell_date','<',$this->app->nextDate($fin_end_date))
                ->addCondition('sell_duration','<=',365)
                ;

            if($client_id)
                $tra->_dsql()->group('company_id');
            else
                $tra->_dsql()->group('client_id');

            $grid = $this->add('Grid');
            $grid->setModel($tra,['client','company','total_buy_amount','total_sell_amount','short_term_amount','STCP']);
            // $grid->addPaginator($ipp=50);

            $grid->addHook('formatRow',function($g){
                $g->current_row_html['STCP'] = round(abs($g->model['STCP']),2);  
                $g->current_row_html['short_term_amount'] = round(abs($g->model['short_term_amount']),2);  
            });

            $grid->addHook('formatTotalsRow',function($g){
               $g->current_row_html['STCP'] = abs(round((($g->totals['total_sell_amount'] - $g->totals['total_buy_amount'])/$g->totals['total_buy_amount'])*100,2));
               $g->current_row_html['short_term_amount'] = abs(round(($g->totals['total_sell_amount'] - $g->totals['total_buy_amount']),2));
            });

            if($tra->count()->getOne()){
                $ex_btn = $grid->addButton('Export CSV');
                $ex_btn->js("click")->univ()->location($this->api->url(null, array($ex_btn->name => "1")));

                $grid->addTotals(['total_sell_amount','total_buy_amount','short_term_amount','STCP']);
                if($_GET[$ex_btn->name] == "1"){
                    $this->app->stickyForget($ex_btn->name);
                    $this->export('short');
                }
            }


            $grid->add('VirtualPage')
                ->addColumn('detail','Detail')
                ->set(function($page)use($fin_start_date,$fin_end_date){
                    $id = $_GET[$page->short_name.'_id'];
                    $old_model = $this->add('Model_FifoSell')->load($id);

                    $m = $page->add('Model_FifoSellProfit');
                    $m->addExpression('date')->set(function($m,$q){
                        return $q->expr('Date_Format([0],"%d %M %Y")',[$m->getElement('tran_date')]);
                    })->caption('Buy Date');

                    $m->addCondition('client_id',$old_model['client_id']);
                    $m->addCondition('company_id',$old_model['company_id']);
                    $m->addCondition('sell_date','>=',$fin_start_date);
                    $m->addCondition('sell_date','<',$this->app->nextDate($fin_end_date));
                    $m->addCondition('sell_duration','<',365);

                    $g = $page->add('Grid');
                    $g->add('View',null,'quick_search')->setHtml('<strong>Client:</strong> '.$old_model['client']);
                    $g->setModel($m,['company','date','buy_qty','buy_price','buy_amount','sell_date_only','sell_qty','sell_price','sell_amount','pl','gain']);
                    $g->addTotals(['buy_amount','sell_amount','pl','gain']);

                    $g->addHook('formatRow',function($g){
                        if($g->model['pl'] < 0 ){
                            $g->current_row_html['pl'] = abs($g->model['pl']);
                            $g->current_row_html['gain'] = abs(round(abs($g->model['gain']),2));
                        }
                    });

                    $g->addHook('formatTotalsRow',function($g){
                        $g->current_row_html['pl'] = abs($g->totals['pl']);
                        $g->current_row['gain'] = abs(round((($g->totals['sell_amount'] - $g->totals['buy_amount'])/$g->totals['buy_amount']*100),2));
                    });

            });  

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

    function export($type){
        if($_GET['client']){
            $c_model = $this->add('Model_Client')->load($_GET['client']);
            $c_name = $c_model['name'];
        }else{
            $c_name = "All";
        }


        $output_type = "text/csv";
        $output_disposition = "attachment";
        $output = "Client,Stock,Buy Date,Buy Qty, Buy Price,Buy Amount, Sell Date,Sell Qty,Sell Price,Sell Amount, Profit\Loss, Gain (%)"."\r\n";
        $data = [];

        $m = $this->add('Model_FifoSellProfit');
        $m->addExpression('date')->set(function($m,$q){
            return $q->expr('Date_Format([0],"%d %M %Y")',[$m->getElement('tran_date')]);
        })->caption('Buy Date');
        
        if($type == "short"){
            $output_filename = str_replace(" ", "_",$c_name)."-ShortTerm-".$_GET['financial_year']."csv";

            if($_GET['client'])
                $m->addCondition('client_id',$_GET['client']);
            $m->addCondition('sell_date','>=',$this->financial_start_date);
            $m->addCondition('sell_date','<',$this->app->nextDate($this->financial_end_date));
            $m->addCondition('sell_duration','<=',365);
        }
        
        if($type == "long"){
            $output_filename = str_replace(" ", "_",$c_name)."-LongTerm-".$_GET['financial_year'].".csv";
            
            if($_GET['client'])
                $m->addCondition('client_id',$_GET['client']);

            $m->addCondition('sell_date','>=',$this->financial_start_date);
            $m->addCondition('sell_date','<',$this->app->nextDate($this->financial_end_date));
            $m->addCondition('sell_duration','>',365);
        }
        
        $totals = [];
        foreach ($m as $record) {
            $output .= $record['client'].",".$record['company'].",".$record['date'].",".$record['buy_qty'].",".$record['buy_price'].",".$record['buy_amount'].",".$record['sell_date_only'].",".$record['sell_qty'].",".$record['sell_price'].",".$record['sell_amount'].",".$record['pl'].",".round($record['gain'],2)."\n";
            $totals['buy_amount'] += $record['buy_amount'];
            $totals['sell_amount'] += $record['sell_amount'];
        }
        
        $totals['profit'] = $totals['sell_amount'] - $totals['buy_amount'];
        if($totals['buy_amount'] > 0)
            $totals['gain'] = round((($totals['profit'] / $totals['buy_amount'])*100),2);
        
        $output .= " ".","." ".","." ".","." ".","." ".",".$totals['buy_amount'].","." ".","." ".","." ".",".$totals['sell_amount'].",".$totals['profit'].",".round($totals['gain'],2)."\n";

        header("Content-type: " . $output_type);
        header("Content-disposition: " . $output_disposition . "; filename=\"" . $output_filename . "\"");
        header("Content-Length: " . strlen($output));
        print $output;
        exit;

    }


    function exportAllStock(){
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
        $model->addCondition('created_at','>=',$this->financial_start_date);
        $model->addCondition('created_at','<',$this->financial_end_date);
        $model->setOrder('created_at','desc');

        $record = $model->getRows();
        
        // // [client_id] = ['0'=[],'1'=[]]        
        $client_data = [];
        foreach ($record as $table_id=>$data) {
            if(!isset($client_data[$data['client_id']])){
                $client_data[$data['client_id']] = [];
                $client_data[$data['client_id']]['totals'] = ['buy_amount'=>0,'fifo_remaining_amount'=>0,'cmp_amount'=>0];
            } 

            $t = [
                    'client'=>$data['client'],
                    'date'=>$data['date'],
                    'company'=>$data['company'],
                    'buy_qty'=>$data['buy_qty'],
                    'buy_value'=>$data['buy_value'],
                    'buy_amount'=>$data['buy_amount'],
                    'fifo_remaining_qty'=>$data['fifo_remaining_qty'],
                    'fifo_remaining_amount'=>$data['fifo_remaining_amount'],
                    'cmp'=>$data['cmp'],
                    'cmp_amount'=>$data['cmp_amount'],
                    'pl'=>$data['pl'],
                    'gain'=>$data['gain']
                ];
            $client_data[$data['client_id']][] = $t;

            $client_data[$data['client_id']]['totals'] = [
                                    'buy_amount'=> $data['buy_amount']+ $client_data[$data['client_id']]['totals']['buy_amount'],
                                    'fifo_remaining_amount'=>  $data['fifo_remaining_amount']+ $client_data[$data['client_id']]['totals']['fifo_remaining_amount'],
                                    'cmp_amount'=>  $data['cmp_amount']+ $client_data[$data['client_id']]['totals']['cmp_amount'],
                            ];
        }


        $output_type = "text/csv";
        $output_disposition = "attachment";
        $output = "Client, Date, Company, Buy Qty, Buy Value, Buy Amount, Hold Qty, Hold Amount, Current Value (CMP), Current Value Amount , Profit / Loss, Gain (%)"."\r\n";

        foreach ($client_data as $c_id => $values) {
            foreach ($values as $key => $value) {
                if($key != "totals")
                    $output .= $value['client'].",".$value['date'].",".$value['company'].",".$value['buy_qty'].",".$value['buy_value'].",".$value['buy_amount'].",".$value['fifo_remaining_qty'].",".$value['fifo_remaining_amount'].",".$value['cmp'].",".$value['cmp_amount'].",".$value['pl'].",".round($value['gain'],2)."\n\r";
                }
            $value = $values['totals'];
            $output .= "Total,".",".",".",".",".$value['buy_amount'].",".",".$value['fifo_remaining_amount'].",".",".$value['cmp_amount'].",".",".","."\n\r";
            $output .= "".",".",".",".",".",".",".",".",".",".",".","."\n\r";
                
        }

        trim($output,",");
        $output_filename = "All-client-stock-report-of-".$_GET['financial_year'].".csv";
        header("Content-type: " . $output_type);
        header("Content-disposition: " . $output_disposition . "; filename=\"" . $output_filename . "\"");
        header("Content-Length: " . strlen($output));
        print $output;
        exit;
    }

}
