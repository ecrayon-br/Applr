<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

// Exibe o destaque de conteudo
function smarty_function_PUBLICA_Blog_getBlogLastComments($params, &$smarty) {
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
		$params['linkSeparator'] = '<br /><br />';
	}
	
	// Defines QUERY LIMIT
	if(!isset($params['tableLimit']) || empty($params['tableLimit'])) {
		$params['tableLimit'] = 50;
	}
	
	// Checks params
	if(!isset($params['commentSectionID']) || !is_numeric($params['commentSectionID'])) return;
	
	// Gets BLOG section data
	$objBlogSection = fnConfigSecao($params['blogSectionID']);
	
	// Gets BLOG COMMENTS section data
	$objCommentSection = fnConfigSecao($params['commentSectionID']);
	
	// Gets BLOG POSTS - COMMENTS relationship DB Entity's name
	$strQuery = 'SELECT tb_secao_relacionamento.bt_tabela FROM tb_secao_relacionamento WHERE tb_secao_bn_id = "' . $params['commentSectionID'] . '" AND bn_secao = "' . $params['blogSectionID'] . '";';
	$strBlogCommentsRelTable = $conexao->getOne($strQuery);
	
	// Gets total comments
	$strBlogPK = str_replace('_conteudo','',$objBlogSection->bt_tabela);
	$strBlogCommentsPK = str_replace(array('tb_rel_','_'.$strBlogPK),'',$strBlogCommentsRelTable).'_bn_id';
	$strBlogPK .= '_bn_id';
	
	$strWhere 	= str_replace('bb_delete'		,$objBlogSection->bt_tabela.'.bb_delete'	,REFINAMENTO);
	$strWhere 	= str_replace('bb_ativo'		,$objBlogSection->bt_tabela.'.bb_ativo'		,$strWhere);
	$strWhere 	= str_replace('bd_publicacao'	,$objBlogSection->bt_tabela.'.bd_publicacao',$strWhere);
	$strWhere 	= str_replace('bd_expiracao' 	,$objBlogSection->bt_tabela.'.bd_expiracao'	,$strWhere);
	$strWhere  .= ' AND ' . $objBlogSection->bt_tabela . '.tb_idioma_bn_id = ' . $params['languageId'];
	if(isset($params['blogPostID']) && !empty($params['blogPostID'])) $strWhere  .= ' AND ' . $objBlogSection->bt_tabela . '.bn_id = "' . $params['blogPostID'] . '"';
	
	// Gets DB data
	$strQuery = 'SELECT
					'.$objCommentSection->bt_tabela.'.*,
					'.$objBlogSection->bt_tabela.'.permalink
				 FROM	'.$objCommentSection->bt_tabela.'
				 JOIN	'.$strBlogCommentsRelTable.' ON '.$strBlogCommentsRelTable.'.'.$strBlogCommentsPK.' = '.$objCommentSection->bt_tabela.'.bn_id
				 JOIN  '.$objBlogSection->bt_tabela.' ON '.$strBlogCommentsRelTable.'.'.$strBlogPK.' = '.$objBlogSection->bt_tabela.'.bn_id
				 LIMIT '.$params['tableLimit'].';';
	$objQuery = $conexao->getAll($strQuery,array(),DB_FETCHMODE_OBJECT);

	// Sets HREF link for each tag
	$arrComments = array();
	foreach($objQuery AS $objComment) {
		$arrComments[] = '<a href="'.HTTP.str_replace('tb_conteudo_','',$objBlogSection->bt_tabela).'/'.$objComment->permalink.'">'.substr(trim($objComment->bt_texto),0,140).(strlen(trim($objComment->bt_texto)) > 140 ? '...' : '').'</a>';
	}
	
	return implode($params['linkSeparator'],$arrComments);
}
?>
