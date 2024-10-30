
/**
 * Used in server edit page
 * @param $selObj
 * @return
 */
function changeType($selObj){
	var sel = $selObj.val();
	var disabled = false;
	if (sel == 'mail') {
		disabled = true;
		jQuery('#server-form').find(':input[name="host"]').val('localhost');
	}
	jQuery('#server-form').find(':input[name="host"]').attr('disabled', disabled);
	jQuery('#server-form').find(':input[name="auth"]').attr('disabled', disabled);
	jQuery('#server-form').find(':input[name="port"]').attr('disabled', disabled);
	jQuery('#server-form').find(':input[name="account"]').attr('disabled', disabled);
	jQuery('#server-form').find(':input[name="password"]').attr('disabled', disabled);
}
/**
 * Used for list page search action
 */
function searchAction(){
	jQuery('#search_form').find(':input[name="action"]').val('search');
	jQuery('#search_form').submit();
}
/**
 * Used for list page delete action
 * @return
 */
function deleteAction(){
	jQuery('#list_form').find(':input[name="action"]').val('delete');
	jQuery('#list_form').submit();
}
/**
 * Used for list page send action
 * @return
 */
function sendAction(){
    jQuery('#list_form').find(':input[name="action"]').val('mail');
    jQuery('#list_form').submit();
}
/**
 * Preview mail content
 */
function preveiwAction(){
	var formID = 'mail-form';
	jQuery('#'+formID).find(':input[name="mode"]').val('preview');
	var enc = jQuery('#'+formID + ' #content');
	enc.val(encodeURIComponent(enc.val()));
	jQuery('#'+formID).attr('target', '_blank').submit().removeAttr('target');
	enc.val(decodeURIComponent(enc.val()));
}
/**
 * Export the user data
 */
function exportAction(){
	jQuery('#search_form').find(':input[name="action"]').val('mailmaga_x_export_csv');
	jQuery('#search_form').attr('action','admin-ajax.php').attr('target', '_blank').submit().removeAttr('target').removeAttr('action');
}

/**
 * Prompt text 
 */
function initPromptText(){
	jQuery('.input-text').each(
			function(){
			setPromptStatus(this);
			jQuery(this).bind("focus", function(event){
				jQuery(this).siblings('.prompt-text').addClass('screen-reader-text');
			}).bind("blur", function(event){
				setPromptStatus(this);
			});
		});
}
function setPromptStatus(inputObj){
	if(jQuery(inputObj).val() == ''){
		jQuery(inputObj).siblings('.prompt-text').removeClass('screen-reader-text');
	}else{
		jQuery(inputObj).siblings('.prompt-text').addClass('screen-reader-text');
	}
}

/**
 * Send mail action
 */
var sendBtn;
var intervalId;
function startSendMail(aObj){
	sendBtn = aObj;
	showProcess(sendBtn);
	
	var formID = 'mail-form';
	jQuery('#'+formID).find(':input[name="mode"]').val('mail_json');
	var enc = jQuery('#'+formID + ' #content');
	enc.val(encodeURIComponent(enc.val()));
	var param = jQuery('#'+formID).serialize();
	enc.val(decodeURIComponent(enc.val()));
	var mailJSON;
	//alert(jQuery('#'+formID).attr('action') + '?' + param);
	jQuery.ajax({
		  url: jQuery('#'+formID).attr('action'),
		  type: "POST",
		  data: param,
		  dataType: "json",
		  success: function(data) {
			console.log(data);
			if(data['result'] == false){
				var error = '';
				for(var i in data['error']){
					error += data['error'][i] + '\n';
				}
				hideProcess(sendBtn);
				alert(error);
			}else{
				mailJSON = data['mail_json'];
				//sendMailFromJson(mailJSON , 0, 0, false);
				cronSendMail(mailJSON['mail_id']);
				//TODO
				window.setTimeout(function() { 
					refreshCronProcess(aObj,mailJSON['mail_id']);
				}, 3000);
				//window.setTimeout("refreshCronProcess('"+mailJSON['mail_id']+"')", 3000); 
				//refreshCronProcess(mailJSON['mail_id']);
			}
		}
	});
}
/**
 * 
 * @param {Object} aObj
 */
function startSendTestMail(aObj){
	sendBtn = aObj;
	var formID = 'mail-form';
	var test_mail = jQuery('#'+formID).find(':input[name="test_mail"]').val();
	if(test_mail.length == 0){
		alert('Mail address cannot be empty.');
		return;
	}
	var sendto = [];
	var mailObj = {
		'user_id' : 0,
		'user_name' : '',
		'user_email' : test_mail
	};
	var mail_servers = [];
	jQuery('#'+formID+' .mail_servers').each(function(){
		if(jQuery(this).is(':checked') == true && jQuery(this).val() > 0){
			mail_servers.push(jQuery(this).val());
			sendto.push(mailObj);
		}
	});
	if(mail_servers.length == 0){
		alert('Mail server cannot be empty.');
		return;
	}
	var mailJSON = {
		'mail_id' : 0,
		'mail_servers' : mail_servers,
		'send_to' : sendto
	};
	showProcess(sendBtn);
	//console.log(mailJSON);
	sendMailFromJson(mailJSON, 0, 0, true);
}
/**
 * send mail from json in loop
 * @param {Object} mailJSON
 */
