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


include 'config.php';


/*********************************************************/
/*********************************************************/
/**********										**********/
/**********		WEBSITE ENV & CREDENTIALS		**********/
/**********										**********/

if(strpos($_SERVER["HTTP_HOST"],URI_DOMAIN_DEVELOP) !== false) {

	define('URI_DOMAIN'	,URI_DOMAIN_DEVELOP);
	define('LOCAL_DIR'	,'/' . DIR_DEVELOP . '/www/');

	error_reporting(0);
	
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

	error_reporting(E_ALL&~E_NOTICE);
	
	define('DB_HOST'	,DB_PROD_HOST);
	define('DB_USER'	,DB_PROD_USER);
	define('DB_PASSWORD',DB_PROD_PWD);
	define('DB_NAME'	,DB_PROD_DB);
}


/*********************************************************/
/*********************************************************/
/**********										**********/
/**********				INCLUDE PATH			**********/
/**********										**********/

$strPath = $_SERVER['DOCUMENT_ROOT'].LOCAL_DIR.'PEAR/';
set_include_path(get_include_path().PATH_SEPARATOR.$strPath);


/*********************************************************/
/*********************************************************/
/**********										**********/
/**********		  SYS CONFIGS AND VARS			**********/
/**********										**********/

ini_set("memory_limit",MEMORY_LIMIT);

date_default_timezone_set(TIMEZONE);

define('SYS_WHERE'	,'delete = 0 AND active = 1 AND date_publish <= NOW() AND (date_expire  >= NOW() OR date_expire = "0000-00-00 00:00:00" OR date_expire IS NULL)');
define('SYS_DIR'	,(LOCAL_DIR != '' && LOCAL_DIR != '/' ? LOCAL_DIR : '/'));
define('SYS_ROOT'	,$_SERVER['DOCUMENT_ROOT'] . SYS_DIR);
define('HTTP'		,'http://'. URI_DOMAIN . SYS_DIR);

if(!isset($_SESSION[PROJECT])) {
	$_SESSION[PROJECT] 				= array();
	$_SESSION[PROJECT]['SYS_ROOT'] 	= SYS_ROOT;
}

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
function __autoload($strPackage) {
	// Creates Package's Name array
	$arrFolder	= array_reverse(explode('_',$strPackage));
	
	// Checks if $strPackage is a Main Library class or a Application class
	// If $strPackage is a single word and is directory and class' name are identical, it's a Main Library class; otherwise, it's an Application class
	if(count($arrFolder) == 1) $arrFolder[] = $arrFolder[0];

	// Defines Package's File Path
	$strFile	= $arrFolder[0] . '.php';
	$strPath	= (is_file(SYS_ROOT . 'lib/' . $strFile) ? SYS_ROOT . 'lib/' . $strFile : SYS_ROOT . 'lib/' . implode('/',$arrFolder) . '.php');
	
	// Includes File
	include_once $strPath;
}

// Secures PHP superglobals
Controller::secureGlobals();


/*********************************************************/
/*********************************************************/
/**********										**********/
/**********			  PROJECT CONFIG			**********/
/**********										**********/

