<?php

class page_setting extends basePage{

    function init(){
        parent::init();
 		
        $apartment_id = 25;

 		$tabs = $this->add('Tabs');
 		$ac_tab = $tabs->addTab('Apartment Config');

 		$ac_tabs = $ac_tab->add('Tabs');
 		
 		$basic_tab = $ac_tabs->addTab('Basic');
 		$fees_tab = $ac_tabs->addTab('Fees Management');
 		$penalty_tab = $ac_tabs->addTab('Penalty Management');
 		$pass_tab = $ac_tabs->addTab('Change Passowrd');

 		// basic informtion
 		$section = $basic_tab->add('View')->addClass('container')->setStyle('width','80%');
 		$apartment_model = $section->add('Model_Apartment');
 		$apartment_model->load($apartment_id);
 		$a_basic_form = $section->add('Form',null,null,['form/stacked']);
 		$a_basic_form->setModel($apartment_model,['name','address','city_id','state_id','country_id','pincode','code','is_active','is_verified','contact_persion_name','contact_email_id','contact_mobile_no','inauguration_date','builder_name','builder_mobile_no','builder_email']);
 		$a_basic_form->addSubmit();
 		if($a_basic_form->isSubmitted()){
 			$a_basic_form->update();
 			$a_basic_form->js(null,$a_basic_form->js()->reload())->univ()->successMessage('saved')->execute();
 		}

 		// fees managemnt
		$section = $fees_tab->add('View')->addClass('container')->setStyle('width','80%');
		$form = $section->add('Form',null,null,['form/stacked']);
		$form->setModel('Apartment',['per_month_per_member_maintenance_fees','last_day_of_member_fees_submission','remind_after_days','remind_one_time'])->load($apartment_id);
		$form->addSubmit('update');
		if($form->isSubmitted()){
			$form->update();
 			$form->js(null,$form->js()->reload())->univ()->successMessage('saved')->execute();
		}

		// penalty menagement
		$section = $penalty_tab->add('View')->addClass('container')->setStyle('width','80%');
		$form = $section->add('Form',null,null,['form/stacked']);
		$form->setModel('Apartment',['penalty','penalty_apply_after_days_from_last_date_of_submission','penalty_one_time','penalty_recurring'])->load($apartment_id);
		$form->addSubmit('update');
		if($form->isSubmitted()){
			$form->update();
 			$form->js(null,$form->js()->reload())->univ()->successMessage('saved')->execute();
		}

		// passowrd changed
		$section = $pass_tab->add('View')->addClass('container')->setStyle('width','80%');
		$form = $section->add('Form',null,null,['form/stacked']);
		$form->addField('Readonly','username')->set($this->app->auth->model['username']);
		$form->addField('password','current_password')->validate('required');
		$form->addField('password','new_password')->validate('required');
		$form->addField('password','confirm_new_password')->validate('required');

		$form->addSubmit('Change Password');
		if($form->isSubmitted()){
			if($form['new_password'] != $form['confirm_new_password'])
				$form->error('new_password','password must be same');

			$user = $this->add('Model_User')->addCondition('username',$this->app->auth->model['username']);
          	$this->add('BasicAuth')
                ->usePasswordEncryption('md5')
                ->addEncryptionHook($user);
            $user['password'] = $form['new_password'];
            $user->save();
            $form->js(null,$form->js()->reload())->univ()->successMessage('password updated')->execute();
		}		

     	// $tab = $this->add('View_MTabs');
     	// $config = $tab->addTab('config');
     	// $config2 = $tab->addTab('config1');
     	// $form = $config->add('Form');
     	// $form->addField('hello');
    }
}