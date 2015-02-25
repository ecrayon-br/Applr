<!--
var ComboBox = {}
ComboBox.injectCombo = function(elementId,visibleItems,callback)
{
	if(!callback)
		callback = "0";
		
	var element = $('#'+elementId)[0];
	var width = $(element).width();
	var initLabel = $('.combo-label',element).text();
	var contentList = [];
	$.each($('li',element),function(index,value){
		var label = $(value).text()
		var data = $(value).attr('data');
		if(!data){
			data = label
		}
		
		contentList.push({data:data,label:label});
	});
	
	$('ul',element).remove();
	
	
	$(element).addClass('combobox');
	$(element).addClass('scrollpanel');
	$(element)[0].setAttribute("callback",callback);
	element.data = contentList;
	element.callback = callback;
	element.visibleItems = visibleItems;
	
	//$(element).css({width:width});
	
		var html = (initLabel ? '	<div class="combo-label">'+initLabel+'</div>' : '');
		var html = '';
		html += '	<div class="list">';
		html += '		<div class="viewport">';
		html += '			<div class="overview">';
		
		for(var i=0; i<contentList.length; i++)
		{
			html += '			   <div class="combo-item" data-index="'+i+'" data-value="'+contentList[i].data+'">'+contentList[i].label+'</div>';
		}//
		
		html += '			</div>';
		html += '		</div>';		
		html += '		<div class="scrollbar">';
		html += '			<div class="track"></div>';
		html += '			<div class="thumb">';
		html += '			</div>';
		html += '		</div>';
		html += (initLabel ? '	</div>' : '');
		
		
	$(element).append(html);
	
	var h = visibleItems*($('.combo-item',element).height()+1) - 1;

	$(".list",element).css({height:h+'px'});
	$(".list",element).tinyscrollbar({axis:'y',size:h,sizethumb:Math.floor(h*.25)});
	$('.combo-item',element).on('click',ComboBox.handleCombo);
	$('.combo-item',element).attr('callback',callback);
	
	//if(!initLabel) $(document).on('click',closeCombos);
	
	$('.combo-label',element).on('click',ComboBox.openCombo);
	
	element.refreshData = function(contentList,keepOpen){
		console.log('refreshData');
		var list = $('.list',this);
		var minHeight = $('.combo-item',this).height();
		if(!minHeight)
			minHeight = 28;
		var h = this.visibleItems*(minHeight+1) - 1;
		
		list.css({height:h+'px'});
	
		var overview = $('.overview',this);
		var state = list.css('display');
		this.data = contentList;
		
		$('.combo-item',this).off();

		overview.html('');
		var html = '';
		for(var i=0; i<contentList.length; i++)
		{
			html += '			   <div class="combo-item" data-index="'+i+'" data-value="'+contentList[i].data+'">'+contentList[i].label+'</div>';
		}//
		list.show();
		overview.append(html);		
		$('.combo-item',this).on('click',ComboBox.handleCombo);
		$('.combo-item',this).attr('callback',this.callback);
		list.tinyscrollbar_update('top');
		this.checkScroll();
		if(!keepOpen)
			list.css('display',state);
		else{
			$(document).off('click');
			setTimeout( function(){ $(document).on('click',ComboBox.closeCombos); }, 10 );
		}
	}
	element.open = function(){
		console.log('open');
		var list =  $('.list',this);
		if(list.css('display') == 'block')
		{
			this.close();
		}
		else
		{
			setTimeout( function(){ $(document).on('click',ComboBox.closeCombos); }, 10 );
			list.show();
			list.tinyscrollbar_update('top');
			this.checkScroll();
		}
	}
	element.close = function(){
		console.log('close');
		$(document).off('click',ComboBox.closeCombos);
		var list =  $('.list',this);
		list.hide();
	}
	element.hideSlider = function(){
		console.log('hideSlider');
		var scrollBar =  $('.scrollbar',this);
		scrollBar.hide();
	}
	element.showSlider = function(){
		console.log('showSlider');
		var scrollBar =  $('.scrollbar',this);
		scrollBar.show();
	}
	element.setData = function(index){
		console.log('setData');
		$('.combo-label',this).text(this.data[index].label);
		$.each($('.combo-item',this),function(i,value){
			if($(this).attr('data-index') == index)
			{
				$(this).trigger('click');
			}
		})
	}
	element.setDataManually = function(data){
		$('.combo-label',this).text(data.label);
		$('.combo-label',this).attr('data',data.data);
	}
	element.getData = function(){
		var data = $('.combo-label',this).attr('data');
		var label = $('.combo-label',this).text();
		return {data:data,label:label};
	}
	element.addNewItem = function(data,index){
		var html = '<div class="combo-item" data-index="'+index+'" data-value="'+data.data+'">'+data.label+'</div>';
		var before = null;
		$.each($('.combo-item',this),function(idx,value){
			var i = parseInt($(value).attr('data-index'));
			if(i >= index){
				if(!before)
				before = $(value);
				
				i++;
				$(value).attr('data-index',i)
			}
		});
		var newItem = $(html);
		newItem.on('click',ComboBox.handleCombo);
		newItem.attr('callback',this.callback);
		if(before)
			before.before(newItem);
		else
			$('.overview',this).append(newItem);
	}
	
	element.checkScroll = function(){
		console.log('checkScroll');
		if($('.combo-item',this).length > this.visibleItems ){
			this.showSlider();
			$(".list",this).height(this.visibleItems*28 + 4);
		}
		else{
			this.hideSlider();
			$(".list",this).height($('.combo-item',this).length*28 + 2);
		}
		/*if($('.overview',this).innerHeight() <= $('.list',this).innerHeight())
		{
			this.hideSlider();
			//$(".list",this).height($('.overview',this).innerHeight());
		}else{
			this.showSlider();
		}*/
	}
	
	element.addError = function(errorType){
		this.removeError();
		$(this).parent().addClass('error');
		if(errorType && errorType != "")
			$(this).parent().addClass(errorType);
	}
	element.removeError = function(){
		$(this).parent().removeClass('error');
		$(this).parent().removeClass('type1');
		$(this).parent().removeClass('type2');
		$(this).parent().removeClass('type3');
	}
	
	element.checkScroll();
	
	$(".list",element).hide();
	return element;
	
}
ComboBox.hideSlider = function(element)
{
	console.log('hideSlider');
	var bar =  $('.scrollbar',$(element));
	bar.hide();
	
}
ComboBox.showSlider = function(element)
{
	console.log('showSlider');
	var bar =  $('.scrollbar',$(element));
	bar.show();
	
}
ComboBox.handleCombo = function(e)
{
	console.log('handleCombo');
	ComboBox.closeCombos();
	var self = this;

	if(!$(e.target).attr("callback"))
		self = $(e.target).parent();
		
	e.label = $(self).text();
	e.data = $(self).attr('data-value');
	e.index = $(self).attr('data-index');

	$(self).parent().parent().parent().parent().find('.combo-label').text(e.label);
	$(self).parent().parent().parent().parent().find('.combo-label').attr('data',e.data);
	var call = $(self).attr("callback");
	if(call != "0")
		eval(call)(e)
}
ComboBox.openCombo = function(action)
{	
	console.log('openCombo');
	var combo_display_check,list,element;
	if(action == 'force'){
		element = $(this);
	}
	else{
		element = $($(this).parent());
	}
	list = $('.list',$(element));
	combo_display_check = list.css("display");
	ComboBox.closeCombos();
	//element.removeError();
	element[0].removeError();
	if(combo_display_check != "block")
	{
		list.show();
		list.tinyscrollbar_update('top');
		setTimeout( function(){ $(document).on('click',ComboBox.closeCombos); }, 10 );
		//$(this).parent();
	}
	//trace('list:' + $('.viewport .overview .combo-item',list).html());
	element[0].checkScroll();
	
	$(element).css({width:$(element).width()});
}
ComboBox.closeCombos = function(e)
{	
	console.log('closeCombos');
	$(document).off('click',ComboBox.closeCombos);
	$('.combobox').each(function(i) {
		
		var list = $('.list',$(this));
		if(list.css("display") == "block")
		{
			if(e && $(this).hitTest(e, e.pageX,e.pageY))//(list.hitTest(e.clientX,e.clientY) || $(this).hitTest(e.clientX,e.clientY)))
			{
				
			}
			else
			{
				list.hide();
			}
		}
	});
}

$.fn.hitTest = function(e, x, y){
	var box = $(this).closest('.filter-box');
	var list = $('.list',box);
	var bounds = list.offset();
	
	bounds.right = bounds.left + list.outerWidth();
	bounds.bottom = bounds.top + list.outerHeight();

	console.log('-----------------');
	console.log(y);
	console.log(bounds.top);
	console.log(bounds.bottom);
	console.log(x);
	console.log(bounds.left);
	console.log(bounds.right);
	/*
	console.log(x >= (bounds.left));
	console.log(x <= (bounds.right));
	console.log(y >= (bounds.top));
	console.log(y <= (bounds.bottom));
	*/
	console.log('-----------------');

	if(x >= (bounds.left) && x <= (bounds.right) && y >= (bounds.top) && y <= (bounds.bottom)){
		return true;
	}
	return false;
}
-->