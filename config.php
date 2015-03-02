<?php
/******************************************************/
/******************************************************/
/***												***/
/*** 			CLIENT AND PROJECT CONFIG			***/
/***												***/
define('FRAME_VIEW'			,false);
define('FRAME_URL'			,'url/do/frame/de/suporte.php');

define('CLIENT'				,'ECRAYON');
define('PROJECT'			,'APPLR');
define('PROJECT_ID'			,1);
define('CLIENT_EMAIL'		,'contato@ecrayon.com.br');

define('MAIN_SECTION'		,1);
define('MAIN_LANGUAGE'		,1);
define('CMS_ROOT_TEMPLATE'	,SYS_ROOT . 'cms/views/');

define('DEBUG'				,false);


/******************************************************/
/******************************************************/
/***												***/
/*** 			PHP ENVIRONMENT CONFIG 				***/
/***												***/

	/*
	 * ALLOWED DB TYPES
	 * 
		fbsql  -> FrontBase
		ibase  -> InterBase / Firebird (requires PHP 5)
		mssql  -> Microsoft SQL Server (NOT for Sybase. Compile PHP --with-mssql)
		mysql  -> MySQL
		mysqli -> MySQL (supports new authentication protocol) (requires PHP 5)
		oci8   -> Oracle 7/8/9/10
		pgsql  -> PostgreSQL
		querysim -> QuerySim
		sqlite -> SQLite 2
	 *
	 */

define('MEMORY_LIMIT'	,'128M');
define('TIMEZONE'		,'America/Sao_Paulo');
define('DB_TYPE'		,'mysql');


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