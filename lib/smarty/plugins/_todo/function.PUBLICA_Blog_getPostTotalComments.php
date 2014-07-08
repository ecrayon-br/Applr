<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

// Exibe o destaque de conteudo
function smarty_function_PUBLICA_Blog_getPostTotalComments($params, &$smarty) {
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
	
	// Checks params
	if(!isset($params['commentSectionID']) || !is_numeric($params['commentSectionID'])) return;
	if(!isset($params['blogPostID']) || !is_numeric($params['blogPostID'])) return;
	
	// Gets BLOG section data
	$objSection = fnConfigSecao($params['blogSectionID']);
	
	// Gets BLOG COMMENTS DB Entity's name
	$strQuery = 'SELECT tb_secao_relacionamento.bt_tabela FROM tb_secao_relacionamento WHERE tb_secao_bn_id = "' . $params['commentSectionID'] . '" AND bn_secao = "' . $params['blogSectionID'] . '";';
	$strBlogCommentsRelTable = $conexao->getOne($strQuery);
	
	// Gets total comments
	$strBlogPK = str_replace('_conteudo','',$objSection->bt_tabela);
	$strBlogCommentsPK = str_replace(array('tb_rel_','_'.$strBlogPK),'',$strBlogCommentsRelTable).'_bn_id';
	$strBlogPK .= '_bn_id';
	
	$strWhere 	= str_replace('bb_delete'		,$objSection->bt_tabela.'.bb_delete'	,REFINAMENTO);
	$strWhere 	= str_replace('bb_ativo'		,$objSection->bt_tabela.'.bb_ativo'		,$strWhere);
	$strWhere 	= str_replace('bd_publicacao'	,$objSection->bt_tabela.'.bd_publicacao',$strWhere);
	$strWhere 	= str_replace('bd_expiracao' 	,$objSection->bt_tabela.'.bd_expiracao'	,$strWhere);
	$strWhere  .= ' AND ' . $objSection->bt_tabela . '.tb_idioma_bn_id = ' . $params['languageId'] . ' AND ' . $objSection->bt_tabela . '.bn_id = "' . $params['blogPostID'] . '"';
	
	$strQuery = 'SELECT
					COUNT('.$strBlogCommentsPK.') 
				 FROM  '.$objSection->bt_tabela.'
				 JOIN  '.$strBlogCommentsRelTable.' ON '.$strBlogCommentsRelTable.'.'.$strBlogPK.' = '.$objSection->bt_tabela.'.bn_id
				 WHERE '.$strWhere;
	//echo $strQuery;
	return $conexao->getOne($strQuery);
}
?>
