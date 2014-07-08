<?php
// Exibe o destaque de conteudo
function smarty_function_PUBLICA_printStatus($params, &$smarty) {
	// Verifica a validade do parametro
	switch(intval($params['var'])) {
		case 0:
		default:
	        return $smarty->_tpl_vars['T_inativo'];
        break;
        
		case 1:
	        return $smarty->_tpl_vars['T_ativo'];
        break;
	}
}
?>
