<?php
/******************************************************/
/******************************************************/
/***												***/
/*** 			PHP ENVIRONMENT CONFIG 				***/
/***												***/
define('MEMORY_LIMIT'	,'100M');
define('TIMEZONE'		,'America/Sao_Paulo');
define('DB_TYPE'		,'mysql');


/******************************************************/
/******************************************************/
/***												***/
/*** PRODUCTION ENVIRONMENT CONFIG AND CREDENTIALS  ***/
/***												***/
define('URI_DOMAIN_PROD',	'applr.com.br');
define('DB_PROD_HOST',		'localhost');
define('DB_PROD_DB',		'ecrayon_applr');
define('DB_PROD_USER',		'');
define('DB_PROD_PWD',		'');


/******************************************************/
/******************************************************/
/***												***/
/***   HOMOLOG ENVIRONMENT CONFIG AND CREDENTIALS   ***/
/***												***/
define('URI_DOMAIN_HOMOLOG','projetos.ecrayon.com.br');
define('DIR_HOMOLOG',		'applr');
define('DB_HOMOLOG_HOST',	'localhost');
define('DB_HOMOLOG_DB',		'ecrayon_applr');
define('DB_HOMOLOG_USER',	'ecrayon_projetos');
define('DB_HOMOLOG_PWD',	'sys24ecrayon11');


/******************************************************/
/******************************************************/
/***												***/
/*** DEVELOPMENT ENVIRONMENT CONFIG AND CREDENTIALS ***/
/***												***/
define('URI_DOMAIN_DEVELOP','127.0.0.1');
define('DIR_DEVELOP',		'applr');
define('DB_DEVELOP_HOST',	'localhost');
define('DB_DEVELOP_DB',		'ecrayon_applr');
define('DB_DEVELOP_USER',	'root');
define('DB_DEVELOP_PWD',	'');


/******************************************************/
/******************************************************/
/***												***/
/*** 			CLIENT AND PROJECT CONFIG			***/
/***												***/
define('FRAME_VIEW'		,false);
define('FRAME_URL'		,'url/do/frame/de/suporte.php');

define('CLIENT'			,'ECRAYON');
define('PROJECT'		,'APPLR');
define('PROJECT_ID'		,1);
define('CLIENT_EMAIL'	,'contato@ecrayon.com.br');

define('MAIN_SECTION'	,21);
define('MAIN_LANGUAGE'	,1);


/******************************************************/
/******************************************************/
/***												***/
/*** 				 APPLR VARIABLES				***/
/***												***/
define('VAR_DEBUG'		,'debug');
define('VAR_USER'		,'user');
define('VAR_LANGUAGE'	,'lang');
define('VAR_SECTION'	,'sect');
define('VAR_CONTENT'	,'cont');
define('VAR_GALLERY'	,'gall');
define('VAR_PAGING'		,'page');
define('VAR_HOME'		,'home');
define('VAR_ACTION'		,'action');
define('VAR_PREVIEW'	,'preview');
define('VAR_DELETE'		,'delete');
define('VAR_SEARCH'		,'search');
define('TERM_SEARCH'	,'searchby');
define('RESULTS'		,'contData');
?>