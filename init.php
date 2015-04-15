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

if(!isset($_SERVER["HTTP_HOST"]) || strpos($_SERVER["HTTP_HOST"],URI_DOMAIN_DEVELOP) !== false) {

	define('URI_DOMAIN'	,URI_DOMAIN_DEVELOP);
	define('LOCAL_DIR'	, (empty($_SERVER['DOCUMENT_ROOT']) ? '' : '/' . DIR_DEVELOP . '/www/'));

	error_reporting(E_ERROR); #E_ALL&~E_NOTICE&~E_WARNING&~E_DEPRECATED&~E_STRICT);
	
	define('DB_HOST'	,DB_DEVELOP_HOST);
	define('DB_USER'	,DB_DEVELOP_USER);
	define('DB_PASSWORD',DB_DEVELOP_PWD);
	define('DB_NAME'	,DB_DEVELOP_DB);

} elseif(isset($_SERVER["HTTP_HOST"]) && strpos($_SERVER["HTTP_HOST"],URI_DOMAIN_HOMOLOG) !== false) {

	define('URI_DOMAIN'	,($_SERVER["HTTP_HOST"] == DIR_HOMOLOG.'.'.URI_DOMAIN_HOMOLOG ? DIR_HOMOLOG.'.'.URI_DOMAIN_HOMOLOG : URI_DOMAIN_HOMOLOG));
	define('LOCAL_DIR'	,(URI_DOMAIN == DIR_HOMOLOG.'.'.URI_DOMAIN_HOMOLOG ? '' : '/' . DIR_HOMOLOG) . '/');

	error_reporting(E_ERROR);
	
	define('DB_HOST'	,DB_HOMOLOG_HOST);
	define('DB_USER'	,DB_HOMOLOG_USER);
	define('DB_PASSWORD',DB_HOMOLOG_PWD);
	define('DB_NAME'	,DB_HOMOLOG_DB);

} else {
	
	define('URI_DOMAIN'	,URI_DOMAIN_PROD);
	define('LOCAL_DIR'	,'/');

	error_reporting(E_ALL);
	
	define('DB_HOST'	,DB_PROD_HOST);
	define('DB_USER'	,DB_PROD_USER);
	define('DB_PASSWORD',DB_PROD_PWD);
	define('DB_NAME'	,DB_PROD_DB);
}

define('SYS_DIR'	, (LOCAL_DIR != '' && LOCAL_DIR != '/' ? LOCAL_DIR : '/'));
define('ROOT'		, (empty($_SERVER['DOCUMENT_ROOT']) ? realpath(dirname($_SERVER['PHP_SELF'])) : $_SERVER['DOCUMENT_ROOT']));
define('SYS_ROOT'	, ROOT . SYS_DIR);
define('SMARTY_DIR'	, SYS_ROOT . 'lib/smarty/');

/*********************************************************/
/*********************************************************/
/**********										**********/
/**********				INCLUDE PATH			**********/
/**********										**********/

set_include_path(get_include_path() . PATH_SEPARATOR . SYS_ROOT . PATH_SEPARATOR . SYS_ROOT . 'lib/PEAR/' . PATH_SEPARATOR . SMARTY_DIR);

include SYS_ROOT . 'config.php';

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
	/**
	 * APPLR LIB files
	 */
	// Creates Package's Name array
	$arrFolder	= explode('_',$strPackage); # array_reverse(explode('_',$strPackage));
	
	// Checks if $strPackage is a Main Library class or a Application class
	// If $strPackage is a single word and is directory and class' name are identical, it's a Main Library class; otherwise, it's an Application class
	if(count($arrFolder) == 1) $arrFolder[] = $arrFolder[0];

	// Defines Package's File Path
	$strFile	= $arrFolder[0] . '.php';
	$strPath	= (is_file(SYS_ROOT . 'lib/' . $strFile) ? SYS_ROOT . 'lib/' . $strFile : SYS_ROOT . 'lib/' . implode('/',$arrFolder) . '.php');

	// Defines Package's File Path FIXES UPPERCASE
	$strPathFix	= (is_file(SYS_ROOT . 'lib/' . $strFile) ? SYS_ROOT . 'lib/' . $strFile : SYS_ROOT . 'lib/' . implode('/',$arrFolder) . '.php');
	$strPathFix = str_replace($strFile,strtolower($strFile),$strPathFix);
	
	// Tests if path/to/file exists in include_path
	/*
	if( function_exists('stream_resolve_include_path') && ($strIncludePath = stream_resolve_include_path($strPath)) !== false) {
		echo '<h1>here</h1>';
		echo $strIncludePath.'<br>'; 
		include_once $strIncludePath; 
	
	// Tests if /file exists in include_path
	} elseif( function_exists('stream_resolve_include_path') && ($strIncludePath = stream_resolve_include_path($strFile)) !== false) {
		echo '<h1>there</h1>';
		echo $strIncludePath.'<br>'; 
		include_once $strIncludePath;
	
	// Tests other path/to/file locations
	} else
	*/if(is_file($strPath)) {
		// Includes File
		include_once $strPath;
	} elseif(is_file($strPathFix)) {
		// Includes File
		include_once $strPathFix;
	} else {
		/**
		 * APPLR common files
		 */
		// Creates Package's Name array
		$arrFolder	= array_reverse(explode('_',$strPackage));
		
		// Checks if $strPackage is a Main Library class or a Application class
		// If $strPackage is a single word and is directory and class' name are identical, it's a Main Library class; otherwise, it's an Application class
		if(count($arrFolder) == 1) $arrFolder[] = $arrFolder[0];
		
		// Defines APPLR dir
		$strRequestURI = ( strpos($_SERVER['REQUEST_URI'],'/cms/') == 0 ? str_replace('/cms/','cms/',$_SERVER['REQUEST_URI']) : ( strpos($_SERVER['REQUEST_URI'],'/site/') == 0 ? str_replace('/site/','site/',$_SERVER['REQUEST_URI']) : $_SERVER['REQUEST_URI'] ) );
		$strApplrDir = explode('/', str_replace(array( (SYS_DIR != '/' ? SYS_DIR : '') ,'main'),array('','site'),$strRequestURI) );
		$strApplrDir = ($strApplrDir[0] == 'cms' ? 'cms/' : 'site/');

		// Defines Package's File Path
		$strPath	= SYS_ROOT . $strApplrDir . implode('/',$arrFolder) . '.php';

		// Includes File
		if(is_file($strPath)) include_once $strPath;
	}
}
spl_autoload_register('APPLR_autoload');

$objController = new Controller();
#Controller::setInitVars();
?>