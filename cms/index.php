<?php
include_once '../init.php';

function setUCfirst(&$strValue,$strKey) {
	$strValue = ucfirst($strValue);
}

if($_REQUEST['debug']) { echo '<pre>'; print_r($_SESSION[PROJECT]['URI_SEGMENT']); die(); }

// Gets URI controller and action params
if((ACTION == '' || ACTION == 'login') && !authUser_Controller::isLoggedIn()) {
	$strController	= 'Login_controller';
} elseif((ACTION == '' || ACTION == 'login') && authUser_Controller::isLoggedIn() && $_SESSION[PROJECT]['URI_SEGMENT'][3] !== 'out') {
	$strController	= 'Main_controller';
} else {
	$arrTmp			= explode('-',ACTION);
	array_walk($arrTmp,'setUCfirst');
	$strController	= implode('',$arrTmp) . '_controller';
}

$strAction		= (!empty($_SESSION[PROJECT]['URI_SEGMENT'][3]) ? $_SESSION[PROJECT]['URI_SEGMENT'][3] : '');
$arrTmp			= explode('-',$strAction);
array_walk($arrTmp,'setUCfirst');
$strAction		= implode('',$arrTmp);
$strLowerAction = strtolower($strAction);

if(!empty($strAction) && method_exists($strController,$strAction)) {
	#echo '<h1>' . $strController . (!empty($strAction) ? '->' . $strAction : '') . '</h1>';
	// If action is set, initializes controller and executes action method
	$objClass = new $strController(false);
	$objClass->$strAction();
} elseif(!empty($strAction) && method_exists($strController,$strLowerAction)) {
	#echo '<h1>' . $strController . (!empty($strLowerAction) ? '->' . $strLowerAction : '') . '</h1>';
	// If action is set, initializes controller and executes action method
	$objClass = new $strController(false);
	$objClass->$strLowerAction();
} else {
	#echo '<h1>' . $strController . '</h1>';
	// If action is empty, initializes controller and renders default view
	$objClass = new $strController();
}

?>