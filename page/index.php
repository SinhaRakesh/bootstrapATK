<?php
class page_index extends basePage
{
    function init()
    {
        parent::init();

        $this->app->layout->template->trySet('title','`R`-Apartment.');
        $this->app->layout->template->trySet('subtitle','your apartment buddy.');
        
        // // setting up material class
        
        // $this->add('H1')->set('Apartment');
        // $this->add('P')->set('This is a frontend of your web app but it does not have anything yet.');

        // // $this->add('P')->set('Open frontend/page/index.php file in your text editor and follow documentation.');

        // $this->add('Button')
        //     ->set(array('Agile Toolkit Book', 'icon'=>'book', 'swatch'=>'green'))
        //     ->link('http://book.agiletoolkit.org/app/frontend.html');
        
        // $form = $this->add('Form',null,null,['form/stackedWithFloatingLabel']);
        // $form->addField("name");
        // $form->addSubmit('Save');
        // if($form->isSubmitted()){
        //     $form->js()->univ()->successMessage('saved')->execute();
        // }

    }
}
