var auth_window;
var atach_window;

var is_ios_mobile = /iPad|iPhone|iPod/.test(navigator.userAgent);

var connect_attch = new ConnectAttch();

var connect_end_res = {
	check: [],
	click: []
};

if(window.jQuery){
	$('a[data-connect]').click( function () {
		button = $(this);
		href = button.attr('href');
		
		if(href.substring(0, 4) == 'http')
			return true;

		service = button.data('connect');
		if(service)
			connect_attch.buttonClick(service, button);

		return false;
	});

	window.onload = function() {
		$("a[data-connect]").each( function(){		
			button = $(this);
			service = button.data('connect');
			if(service)
				connect_attch.buttonCheck(service, button);
		});
	};
}

else
	console.log('jQuery is not loaded');

var connect_s_check = [];
function ConnectAttch(){
	var end_button;
	var end_service;
	var method;


	this.buttonCheck = function(service, button){
		if(!connect_s_check[service]){	
			$.ajax({
		        type: "POST",
		        url: '/connect/jquery/' + service,
		        data: {
		        	method: 'check_attach'
		        },
		        success: function(data) {
		        	connect_end_res.check.push(data);
		        	if(data.status == 'success'){
		        		if(data.comment == 'connected')
			        		connect_s_check[service] = 5;

			        	else 
			        	if(data.comment == 'not attached')
				        	connect_s_check[service] = 4;

				        else
				        	connect_s_check[service] = 3;
		        	}else
		        	if(data.comment == 'disabled')
			        	connect_s_check[service] = 2;
		        	else
			        	connect_s_check[service] = 1;

		        	buttonCheck_(service, button);
		        }
		    });
		}else
			buttonCheck_(service, button);
	};

	function buttonCheck_(service, button){
		if(button.data('connect-setting_text') && (service == 'setting' || button.data('connect-method') == 'setting' || (connect_s_check[service] === 4 && !button.data('connect-attach_text')) || (connect_s_check[service] === 5 && !button.data('connect-unlink_text')) || (!button.data('connect-attach_text') && !button.data('connect-unlink_text')) ) ){
			if($('#connect_setting').length == 0){
				button.html('<span class="loading_text-ani"></span>');
				method = 'error';
			}

			else{
				button.html(button.data('connect-setting_text'));
				method = 'setting';
			}

			button.attr('data-connect-method', method);
			button.data('connect-method', method);
			button.attr('href', '///');

		}else
		if(connect_s_check[service] === 5 && button.data('connect-unlink_text') && button.data('connect-attach_text')){
			button.html(button.data('connect-unlink_text'));
			method = 'unlink';
			button.attr('data-connect-method', method);
			button.data('connect-method', method);

			if(is_ios_mobile)
				setLink(button, method, service);
			else
				button.attr('href', '///');
		}else
        if(connect_s_check[service] === 4 && button.data('connect-attach_text')){
			button.html(button.data('connect-attach_text'));
			method = 'attach';
			button.attr('data-connect-method', method);
			button.data('connect-method', method);

			if(is_ios_mobile)
				setLink(button, method, service);
			else
				button.attr('href', '///');
		}else{
			if(button.data('set_elmnt_id') && (hide_elmnt = button.parents('[data-id="'+button.data('set_elmnt_id') + '"]')))
				hide_elmnt.remove();
			button.remove();
		}
	}

	function setLink(a_elmnt, method, service, link){	
		if(link){
    		a_elmnt.attr('href', link);
    		a_elmnt.attr('target', '_blank');

    		return;
		}

		$.ajax({
	        type: "POST",
	        url: '/connect/jquery/' + service,
	        data: {
	        	method: method
	        },
	        success: function(data) {
	        	connect_end_res.click.push(data);
	        	if(data.status == 'success'){
	        		a_elmnt.attr('href', data.result.link);
	        		a_elmnt.attr('target', '_blank');
	        	}
	        }
	    });
	}

	this.buttonClick = function(service, button){
		if(windowCheck(service, button))
			return;

		method = 'attach';

		if(button.data('connect-method'))
			method = button.data('connect-method');

		if(method == 'setting'){
			if($('#connect_setting').length > 0)
				UIkit.modal($('#connect_setting')).show();

			else
				button.remove();
			return false;
		}

		else 
		if(method == 'error')
			button.remove();
		

		if(window[service] && !window[service].closed)
			return window[service].focus();
		
		$.ajax({
	        type: "POST",
	        url: '/connect/jquery/' + service,
	        data: {
	        	method: method
	        },
	        success: function(data) {
	        	connect_end_res.click.push(data);

	        	if(data.status == 'success'){
            		setTimeout(function() { 
	            		if(is_ios_mobile || typeof window[service] === 'undefined')
							setLink(button, method, service, data.result.link); 
            		}, 500);

	        		end_button = button;
	        		end_service = service;

	        		if(data.result.link)
	        			window[service] = window.open(data.result.link, '_blank', 'top=100, left=100, width=600, height=500, location=no, resizable=yes, toolbar=no');
	        		
		        	if(data.comment == 'connected'){
			        	connect_s_check[service] = 5;
		        		windowClose();
		        	}

			        setTimeout(function(){
			        	if(typeof window[service] != 'undefined')
			        		check(service, button);
			        }, 1000 * 1);
	        	}
	        }
	    });
	};

	function check(service, button){
		if(windowCheck(service, button))
			return;

		$.ajax({
	        type: "POST",
	        url: '/connect/jquery/' + service,
	        data: {
	        	method: 'check_attach'
	        },
	        success: function(data) {
	        	if(data.status == 'success'){
	        		end_button = button;
	        		end_service = service;

	        		if(data.comment == 'connected'){
			        	connect_s_check[service] = 5;

						if(method == 'attach')
		        			windowClose();

		        		else
		        			setTimeout(function(){
				        		check(service, button);
					        }, 1000 * 2);
	        		}

		        	else
		        	if(data.comment == 'not attached'){
			        	connect_s_check[service] = 4;

						if(method == 'unlink')
		        			windowClose();

		        		else
		        			setTimeout(function(){
				        		check(service, button);
					        }, 1000 * 2);
		        	}
	        	}
	        }
	    });
	}

	function windowCheck(service, button){
		if((button.data('connect-method') && method && method != button.data('connect-method')) || (end_button && end_service && end_button != button && end_service != service))
			return windowClose();

		return false;
	}

	function windowClose(){
    	if(window[end_service] && !window[end_service].closed)
        	window[end_service].close();

        else
        	return false;

    	window[end_service] = null;

		buttonCheck_(end_service, end_button);

		return true;
	}
}

