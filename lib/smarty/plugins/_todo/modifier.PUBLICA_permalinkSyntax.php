<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty PUBLICA_permalinkSyntax modifier plugin
 *
 * Type:     modifier<br>
 * Name:     PUBLICA_permalinkSyntax<br>
 * Purpose:  Returns $string fomatted according to PERMALINK syntax
 * @author   Diego Flores <diegotf at gmail dot com>
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_PUBLICA_permalinkSyntax($string)
{
    return fnSintaxePermalink(html_entity_decode($string,ENT_QUOTES,'UTF-8'));
}

/* vim: set expandtab: */

?>
