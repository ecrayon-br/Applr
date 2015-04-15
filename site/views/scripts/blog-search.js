BlogSearch = {};
BlogSearch.rendered = false;

BlogSearch.searchByTerm = function() {
	console.log('BlogSearch.send');
	
	CONFIG.clearError();

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


BlogSearch.searchByTag = function() {
	console.log('BlogSearch.send');
	
	CONFIG.clearError();

	var error		= false;
	var name 		= $(this).attr('search');
	var field_name 	= $(this).attr('field_name');
	var field_match = $(this).attr('field_match');
	var action		= HTTP + 'blog/search/';
	
	if(name == '') {
		error = true;
		 $('.search input#search').parent().addClass('error type1');
	}

	if(!error) {
		$.ajax({
			type: 'GET',
			url: action,
			data: {
					'search': name,
					'field_name': field_name,
					'field_match': field_match,
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
		
		$('.extra .search .button').click(BlogSearch.searchByTerm);
		$('.extra .box .tag-search').click(BlogSearch.searchByTag);
		$('.extra .search input#search').enterKey(BlogSearch.searchByTerm);
		
	}
}

$(function(){
	BlogSearch.render();
});