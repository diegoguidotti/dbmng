<?php
function _climasouth_assistance()
{
	dbmng_add_drupal_libraries();
	$aForm = _climasouth_create_aForm_assistance();
	
	unset($aForm['fields']['req_name']);
	unset($aForm['fields']['req_email']);
		
	global $user;
	$user = user_load($user->uid);
//	drupal_set_message("<pre>".print_r($user,true)."</pre>");


	$firstname = "";
	$lastname  = "";
	$ii= field_get_items('user', $user, 'field_first_name');
	if(isset($ii[0]['value']))
		{
			$firstname = check_plain($ii[0]['value']);
		}

	$iilast= field_get_items('user', $user, 'field_last_name');
	if(isset($ii[0]['value']))
		{
			$lastname = check_plain($iilast[0]['value']);
		}


	
	//drupal_set_message("Name: ".$firstname."<br/>Surname:".$lastname."<br/>email:".$user->mail);
	
	$aParam = array();
	$aParam['ui']['btn_name'] = "Send";
	$aParam['captcha'] = 1;
	$aParam['auto_field']['uid']['I'] = $user->uid;

	$aParam['auto_field']['req_name']['I'] = $firstname." ".$lastname;
	$aParam['auto_field']['req_email']['I'] = $user->mail;
	
	$html = "";
	if( !isset($_POST['id_c_voc_topic']) )
		{
			$html .= dbmng_create_form($aForm, $aParam, 0);
		}
	else
		{	
			if( isset($_POST['captcha']) )
				{
					if( intval($_POST['captcha']) == intval($_POST['dbmng_x'])+intval($_POST['dbmng_y']) )
						{
							$_POST['req_name']  = $firstname." ".$lastname;
							$_POST['req_email'] = $user->mail;
							
							$sql = "select * from c_voc_topic where id_c_voc_topic = :id_c_voc_topic";
							$var = array(':id_c_voc_topic'=> $_POST['id_c_voc_topic']);
							$res = dbmng_query( $sql, $var );
							if(dbmng_num_rows($res)>0){
								$rec   = dbmng_fetch_object($res);
								$topic = $rec->topic;
								$email = $rec->email;
							}
							
							
							// To send HTML mail, the Content-type header must be set
							$headers  = 'MIME-Version: 1.0' . "\r\n";
							$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
							$headers .= 'From: '.$_POST['req_email'].'' . "\r\n" .
											    'Reply-To: '.$_POST['req_email']."\r\n" .
											    'X-Mailer: PHP/' . phpversion();

							$aInput['to'] = $email;
							$aInput['topic'] = $topic;
							$aInput['subject'] = '[climasouth] Request assistance';
							$aInput['req_organisation'] = $_POST['req_organisation'];
							$aInput['message'] = $_POST['req_message'];
							$aInput['headers'] = $headers;
							$aInput['email'] = $_POST['req_email'];
							$aInput['attach_file'] = $_FILES['req_file'];
							$aInput['timestamp'] = time();
							
							$bmail = climasouth_mailto($aInput);
							if( $bmail )
								{
									$html .= t("Your request has been sent")."<br/>";
									$html .= "<br/>".t('Date').": ".date('Y-m-d H:i:s', $aInput['timestamp']);
									$html .= "<br/>".t('From').": ".$aInput['email'];
									$html .= "<br/>".t('To').": ".$aInput['to'];
									$html .= "<br/>".t('Topic').": ".$aInput['topic'];
									$html .= "<br/>".t('Organization').": ".$aInput['req_organisation'];
									$html .= "<br/>".t('Subject').": ".$aInput['subject'];
									$html .= "<br/>".t('Message').": ".$aInput['message'];
									//$html .= "headers: ".$aInput['headers']."<br/>";
									if( isset($aInput['attach_file']['name']) )
										$html .= "<br/>".t('Attach file').": ".$aInput['attach_file']['name'];

									$aParam['auto_field']['timestamp'] = $aInput['timestamp'];
									$ret = dbmng_insert($aForm, $aParam);
								}
							else
								{
									$html .= t("There was an error in sending the email. Try it again!");
								}
						}
					else
						{
							$html .= t("wrong captcha!");
							$html .= dbmng_create_form($aForm, $aParam, 3);
						}
				}
			else
				{
					$html .= t("insert captcha!");
					$html .= dbmng_create_form($aForm, $aParam, 3);
				}
		}
	
	return $html;
}


