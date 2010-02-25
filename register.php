<?php
  include("include/config.php");
  
  $login = $db->escape($_POST['login']);
  $passwd = $db->escape($_POST['passwd']);
  $email = $db->escape($_POST['email']);
  
  $out = new stdClass;
  
  if (checkMail($email)) {  
   $result = $db->query("SELECT `id` FROM `eblip_users` WHERE `email` = '".$email."'");
   if (empty($result)) {
    
    $db->query("INSERT INTO `eblip_users` (`login`, `passwd`, `email`) VALUES ('".$login."','".$passwd."','".$email."')");
    session_start();
    $_SESSION['login'] = $login;
    
    $out->result = true;   
   } else {
    $out->result = false;
    $out->error = 'Podany E-mail jest już używany';    
   }
  } else {
   $out->result = false;
   $out->error = 'Podany E-mail jest nieprawidłowy';   
  }
  echo json_encode($out);
?> 