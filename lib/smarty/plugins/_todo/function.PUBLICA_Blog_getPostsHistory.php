<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

// Exibe o destaque de conteudo
function smarty_function_PUBLICA_Blog_getPostsHistory($params, &$smarty) {
	global $conexao;
	
	// Defines SECTION ID
	if(!isset($params['blogSectionID']) || !is_numeric($params['blogSectionID'])) {
		$params['blogSectionID'] = $smarty->_tpl_vars['SECAO'];
	}
	if(is_null($params['blogSectionID']) || !is_numeric($params['blogSectionID']) || $params['blogSectionID'] <= 0) return;
	
	// Defines LANGUAGE ID
	if(!isset($params['languageId']) || !is_numeric($params['languageId'])) {
		$params['languageId'] = $smarty->_tpl_vars['IDIOMA'];
	}
	if(is_null($params['languageId']) || !is_numeric($params['languageId']) || $params['languageId'] <= 0) return;
	
	// Defines LINK SEPARATOR
	if(!isset($params['linkSeparator']) || empty($params['linkSeparator'])) {
		$params['linkSeparator'] = "\n";
	}
	
	// Defines GROUP TYPE
	if(!isset($params['groupType']) || empty($params['groupType'])) {
		$params['groupType'] = 'month-year';
	}
	
	// Defines GROUP TYPE
	if(!isset($params['listType']) || empty($params['listType'])) {
		$params['listType'] = 'select';
	}
	
	// Defines QUERY LIMIT
	if(!isset($params['tableLimit']) || empty($params['tableLimit'])) {
		$params['tableLimit'] = 12;
	}
	
	// Gets BLOG section data
	$objBlogSection = fnConfigSecao($params['blogSectionID']);
	
	// Gets total comments
	$strWhere 	= str_replace('bb_delete'		,$objBlogSection->bt_tabela.'.bb_delete'	,REFINAMENTO);
	$strWhere 	= str_replace('bb_ativo'		,$objBlogSection->bt_tabela.'.bb_ativo'		,$strWhere);
	$strWhere 	= str_replace('bd_publicacao'	,$objBlogSection->bt_tabela.'.bd_publicacao',$strWhere);
	$strWhere 	= str_replace('bd_expiracao' 	,$objBlogSection->bt_tabela.'.bd_expiracao'	,$strWhere);
	$strWhere  .= ' AND ' . $objBlogSection->bt_tabela . '.tb_idioma_bn_id = ' . $params['languageId'];
		
	// Gets DB data
	switch($params['groupType']) {
		case 'month-year':
		default:
			$strQuery = 'SELECT DISTINCT
							MONTH(bd_publicacao)	AS month,
							YEAR(bd_publicacao)		AS year,
							CONCAT(YEAR(bd_publicacao),"-",IF(MONTH(bd_publicacao) < 10,CONCAT("0",MONTH(bd_publicacao)),MONTH(bd_publicacao)),"-01") AS value
						 FROM	'.$objBlogSection->bt_tabela.'
						 WHERE	'.$strWhere.'
						 LIMIT '.$params['tableLimit'].';';
		break;
	}
	$objQuery = $conexao->getAll($strQuery,array(),DB_FETCHMODE_OBJECT);
	
	// Sets HISTORY LINKs
	$arrReturnData = array();
	switch($params['listType']) {
		case 'select':
		default:
			foreach($objQuery AS $objData) {
				switch($params['groupType']) {
					case 'month-year':
					default:
						$arrReturnData[] = '<option value="'.$objData->value.'" ' . (isset($params['selectedValue']) && $params['selectedValue'] == $objData->value ? 'selected' : '') . '>'.date("M / Y",strtotime($objData->value)).'</option>';
					break;
				}
			}
		break;
	}
	
	return implode($params['linkSeparator'],$arrReturnData);
}
?>
