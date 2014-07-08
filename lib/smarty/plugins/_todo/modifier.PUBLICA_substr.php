<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty substr modifier plugin
 *
 * Type:     modifier<br>
 * Name:     PUBLICA_substr<br>
 * Purpose:  Return part of a string
 * @author   Diego Flores <diegotf at gmail dot com>
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_PUBLICA_substr($string, $intStart = 0, $intLength = 0)
{
    return ($intLength == 0 ? substr($string,intval($intStart)) : substr($string,intval($intStart),intval($intLength)));
}

/* vim: set expandtab: */

?>
