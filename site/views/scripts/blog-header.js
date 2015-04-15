BlogHeader = {};
BlogHeader.rendered = false;

BlogHeader.send = function() {
	console.log('BlogHeader.send');
	
	CONFIG.clearError();
	
	var error		= false;
	var action		= HTTP + 'hotspot/save';
	var hotspotID	= ($(this).attr('hotspot-id') ? $(this).attr('hotspot-id') : $('.form').attr('hotspot-id'));
	var name 		= ($(this).attr('name') ? $(this).attr('name') : $('.mailing .form input#name').val()).trim();
	var display		= ($(this).attr('display') == 'box' ? 'box' : 'header');
	var box			= $(this);
	
	if(name == '') {
		error = true;
		$('.mailing .form input#name').parent().addClass('error type1');
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
					'preview': 1,
					'hotspot-id': 1,
					'rel_avatar': 4,
					'redirect-uri': SECTION_SEGMENT + '/' + PERMALINK
				  },
			beforeSend:function(){
				//if you need something to happen before the call
			},
			success:function(data){
				data = JSON.parse(data);
				//console.log(data);
				//console.log($('.msg.hide'));
				//console.log(display);
				
				if(display == 'box') {
					BlogHeader.box = box.closest('.box');
					
					BlogHeader.box.animate({opacity: 0},{duration: 500, start: function() {
							//$('.mailing').hide(500);
						}, complete: function() {
							console.log(BlogHeader.box);
							BlogHeader.box.html(data.alert.msg);
							
							BlogHeader.box.animate({opacity: 1},{duration: 500});
					}});
				} else if($('.msg.hide')[0]) {
					$('.form').hide(500);
					
					if(data.alert.msg != '') 	$('.msg.hide').html(data.alert.msg);
					$('.msg.hide').show(500);
				} else {
					$('.msg.show').animate({opacity: 0},{duration: 500, complete: function() {
						$('.msg.show').html(data.alert.msg);
						
						$('.msg.show').animate({opacity: 1},{duration: 500});
					}});
				}
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
		$('.box .button[display=box]').click(BlogHeader.send);
		$('.header .mailing input#name').enterKey(BlogHeader.send);
		
	}
}

$(function(){
	BlogHeader.render();
});