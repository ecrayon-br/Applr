<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty upper modifier plugin
 *
 * Type:     modifier<br>
 * Name:     upper<br>
 * Purpose:  convert string to ucfirst case
 * @author   Diego Flores <diego dot flores at ecrayon dot com dot br>
 * @param string
 * @return string
 */
function smarty_modifier_PUBLICA_ucfirst($string)
{
    return ucfirst(strtolower($string));
}

?>
