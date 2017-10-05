<?php
class page_login extends basePage{

    function init(){
        parent::init();

        if($this->app->auth->isLoggedin()){
            $this->add('View_Info')->addClass('card card-signup')->set('Already Logged IN ');
            $this->add('Button')->addClass('btn btn-danger btn-block')->set('Logout')->js('click')->univ()->location($this->app->url('logout'));
            return;
        }

		$form = $this->app->layout->add('Form',null,null,['form/empty']);
        $login_layout = $form->add('View',null,null,['form/login']);
        $form->setLayout($login_layout);
        $form->addClass('material_form');

        $form->addField('line','username',"")->validate('required');
        $form->addField('password','password',"")->validate('required');

		$form->js('click',$form->js()->submit())->_selector('.apartment-login-btn');

        if($form->isSubmitted()){
            if($id = $this->app->auth->verifyCredentials($form['username'],$form['password'])){
                $this->app->auth->loginByID($id);
                $this->app->auth->model->updateLastDate();
                
                $this->app->redirect($this->app->url('dashboard'));
            }
            $form->getElement('password')->displayFieldError('Incorrect login information');
        }
    }

    // function defaultTemplate(){
    	// return ['page/login'];
    // }
}