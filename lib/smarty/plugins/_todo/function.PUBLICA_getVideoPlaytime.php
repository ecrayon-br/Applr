<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

// Exibe o destaque de conteudo
function smarty_function_PUBLICA_getVideoPlaytime($params, &$smarty) {
	if(!isset($params['var']) || empty($params['var'])) {
		return false;
	}
	
	$objID3 = $smarty->_tpl_vars['objID3']->analyze(str_replace(UPLOAD,ROOT_UPLOAD,$params['var']));
	
	return $objID3['playtime_string'];
}
?>