$objController 	= new Controller();
$objModel 		= new Model();
if($objModel->boolConnStatus) {
	$objConfig		= $objModel->select('config.*, project.*',array('config','project'),'','project.id = config.project_id AND config.project_id = ' . PROJECT_ID);
	
	// Admin user
	if(isset($_SESSION[VAR_USER]) && $_SESSION[VAR_USER] === false) {
		$boolAdmin 	= $objModel->select('admin','usr_data','id = "' . $_SESSION[VAR_USER] . '"')->admin;
		if($boolAdmin == 1) {
			define('ADM_USER',1);
		} else {
			define('ADM_USER',0);
		}
	} else {
		define('ADM_USER',0);
	}
	
	// Title and SEO
	define('TITLE'				,$objConfig->name);
	define('DESCRIPTION'		,$objConfig->description);
	define('BRAND_IMG'			,$objConfig->logo);
	define('START_DATE'			,$objConfig->start_date);
	
	// E-mail authentication
	define('EMAIL_AUTH'			,$objConfig->mail_auth);
	define('EMAIL_AUTH_HOST'	,$objConfig->mail_auth_host);
	define('EMAIL_AUTH_USER'	,$objConfig->mail_auth_user);
	define('EMAIL_AUTH_PWD'		,$objConfig->mail_auth_password);
	
	// E-mail receivers
	define('EMAIL'				,$objConfig->mail_sys);
	define('EMAIL_PUBLIC'		,$objConfig->mail_public);
	define('EMAIL_CONTACT'		,$objConfig->mail_contact);
	define('EMAIL_USER'			,$objConfig->mail_user);
	
	// Paging lists
	define('PAGING_LIMIT'		,$objConfig->paging_listing);
	
	// RSS content
	define('RSS_GROUP'			,$objConfig->rss_group);
	define('RSS_LIMIT'			,$objConfig->rss_limit);
	
	// Directories path
	if(strrpos($objConfig->dir_upload,'/')		!= (strlen($objConfig->dir_upload)-1)) 		$objConfig->dir_upload 		.= '/';
	if(strrpos($objConfig->dir_image,'/')		!= (strlen($objConfig->dir_image)-1)) 		$objConfig->dir_image 		.= '/';
	if(strrpos($objConfig->dir_video,'/')		!= (strlen($objConfig->dir_video)-1)) 		$objConfig->dir_video 		.= '/';
	if(strrpos($objConfig->dir_template,'/')	!= (strlen($objConfig->dir_template)-1))	$objConfig->dir_template 	.= '/';
	if(strrpos($objConfig->dir_static,'/')		!= (strlen($objConfig->dir_static)-1)) 		$objConfig->dir_static 		.= '/';
	if(strrpos($objConfig->dir_dynamic,'/')		!= (strlen($objConfig->dir_dynamic)-1)) 	$objConfig->dir_dynamic 	.= '/';
	if(strrpos($objConfig->dir_xml,'/') 		!= (strlen($objConfig->dir_xml)-1)) 		$objConfig->dir_xml 		.= '/';
	if(strrpos($objConfig->dir_rss,'/') 		!= (strlen($objConfig->dir_rss)-1)) 		$objConfig->dir_rss 		.= '/';
	
	$strWebUpload			= str_replace('/images','/upload',$objConfig->dir_image);
	define('ROOT_WEB_UPLOAD',SYS_ROOT.$strWebUpload);
	define('ROOT_UPLOAD'	,( is_dir($objConfig->dir_upload) ? $objConfig->dir_upload : SYS_ROOT.$objConfig->dir_upload) );
	define('ROOT_IMAGE'		,SYS_ROOT.$objConfig->dir_image);
	define('ROOT_VIDEO'		,SYS_ROOT.$objConfig->dir_video);
	define('ROOT_TEMPLATE'	,SYS_ROOT.$objConfig->dir_template);
	define('ROOT_ESTATICO'	,SYS_ROOT.$objConfig->dir_static);
	define('ROOT_DINAMICO'	,SYS_ROOT.$objConfig->dir_dynamic);
	define('ROOT_XML'		,SYS_ROOT.$objConfig->dir_xml);
	define('ROOT_RSS'		,SYS_ROOT.$objConfig->dir_rss);
	
	define('WEB_UPLOAD'		,SYS_DIR.$strWebUpload);
	define('UPLOAD'			,SYS_DIR.$objConfig->dir_upload);
	define('IMAGE'			,SYS_DIR.$objConfig->dir_image);
	define('VIDEO'			,SYS_DIR.$objConfig->dir_video);
	define('TEMPLATE'		,SYS_DIR.$objConfig->dir_template);
	define('ESTATICO'		,SYS_DIR.$objConfig->dir_static);
	define('DINAMICO'		,SYS_DIR.$objConfig->dir_dynamic);
	define('XML'			,SYS_DIR.$objConfig->dir_xml);
	define('RSS'			,SYS_DIR.$objConfig->dir_rss);
	
	define('HTTP_WEB_UPLOAD','http://'.URI_DOMAIN.SYS_DIR.$strWebUpload);
	define('HTTP_UPLOAD'	,'http://'.URI_DOMAIN.SYS_DIR.$objConfig->dir_upload);
	define('HTTP_IMAGE'		,'http://'.URI_DOMAIN.SYS_DIR.$objConfig->dir_image);
	define('HTTP_VIDEO'		,'http://'.URI_DOMAIN.SYS_DIR.$objConfig->dir_video);
	define('HTTP_ESTATICO'	,'http://'.URI_DOMAIN.SYS_DIR.$objConfig->dir_static);
	define('HTTP_DINAMICO'	,'http://'.URI_DOMAIN.SYS_DIR.$objConfig->dir_dynamic);
	define('HTTP_XML'		,'http://'.URI_DOMAIN.SYS_DIR.$objConfig->dir_xml);
	define('HTTP_RSS'		,'http://'.URI_DOMAIN.SYS_DIR.$objConfig->dir_rss);
	
	
	/*********************************************************/
	/*********************************************************/
	/**********										**********/
	/**********			   CONTENT VARS				**********/
	/**********										**********/
	
	// Checks for friendly URL
	if(isset($_REQUEST[PROJECT.'_friendlyURL']) && $_REQUEST[PROJECT.'_friendlyURL'] == 1) {
		$_REQUEST[VAR_ACTION] = $objController->getURISegment();
		
		if(is_null($_REQUEST[VAR_ACTION])) {
			// Sets alternative names to main sessions
			switch($arrURL[1]) {
				default:
				break;
			}

			$strTable = str_replace('-','_',$arrURL[1]);
	
			// Gets SECTION ID
			$_REQUEST[VAR_SECTION] = $objModel->select('id','sec_config','','table = "' . $strTable . '"')->id;
			if(DB::isError($_REQUEST[VAR_SECTION])) $_REQUEST[VAR_SECTION] = MAIN_SECTION;
	
			// Gets LANGUAGE ID
			$mxdLanguage 	= (isset($arrURL[3]) ? $arrURL[3] : (isset($arrURL[2]) ? $arrURL[2] : 0) );			
			$tempLanguage = $objModel->select('id','sys_language','','(acronym = "' . $mxdLanguage . '" ' . (is_numeric($mxdLanguage) ? ' OR id = "' . $mxdLanguage . '"' : '') . ') AND status = 1')->id;
			
			if(DB::isError($tempLanguage) || empty($tempLanguage)) {
				$tempLanguage = (isset($_SESSION[VAR_SECTION]) ? $_SESSION[VAR_SECTION] : MAIN_LANGUAGE);
			}
			$_SESSION[VAR_SECTION] = $tempLanguage;
			$strLangWhere = ' AND sys_language_id = ' . $tempLanguage;
	
			// Gets CONTENT ID
			if(isset($arrURL[2])) {
				$_REQUEST[VAR_CONTENT] = $objModel->select('id','ctn_' . $strTable,'','sys_permalink = "' . $arrURL[2] . '" AND ' . SYS_WHERE . $strLangWhere)->id;
				if(DB::isError($_REQUEST[VAR_CONTENT])) unset($_REQUEST[VAR_CONTENT]);
			}
	
			// If URL don't defines CONTENT ID, checks for HOME content
			if(empty($_REQUEST[VAR_CONTENT])) {
				$objSection = ( isset($_REQUEST[VAR_SECTION]) ? $objController->getSectionConfig($_REQUEST[VAR_SECTION]) : $objController->getSectionConfig(MAIN_SECTION) );
				if(isset($objSection) && $objSection->home != 1 && empty($objSection->tpl_list)) {
					$_REQUEST[VAR_CONTENT] = $objModel->select('main.id','ctn_' . $strTable . ' AS main','rel_sec_language','(main.title = LOWER("home") OR rel_sec_language.name = main.title) AND ' . SYS_WHERE  . str_replace(' AND ',' AND rel_sec_language.',$strLangWhere))->id;
					if(DB::isError($_REQUEST[VAR_CONTENT]) || empty($_REQUEST[VAR_CONTENT])) {
						$_REQUEST[VAR_CONTENT] = $objModel->select('MAX(id)','ctn_' . $strTable,'',SYS_WHERE);
					}
				}
			}
			
			define('SECTION'			, $arrURL[1]);
			define('SECTION_CONFIG'		, $objSection);
			define('SECTION_PERMALINK'	,Controller::permalinkSyntax(str_replace('ctn_','',$objSection->table)));
		}
	} else {
		if(isset($_REQUEST[VAR_SECTION])) {
			define('SECTION'		,$_REQUEST[VAR_SECTION]);
		} elseif(!isset($inSistema)) {
			define('SECTION'		,MAIN_SECTION);
		}
		$objSection = $objController->getSectionConfig(SECTION);
		define('SECTION_CONFIG'		,$objSection);
		define('SECTION_PERMALINK'	,Controller::permalinkSyntax(str_replace('ctn_','',$objSection->table)));
	}
	
	// Language
	if(isset($tempLanguage)) {
		define('LANGUAGE',$tempLanguage);
	} elseif(isset($_REQUEST[VAR_LANGUAGE])) {
		define('LANGUAGE',$_REQUEST[VAR_LANGUAGE]);
	} elseif(isset($_SESSION[VAR_LANGUAGE])) {
		define('LANGUAGE',$_SESSION[VAR_LANGUAGE]);
	} else {
		define('LANGUAGE',MAIN_LANGUAGE);
	}
	$_SESSION[VAR_LANGUAGE] = LANGUAGE;
	
	// Locale
	$strLocale = $objModel->select('acronym','sys_language','','id = "' .LANGUAGE. '";')->acronym;
	setlocale(LC_ALL,$strLocale);
	define('LOCALE',$strLocale);
	
	// Content
	if(isset($_REQUEST[VAR_CONTENT])) {
		// Checks if VAR_CONTENT and VAR_LANGUAGE values are a valid pair or if there is an equivalent pair for LANG_CONTENT
		$intContent = $objModel->select('id',$objSection->table,'','rel_sec_language_id = "' . LANGUAGE . '" AND IF(rel_sec_language_id = 1 AND lang_content IS NOT NULL,lang_content = "'.$_REQUEST[VAR_CONTENT].'",id = "'.$_REQUEST[VAR_CONTENT].'")');
		define('CONTENT',$intContent);
	} else {
		define('CONTENT',NULL);
	}
} else {
	if(isset($_REQUEST[PROJECT.'_friendlyURL']) && $_REQUEST[PROJECT.'_friendlyURL'] == 1) {
		$_REQUEST[VAR_ACTION] = $objController->getURISegment();
	}
	#die('<h1>WARNING: Error loading section configs!</h1>');
}
	
