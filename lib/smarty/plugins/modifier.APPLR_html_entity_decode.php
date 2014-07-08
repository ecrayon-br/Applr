<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsModifier
 */

function entityUpperFirst($string) {
	$arrVowelsLower = array(
						'&AACUTE;', '&AGRAVE;', '&ACIRC;', '&ATILDE;', '&AUML;',
						'&EACUTE;', '&EGRAVE;', '&ECIRC;', '&EUML;',
						'&IACUTE;', '&IGRAVE;', '&ICIRC;', '&IUML;',
						'&OACUTE;', '&OGRAVE;', '&OCIRC;', '&OTILDE;', '&OUML;',
						'&UACUTE;', '&UGRAVE;', '&UCIRC;', '&UUML;',
						'&CCEDIL;', '&ORDM;',	'&DEG'
						);
	$arrVowelsUpper  = array(
						'&Aacute;', '&Agrave;', '&Acirc;', '&Atilde;', '&Auml;',
						'&Eacute;', '&Egrave;', '&Ecirc;', '&Euml;',
						'&Iacute;', '&Igrave;', '&Icirc;', '&Iuml;',
						'&Oacute;', '&Ograve;', '&Ocirc;', '&Otilde;', '&Ouml;',
						'&Uacute;', '&Ugrave;', '&Ucirc;', '&Uuml;',
						'&Ccedil;', '&ordm;',	'&deg'
						);
	$string 		  = str_replace($arrVowelsLower,$arrVowelsUpper,$string);
	
	return $string;
}

/**
 * Smarty HTML Entity Decode modifier plugin
 *
 * Type:     modifier<br>
 * Name:     PUBLICA_html_entity_decode<br>
 * Purpose:  convert html entities to CHARSET charecter
 * 
 * @param	string $string input string
 * @param	string $charset user defined charset, default is UTF-8
 * 
 * @return	string
 * 
 * @author	Diego Flores <diego at gmail dot com>
 * 
 */
function smarty_modifier_APPLR_html_entity_decode($string,$charset = 'UTF-8')
{
    return entityUpperFirst(html_entity_decode($string,ENT_QUOTES,$charset));
}

?>