function Connect(explr, token, time){

	msg_body = (document.getElementById('msg_' + explr).parentElement).parentElement;

	var elmnt = new ElementController();

	var form = document.getElementById(explr);
	var submit = form.querySelector('input[name="enter"]');
	var email = form.querySelector('input[name="email"]');
	var remember = form.querySelector('input[name="remember_me"]');
	var password = form.querySelector('input[name="pass"]');
	var forgot_block = form.querySelector('.modal-form-forgot-wrap');

	var hash = '';
	var status = null;

	function windowClose(service){
    	document.getElementById('load-' + service + '-' + explr).style.display = 'none';

    	if(window[service] && !window[service].closed)
        	window[service].close();

    	window[service] = null;

    	if(!window[status] || window[status].closed){
			elmnt.unsetDisabled(submit, forgot_block, remember, email, password);
			elmnt.unsetReadonly(email, password);
    	}

    	return;
	}

	window.addEventListener('focus',  function() {
		if(status)
			checkAuth(status, 1);

		else
			checkAuth('check', 1);
	});


	function checkAuth(service, wind_ignore){
		if(!wind_ignore && (status != service || (window[service] && window[service].closed) || auth_window != window[service]))
        	return windowClose(service);

		var data = "time=" + time + "&connect_token=" + token + "&hash=" + hash + "&email=" + email.value;

		if(remember.checked)
			data += "&remember=1";

	  	var xhr = new XMLHttpRequest();

	  	xhr.open("POST", "/connect/authLoad/" + service, true);

	  	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	  	xhr.send(data); 
	  	xhr.onreadystatechange = function(){
	    	if (xhr.readyState == 4){
	      		if(xhr.status == 200){
			        var result = JSON.parse(xhr.responseText); 

			        if(result.result && result.result.msg)
				        document.getElementById("msg_" + explr).innerHTML = result.result.msg;

		            if(result.status == 'success'){
		            	windowClose(service);

		            	if(result.result.url){
		            		window.location.href = result.result.url;
							window.location.replace(result.result.url);
		            		window.location.reload();
		            	}else{
		            		 window.location.reload();
		            		 location.reload();
		            	}

		           	}else if(result.comment != 'hash not found')
		            	status = null;
		      	}else
			      	status = null;
		    }
	  	};

		setTimeout(function(){
			checkAuth(service);
		}, 500 * 3);
	}
	this.auth = function (service, id){
		if(window[service] && !window[service].closed)
			return window[service].focus();

	  	var data = "time=<?=$time?>&serviceID=" + id + "&connect_token=" + token + "&email=" + email.value;

		if(remember.checked)
			data += "&remember=1";

	  	var xhr = new XMLHttpRequest();

	  	xhr.open("POST", "/connect/auth/" + service, true);

	  	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	  	xhr.send(data); 

	  	xhr.onreadystatechange = function(){
	    	if (xhr.readyState == 4){
	      		if(xhr.status == 200){

			        var result = JSON.parse(xhr.responseText); 

			        if(result.result && result.result.msg)
				        document.getElementById("msg_" + explr).innerHTML = result.result.msg;

		            if(result.status == 'success' && result.result.hash){
		            	hash = result.result.hash;
		            	status = service;

		            	document.getElementById('load-' + service + '-' + explr).style.display = 'block';

		            	if(result.result.link){
		            		setTimeout(function() { 
			            		if(is_ios_mobile || typeof window[service] === 'undefined')
									or_linkGo(result.result.link, service); 
		            		}, 500);

					    	elmnt.setDisabled(submit, forgot_block, remember, email, password);
					    	elmnt.setReadonly(email, password);

			            	window[service] = window.open(result.result.link, '_blank', 'top=100, left=100, width=600, height=500, location=no, resizable=yes, toolbar=no');
			            	auth_window = window[service];
						  	elmnt.Onclick("auth_window.focus();");
					    }

			            else
			            	window[service] = null;
		            	
		            	setTimeout(function(){
			            	checkAuth(service);
		            	}, 1000 * 1);
		           	}
		      	}
		    } 
	  	};
	};

	function or_linkGo(href, service){
		var a = document.getElementById('ConnectGoLink_vkontakte');
		if(a != null)
			msg_body.removeChild(document.getElementById('ConnectGoLink_vkontakte'));

		a = document.getElementById('ConnectGoLink_telegram');
		if(a != null)
			msg_body.removeChild(document.getElementById('ConnectGoLink_telegram'));

		a = document.createElement('a');

		a.setAttribute('href', href); 		
		a.setAttribute('target', '_blank');
		 
		a.setAttribute('class', 'or_link button btn-add-rev'); 
		a.setAttribute('id', 'ConnectGoLink_' + service); 
		a.textContent = 'Кликните, если не открылось окно';  
		msg_body.append(a);
	}

}

