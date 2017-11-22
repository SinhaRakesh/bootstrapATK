<?php

class page_index extends Page {

    public $title='Todays Buy Report';
    public $on_date;
    function init() {
        parent::init();

        $on_date = $this->app->stickyGET('date');
        if(!$on_date) $on_date = $this->app->today;

        $this->on_date = $on_date;
        $on_date_read = date('d M Y',strtotime($on_date));

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

        $m->getElement('today_sell_value')->caption('Share Sold (INR) on <br/>'.$on_date_read);
        $m->getElement('today_buying_value')->caption('Share Purchase (INR) on <br/>'.$on_date_read);

        $m->getElement('buy_value')->caption('Share Value (INR) as on <br/>'.$on_date_read);
        $m->getElement('buy_current_value')->caption('Current Value (INR)');
        $m->getElement('profit')->caption('Profit/<p style="color:red;">Loss</p>');
        
        $grid = $this->add('Grid');
        $grid->addClass('rgrid');
        $grid->setModel($m,['name','client_code','today_buying_value','today_sell_value','buy_value','buy_current_value','current_pl','net_investment','profit','ror']);
        $grid->addPaginator($ipp=50);
        $grid->addHook('formatRow',function($g){
            $g->current_row_html['profit'] = round(abs($g->model['profit']),2);
            $g->current_row_html['ror'] = round(abs($g->model['ror']),2);
        });
        $grid->addQuickSearch(['name','client_code'],['placeholder'=>'Search By Client Name OR ID']);
        $grid->add('View',null,'quick_search')->set('Data as on date : '.$on_date_read);

        // $grid->addSno();

        $grid->add('VirtualPage')
        ->addColumn('detail','Transaction Report')
        ->set(function($page){
            $id = $_GET[$page->short_name.'_id'];

            $m = $page->add('Model_Transaction',['table_alias'=>'dt'])
                ->addCondition('client_id',$id)
                ->addCondition('created_at','>=',$this->on_date)
                ->addCondition('created_at','<',$this->app->nextDate($this->on_date))
                ->addCondition('net_qty','>',0)
                ;

            $g = $page->add('Grid');
            $g->setModel($m,['date_only','company','buy_qty','buy_value','buy_amount','sell_qty','sell_value','sell_amount']);
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
