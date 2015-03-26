SendMail = {};
SendMail.rendered = false;

SendMail.send = function() {
	console.log('SendMail.send');
	
	CONFIG.clearError();
	
	var action		= HTTP + 'hotspot/save';
	var hotspotID	= ($(this).attr('hotspot-id') ? $(this).attr('hotspot-id') : $('.form').attr('hotspot-id'));
	var name 		= ($(this).attr('name') ? $(this).attr('name') : $('.form input#name').val()).trim();
	
	if(name == '') {
		 $('.form input#name').parent().addClass('error type1');
	} else {
		$.ajax({
			type: 'POST',
			url: action,
			data: {
					'name': name,
					'hotspot-id': hotspotID,
					//'redirect-uri': redirectURI
				  },
			beforeSend:function(){
				//if you need something to happen before the call
			},
			success:function(data){
				data = JSON.parse(data);
				console.log(data);
				
				$('.form').hide(500);
				$('.msg.show').hide(500);
				$('.note').hide(500);
				
				switch(data.status == 1){
					case 0:
					break;
					
					case 1:
						//
					break;
					
					case 2:
						
					break;
				}
				
				if(data.alert.msg != '') 	$('.msg.hide').html(data.alert.msg);
				if(data.alert.color != '') 	$('.msg.hide').addClass(data.alert.color);
				$('.msg.hide').show(500);
			},
			error:function(data){
				//if you need something to happen when an error occurs
			},
		});
	}
}

SendMail.render = function() {
	console.log('SendMail.render');
	if(!SendMail.rendered){
		SendMail.rendered = true;
		
	}
}

$(function(){
	SendMail.render();
});