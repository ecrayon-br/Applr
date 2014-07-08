<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

// Exibe o destaque de conteudo
function smarty_function_PUBLICA_getTableName($params, &$smarty) {
	global $conexao;
	
	// Defines SECTION ID
	if(!isset($params['sectionId']) || !is_numeric($params['sectionId'])) {
		$params['sectionId'] = $smarty->_tpl_vars['SECAO'];
	}
	if(is_null($params['sectionId']) || !is_numeric($params['sectionId']) || $params['sectionId'] <= 0) return;
	
	$strQuery = 'SELECT REPLACE(tb_secao.bt_tabela,"tb_conteudo_","") FROM tb_secao WHERE tb_secao.bn_id = "' . $params['sectionId'] . '";';
	
	$strSection = $conexao->getOne($strQuery);
	
	if(isset($params['assign']) && !empty($params['assign'])) {
		$smarty->assign($params['assign'],$strSection);
	} else {
		return $strSection;
	}
}
?>
