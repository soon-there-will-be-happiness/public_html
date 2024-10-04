/*jshint -W104*/
/*jshint -W119*/

var is_auth = false;
// var session_end = 0;

async function checkSession() {
	$.ajax({
        url: '/admin/sessioncheck',
        success: function(data){
            if(data.status == 'success'){
            	if(data.result.authorization){
            		result = data.result;

            		if(result.admin_token && (result.admin_token).length > 0 && admin_token != result.admin_token){
            			$('input[name="token"][value="' + admin_token + '"').each(function() {
							$(this).val(result.admin_token);
					   	});
						admin_token = result.admin_token;
            		}

            		is_auth = data.result.authorization;

            		// if(result.end_time && result.end_time != session_end)
	            	// 	session_end = result.end_time + getDifferenceTime(result.server_time) * 60 * 60;

	            	hideForm();

            	}else{
					showForm();
            		is_auth = false;
            	}
            }
        }, 
        error: function(){
        	if(is_auth)
        		location.reload();

        	is_auth = false;
        }
	});
	
	return is_auth;
}


$(document).ready(function () {
	checkSession();

	setInterval(function(){
		checkSession();
	}, 1000 * 60);

	$('form').on('submit',function(e) {
		checkSession();
		return true;
	});

	$("form").change(function() {
		checkSession();
	});

	$('form').submit( function(){
		if(is_auth)
			return true;
		return false;
	});
});

$(window).focus(function() {
    checkSession();
});

async function showForm(){

	if($('#auth_form').length > 0)
		return;

	jQuery('<iframe>', {
		src: '/admin/sessionlogin',
	    id: 'auth_form',
	    class: '',
	    style: '  width: 100vw;  height: 100vh;  position: fixed;  z-index: 9999;'
	}).prependTo('html');

	$("#auth_form").on("load", () => {
		form_con = $("#auth_form").contents();

	    let iframeBody = form_con.find("head");
	    let iframeSpan = form_con.find("span");

	    span_html = "<div style=\"font-size: 14px\" class=\"admin_warning\">Сессия закончилась. Для продолжения работы, вам необходимо авторизоваться.</div>";

	    $(iframeBody).append("<style>body{ background: #525252c7; }</style>");
	    $(iframeSpan).html(span_html);

		form_con.find("form").submit(function(e) {
			checkSession();

		    e.preventDefault();

		    $.ajax({
		        type: "POST",
		        url: '/admin/sessionlogin',
		        data: $(this).serialize(),
		        success: function(data) {
		        	if(data.status == 'success'){
		        		$(iframeSpan).html(span_html);
		        		checkSession();
		        	}

		        	else{
		        		form_con.find('input[name="login"]').val("");
		        		form_con.find('input[name="pass"]').val("");

		        		warning_html = "<div class=\"admin_warning\">Неверный пароль!</div>";
		        		$(iframeSpan).html(span_html + warning_html);
		        	}
		        }
		    });

		    return false;
		});
	});

	$('html, body').css({
	    overflow: 'hidden',
	    height: '100%'
	});
}

function hideForm(){
	if($('#auth_form').length > 0){

		$('html, body').css({
		    overflow: 'auto',
		    height: 'auto'
		});

		$('#auth_form').remove();

		checkSession();
	}
}

function getAttr(element, attr_name){
	if(element.length == 0)
		return false;

	attr = element.attr(attr_name);

	if(typeof attr === 'undefined' || attr === false)
		return false;

	return attr;
}