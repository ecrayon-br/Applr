<?php
if(file_exists('../../cms/include-sistema/classe_smarty.php')) {
	include_once '../../cms/include-sistema/classe_smarty.php';
} else {
	include_once '../../../cms/include-sistema/classe_smarty.php';
}

// Exibe o destaque de conteudo
function smarty_function_PUBLICA_printLiteralDate($params, &$smarty) {
	if(!isset($params['intLanguage'])) $params['intLanguage'] = $smarty->_tpl_vars['IDIOMA'];
	if(!isset($params['dateValue'])) return;
	
	$smarty = new smartyConnect;
	include_once 'modifier.date_format.php';
	
	// Verifica a validade do parametro
	switch($params['intLanguage']) {
		case 0:
	        return;
        break;
        
		case 1: // pt_BR
		default:
	        return utf8_encode(smarty_modifier_date_format($params['dateValue'],"%d de %B de %Y"));
        break;
        
		case 3: // EN
	        return utf8_encode(smarty_modifier_date_format($params['dateValue'],"%d %B %Y"));
        break;
	}
}
?>
