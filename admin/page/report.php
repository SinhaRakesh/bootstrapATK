<?php

class page_report extends Page {

    public $title='Report';

    function init() {
        parent::init();

        $this->app->stickyGET('type');
        $this->app->stickyGET('client');

        $form = $this->add('Form');
        $fld_client = $form->addField('DropDown','client');
        $fld_client->setModel('Client');
        $fld_client->setEmptyText('All');

        $fld_type = $form->addField('DropDown','report_type');
        $fld_type->setValueList([
                'stock_report'=>'Stock Report (till date)',
                'short_term'=>'Short Term Gain Report',
                'long_term'=>'Long Term Gain Report'
            ]);

        $form->addSubmit('Generate Report');

        $client_data = $this->add('Model_ClientData');
        if($c_id = $_GET['client'])
            $client_data->addCondition('id',$c_id);
        
        $field_to_show = ['name','client_code'];

        if($r_type = $_GET['type']){
            if($r_type == "stock_report"){
                $field_to_show = ['name','client_code'];
            }elseif($r_type == "short_term"){
                $field_to_show = ['name','client_code','short_total_buy_amount','short_total_sell_amount','short_term_capital_gain'];
            }elseif($r_type == "long_term"){
                $field_to_show = ['name','client_code','long_total_buy_amount','long_total_sell_amount','long_term_capital_gain'];
            }
        }

        $grid = $this->add('Grid');
        $grid->setModel($client_data,$field_to_show);
        $grid->addPaginator($ipp=25);

        if($form->isSubmitted()){
            $grid->js()->reload(['client'=>$form['client'],'type'=>$form['report_type']])->execute();
        }


    }
}
