<?php
 session_start();
 
 include("include/config.php");

 $out = new stdClass;
 
 if (isset($_SESSION['login'])) {
  $login = $db->escape($_SESSION['login']); 
  $email = $db->escape($_POST['email']);
 	 
		$user = $db->get_row("SELECT * FROM `eblip_users` WHERE `login` = '".$login."'", ARRAY_A);
 
 	if (!empty($user)) {
 	
 		$row = $db->get_row("SELECT * FROM `eblip_alt_emails` WHERE `user_id` = ".$user['id']." AND `email` = '".$email."'", ARRAY_A);
 	
 		if (!empty($row)) {
  		$out->result = true;
  		$db->query("DELETE FROM `eblip_alt_emails` WHERE `id` = ".$row['id']);
  	} else {
  		$out->result = false;
  		$out->error = 'E-mail nie należy do tego użytkownika';  
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