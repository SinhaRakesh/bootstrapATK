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
        $col = $form->add('Columns');
        $col1 = $col->addColumn(8);
        $col2 = $col->addColumn(4);
        // $template = $this->add('GiTemplate');
        // $template->loadTemplateFromString('{$Content}<div class="alert alert-danger">{$form_buttons}</div>');
        // $form->setLayout($template);

        $col1->addField('DatePicker','date')->set($on_date);
        $col2->addSubmit('Filter');

        $m = $this->add('Model_ClientData',['on_date'=>$on_date]);

        $m->addCondition('buy_value','>',0);
        $m->getElement('today_buying_value')->caption('Share Purchase (INR)');
        $m->getElement('today_sell_value')->caption('Share Sold (INR)');
        $m->getElement('buy_value')->caption('Share Value (INR)');
        $m->getElement('buy_current_value')->caption('Current Value (INR)');
        $m->getElement('profit')->caption('Profit/<p style="color:red;">Loss</p>');
        
        $grid = $this->add('Grid');
        $grid->setModel($m,['name','today_buying_value','today_sell_value','buy_value','buy_current_value','net_investment','buy_current_value','profit','ror']);
        $grid->addPaginator($ipp=50);
        $grid->addHook('formatRow',function($g){
            $g->current_row_html['profit'] = round(abs($g->model['profit']),2);
            $g->current_row_html['ror'] = round(abs($g->model['ror']),2);
        });
        $grid->addQuickSearch(['name'],['placeholder'=>'Search By Client']);
        $grid->add('View',null,'quick_search')->set('Data as on date : '.date('d M Y',strtotime($on_date)));

        // $grid->addSno();

        $grid->add('VirtualPage')
        ->addColumn('detail','Report Transaction',['title'=>'Report Transaction'])
        ->set(function($page){
            $id = $_GET[$page->short_name.'_id'];

            $m = $page->add('Model_Transaction',['table_alias'=>'dt'])
                ->addCondition('client_id',$id)
                // ->addCondition('created_at','>=',$this->on_date)
                ->addCondition('created_at',$this->on_date)
                ->addCondition('fifo_remaining_qty','>',0)
                ;
            $g = $page->add('Grid');
            $g->setModel($m,['company','buy_qty','buy_value','buy_amount','sell_qty','sell_value','sell_amount','created_at']);
            $g->addPaginator($ipp=50);
            
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
