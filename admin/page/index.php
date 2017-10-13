<?php

class page_index extends Page {

    public $title='Dashboard';

    function init() {
        parent::init();

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
