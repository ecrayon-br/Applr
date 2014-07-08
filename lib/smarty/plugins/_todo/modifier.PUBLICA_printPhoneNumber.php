<?php
/**
 * Prints phone number string using mask
 *
 * @param	array	$params	Variables defined at SMARTY tag; possible values describes at the function scope
 * @param	object	$smarty	SMARTY object
 * @return	void
 */
function smarty_modifier_PUBLICA_printPhoneNumber($string,$type = 0,$boolClearChars = false) {
	// Verifies if phone value is defined
	if(!isset($string) || empty($string) || $string === 0 || $string === '0') {
		return;
	}

	$strDDD = '';
	$valor	= str_replace(array('(',')','-','.',' '),'',$string);

	$intPre = substr($valor,-8,4);
	$intSuf = substr($valor,-4,4);
	$strNum	= $intPre.'-'.$intSuf;

	if(strlen($valor) > 8) {
		$strDDD = '('.str_replace(array($intPre.$intSuf,$strNum),'',$valor).') ';
	}
	/*
	if(strlen($string) > 8) {
		var_dump($string);
		$intPre = (strlen($string) == 10 ? substr($string,-8,4) : substr($string,-9,4));
		var_dump($intPre);
		$intSuf = substr($string,-4,4);
		var_dump($intSuf);
		$strNum = $intPre.'-'.$intSuf;
		var_dump($strNum);
		$strDDD = '('.str_replace(array($intPre.$intSuf,$strNum),'',$string).') ';
		var_dump($strDDD);
	}
	*/
	if($boolClearChars) {
		return str_replace(array('(',')','-','.',' '),'',($type == 0 ? $strDDD.$strNum : ($type == 1 ? $strDDD : $strNum)));
	} else {
		return ($type == 0 ? $strDDD.$strNum : ($type == 1 ? $strDDD : $strNum));
	}
}
?>