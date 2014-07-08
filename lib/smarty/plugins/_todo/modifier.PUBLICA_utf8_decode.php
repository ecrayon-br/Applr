<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty plugin
 *
 * Type:     modifier<br>
 * Name:     PUBLICA_utf8_decode<br>
 * Date:     Feb 26, 2003
 * Purpose:  convert \r\n, \r or \n to <<br>>
 * Input:<br>
 *         - contents = contents to encode
 *         - preceed_test = if true, encodes string into URL-friendly characters
 * Example:  {$text|PUBLICA_utf8_decode}
 * @version  1.0
 * @author   Diego Flores <diegotf at gmail dot com>
 * @param string
 * @return string
 */
function smarty_modifier_PUBLICA_utf8_decode($string)
{
    return utf8_decode($string);
}

/* vim: set expandtab: */

?>
