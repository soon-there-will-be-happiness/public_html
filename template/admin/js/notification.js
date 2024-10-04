function Notification() {

	if($('#notification_block').length == 0)
		$('.breadcrumb').after('<span id="notification_block"></span>');

	var messages_block = $('#notification_block');
	var message_id = 0;

	this.addMessage = function(value, type, time, id){
		
		if((type !== 'message' && type !== 'warning')) 
			return;

		if(!id)
			id = "notifcid-" + message_id++;

		messages_block.html( messages_block.html() + 
			"<div data-ntf_id=\"" + id + "\" class=\"admin_" + type + "\" id=\"" + id + "\">" + value + "</div>"
		);

		setTimeoutHideMessage(id, time);
	};

	this.hideMessage = hideMessage;

	function setTimeoutHideMessage(id, time){
		if(!time)
			time = 1;

		setTimeout(function() {
			hideMessage(id);
		}, 1000 * time);
	}

	function hideMessage(id){
		$('[data-ntf_id="' + id + '"]').fadeOut('fast');

		setTimeout(function(){
			$('[data-ntf_id="' + id + '"]').remove();
		}, 1000 * 1);
	}
}
