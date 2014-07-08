<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

// Exibe o destaque de conteudo
function smarty_function_PUBLICA_Blog_printTags($params, &$smarty) {
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
	
	// Defines DB Entity's TAG field
	if(!isset($params['blogTagField']) || empty($params['blogTagField'])) return;
	
	// Defines LINK SEPARATOR
	if(!isset($params['linkSeparator']) || empty($params['linkSeparator'])) {
		$params['linkSeparator'] = '<br /><br />';
	}
	
	// Defines QUERY LIMIT
	if(!isset($params['tableLimit']) || empty($params['tableLimit'])) {
		$params['tableLimit'] = 50;
	}
	
	// Gets BLOG TAGS data
	$objSection = fnConfigSecao($params['blogSectionID']);
	
	$strWhere 	= str_replace('bb_delete'		,$objSection->bt_tabela.'.bb_delete'	,REFINAMENTO);
	$strWhere 	= str_replace('bb_ativo'		,$objSection->bt_tabela.'.bb_ativo'		,$strWhere);
	$strWhere 	= str_replace('bd_publicacao'	,$objSection->bt_tabela.'.bd_publicacao',$strWhere);
	$strWhere 	= str_replace('bd_expiracao' 	,$objSection->bt_tabela.'.bd_expiracao'	,$strWhere);
	$strWhere  .= ' AND ' . $objSection->bt_tabela . '.tb_idioma_bn_id = ' . $params['languageId'];
	$strWhere  .= ' AND ' . $objSection->bt_tabela . '.' . $params['blogTagField'] .' != ""';
	$strWhere  .= ' AND ' . $objSection->bt_tabela . '.' . $params['blogTagField'] .' IS NOT NULL';
	
	$strQuery	= 'SELECT
					'.$params['blogTagField'].' 
					FROM  '.$objSection->bt_tabela.'
					WHERE '.$strWhere.'
				 	LIMIT '.$params['tableLimit'].';';
	$arrQuery	= $conexao->getCol($strQuery);
	$strTags	= implode(',',$arrQuery);
	
	// Configures TAG link
	$arrTags = explode(',',$strTags);
	$arrTags = array_unique($arrTags);
	
	// Sets HREF link for each tag
	foreach($arrTags AS &$strTag) {
		$strTag = '<a href="'.HTTP_DINAMICO.'/index.php?'.IN_SECAO.'='.$smarty->_tpl_vars['SECAO'].'&'.IB_BUSCA.'=1&'.$params['field'].'='.trim(strtolower($strTag)).'">'.trim(strtolower($strTag)).'</a>';
	}
	
	return implode($params['linkSeparator'],$arrTags);
}
?>
