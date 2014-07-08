<?php
// Exibe o destaque de conteudo
function smarty_function_PUBLICA_printZodiac($params, &$smarty) {
	// Verifica a validade do parametro
	switch(intval($params['var'])) {
		case 0:
		default:
	        return;
        break;
        
		case 1:
	        return $smarty->_tpl_vars['T_SIGNO_aquario'];
        break;
        
		case 2:
	        return $smarty->_tpl_vars['T_SIGNO_peixes'];
        break;
        
		case 3:
	        return $smarty->_tpl_vars['T_SIGNO_aries'];
        break;
        
		case 4:
	        return $smarty->_tpl_vars['T_SIGNO_touro'];
        break;
        
		case 5:
	        return $smarty->_tpl_vars['T_SIGNO_gemeos'];
        break;
        
		case 6:
	        return $smarty->_tpl_vars['T_SIGNO_cancer'];
        break;
        
		case 7:
	        return $smarty->_tpl_vars['T_SIGNO_leao'];
        break;
        
		case 8:
	        return $smarty->_tpl_vars['T_SIGNO_virgem'];
        break;
        
		case 9:
	        return $smarty->_tpl_vars['T_SIGNO_libra'];
        break;
        
		case 10:
	        return $smarty->_tpl_vars['T_SIGNO_escorpiao'];
        break;
        
		case 11:
	        return $smarty->_tpl_vars['T_SIGNO_sagitario'];
        break;
        
		case 12:
	        return $smarty->_tpl_vars['T_SIGNO_capricornio'];
        break;
	}
}
?>
