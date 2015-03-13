<?php
include_once '../init.php';

function setUCfirst(&$strValue,$strKey) {
	$strValue = ucfirst($strValue);
}

if($_REQUEST['debug']) { echo '<pre>'; print_r($_SESSION[PROJECT]['URI_SEGMENT']); die(); }

if(ACTION == '') {

	$arrTmp			= explode('-',str_replace('_','-',SECTION_SEGMENT));
	array_walk($arrTmp,'setUCfirst');
	$strController	= implode('',$arrTmp) . '_controller';
	
	if(!class_exists($strController)) $strController	= 'Main_controller';
} else {
	$arrTmp			= explode('-',str_replace('_','-',ACTION));
	array_walk($arrTmp,'setUCfirst');
	$strController	= implode('',$arrTmp) . '_controller';
}

$strAction		= (!empty($_SESSION[PROJECT]['URI_SEGMENT'][2]) ? $_SESSION[PROJECT]['URI_SEGMENT'][2] : '');
$arrTmp			= explode('-',$strAction);
array_walk($arrTmp,'setUCfirst');
$strAction		= implode('',$arrTmp);

#error_reporting(E_ALL);

if(empty($strAction) || !method_exists($strController,$strAction)) {
	#echo '<h1>' . $strController . '</h1>';
	// If action is empty, initializes controller and renders default view
	$objClass = new $strController();
} else {
	#echo '<h1>' . $strController . (!empty($strAction) ? '->' . $strAction : '') . '</h1>';
	// If action is set, initializes controller and executes action method
	$objClass = new $strController(false);
	$objClass->$strAction();
}
?>