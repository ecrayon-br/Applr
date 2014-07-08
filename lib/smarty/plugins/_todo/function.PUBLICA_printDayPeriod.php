<?php
// Exibe o destaque de conteudo
function smarty_function_PUBLICA_printDayPeriod($params, &$smarty) {
	// Verifica a validade do parametro
	switch(intval($params['var'])) {
		case 0:
		default:
	        return;
        break;
        
		case 1:
	        return $smarty->_tpl_vars['T_a_craseado'].' '.$smarty->_tpl_vars['T_manha'];
        break;
        
		case 2:
	        return $smarty->_tpl_vars['T_a_craseado'].' '.$smarty->_tpl_vars['T_tarde'];
        break;
        
		case 3:
	        return $smarty->_tpl_vars['T_a_craseado'].' '.$smarty->_tpl_vars['T_noite'];
        break;
	}
}
?>
