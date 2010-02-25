<?php
 session_start();
 
 include("include/config.php");

 $out = new stdClass;
 
 if (isset($_SESSION['login'])) {
  $login = $db->escape($_SESSION['login']); 
 	
		$user = $db->get_row("SELECT * FROM `eblip_users` WHERE `login` = '".$login."'", ARRAY_A);
 
 	if (!empty($user)) {
  	$out->result = true;
  	$out->phone_no = $user['phone_no'];
  	$out->phone_code = $user['phone_code'];
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