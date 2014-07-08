<?php
// Exibe o destaque de conteudo
function smarty_function_PUBLICA_printEyeColor($params, &$smarty) {
	
	// Verifica a validade do parametro
	switch(intval($params['var'])) {
		case 0:
	        return $smarty->_tpl_vars['T_cor_mel'];
        break;
        
		case 1:
	        return $smarty->_tpl_vars['T_cor_azuis'];
        break;
        
		case 2:
	        return $smarty->_tpl_vars['T_cor_verdes'];
        break;
        
		case 3:
	        return $smarty->_tpl_vars['T_cor_castanhos'];
        break;
        
		case 4:
	        return $smarty->_tpl_vars['T_cor_pretos'];
        break;
        
		default:
	        return;
        break;
	}
}
?>
