SendMail = {};
SendMail.rendered = false;

SendMail.send = function() {
	console.log('SendMail.send');
	
	CONFIG.clearError();
	
	var error		= false;
	var action		= HTTP + 'contato/sendMail';
	var name 		= ($(this).attr('name') 	? $(this).attr('name') 		: $('.box .form input#name').val()).trim();
	var mail 		= ($(this).attr('mail') 	? $(this).attr('mail') 		: $('.box .form input#mail').val()).trim();
	var subject		= ($(this).attr('subject') 	? $(this).attr('subject') 	: $('.box .form input#subject').val()).trim();
	var text 		= ($(this).attr('text') 	? $(this).attr('text') 		: $('.box .form textarea#text').val()).trim();

	console.log(action);
	console.log($('.form input#name'));
	console.log($('.form input#name').val);
	console.log(name);
	console.log(mail);
	console.log(subject);
	console.log(text);
	
	if(name == '') {
		error = true;
		$('.box .form input#name').closest('.form').addClass('error type1');
	}
	if(mail == '') {
		error = true;
		$('.box .form input#mail').closest('.form').addClass('error type1');
	} else if(!CONFIG.checkMailSyntax(mail)) {
		error = true;
		$('.box .form input#mail').closest('.form').addClass('error type2');
	}
	if(subject == '') {
		error = true; 
		$('.box .form input#subject').closest('.form').addClass('error type1');
	}
	if(text == '') {
		error = true;
		$('.box .form textarea#text').closest('.form').addClass('error type1');
	}
	
	if(!error) {
		$.ajax({
			type: 'POST',
			url: action,
			data: {
					'name': name,
					'mail': mail,
					'subject': subject,
					'text': text
				  },
			beforeSend:function(){
				//if you need something to happen before the call
			},
			success:function(data){
				data = JSON.parse(data);
				console.log(data);

				$('.content .list .form').animate({opacity: 0},{duration: 500, complete: function() {
					$('.content .list .box .form:first').html(data.alert.msg);
					
					$('.content .list .box .form:first').animate({opacity: 1},{duration: 500});
				}});
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
		
		$('.box .form .button').click(SendMail.send);
		$('.box .form .input input').enterKey(SendMail.send);
		
	}
}

$(function(){
	SendMail.render();
});