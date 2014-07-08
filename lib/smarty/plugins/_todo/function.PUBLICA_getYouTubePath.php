<?php
// Returns YouTube video path from string given in $params['value']
function smarty_function_PUBLICA_getYouTubePath($params, &$smarty) {
	// Checks params
	if(!isset($params['value']) || !is_string($params['value'])) {
		return;
	}
	if(!isset($params['querystring']) || !is_string($params['querystring'])) {
		$params['querystring'] = '';
	}
	$params['value'] = str_replace('youtu.be/','youtube.com/embed/',$params['value']);
	$params['value'] = str_replace('watch?v=','embed/',$params['value']);
	$params['value'] = str_replace('embed/embed/','embed/',$params['value']);
	
	if(strstr($params['value'],'&')) {
		$params['value'] .= '&'.$params['querystring'];
	} else {
		$params['value'] .= '?'.$params['querystring'];
	}
	
	return $params['value'];
}
?>