/*********************************************************/
/*********************************************************/
/**********										**********/
/**********			  NAVIGATION VARS			**********/
/**********										**********/

// Home
if(isset($_REQUEST[VAR_HOME])) {
	define('HOME',$_REQUEST[VAR_HOME]);
} elseif(defined('SECTION') && is_numeric(SECTION) && is_object($objSection)) {
	define('HOME',$objSection->home);
} else {
	define('HOME',0);
}

// Actual page
if(isset($_REQUEST[VAR_PAGING])) {
	define('PAGE',$_REQUEST[VAR_PAGING]);
} else {
	define('PAGE',1);
}

// Media Gallery
if(isset($_REQUEST[VAR_GALLERY])) {
	define('MEDIA_GALLERY',$_REQUEST[VAR_GALLERY]);
} else {
	define('MEDIA_GALLERY',NULL);
}

// Preview
if(isset($_REQUEST[VAR_PREVIEW])) {
	define('PREVIEW',$_REQUEST[VAR_PREVIEW]);
} else {
	define('PREVIEW',0);
}

// Action
if(isset($_REQUEST[VAR_ACTION])) {
	define('ACTION',$_REQUEST[VAR_ACTION]);
} else {
	define('ACTION','');
}

// Search
if(isset($_REQUEST[TERM_SEARCH]) && !empty($_REQUEST[TERM_SEARCH])) {
	define('SEARCH',$_REQUEST[TERM_SEARCH]);
} elseif(isset($_REQUEST[VAR_SEARCH]) && !empty($_REQUEST[VAR_SEARCH])) {
	define('SEARCH',$_REQUEST[VAR_SEARCH]);
} else {
	define('SEARCH','');
}

// Debug
if(isset($_REQUEST[VAR_DEBUG])) {
	define('DEBUG',$_REQUEST[VAR_DEBUG]);
} else {
	define('DEBUG',false);
}
?>