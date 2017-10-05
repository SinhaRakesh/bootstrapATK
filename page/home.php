<?php
class page_home extends basePage{

    function init(){

        parent::init();
    	
        $this->app->stickyGET('param1');
        $this->app->stickyGET('param2');
        $this->add('View_Info')->set($_GET['param1']);
        $this->add('View_Info')->set($_GET['param2']);
        $this->add('View_Info')->set($_GET['param3']);
        // $crud = $this->add('CRUD');
        // $crud->setModel('User');

        // $this->app->layout->template->tryDel('banner_section');

        // $this->app->stickyGET('reload');
        // $this->app->layout->add('View',null,'banner_content')->setHtml('<div class="brand"> hello</div>');
    	// $v = $this->add('View')->set("hello =".$_GET['param1']." = ".$_GET['param2']." reload = ".$_GET['reload']." = rand ".rand(90,100));
    	// $this->add('Button')->js('click',$v->js()->reload(['reload'=>rand(111,9999)]));

        $form = $this->add('Form');
        $f_c = $form->addField('DropDown','country');
        $f_s = $form->addField('DropDown','state');

        $state = $this->add('Model_State');
        $country = $this->add('Model_Country');
        $f_c->setModel($country);
        if($this->app->stickyGET('country_id'))
            $state->addCondition('country_id',$_GET['country_id']);
        
        $f_s->setModel($state);

        $f_c->js('change',$form->js()->atk4_form('reloadField','state',[$this->app->url(null,['cut_object'=>$f_s->name]),'country_id'=>$f_c->js()->val()]));
    }
}