function sendMailFromJson(mailJSON,start_user,start_server,is_test){
	var formID = 'mail-form';
	
	var mail_id = mailJSON['mail_id'];
	if((mail_id == null || mail_id == 0) && is_test == false) return false;
	
	var mail_servers = mailJSON['mail_servers'];
	if(mail_servers == null || mail_servers.length == 0) return false;
	
	var send_to = mailJSON['send_to'];
	if(send_to == null || send_to.length == 0) return false;
	
	if((start_user + 1) > send_to.length){
		hideProcess(sendBtn);
		if (is_test == true) {
			jQuery(sendBtn).siblings(':input[name="test_mail"]').val('');
			alert('Send test mail completed.');
			return;
		}
		submitToMailCompleted();
		return true;
	}
	
	var param = 'action=mailmaga_x_send_mail&mode=send';
	if(is_test == true){
		//Flag for test mail
		param += '&is_test=1';
	}
	param += '&mail_id='+mail_id;
	var sk = start_server;
	param += '&server_id='+mail_servers[sk];
	var uk = start_user;
	param += '&user_id='+send_to[uk]['user_id'];
	param += '&user_name='+send_to[uk]['user_name'];
	param += '&user_email='+send_to[uk]['user_email'];
	console.log(param);
	//alert(jQuery('#'+formID).attr('action') + '?' + param);
	jQuery.ajax({
		  url: jQuery('#'+formID).attr('action'),
		  type: "POST",
		  data: param,
		  dataType: "json",
		  success: function(data) {
			console.log(data);
			changeProcess(start_user + 1 , send_to.length);
			if(data['result'] != true){
				var error = '';
				for(var i in data['error']){
					error += data['error'][i] + '<br />';
				}
				if(jQuery('#message').length > 0){
					jQuery('#message p').append(error);
				}else{
					jQuery('#poststuff').before('<div id="message" class="updated"><p>'+error+'</p></div>');
				}
				
			}
			start_user++;
			start_server++;
			if((start_server + 1) > mail_servers.length) start_server = 0;
			sendMailFromJson(mailJSON,start_user,start_server,is_test);
		}
	});
}
/**
 * Send mail in backend
 * @param {Object} mail_id
 */
function cronSendMail(mail_id){
	var param = 'action=mailmaga_x_send_mail&mode=cron_send';
	param += '&mail_id='+mail_id;
	jQuery.ajax({
		  url: 'admin-ajax.php',
		  type: "POST",
		  data: param,
		  dataType: "json",
		  success: function(data) {
			console.log('cronSendMail: ' + data);
		  },
		  statusCode: {
		    404: function() {
		      console.log('404 ERROR');
		    }
		  }
	});
}
/**
 * Refresh mail sending process
 * @param {Object} aObj
 * @param {Object} mail_id
 */
function refreshCronProcess(aObj,mail_id){
	var param = 'action=mailmaga_x_send_mail&mode=cron_process';
	param += '&mail_id='+mail_id;
	console.log(param);
	jQuery.ajax({
		  url: 'admin-ajax.php',
		  type: "POST",
		  data: param,
		  dataType: "json",
		  success: function(data) {
			  console.log(data);
		      if (data['result'] == true) {
			  	changeProcess(data['process']['sent'] , data['process']['total']);
			  	if(data['process']['sent'] == data['process']['total']){
			  		hideProcess(aObj);
			  		
			  		submitToMailCompleted();
			  		return;
			  	}
			  	if(data['process']['sent'] > 0){
			  		jQuery(aObj).hide();
			  		jQuery(aObj).siblings(':input[name="background"]').css('display','inline-block');
			  	}
			  	window.setTimeout(function() { 
					refreshCronProcess(aObj,mail_id);
				}, 3000);
			  }else{
			  	//alert('Request ERROR');
			  }
		  }
	});
	
}
/**
 * Change process every mail
 * @param Int complete
 * @param Int total
 */
var processText = '';
function changeProcess(completed,total){
	var per = Math.floor(completed / total * 100);
	jQuery(sendBtn).siblings('.process').text(processText + '...('+per+'%)');
	
}
/**
 * Show proccess status text
 * @param {Object} aObj
 */
function showProcess(aObj){
	jQuery(aObj).siblings('.spinner').css('display','inline');
	jQuery(aObj).siblings('.process').css('display','inline-block');
	if(processText == ''){
		processText = jQuery(sendBtn).siblings('.process').text();
	}
	jQuery(sendBtn).siblings('.process').text(processText + '...');
}
/**
 * Hide proccess status text
 * @param {Object} aObj
 */
function hideProcess(aObj){
	jQuery(aObj).siblings('.spinner').hide();
	jQuery(aObj).siblings('.process').hide();
	jQuery(sendBtn).siblings('.process').text(processText + '...');
}
/**
 * Show complete page
 */
function submitToMailCompleted(){
	location.href = 'admin.php?page=mailmaga_x_page_history&message=1';
}
/**
 * Run in background
 * @return
 */
function runInbackground(){
	location.href = 'admin.php?page=mailmaga_x_page_history';
}
