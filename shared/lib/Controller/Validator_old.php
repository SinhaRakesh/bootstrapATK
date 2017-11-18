<?php

class Controller_Validator extends \Controller_Validator{

	function init(){
		parent::init();
        
	}

	function rule_email($a){   
        if( $a and ! filter_var($a, FILTER_VALIDATE_EMAIL)){
            return $this->fail('Must be a valid email address');
        }
    }

    function rule_required($a){
        if ($a==='' || $a===false || $a===null) {
            return $this->fail('must not be empty');
        }
    }

    function rule_indianMobile($a,$field){
        $a = trim($a);
        if(strlen($a) != 10 || !in_array(substr($a,0,1), [8,9,7]))
            return $this->fail('must be a 10 digit number and start from 9,8 or 7'); 
    }
}