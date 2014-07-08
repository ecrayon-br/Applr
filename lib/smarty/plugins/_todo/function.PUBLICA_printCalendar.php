<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../site/include-sistema/sys/class/tableClass.php';
	include_once '../../site/include-sistema/sys/class/calendarClass.php';
} else {
	include_once '../../../site/include-sistema/sys/class/tableClass.php';
	include_once '../../../site/include-sistema/sys/class/calendarClass.php';
}

/**
 * Specialized PUBLICA's Calendar class
 *
 */
class PUBLICA_printCalendar extends calendarClass {
	var $this_year					= 0;
	var $this_month					= 0;
	var $this_day					= 0;
	var $days_highlight				= '';
	var $today_highlight			= 'today';
	var $class 						= 'calendar';
	var $language					= 1;
	var $months						= array('Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
	
	function PUBLICA_printCalendar($intYear = 0,$intMonth = 0,$intDay = 0) {
		if($intYear 	> 0)$this->this_year 	= $intYear;
		if($intMonth 	> 0)$this->this_month 	= $intMonth;
		if($intDay 	> 0) 	$this->this_day 	= $intDay;
	}
	
	function fetchcustomcolumn(&$columndata) {
		if($this->day>0) $columndata["class"]	= ($this->year==$this->this_year && $this->month==$this->this_month && $this->day==$this->this_day ? $this->today_highlight : $this->days_highlight);
		return 1;
	}
};

/**
 * Prints Publica calendar gadget
 *
 * @param	array	$params	Variables defined at SMARTY tag; possible values describes at the function scope
 * @param	object	$smarty	SMARTY object
 * @return	void
 */
function smarty_function_PUBLICA_printCalendar($params, &$smarty) {
	// Defines YEAR TO DISPLAY
	if(!isset($params['yearDisplay']) || $params['yearDisplay'] == '' || strcmp(intval($params['yearDisplay']),$params['yearDisplay']) != 0 || $params['yearDisplay'] <= 0) {
		$params['yearDisplay'] = intval(date("Y"));
	}

	// Defines MONTH TO DISPLAY
	if(!isset($params['monthDisplay']) || $params['monthDisplay'] == '' || strcmp(intval($params['monthDisplay']),$params['monthDisplay']) != 0 || $params['monthDisplay'] < 1 || $params['monthDisplay'] > 12) {
		$params['monthDisplay'] = intval(date("n"));
	}

	// Defines YEAR NOW
	if(!isset($params['yearNow']) || $params['yearNow'] == '' || strcmp(intval($params['yearNow']),$params['yearNow']) != 0 || $params['yearNow'] <= 0) {
		$params['yearNow'] = intval(date("Y"));
	}

	// Defines MONTH NOW
	if(!isset($params['monthNow']) || $params['monthNow'] == '' || strcmp(intval($params['monthNow']),$params['monthNow']) != 0 || $params['monthNow'] < 1 || $params['monthNow'] > 12) {
		$params['monthNow'] = intval(date("n"));
	}

	// Defines DAY NOW
	if(!isset($params['dayNow']) || $params['dayNow'] == '' || strcmp(intval($params['dayNow']),$params['dayNow']) != 0 || ($params['dayNow'] < 1 && $params['monthNow'] == date('m')) || $params['dayNow'] > 31) {
		$params['dayNow'] = intval(date("j"));
	}
	
	// Declares Calendar object
	$objCalendar				= new PUBLICA_printCalendar($params['yearNow'],$params['monthNow'],$params['dayNow']);
	
	// Defines months' name
	$objCalendar->months 		= array($smarty->_tpl_vars['T_janeiro'],$smarty->_tpl_vars['T_fevereiro'],$smarty->_tpl_vars['T_marco'],$smarty->_tpl_vars['T_abril'],$smarty->_tpl_vars['T_maio'],$smarty->_tpl_vars['T_junho'],$smarty->_tpl_vars['T_julho'],$smarty->_tpl_vars['T_agosto'],$smarty->_tpl_vars['T_setembro'],$smarty->_tpl_vars['T_outubro'],$smarty->_tpl_vars['T_novembro'],$smarty->_tpl_vars['T_dezembro']);
	
	// Defines weekdays' initials
	switch($smarty->_tpl_vars['IDIOMA']) {
		case 2:		// Espanol
			$objCalendar->week_day_names 	= array('D','L','M','M','J','V','S');
		break;
		
		case 3:		// English
			$objCalendar->week_day_names 	= array('S','M','T','W','T','F','S');
		break;
		
		default:	// Portugues
			$objCalendar->week_day_names 	= array('Dom','Seg','Ter','Qua','Qui','Sex','S&aacute;b');
		break;
	}
	
	// Defines today's date
	$objCalendar->this_year			= $params['yearNow'];
	$objCalendar->this_month		= $params['monthNow'];
	$objCalendar->this_day			= $params['dayNow'];
	
	// Defines Calendar's month and year to display
	$objCalendar->year				= $params['yearDisplay'];
	$objCalendar->month				= $params['monthDisplay'];

	// Defines Calendar's link strings
	$objCalendar->headerhtml		= (!isset($params['headerhtml']) ? '<th colspan="7" class="header"><a href="javascript:updateCalendar('.($objCalendar->month > 1 ? $objCalendar->year : ($objCalendar->year - 1)).','.($objCalendar->month > 1 ? $objCalendar->month-1 : 12).',\'tbCalendar\','.$objCalendar->this_year.','.$objCalendar->this_month.','.$objCalendar->this_day.');"><img src="' . $smarty->_tpl_vars['DINAMICO'] . 'imagens/geral/esqP.png" width="25" height="19" border="0" align="absmiddle" /></a>&nbsp;&nbsp;<a href="javascript:searchCalendar('.$objCalendar->year.','.$objCalendar->month.',0);">'.$objCalendar->months[$objCalendar->month-1].' '.$objCalendar->year.'</a>&nbsp;&nbsp;<a href="javascript:updateCalendar('.($objCalendar->month < 12 ? $objCalendar->year : ($objCalendar->year + 1)).','.($objCalendar->month < 12 ? $objCalendar->month+1 : 1).',\'tbCalendar\','.$objCalendar->this_year.','.$objCalendar->this_month.','.$objCalendar->this_day.');"><img src="' . $smarty->_tpl_vars['DINAMICO'] . 'imagens/geral/dirP.png" width="25" height="19" border="0" align="absmiddle" /></a></th>' : $params['headerhtml']);
	$objCalendar->day_link			= (!isset($params['day_link']) ? 'javascript:searchCalendar('.$objCalendar->year.','.$objCalendar->month.',#DAY#);" id="lkCalendar_#DAY#' : $params['day_link']);
	$objCalendar->footerhtml		= (!isset($params['footerhtml']) ? '<th colspan="7" class="header"><a href="javascript:searchCalendar('.date('Y').','.date('m').','.date('d').');">Ver Hoje</a></th>' : $params['footerhtml']);

	if(isset($params['calendarData'])) {
		$objCalendar->calendarData	= $params['calendarData'];
	}
	
	// Prints Calendar's HTML
	echo $objCalendar->outputcalendar();
}
?>
