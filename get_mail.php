<?php
header('Content-type: text/plain; charset=utf-8'); 

require 'include/read_attachment.class.php';
include("include/blipapi.class.php");
include("include/config.php");

$email = EMAIL;
$passwd = EMAIL_PASSWD;
$server = EMAIL_SERVER;
$savedirpath = "inbox";

$mbox = imap_open ($server,  $email, $passwd) or die("Can't connect: " . imap_last_error());
echo imap_num_msg($mbox)." wiadomości w kolejce\n";
for ($jk = 1; $jk <= imap_num_msg($mbox); $jk++)
{
 $head = imap_fetch_overview($mbox, $jk);                                                                      
 $image = null;
 $elements = imap_mime_header_decode($head[0]->subject);
 $content = '';                                 

 foreach ($elements as $element) {  
  $converted = iconv($element->charset, 'UTF-8', $element->text);                     
  $content .= ('' == $converted) ? $element->text : $converted; 
 }

 preg_match('/\<(.*)\>/', $head[0]->from, $matches); 
 $from = $matches[1];
 if ('' == $from) $from = $head[0]->from;
 
 $user = $db->get_row("SELECT * FROM `eblip_users` WHERE `email` = '".$from."' OR `id` IN (SELECT `user_id` FROM `eblip_alt_emails` WHERE `email` = '".$from."')", ARRAY_A);

 if (!empty($user) && ('' != $content)) {
  echo "Status użytkownika ". $user['login'] ."\n";
  $structure = imap_fetchstructure($mbox, $jk );    

  $parts = $structure->parts;
  if (1 < count($parts)) {
   $part = $parts[1];
   
   if (5 == $part->type) {

    $params = $part->dparameters;
    $filename = $part->dparameters[0]->value;
           
    $mege = "";
    $data = "";
    $mege = imap_fetchbody($mbox, $jk, 2);  
    $filename = dirname( __FILE__)."/$savedirpath/$filename";
    $fp = fopen($filename, w);
    $data = readattachment::getdecodevalue($mege, $part->type); 
    fputs($fp, $data);
    fclose($fp);
    echo "Zapisano '$filename'\n";
    $image = '@'.realpath($filename);
   } else {
 	error('Zły załącznik w mailu od <'.$from.'>', "Treść:\n\n---\n".$content."\n---");
   }
  }
  
  $blipapi = new BlipApi($user['login'], $user['passwd']);
  $blipapi->uagent = 'eBlip';
  $blipapi->debug = false;
  try {
   $blipapi->connect();
   
   if ('>' == substr($content, 0, 1)) {
   	// DM   
   	preg_match('/^\>([a-zA-Z0-9]+)[\:]*\s(.*)/', $content, $matches);
   	$recipient = $matches[1]; 	
   	$content = $matches[2];
    if (null == $image) $content = urlencode($content);
   	$res = $blipapi->dirmsg_create($content, $recipient, $image);
   } else {
    if (null == $image) $content = urlencode($content);                                                         
   	$res = $blipapi->status_create($content, $image);
   }
   
   if (201 == $res['status_code']) {
    echo 'Utworzono status '.$res['headers']['location']."\n";
    $db->query('UPDATE `eblip_users` SET `count` = `count` + 1 WHERE `id` = '.$user['id']);
    $db->query("INSERT INTO `eblip_blips` SET `datetime` = NOW(), `user_id` = ".$user['id']. ", `url` = '".$res['headers']['location']."'");
    
    // Powiadomienie SMS
    if (2 == $user['phone_status']) {
    	sendSms($user['phone_no'], "Ustawiono status: ".$res['headers']['location']);
    }
   }
   
   if (null != $image) {
    unlink($filename);   
   }
  } catch (Exception $e) {
  	error('Błąd tworzenia statusu dla ^'.$user['login'], "Treść:\n\n---\n".$content."\n---\n\nObrazek: ".$image);
  }                                        
 } else {
 	error('Zły mail od <'.$from.'>', "Treść:\n\n---\n".$content."\n---");
 }
 
 if (null != $res) {
  if (201 == $res['status_code']) {
   //imap_delete($mbox, $jk);
   imap_mail_move($mbox, $jk, 'INBOX.Done');
  }  
 } else {
  //imap_delete($mbox, $jk);
  if (imap_mail_move($mbox, $jk, 'INBOX.Error')) echo "Przeniesiono do folderu Error\n";
  else echo "Błąd podczas przenoszenia\n";
 }
 
}

imap_close($mbox, CL_EXPUNGE);

?>
