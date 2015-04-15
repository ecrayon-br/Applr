Hotspot = {};
Hotspot.rendered = false;

Hotspot.send = function() {
	console.log('Hotspot.send');
	
	CONFIG.clearError();
	
	var error		= false;
	var action		= HTTP + 'hotspot/save';
	var hotspotID	= ($(this).attr('hotspot-id') ? $(this).attr('hotspot-id') : $('.form.field').attr('hotspot-id'));
	var avatarID	= ($(this).attr('avatar-id') ? $(this).attr('avatar-id') : $('.form.field').attr('avatar-id'));
	var name 		= ($(this).attr('name') ? $(this).attr('name') : $('.form.field input#name').val()).trim();
	
	if(name == '') {
		error = true;
		 $('.form.field input#name').parent().addClass('error type1');
	} else if(!CONFIG.checkMailSyntax(name)) {
		error = true;
		$('.mailing .form input#name').parent().addClass('error type2');
	}

	if(!error) {
		$.ajax({
			type: 'POST',
			url: action,
			data: {
					'name': name,
					'hotspot-id': hotspotID,
					'rel_avatar': avatarID
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
				
				if(data.alert.msg != '') 	$('.msg.hide').html(data.alert.msg);
				if(data.alert.color != '') 	$('.msg.hide').addClass(data.alert.color);
				$('.msg.hide').show(500);
				
				switch(data.status){
					case 0:
					break;
					
					case 1:
						//
					break;
					
					case 2:
						setTimeout( function() { window.location.href = data.redirectURI; }, 5000);
					break;
				}
			},
			error:function(data){
				//if you need something to happen when an error occurs
			},
		});
	}
}

Hotspot.render = function() {
	console.log('Hotspot.render');
	if(!Hotspot.rendered){
		Hotspot.rendered = true;
		
		$('.button').click(Hotspot.send);
		$('.box .body .form input#name').enterKey(Hotspot.send);
	}
}

$(function(){
	Hotspot.render();
});