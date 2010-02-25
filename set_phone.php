<?php
 session_start();
 
 include("include/config.php");

 $out = new stdClass;
 
 if (isset($_SESSION['login'])) {
  $login = $db->escape($_SESSION['login']);
  
  // Ustawianie stanu
  $phone_status = (isset($_POST['phone_status'])) ? (int)$_POST['phone_status'] : -1;
  if (-1 < $phone_status) {
  	$out->result = true;
  	$db->query("UPDATE `eblip_users` SET `phone_status` = ".$phone_status." WHERE `login` = '".$login."'");	
  }
  
  // Wysyłanie kodu
  $phone_no = (isset($_POST['phone_no'])) ? $db->escape($_POST['phone_no']) : -1;
  if (-1 != $phone_no) {
  	$code = rand(1, 9999);
  	while (4 > strlen($code)) $code = '0'.$code;
  	if (sendSms($phone_no, 'Kod weryfikacji eBlip: '.$code)) {
  		$out->result = true;
  		$db->query("UPDATE `eblip_users` SET `phone_no` = '".$phone_no."', `phone_code` = '".$code."' WHERE `login` = '".$login."'");	
  	} else {
  		$out->result = false;
  		$out->error = "Zły format numeru.\nPoprawny to: 500123456";
  	}
  }
  
  // Sprawdzenie kodu
  $phone_code = (isset($_POST['phone_code'])) ? $db->escape($_POST['phone_code']) : -1;
  if (-1 != $phone_code) {
  	$row = $db->get_row("SELECT * FROM `eblip_users` WHERE `login` = '".$login."'", ARRAY_A);
  	if ($phone_code == $row['phone_code']) {
  		$out->result = true;
  		$db->query("UPDATE `eblip_users` SET `phone_status` = 2, `phone_code` = '' WHERE `login` = '".$login."'");	
  	} else {
  		$out->result = false;
  		$out->error = "Podany kod jest nieprawidłowy";
  	} 	
  }

 } else {
  $out->result = false;
  $out->error = 'Niezalogowany';
 }
 
 echo json_encode($out);
?>