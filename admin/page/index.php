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

        if($form->isSubmitted()){
            $grid->js()->reload(['date'=>$form['date']])->execute();
        }

        // $this->add('View_Box')
        //     ->setHTML('Welcome to your new Web App Project. Get started by opening '.
        //         '<b>admin/page/index.php</b> file in your text editor and '.
        //         '<a href="http://book.agiletoolkit.org/" target="_blank">Reading '.
        //         'the documentation</a>.');        

        // $form = $this->add('Form');
        // $form->addField('name');
        // $form->addField('DatePicker','dob');
        // $form->addSubmit('save');


        // $fo = [
        //     "autoOpen"=>false,
        //     "modal" => true,
        //     "width" => 500,
        //     "title" => "Some title",
        //     "hide"=> [
        //             "effect"=> "scale",
        //             "easing"=> "easeInBack",
        //         ]
        // ];

        // $crud = $this->add('CRUD');
        // $crud->setModel('Outbox');
        // $crud->grid->addQuickSearch(['name']);

        // $crud = $this->add('CRUD');
        // $crud->setModel('DailyBhav');

        // $crud = $this->add('CRUD');
        // $crud->setModel('Client'); 

        // $this->add('View_DashboardCount');
    }
}