window.addEventListener('load', function() {
	var xhr = new XMLHttpRequest();

  	xhr.open("GET", "/connect/check_auth", true);

  	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  	xhr.send(''); 
  	xhr.onreadystatechange = function(){
    	if (xhr.readyState == 4){
      		if(xhr.status == 200){
		        var result = JSON.parse(xhr.responseText); 

		        if(result.result && result.result.msg)
			        document.getElementById("msg_" + explr).innerHTML = result.result.msg;

	            if(result.status == 'success'){

	            	if(result.result.url){
	            		window.location.href = result.result.url;
						window.location.replace(result.result.url);
	            		window.location.reload();
	            	}else{
	            		 window.location.reload();
	            		 location.reload();
	            	}

	           	}
           	}
	    }
  	};
});


function ElementController(){
	var onclick = '';

	this.Onclick = function(code){
		onclick = code;
	};

	this.hide = function(){
		[].forEach.call(arguments, function (elmnt) {
    		elmnt.setAttribute('style', 'display: none !important');
	    });
	};

	this.show = function(){
		[].forEach.call(arguments, function (elmnt) {
    		elmnt.removeAttribute('style');
	    });
	};

	this.setReadonly = function(){
		[].forEach.call(arguments, function (elmnt) {
    		elmnt.readOnly = true;
	    });
	};

	this.unsetReadonly = function(){
		[].forEach.call(arguments, function (elmnt) {
    		elmnt.readOnly = false;
	    });
	};

	this.setDisabled  = function(){
		[].forEach.call(arguments, function (elmnt) {
    		elmnt.setAttribute('onclick', onclick + ' return false;');
	    });
	};

	this.unsetDisabled = function(){
		[].forEach.call(arguments, function (elmnt) {
    		elmnt.removeAttribute('onclick');
	    });
	};
}


