<?php
 include(dirname(__FILE__)."/ez_sql_core.php");
 include(dirname(__FILE__)."/ez_sql_mysql.php");
 
 define('DB_NAME', 'eblip');    
 define('DB_USER', '');  
 define('DB_PASSWORD', ''); 
 define('DB_HOST', 'localhost');

 define('EMAIL', 'eblip@serwer.pl');
 define('EMAIL_PASSWD', 'mojehasło')
 define('EMAIL_SERVER', '{serwer.pl:143/novalidate-cert}')

 
 $db = new ezSQL_mysql(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
 $db->query('SET NAMES utf8');
 
 
 function checkMail($mail) {      
   $mail = trim($mail);      

   $regex = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';

   if (preg_match($regex, $mail)) {
     if (function_exists('checkdnsrr')) {
       list($login,$server) = explode('@',$mail);
       if (checkdnsrr($server, 'MX') || checkdnsrr($server, 'A')) {
         return true;
       }
       return false;
     }
     return true;
   }
   return false;
 }
 
 /**
  * Wysyła SMS o podanej treści
  */
 function sendSms($number, $message) {
  $number = str_replace("+", "", $number);
  $number = str_replace("-", "", $number);
  $number = str_replace(" ", "", $number);      
        
  if (9 == strlen($number)) {
   // TODO: Funkcja wysyłająca SMS            
   return true;   
  } else
   return false;
 }
 
 function error($err_title, $err_msg) {
 	global $db;
 	
 	$sql = "INSERT INTO `eblip_errors` (title, message) VALUES ('".$db->escape($err_title)."','".$db->escape($err_msg)."')";
  	$db->query($sql);
 }
 
 function is_utf ($t)
 {
  if ( @preg_match ('/.+/u', $t) )
   return true;
  else
   return false;
 }
?>
