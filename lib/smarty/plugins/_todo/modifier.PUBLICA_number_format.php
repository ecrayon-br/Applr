<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty number format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     PUBLICA_number_format<br>
 * Purpose:  formats float numbers
 * @author   Diego Flores <diego at gmail dot com>
 * @param string
 * @return string
 */
function smarty_modifier_PUBLICA_number_format($number,$decimals,$dec_point,$thousands_sep)
{
	if(is_numeric($number)) $string = number_format($number,$decimals,$dec_point,$thousands_sep); else $string = 0;
	
	return $string;
}

?>
