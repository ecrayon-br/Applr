<?php
include_once '../init.php';

if($_REQUEST['debug']) { echo '<pre>'; print_r($_SESSION[PROJECT]['URI_SEGMENT']); die(); }

// Gets URI controller and action params
if((ACTION == '' || ACTION == 'login') && !authUser_Controller::isLoggedIn()) {
	$strController	= 'Login_controller';
} elseif((ACTION == '' || ACTION == 'login') && authUser_Controller::isLoggedIn() && $_SESSION[PROJECT]['URI_SEGMENT'][3] !== 'out') {
	$strController	= 'Main_controller';
} else {
	$strController	= ucfirst(ACTION) . '_controller';
}
$strAction		= (!empty($_SESSION[PROJECT]['URI_SEGMENT'][3]) ? $_SESSION[PROJECT]['URI_SEGMENT'][3] : '');

if(empty($strAction) || !method_exists($strController,$strAction)) {
	// If action is empty, initializes controller and renders default view
	$objClass = new $strController();
} else {
	// If action is set, initializes controller and executes action method
	$objClass = new $strController(false);
	$objClass->$strAction();
}
?>