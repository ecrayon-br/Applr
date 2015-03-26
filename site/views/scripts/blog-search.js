BlogSearch = {};
BlogSearch.rendered = false;

BlogSearch.send = function() {
	console.log('BlogSearch.send');
	
	CONFIG.clearError();

	console.log($(this));
	console.log($(this).attr('search'));
	
	var action		= HTTP + 'blog/search';
	var name = ($(this).attr('search') ? $(this).attr('search') : $('.search input#search').val().trim() );
	
	if(name == '') {
		 $('.search input#search').parent().addClass('error type1');
	} else {
		$.ajax({
			type: 'POST',
			url: action,
			data: {
					'search': name,
					'template': 'blog-grid-content.html'
				  },
			beforeSend:function(){
				//if you need something to happen before the call
			},
			success:function(data){
				data = JSON.parse(data);
				//console.log(data);
				
				$('.content .list').animate({opacity: 0},{duration: 500, complete: function() {
					$('.content .list').html(data.html);
					
					$('.content .list').animate({opacity: 1},{duration: 500});
				}});
				
			},
			error:function(data){
				//if you need something to happen when an error occurs
			},
		});
	}
}

BlogSearch.render = function() {
	console.log('BlogSearch.render');
	if(!BlogSearch.rendered){
		BlogSearch.rendered = true;
		
		$('.search .button').click(BlogSearch.send);
		$('.box .tag-search').click(BlogSearch.send);
		
	}
}

$(function(){
	BlogSearch.render();
});