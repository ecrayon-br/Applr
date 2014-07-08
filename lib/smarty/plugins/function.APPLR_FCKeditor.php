<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * APPLR FCKeditor function plugin
 *
 * Type:     function<br>
 * Name:     APPLR_FCKeditor<br>
 * Purpose:  print out a FCKeditor box
 *
 * @author Diego Flores <diegotf [at] gmail [dot] com>
 * 
 * @param array                    $params   parameters
 * 
 * @return mixed
 */
function smarty_function_APPLR_FCKeditor($params) {
	if(empty($params['name'])) return;
	
	// Sets FCKEditor object class
	$objFCK = new FCKeditor($params['name']);
	
	$objFCK->BasePath = HTTP . 'lib/FCKeditor/';
	$objFCK->ToolbarSet = 'APPLR';
	$objFCK->Height = '400';
	
	if(!empty($params['value'])) $objFCK->Value = $params['value'];
	
	$objFCK->Create();
	
    return;
}
?>