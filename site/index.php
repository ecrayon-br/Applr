<?php
include_once '../init.php';

function setUCfirst(&$strValue,$strKey) {
	$strValue = ucfirst($strValue);
}

if($_REQUEST['debug']) { echo '<pre>'; print_r($_SESSION[PROJECT]['URI_SEGMENT']); die(); }

if(ACTION == '') {

	$arrTmp			= explode('-',SECTION_SEGMENT);
	array_walk($arrTmp,'setUCfirst');
	$strController	= implode('',$arrTmp) . '_controller';
	
	if(!class_exists($strController)) $strController	= 'Main_controller';
} else {
	$arrTmp			= explode('-',ACTION);
	array_walk($arrTmp,'setUCfirst');
	$strController	= implode('',$arrTmp) . '_controller';
}

$strAction		= (!empty($_SESSION[PROJECT]['URI_SEGMENT'][3]) ? $_SESSION[PROJECT]['URI_SEGMENT'][3] : '');
$arrTmp			= explode('-',$strAction);
array_walk($arrTmp,'setUCfirst');
$strAction		= implode('',$arrTmp);

if(empty($strAction) || !method_exists($strController,$strAction)) {
	// If action is empty, initializes controller and renders default view
	$objClass = new $strController();
} else {
	// If action is set, initializes controller and executes action method
	$objClass = new $strController(false);
	$objClass->$strAction();
}
?>