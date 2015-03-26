BlogHeader = {};
BlogHeader.rendered = false;

BlogHeader.send = function() {
	console.log('BlogHeader.send');
	
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
					'name': name
				  },
			beforeSend:function(){
				//if you need something to happen before the call
			},
			success:function(data){
				data = JSON.parse(data);
				console.log(data);
				
				$('.form').hide(500);
				
				if(data.alert.msg != '') 	$('.msg.hide').html(data.alert.msg);
				$('.msg.hide').show(500);
			},
			error:function(data){
				//if you need something to happen when an error occurs
			},
		});
	}
}

BlogHeader.render = function() {
	console.log('BlogHeader.render');
	if(!BlogHeader.rendered){
		BlogHeader.rendered = true;
		
		$('.header .button').click(BlogHeader.send);
		
	}
}

$(function(){
	BlogHeader.render();
});