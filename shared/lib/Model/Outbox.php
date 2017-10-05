<?php


class Model_Outbox extends Model_Base_Table{
	public $table = "outbox";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true); //name as verification email, registartion email
		$this->addField('subject');
		$this->addField('body');
		$this->addField('type')->setValueList(['email','sms','whatsup']);
		$this->addField('apartment_id');
		$this->addField('to_member_id');
		$this->addField('from_member_id');
		$this->addField('user_id');

		$this->add('dynamic_model/Controller_AutoCreator');
		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		// throw $this->exception('Field must be of class Field_Reference', 'ValidityCheck')->setField('name');
	}

	// function createNew($name,$to_member_id,$subject,$body,$type,$apartment_id,$to_member_id,$from_member_id,$user = null){

	// 	$this['name'] = $name;
	// 	$this['subject'] = $subject;
	// 	$this['body'] = $body;
	// 	$this['type'] = $type;
	// 	$this['apartment_id'] = $apartment_id;
	// 	$this['to_member_id'] = $to_member_id;
	// 	$this['from_member_id'] = $from_member_id;
	// 	if($user)
	// 		$this['user_id'] = $user->id;
	// 	$this->save();
	// }

	function sendEmail($to="techrakesh91@gmail.com",$subject="Here is the subject",$body="This is the HTML message body <b>in bold!{activation_link}</b>",$user_model){
		if($this->app->getConfig('test_mode'))
			return true;

		if(!$this->app->getConfig('email_send'))
			return false;

		$config = $this->add('Model_Configuration')->tryLoadAny();
		$mail = new PHPMailer;
		// $mail->SMTPDebug = 2;
        $mail->isSMTP();
        $mail->Host = $config['host'];  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $config['username'];                 // SMTP username
        $mail->Password = $config['password'];                 // SMTP password
        $mail->SMTPSecure = $config['smtp_secure'];             // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $config['port']; 

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addReplyTo($config['reply_to'], $config['reply_name']);

        $mail->addAddress($to, "Member");     // Add a recipient
        // $mail->addAddress('techrakesh91@gmail.com');               // Name is optional
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');
        // setting up the image as embedded
        // http://hungrydunia.in/frontend/public/assets/img/hungrydunia/logo.png
        // addEmbeddedImage($path, $cid, $name = '', $encoding = 'base64', $type = '', $disposition = 'inline')
        $mail->Subject = $subject;
        $mail->msgHTML($body);
        $mail->IsHTML(true);

        $mail->AltBody = strip_tags($body);
        if(!$mail->send()) {
            // echo 'Message could not be sent.';
            throw new \Exception("Mailer Error ".$mail->ErrorInfo);
            // echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        } else {
            return true;
            // echo 'Message has been sent';
        }
	}

	function sendSMS($to_number,$sms,$user_model=null){
		$sms_response = $this->add('Controller_SMS')->sendMessage($to_number,$sms);
		return true;
	}

	function embedImages($body){
	    // get all img tags
	    preg_match_all('/<img.*?>/', $body, $matches);
	    if (!isset($matches[0])) return;
	    // foreach tag, create the cid and embed image
	    $i = 1;
	    foreach ($matches[0] as $img)
	    {
	        // make cid
	        $id = 'img'.($i++);
	        // replace image web path with local path
	        preg_match('/src="(.*?)"/', $body, $m);
	        if (!isset($m[1])) continue;
	        $arr = parse_url($m[1]);
	        if (!isset($arr['host']) || !isset($arr['path']))continue;
	        // add
	        $this->AddEmbeddedImage('/home/username/'.$arr['host'].'/public'.$arr['path'], $id, 'attachment', 'base64', 'image/jpeg');
	        $body = str_replace($img, '<img alt="" src="cid:'.$id.'" style="border: none;" />', $body); 
	    }
	    var_dump($body);
		return $body;
	}
}