function climasouth_mailto($aInput)
{
	$htmlbody = "Organisation: ".$aInput['req_organisation']."<br/>"; //" Your Mail Contant Here.... You can use html tags here...";
	$htmlbody .= $aInput['message']; //" Your Mail Contant Here.... You can use html tags here...";
	$to = $aInput['to']; //Recipient Email Address
	$subject = $aInput['subject']; //"Test email with attachment"; //Email Subject
	$email = $aInput['email'];
	
	$headers = "From: ".$email."\r\nReply-To: ".$email;
	$random_hash = md5(date('r', time()));
	$headers .= "\r\nContent-Type: multipart/mixed; 
	boundary=\"PHP-mixed-".$random_hash."\"";
	
	// Set your file path here
	$attachfile = $aInput['attach_file'];
	
	//print_r($attachfile);
	if( !isset($attachfile['error']) && $attachfile['error'] == 0 )
		$attachment = chunk_split(base64_encode(file_get_contents($attachfile['tmp_name']))); 

	//define the body of the message.
	$message = "--PHP-mixed-$random_hash\r\n"."Content-Type: multipart/alternative; 
	boundary=\"PHP-alt-$random_hash\"\r\n\r\n";
	$message .= "--PHP-alt-$random_hash\r\n"."Content-Type: text/html; 
	charset=\"iso-8859-1\"\r\n"."Content-Transfer-Encoding: 7bit\r\n\r\n";


	//Insert the html message.
	$message .= $htmlbody;
	$message .="\r\n\r\n--PHP-alt-$random_hash--\r\n\r\n";


	//include attachment
	$message .= "--PHP-mixed-$random_hash\r\n"."Content-Type: application/zip; 
	name=".$attachfile['name']."\r\n"."Content-Transfer-Encoding: 
	base64\r\n"."Content-Disposition: attachment\r\n\r\n";
	
	if( !isset($attachfile['error']) && $attachfile['error'] == 0 )
		$message .= $attachment;
	
	$message .= "/r/n--PHP-mixed-$random_hash--";
	
	if( $_SERVER["HTTP_HOST"] != 'localhost' )
		{
			//send the email
			$mail = mail( $to, $subject , $message, $headers );
		}
	else
		{
			$mail = true;
		}

	return $mail;
}

