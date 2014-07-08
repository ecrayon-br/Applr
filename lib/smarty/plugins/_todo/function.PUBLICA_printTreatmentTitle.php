<?php
// Exibe o destaque de conteudo
function smarty_function_PUBLICA_printTreatmentTitle($params, &$smarty) {
	// Verifica a validade do parametro
	switch(intval($params['var'])) {
		case 0:
		default:
	        return;
        break;
        
		case 1:
	        return $smarty->_tpl_vars['T_sr'];
        break;
        
		case 2:
	        return $smarty->_tpl_vars['T_sra'];
        break;
        
		case 3:
	        return $smarty->_tpl_vars['T_srta'];
        break;
        
		case 4:
	        return $smarty->_tpl_vars['T_dr'];
        break;
        
		case 5:
	        return $smarty->_tpl_vars['T_dra'];
        break;
	}
}
?>
