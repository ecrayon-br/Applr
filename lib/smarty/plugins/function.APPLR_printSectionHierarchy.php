<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * APPLR_printSectionHierarchy function plugin
 *
 * Type:     function<br>
 * Name:     APPLR_printSectionHierarchy<br>
 * Purpose:  print out a FCKeditor box
 *
 * @author Diego Flores <diegotf [at] gmail [dot] com>
 * 
 * @param array                    $params   parameters
 * 
 * @return mixed
 */
function smarty_function_APPLR_printSectionHierarchy($params,&$template) {
	$objHierarchy = $template->getTemplateVars('objHierarchy');
	if(empty($objHierarchy)) 	return;
	if(empty($params['tpl'])) 	return;
	displayData($template,$objHierarchy,$params['tpl'],$params['spacer']);
}

function displayData(&$template,$objData,$strTPL,$strSpacer_format,$intLevel = 0) {
	
	foreach($objData AS $objTmp) {
		$strSpacer = str_repeat($strSpacer_format, $intLevel);
		
		$template->assign('strSpacer',$strSpacer);
		$template->assign('intLevel',$intLevel);
		$template->assign('objTmp',$objTmp);
		
		if($template->templateExists($strTPL)) {
			$template->display($strTPL);
		}
		
		if(!empty($objTmp->child)) {
			$intNewLevel = $intLevel + 1; 
			displayData($template,$objTmp->child,$strTPL,$strSpacer_format,$intNewLevel);
		}
	}
}
?>