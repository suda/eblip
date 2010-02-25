<?php
 session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
         "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="description" content="eBlip - Blipowanie via E-mail">
<meta name="keywords" content="">
<meta name="creation-date" content="09/20/2007">
<meta name="revisit-after" content="15 days">
<title>eBlip - Blipowanie via E-mail</title>
<style type="text/css" media="screen">
 @import "css/iuix.css";
 
 .row { text-align: center; }
 
 .greenButton {
    -webkit-border-image: url(i/greenButton.png) 0 12 0 12;
    color: #FFFFFF;
    display: block;
    border-width: 0 12px;
    padding: 10px;
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    text-decoration: inherit;    
 }
 
 .redButton {
    -webkit-border-image: url(i/redButton.png) 0 12 0 12;
    color: #FFFFFF;
    display: block;
    border-width: 0 12px;
    padding: 10px;
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    text-decoration: inherit;    
 }
</style>
<script type="application/x-javascript" src="js/iuix.js"></script>
<script type="application/x-javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
<script type="application/x-javascript" src="js/main.js"></script> 
</head>
<body>
 <div class="toolbar">
     <h1 id="pageTitle"></h1>
     <a id="backButton" class="button" href="#"></a>
 </div>
 
 <div id="home" title="eBlip" class="panel"<?php echo (isset($_SESSION['login'])) ? '' : ' selected="true"'; ?>>
  <fieldset>
   <div class="row">
       <h2>Witaj w eBlip</h2>
       Dzięki eBlip możesz wysyłać zdjęcia z iPhone na Blip.pl za pomocą maila.<br /><br />
       Wystarczy się zarejestrować podając login i hasło z Blip-a (będzie ono także używane do logowania do eBlip) oraz<br />
       E-mail z którego będziesz wysyłać zdjęcia.<br />
       <br />
   </div>
  </fieldset>
  <a class="whiteButton" href="#login">Zaloguj</a><br />
  <a class="whiteButton" href="#register">Załóż konto</a>
 </div>
 
 <div id="login" title="Logowanie" class="panel">
 
  <fieldset>   
   <div class="row">   
       <label>Login</label>
       <input type="text" name="login" id="lgn_login"/>
   </div>
   <div class="row">
       <label>Hasło</label>
       <input type="password" name="password" id="lgn_passwd"/>
   </div>
  </fieldset>
  
  <a class="grayButton" onclick="login()" href="javascript:void(0)">Zaloguj</a>
 </div>
 
 <div id="register" title="Rejestracja" class="panel">
  <h2>Blip.pl</h2>
  <fieldset>   
   <div class="row">
       <label>Login</label>
       <input type="text" name="login" id="reg_login"/>
   </div>
   <div class="row">
       <label>Hasło</label>
       <input type="password" name="password" id="reg_passwd"/>
   </div>
   <div class="row">
       <label>Powtórz</label>
       <input type="password" name="password_repeat" id="reg_passwd_repeat"/>
   </div>
  </fieldset>
  
  <h2>E-mail</h2>
  <fieldset>   
   <div class="row">
       <label>Adres</label>
       <input type="text" name="email" id="reg_email"/>
   </div>
   <div class="row">
       <label>Powtórz</label>
       <input type="text" name="email_repeat" id="reg_email_repeat"/>
   </div>
  </fieldset>
  
  <fieldset>
   <div class="row">
       <h2 style="color: #ff0000;">eBlip nie udostępnia nikomu Twojego hasła ani adresu E-mail</h2>       
       <br />
   </div>
  </fieldset>
  
  <a class="grayButton" onclick="register()" href="javascript:void(0)">Załóż konto</a>
 </div>
 
 <div id="done" title="Gotowe" class="panel">
  <fieldset>
   <div class="row">
       <br />
       Możesz teraz ustawiać status,<br />
       wysyłając maila na
       <h2><a href="mailto:eblip@blip.suda.pl">eblip@blip.suda.pl</a></h2>
       w tytule wpisując treść statusu.<br /><br />              
   </div>
  </fieldset>
  <a class="grayButton" href="#profile">Mój profil</a>
 </div>
 
 <div id="profile" title="Mój profil" class="panel"<?php echo (!isset($_SESSION['login'])) ? '' : ' selected="true"'; ?>>
  <fieldset>
   <div class="row">
     <br />
     Ustaw status wysyłając mail na
     <h2><a href="mailto:eblip@blip.suda.pl">eblip@blip.suda.pl</a></h2>
     w tytule wpisując treść statusu.<br /><br />       
   </div>
  </fieldset>
  
  <fieldset>
   <div class="row" style="">
    <div id="loading">
     <br />
     <img src="i/ajax-loader.gif" alt="Ładuję..." />
     <br /><br />
    </div>
    
    <div id="profile_info" style="display: none;">
     <br />
     Statusów ustawionych:
     <h2>0</h2>
    </div>
    <?php if (isset($_SESSION['login'])) echo '<script language="JavaScript">get_profile()</script>'; ?>       
   </div>
  </fieldset>
  
  <h2>Przypisane adresy E-mail
  <a class="button" onclick="add_email()" href="javascript:void(0)" style="position: relative; float: right; margin-top: -15px; margin-right: -5px; font-size: 18px;"><b>+</b></a></h2>
  <fieldset id="emails">

  </fieldset>
   
  <fieldset>
			<div class="row">
<script>
var phone_status = 0;
</script>			
   	<label>Powiadomienia SMS</label>
    <div class="toggle" onclick="toggle_sms()" id="toggle_sms"><span class="thumb"></span><span class="toggleOn">Wł</span><span class="toggleOff">Wył</span></div>
   </div>
  </fieldset>
  <a class="greenButton" onclick="logout()" href="javascript:void(0)">Wyloguj</a><br />
  <!--<a class="redButton" onclick="del_account()" href="javascript:void(0)">Usuń konto</a>-->
 </div>
 
 <div id="phone_settings" title="Ustawienia SMS" class="panel">
 	<fieldset>
   <div class="row loader" style="">
    <br />
    <img src="i/ajax-loader.gif" alt="Ładuję..." />
    <br /><br />
   </div>
   
   <div id="set_phone_no" style="display: none;" class="row">
   	<label>Numer tel.</label>
    <input type="text" />	
   </div> 
   
   <div id="set_phone_code" style="display: none;" class="row">
   	<label>Kod z SMS</label>
    <input type="text" />
   </div>
  </fieldset>	
 </div>
 
 <script type="text/javascript">
  var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
  document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
 </script>
 <script type="text/javascript">
  var pageTracker = _gat._getTracker("UA-1037139-12");
  pageTracker._trackPageview();
 </script>  
</body>
</html>
