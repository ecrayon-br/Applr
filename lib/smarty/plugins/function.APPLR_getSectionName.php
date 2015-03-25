<?php
include_once 'modifier.APPLR_html_entity_decode.php';

// Exibe o destaque de conteudo
function smarty_function_APPLR_getSectionName($params, &$template) {
	$arrConst = get_defined_constants();
	
	// Defines SECTION ID
	if(!isset($params['sectionId']) || !is_numeric($params['sectionId'])) {
		$params['sectionId'] = $arrConst['SECTION'];
	}
	if(is_null($params['sectionId']) || !is_numeric($params['sectionId']) || $params['sectionId'] <= 0) return;

	// Defines SECTION ID
	if(!isset($params['languageId']) || !is_numeric($params['languageId'])) {
		$params['languageId'] = $arrConst['LANGUAGE'];
	}
	
	if(is_null($params['languageId']) || !is_numeric($params['languageId']) || $params['languageId'] <= 0) return;
	
	$objController = new Controller();
	
	$strSection = $objController->recordExists('rel_sec_language.name', 'rel_sec_language', 'sys_language_id = ' . $params['languageId'] . ' AND sec_config_id = ' . $params['sectionId']);

	if(isset($params['assign']) && !empty($params['assign'])) {
		$template->assign($params['assign'],$strSection);
	} else {
		return $strSection;
	}
}
?>
