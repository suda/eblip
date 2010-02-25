function register(){
 var form_ok = true;
 
 if ('' == $('#reg_login').val()) {
  alert('Podaj login do Blip.pl');
  $('#reg_login').focus();
  form_ok = false;
 }
 
 if (form_ok && ('' == $('#reg_passwd').val())) {
  alert('Podaj hasło do Blip.pl');
  $('#reg_passwd').focus();
  form_ok = false;
 }
 
 if (form_ok && ('' == $('#reg_passwd_repeat').val())) {
  alert('Powtórz hasło');
  $('#reg_passwd_repeat').focus();
  form_ok = false;
 }
 
 if (form_ok && ($('#reg_passwd').val() != $('#reg_passwd_repeat').val())) {
  alert('Podane hasła są różne');
  $('#reg_passwd').focus();
  form_ok = false;
 }
 
 /************************ EMAIL ********************************/
 
 if (form_ok && ('' == $('#reg_email').val())) {
  alert('Podaj E-mail');
  $('#reg_email').focus();
  form_ok = false;
 }
 
 if (form_ok && ('' == $('#reg_email_repeat').val())) {
  alert('Powtórz E-mail');
  $('#reg_email_repeat').focus();
  form_ok = false;
 }
 
 if (form_ok && ($('#reg_email').val() != $('#reg_email_repeat').val())) {
  alert('Podane adresy są różne');
  $('#reg_email').focus();
  form_ok = false;
 }

 if (form_ok) {
  var form = new Object();
  
  form.login = $('#reg_login').val();
  form.passwd = $('#reg_passwd').val();
  form.email = $('#reg_email').val();
  
  $.post('register.php', form, function(data){
   if (data.result) {
    iui.showPage(document.getElementById('done'), false);
   } else {
    alert(data.error);
   }
  }, 'json');
 }
 
 return false;
}

function login(){
 var form_ok = true;
 
 if ('' == $('#lgn_login').val()) {
  alert('Podaj login');
  $('#lgn_login').focus();
  form_ok = false;
 }
 
 if (form_ok && ('' == $('#lgn_passwd').val())) {
  alert('Podaj hasło');
  $('#lgn_passwd').focus();
  form_ok = false;
 }
 
 if (form_ok) {
  var form = new Object();
  
  form.login = $('#lgn_login').val();
  form.passwd = $('#lgn_passwd').val(); 
  
  $.post('login.php', form, function(data){
   if (data.result) {
    iui.showPage(document.getElementById('profile'), false);
    get_profile();
   } else {
    alert(data.error);
    $('#lgn_passwd').val(''); 
   }
  }, 'json');
 }
  
 return false;
}

function logout(){
 $.get("logout.php", function(data) {
  iui.showPage(document.getElementById('home'), true);
 });
 
 return false;
}

function get_profile(){
 $.getJSON("profile.php", function(data) {
  if (data.result) {
  $('#loading').hide();
  $('#profile_info h2').html(data.updates_count);
  $('#profile_info').show();
  
  var html = '';
  for (i=0; i<data.emails.length; i++) {
  	del = (null != data.emails[i].alt) ? '<a href="javascript:del_email('+i+')" class="del"><img src="i/delete.png" /></a>' : '';
   html += '<div class="row" id="email_'+i+'"><label>'+data.emails[i].email+'</label>'+del+'</div>';
  }
  $('#emails').html(html);
  phone_status = data.phone_status;
  if (2 == phone_status) $('#toggle_sms').attr('toggled', 'true');
  } else {
   $('#loading').html('<h2 style="color: #ff0000;">'+data.error+'</h2>'); 
  }
 }); 
}

function del_email(id){
	email = $('#email_'+id+' label').html();
	if (confirm('Na pewno chcesz usunąć e-mail "'+email+'"?')) {
		var form = new Object();
  
  form.email = email; 
  
  $.post('del_email.php', form, function(data){
			window.location.reload();
		});
	}
}

function add_email() {
	$('#emails').append('<div class="row"><input type="text" name="new_email" id="new_email" style="padding-left: 10px;" /></div>');
	$('#new_email').focus().blur(function(){
		var form = new Object();
  
  form.email = $('#new_email').val(); 
  
  $.post('add_email.php', form, function(data){
  	data = eval('foo='+data);
			if (data.result) {
				window.location.reload();
			} else {
				$('#new_email').parent().remove();
				alert(data.error);
			}
		});
	});
}

function toggle_sms() {
	if (2 == phone_status) {
		var form = new Object();
  
  form.phone_status = 1; 
		
		$.post('set_phone.php', form, function(data){
			phone_status = 1;
			$('#toggle_sms').removeAttr('toggled');
		});
	} else if (1 == phone_status) {
		var form = new Object();
  
  form.phone_status = 2; 
		
		$.post('set_phone.php', form, function(data){
			phone_status = 2;
			$('#toggle_sms').attr('toggled', 'true');
		});
	} else if (0 == phone_status) {
		iui.showPage(document.getElementById('phone_settings'), false);
  $('#toggle_sms').removeAttr('toggled');
  get_phone();		
	}
}

function get_phone() {
	 $.getJSON("get_phone.php", function(data) {
  if (data.result) {
  	$('#phone_settings div.loader').hide();
  	$('#set_phone_code input').blur(function() {
  		var form = new Object();
  			form.phone_code = $('#set_phone_code input').val(); 

					$.post('set_phone.php', form, function(data){
						data = eval('foo='+data);
						if (data.result) {
							window.location.reload();
						} else alert(data.error);
					});
  	});
			if ('' == data.phone_no) {
				$('#set_phone_no').show();
				$('#set_phone_no input').blur(function() {
					var form = new Object();
  			form.phone_no = $('#set_phone_no input').val(); 

					$.post('set_phone.php', form, function(data){
						data = eval('foo='+data);
						if (data.result) {
							$('#set_phone_no').hide();
							$('#set_phone_code').show();
						} else alert(data.error);
					});	
				});
			} else if ('' != data.phone_code) {
				$('#set_phone_code').show();		
			}
  } else {
   $('#phone_settings div.loader').html('<h2 style="color: #ff0000;">'+data.error+'</h2>'); 
  }
 });
}