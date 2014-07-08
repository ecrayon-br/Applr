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
 * Name:     PUBLICA_urldecode<br>
 * Date:     Feb 26, 2003
 * Purpose:  convert \r\n, \r or \n to <<br>>
 * Input:<br>
 *         - contents = contents to encode
 *         - preceed_test = if true, encodes string into URL-friendly characters
 * Example:  {$text|PUBLICA_urldecode}
 * @version  1.0
 * @author   Diego Flores <diegotf at gmail dot com>
 * @param string
 * @return string
 */
function smarty_modifier_PUBLICA_urldecode($string)
{
    return urldecode($string);
}

/* vim: set expandtab: */

?>
