<?php
/*********************************************************/
/*********************************************************/
/**********										**********/
/**********										**********/
/**********				  WARNING!				**********/
/**********		DO NOT CHANGE ANY LINE BELOW	**********/
/**********										**********/
/**********										**********/
/*********************************************************/


include_once 'credentials.php';


/*********************************************************/
/*********************************************************/
/**********										**********/
/**********		WEBSITE ENV & CREDENTIALS		**********/
/**********										**********/

if(strpos($_SERVER["HTTP_HOST"],URI_DOMAIN_DEVELOP) !== false) {

	define('URI_DOMAIN'	,URI_DOMAIN_DEVELOP);
	define('LOCAL_DIR'	,'/' . DIR_DEVELOP . '/www/');

	error_reporting(E_ERROR); #E_ALL&~E_NOTICE&~E_WARNING&~E_DEPRECATED&~E_STRICT);
	
	define('DB_HOST'	,DB_DEVELOP_HOST);
	define('DB_USER'	,DB_DEVELOP_USER);
	define('DB_PASSWORD',DB_DEVELOP_PWD);
	define('DB_NAME'	,DB_DEVELOP_DB);

} elseif(strpos($_SERVER["HTTP_HOST"],URI_DOMAIN_HOMOLOG) !== false) {

	define('URI_DOMAIN'	,($_SERVER["HTTP_HOST"] == DIR_HOMOLOG.'.'.URI_DOMAIN_HOMOLOG ? DIR_HOMOLOG.'.'.URI_DOMAIN_HOMOLOG : URI_DOMAIN_HOMOLOG));
	define('LOCAL_DIR'	,(URI_DOMAIN == DIR_HOMOLOG.'.'.URI_DOMAIN_HOMOLOG ? '' : '/' . DIR_HOMOLOG) . '/');

	error_reporting(0);
	
	define('DB_HOST'	,DB_HOMOLOG_HOST);
	define('DB_USER'	,DB_HOMOLOG_USER);
	define('DB_PASSWORD',DB_HOMOLOG_PWD);
	define('DB_NAME'	,DB_HOMOLOG_DB);

} else {

	define('URI_DOMAIN'	,URI_DOMAIN_PROD);
	define('LOCAL_DIR'	,'/');

	error_reporting(0);
	
	define('DB_HOST'	,DB_PROD_HOST);
	define('DB_USER'	,DB_PROD_USER);
	define('DB_PASSWORD',DB_PROD_PWD);
	define('DB_NAME'	,DB_PROD_DB);
}

define('SYS_DIR'	,(LOCAL_DIR != '' && LOCAL_DIR != '/' ? LOCAL_DIR : '/'));
define('SYS_ROOT'	,$_SERVER['DOCUMENT_ROOT'] . SYS_DIR);

/**
 * Magic function: Autoload
 *
 * @param		string	$strPackage	Defined Class' name
 * @return		void
 *
 * @since 		2010-04-20
 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
 *
 */
function APPLR_autoload($strPackage) {
	// Creates Package's Name array
	$arrFolder	= explode('_',$strPackage); # array_reverse(explode('_',$strPackage));
	
	// Checks if $strPackage is a Main Library class or a Application class
	// If $strPackage is a single word and is directory and class' name are identical, it's a Main Library class; otherwise, it's an Application class
	if(count($arrFolder) == 1) $arrFolder[] = $arrFolder[0];

	// Defines Package's File Path
	$strFile	= $arrFolder[0] . '.php';
	$strPath	= (is_file(SYS_ROOT . 'lib/' . $strFile) ? SYS_ROOT . 'lib/' . $strFile : SYS_ROOT . 'lib/' . implode('/',$arrFolder) . '.php');
	
	// Includes File
	include_once $strPath;
}
spl_autoload_register('APPLR_autoload');

Controller::setInitVars();
?>