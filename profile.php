<?php
 session_start();
 
 include("include/config.php");

 $out = new stdClass;
 
 if (isset($_SESSION['login'])) {
  $login = $db->escape($_SESSION['login']); 
 	
		$user = $db->get_row("SELECT * FROM `eblip_users` WHERE `login` = '".$login."'", ARRAY_A);
 
 	if (!empty($user)) {
  	$out->result = true;
  	$out->updates_count = $user['count'];
  	$out->phone_status = $user['phone_status'];
  
  	$out->emails = $db->get_results("SELECT `email` FROM `eblip_users` WHERE `login`='".$login."'", ARRAY_A);
  	$alt_emails = $db->get_results("SELECT `email`, 1 AS `alt` FROM `eblip_alt_emails` WHERE `user_id` = ".$user['id'], ARRAY_A);
  	
  	if (!empty($alt_emails)) $out->emails = array_merge($out->emails, $alt_emails);
  	
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