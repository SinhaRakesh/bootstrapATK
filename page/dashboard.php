<?php

class page_dashboard extends basePage{

    function page_index(){
		// if(!$this->app->auth->model->loaded()){
		// 	$this->app->redirect($this->app->url('login'));
		// }

		// if($v_user_id = $this->app->recall('verified_user')){
		// 	$this->add('View_Success')->set('your account is verified successfully'.$v_user_id );
		// 	$this->app->forget('verified_user');
		// }
        $user_name = $this->app->auth->model->id;
        // $this->add('View_Info')->set($user_name);

        $section = $_GET['section'];
        // $view = $this->add('View_Info')->set(rand(9,99));
        // $this->js('click',$view->js()->reload(['type'=>$this->js()->_selectorThis()->attr('data-menu')]))->_selector('.apartment-menu a');

    }
}