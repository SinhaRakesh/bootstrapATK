<?php
/**
 * Undocumented
 */
class Controller_Validator extends Controller_Validator_Advanced
{

	function rule_indianMobile($a){
        $a = trim($a);
        if(strlen($a) != 10 || !in_array(substr($a,0,1), [8,9,7]))
            return $this->fail('must be a 10 digit number and start from 9,8 or 7'); 
    }
}
