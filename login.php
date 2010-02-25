<?php
  include("include/config.php");
  
  $login = $db->escape($_POST['login']);
  $passwd = $db->escape($_POST['passwd']);
  
  $out = new stdClass;
  $result = $db->query("SELECT * FROM `eblip_users` WHERE `login` = '".$login."' AND `passwd` = '".$passwd."'", ARRAY_A);
  if (!empty($result)) {
   session_start();
   $_SESSION['login'] = $login;
   //$_SESSION['phone_no'] = $result['phone_no'];
   $_SESSION['phone_status'] = $result['phone_status'];
   
   $out->result = true;
  } else {
   $out->result = false;
   $out->error = 'Dane nieprawidłowe';
  }
  echo json_encode($out);
?>