function _climasouth_mng_request_assistance()
{
	dbmng_add_drupal_libraries();
	drupal_add_css( "sites/all/modules/climasouth/climasouth_module.css" );

	$aParam = array();
	 
	//$sql = "select a.*, t.topic from c_request_assistance a, c_voc_topic t where a.id_c_voc_topic = t.id_c_voc_topic and id_parent_request_assistance = :id_parent_request_assistance";
	//$sql = "select * from c_request_assistance where id_parent_request_assistance = 0";
	$sql = "select a.*, t.topic from c_request_assistance a, c_voc_topic t where a.id_c_voc_topic = t.id_c_voc_topic and id_parent_request_assistance = 0";
	$res = dbmng_query($sql, array());
	
	$html = "";
	if( !isset($_REQUEST['act']) )
		{
			foreach( $res as $rec)
				{
					
					$sql = "select * from c_request_assistance where id_parent_request_assistance = :id_parent_request_assistance";
					
					$var = array(':id_parent_request_assistance' => $rec->id_c_request_assistance );
					
					$sres = dbmng_query($sql, $var);
/*
	$html .= "<br/>".t('Date').": ".date('Y-m-d H:i:s', $aInput['timestamp']);
	$html .= "<br/>".t('From').": ".$aInput['email'];
	$html .= "<br/>".t('To').": ".$aInput['to'];
	$html .= "<br/>".t('Topic').": ".$aInput['topic'];
	$html .= "<br/>".t('Organization').": ".$aInput['req_organisation'];
	$html .= "<br/>".t('Subject').": ".$aInput['subject'];
	$html .= "<br/>".t('Message').": ".$aInput['message'];
*/					
					$html .= "<div id='climasouth_mng_ass'>";
					$html .= t('Topic').": ".$rec->topic;
					$html .= "<br/>".t('Date').": ".date('Y-m-d H:i:s', $rec->timestamp);
					$html .= "<br/>".t('From').": ".$rec->req_name . " - " . $rec->req_email;
					$html .= "<br/>".t('Organization').": ".$rec->req_organisation;
					$html .= "<br/>".t('Message').": ".$rec->req_message;
					if( isset($rec->req_file) )
						$html .= "<br/>".t('Attach file').": ".$rec->req_file;
					
					$nrecs = dbmng_num_rows($sres);
					if( $nrecs != 0 )
						{
							foreach( $sres as $r)
								{
									$html .= "<br/>num. " . $nrecs . " reply";
								}
						}
					else	
						{
							$html .= "<br/><a href='?act=reply&id=".$rec->id_c_request_assistance."'>".t('Reply')."</a>";
						}
					
					$html .= "</div>";
				}
		}
	else
		{
			$aForm = _climasouth_create_aForm_assistance();
			unset($aForm['fields']['id_c_voc_topic']);
			global $user;
			$aParam = array();
			$aParam['ui']['btn_name'] = "Send";
			$aParam['auto_field']['uid']['I'] = $user->uid;

			if( $_REQUEST['act'] == "reply" )
				{
					$aParam['hidden_vars']['id'] = $_REQUEST['id'];

					$html .= dbmng_create_form($aForm, $aParam, 0);
				}
			else if( isset($_POST['req_message']) )
				{
					$aParam['auto_field']['id_parent_request_assistance'] = $_POST['id'];
					$aParam['auto_field']['timestamp'] = time();

					$ret = dbmng_insert($aForm, $aParam);
					$_POST = array();
				}
		}

		
	return $html;
}

function _climasouth_create_aForm_assistance()
{
	$sql = "select id_c_voc_topic, topic from c_voc_topic";
	$fields = dbmng_query($sql, array());
	$varField= array();
	foreach($fields as $f)
		{				
			$varField[$f->id_c_voc_topic] = $f->topic;
		}		

	$aForm=array(  
			'table_name' => 'c_request_assistance' ,
				'primary_key'=> array('id_c_request_assistance'), 
				'fields'     => array(
						'id_c_request_assistance'  => 
							array('label'   => t('ID') , 
										'label_long' => t('ID Table'),
										'type' => 'bigint',
										'skip_in_tbl' => '1',
										'key' => 1
							), 
						'req_name'  => 
							array('label'   => t('Your name (required)') , 
										'type' => 'varchar',
										'widget' => 'input',
										'nullable' => false,
										'key' => 0
							), 
						'req_email'     => 
							array('label'   => t('Your email (required)'), 
										'type' => 'varchar', 
										'widget'=>'input' ,
										'nullable' => false,
										'key' => 0
							) ,
						'req_organisation'  => 
							array('label'   => t('Your organisation') , 
										'type' => 'varchar',
										'widget'=>'input' ,
										'key' => 0,
										'skip_in_tbl' => '1'
							), 
						'id_c_voc_topic'  => 
							array('label'   => t('Main topic') , 
										'type' => 'int',
										'widget'=>'select' ,
										'key' => 0,
										'skip_in_tbl' => '1',
										'voc_val' => $varField
							), 
						'req_message'  => 
							array('label'   => t('Your message') , 
										'type' => 'varchar',
										'widget'=>'html' ,
										'key' => 0,
										'skip_in_tbl' => '1'
							),
						'req_file'  => 
							array('label'   => t('Attach File') , 
										'type' => 'varchar',
										'widget'=>'file' ,
										'key' => 0,
										'skip_in_tbl' => '1'
							)
				)
	);
	return $aForm;
}

?>
