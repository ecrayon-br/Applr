<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

// Exibe o destaque de conteudo
function smarty_function_PUBLICA_getParentName($params, &$smarty) {
	global $conexao;
	
	// Defines SECTION ID
	if(!isset($params['sectionId']) || !is_numeric($params['sectionId'])) {
		$params['sectionId'] = $smarty->_tpl_vars['SECAO'];
	}
	if(is_null($params['sectionId']) || !is_numeric($params['sectionId']) || $params['sectionId'] <= 0) return;
	
	// Defines SECTION ID
	if(!isset($params['languageId']) || !is_numeric($params['languageId'])) {
		$params['languageId'] = $smarty->_tpl_vars['IDIOMA'];
	}
	if(is_null($params['languageId']) || !is_numeric($params['languageId']) || $params['languageId'] <= 0) return;
	
	$strQuery = 'SELECT tb_secao_idioma.bt_nome FROM tb_secao_idioma JOIN tb_secao WHERE (tb_secao_idioma.tb_secao_bn_id = tb_secao.bn_pai OR tb_secao_idioma.tb_secao_bn_id = tb_secao.bn_id) AND tb_secao.bn_id = "' . $params['sectionId'] . '" AND tb_secao_idioma.tb_idioma_bn_id = "' . $params['languageId'] . '";';
	$strSection = $conexao->getOne($strQuery);
	
	if(isset($params['assign']) && !empty($params['assign'])) {
		$smarty->assign($params['assign'],$strSection);
	} else {
		return $strSection;
	}
}
?>
