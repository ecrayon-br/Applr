<?php
if(file_exists('../../cms/include-sistema/variaveis.php')) {
	include_once '../../cms/include-sistema/variaveis.php';
} else {
	include_once '../../../cms/include-sistema/variaveis.php';
}

// Exibe o destaque de conteudo
function smarty_function_PUBLICA_Blog_printPostTags($params, &$smarty) {
	if(!isset($params['value'])) return;
	if(!isset($params['field'])) return;
	
	// Defines LINK SEPARATOR
	if(!isset($params['linkSeparator']) || empty($params['linkSeparator'])) {
		$params['linkSeparator'] = ', ';
	}
	
	// Explode tags in comma-separator
	$arrTags = explode(',',$params['value']);
	$arrTags = array_unique($arrTags);
	
	// Sets HREF link for each tag
	foreach($arrTags AS &$strTag) {
		$strTag = '<a href="'.HTTP_DINAMICO.'index.php?'.IN_SECAO.'='.$smarty->_tpl_vars['SECAO'].'&'.IB_BUSCA.'=1&'.$params['field'].'='.trim(strtolower($strTag)).'">'.trim(strtolower($strTag)).'</a>';
	}
	
	return implode($params['linkSeparator'],$arrTags);
}
?>
