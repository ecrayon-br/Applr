<?php
// Exibe o destaque de conteudo
function smarty_function_PUBLICA_printYesNo($params, &$smarty) {
	// Verifica a validade do parametro
	switch(intval($params['var'])) {
		case 0:
		default:
	        return $smarty->_tpl_vars['T_nao'];
        break;
        
		case 1:
	        return $smarty->_tpl_vars['T_sim'];
        break;
	}
}
?>
