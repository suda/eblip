<?php
 session_start();
 
 include("include/config.php");

 $out = new stdClass;
 
 if (isset($_SESSION['login'])) {
  $login = $db->escape($_SESSION['login']); 
  $email = $db->escape($_POST['email']);
 	 
		$user = $db->get_row("SELECT * FROM `eblip_users` WHERE `login` = '".$login."'", ARRAY_A);
 
 	if (!empty($user)) {
 	
 		$row = $db->get_row("SELECT * FROM `eblip_alt_emails` WHERE `email` = '".$email."'", ARRAY_A);
 	
 		if (empty($row)) {
  		if (checkMail($email)) {
  			$out->result = true;
  			$db->query("INSERT INTO `eblip_alt_emails` SET `user_id` = ".$user['id'].", `email` = '".$email."'");
  		} else {
  			$out->result = false;
  			$out->error = 'To nie jest prawidłowy E-mail';  
  		}
  	} else {
  		$out->result = false;
  		$out->error = 'Podany E-mail już jest używany';  
  	}  	
  } else {
  	$out->result = false;
  	$out->error = 'Użytkownik nie istnieje';  
  }
 } else {
  $out->result = false;
  $out->error = 'Niezalogowany';
 }
 
 echo json_encode($out);
?>