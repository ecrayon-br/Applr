Hotspot = {};
Hotspot.rendered = false;

Hotspot.send = function() {
	console.log('Hotspot.send');
	
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
	}
}

$(function(){
	Hotspot.render();
});