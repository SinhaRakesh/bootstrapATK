<?php

class Controller_SMS extends AbstractController{

	function sendMessage($no,$msg){
		$curl=$this->add('Controller_CURL');
		$msg=urlencode($msg);


		$config = $this->add('Model_Configuration')->tryLoadAny();
		
		$url = $config['gateway_url'] . "&api_key=".$config['api_key']."&to=".$no."&sender=".$config['sender']."&message=".$msg."&format=".$config['format']."&custom=".$config['custom'];
		ob_start();
			$curl->get($url);
		return ob_get_contents();
	}
}