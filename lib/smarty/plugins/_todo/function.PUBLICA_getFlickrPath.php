<?php
// Returns Flickr photostream path from string given in $params['value']
function smarty_function_PUBLICA_getFlickrPath($params, &$smarty) {
	// Checks params
	if(!isset($params['value'])		|| !is_string($params['value']))	{
		return;
	}
	if(!isset($params['user_id'])	|| !is_string($params['user_id']))	{
		return;
	}
	
	if(isset($params['contacts']) 	&& is_string($params['contacts']) && $params['contacts'] != '') {
		$params['contacts'] = '&contacts='.$params['contacts'];
	} else {
		$params['contacts'] = '';
	}
	if(isset($params['text']) 	&& is_string($params['text']) && $params['text'] != '') {
		$params['text'] = '&text='.$params['text'];
	} else {
		$params['text'] = '';
	}
	if(isset($params['tag_mode']) 	&& is_string($params['tag_mode']) && $params['tag_mode'] != '') {
		$params['tag_mode'] = '&tag_mode='.$params['tag_mode'];
	} else {
		$params['tag_mode'] = '';
	}
	if(isset($params['favorites']) 	&& is_string($params['favorites']) && $params['favorites'] != '') {
		$params['favorites'] = '&favorites='.$params['favorites'];
	} else {
		$params['favorites'] = '';
	}
	if(isset($params['group_id']) 	&& is_string($params['group_id']) && $params['group_id'] != '') {
		$params['group_id'] = '&group_id='.$params['group_id'];
	} else {
		$params['group_id'] = '';
	}
	if(isset($params['frifam']) 	&& is_string($params['frifam']) && $params['frifam'] != '') {
		$params['frifam'] = '&frifam='.$params['frifam'];
	} else {
		$params['frifam'] = '';
	}
	if(isset($params['nsid']) 	&& is_string($params['nsid']) && $params['nsid'] != '') {
		$params['nsid'] = '&nsid='.$params['nsid'];
	} else {
		$params['nsid'] = '';
	}
	if(isset($params['single']) 	&& is_string($params['single']) && $params['single'] != '') {
		$params['single'] = '&single='.$params['single'];
	} else {
		$params['single'] = '';
	}
	if(isset($params['firstIndex']) 	&& is_string($params['firstIndex']) && $params['firstIndex'] != '') {
		$params['firstIndex'] = '&firstIndex='.$params['firstIndex'];
	} else {
		$params['firstIndex'] = '';
	}
	if(isset($params['set_id']) 	&& is_string($params['set_id']) && $params['set_id'] != '') { 
		$params['set_id'] = '&set_id='.$params['set_id'];
	} elseif($intPos = strpos($params['value'],'sets/')) {
		$setId				= substr($params['value'] , ($intPos+5) );
		$setId				= str_replace('/','',$setId);
		$params['set_id']	= '&set_id='.$setId;
	} else {
		$params['set_id']	= '';
	}
	if(isset($params['firstId']) 	&& is_string($params['firstId']) && $params['firstId'] != '') {
		$params['firstId'] = '&firstId='.$params['firstId'];
	} else {
		$params['firstId'] = '';
	}
	
	$strURL  = 	'http://www.flickr.com/slideShow/index.gne?';
	$strURL .= 	'user_id='.$params['user_id'];
	$strURL .= 	$params['contacts'].
				$params['text'].
				$params['tag_mode'].
				$params['favorites'].
				$params['group_id'].
				$params['frifam'].
				$params['nsid'].
				$params['single'].
				$params['firstIndex'].
				$params['set_id'].
				$params['firstId'];
	
	return $strURL;
}
?>
