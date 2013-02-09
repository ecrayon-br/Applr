<?php
class Controller {
	
	static 		$arrSpecialChar		= array('/','*',"'",'=','-','#',';','<','>','+','%','.',' ');
	static		$arrOnlyNumberChar	= array(' ','+','-','_','.',':',',',';','?','(',')','[',']','{','}','/','\\','*','&','%','$','#','@','!','=','<','>','~','º','ª','¬','¢','£','³','²','¹','|','°','§','^');
	static		$arrPermalinkChar	= array(' ','+','_','.',':',',',';','?','(',')','[',']','{','}','/','\\','*','&','%','$','#','@','!','=','<','>','~','º','ª','¬','¢','£','³','²','¹','|','°','§','^');
	static		$arrExtTPL			= array('htm','html','php','tpl');
	
	public		$intError;
	public		$objSmarty;
	public		$strCharSet			= 'UTF-8';
	
	protected	$strAction			= '';
	protected	$objModel;
	protected	$objSection;
	protected	$intProjectID 		= 1;
	
	private		$strTemplate;
	
	static		$strClientName		= CLIENT;
	
	
	/**
	 * Class constructor
	 *
	 * @param 		boolean	$boolRenderView	Defines if View's template is renderized on $this->__construct() or heir method call
	 * @param		string	$strTemplateDir	Sets SMARTY template dir path
	 *
	 * @return		void
	 * 
	 * @since 		2010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 * @todo		configContentLink() auto target and media link
	 * 
	 */
	public function __construct($boolRenderView = false,$strTemplateDir = ROOT_TEMPLATE) {
		if(!is_bool($boolRenderView) && $boolRenderView !== 1 && $boolRenderView !== 0)	$boolRenderView = false;
		
		@session_start();
		@set_time_limit(0);
		
		// Aplies security for superglobal variables
		$this->secureGlobals();
		
		// Defines init configs and vars
		if(!defined('SYS_WHERE')) self::setInitVars();
		
		// Sets MODEL var
		$this->objModel = new Model();
		
		// Instantiates SMARTY object
		$this->setSmarty($strTemplateDir);
		
		// Defines logged user ID
		if(isset($_SESSION[self::$strClientName]['id'])) {
			$this->intProjectID = $_SESSION[self::$strClientName]['id'];
		}
		
		// Renderize View's template
		if($boolRenderView) $this->renderTemplate();
	}
	
	/**
	 * Sets APPLR INIT configs and vars
	 * 
	 * @return	void
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function setInitVars() {
		
		/*********************************************************/
		/*********************************************************/
		/**********										**********/
		/**********				INCLUDE PATH			**********/
		/**********										**********/
		
		set_include_path(get_include_path() . PATH_SEPARATOR . SYS_ROOT . PATH_SEPARATOR . SYS_ROOT . 'lib/PEAR/' . PATH_SEPARATOR . SMARTY_DIR);
		
		include SYS_ROOT . 'config.php';

		/*********************************************************/
		/*********************************************************/
		/**********										**********/
		/**********		  		 SYS CONFIGS			**********/
		/**********										**********/
		
		ini_set("memory_limit",MEMORY_LIMIT);
		
		date_default_timezone_set(TIMEZONE);
		

		/*********************************************************/
		/*********************************************************/
		/**********										**********/
		/**********			  PROJECT CONFIG			**********/
		/**********										**********/
		
		define('SYS_WHERE'	,'delete = 0 AND active = 1 AND date_publish <= NOW() AND (date_expire  >= NOW() OR date_expire = "0000-00-00 00:00:00" OR date_expire IS NULL)');
		define('HTTP'		,'http://'. URI_DOMAIN . SYS_DIR);
		
		if(!isset($_SESSION[PROJECT])) {
			$_SESSION[PROJECT] 				= array();
			$_SESSION[PROJECT]['SYS_ROOT'] 	= SYS_ROOT;
		}
		
		$this->objModel = new Model();
		if($this->objModel->boolConnStatus) {
			$objConfig		= $this->objModel->select('config.*, project.*',array('config','project'),'','project.id = config.project_id AND config.project_id = ' . PROJECT_ID);
		
			// Admin user
			if(isset($_SESSION[PROJECT][VAR_USER]) && $_SESSION[PROJECT][VAR_USER] === false) {
				$boolAdmin 	= $this->objModel->select('admin','usr_data','id = "' . $_SESSION[PROJECT][VAR_USER] . '"')->admin;
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
			define('ROOT_STATIC'	,SYS_ROOT.$objConfig->dir_static);
			define('ROOT_DYNAMIC'	,SYS_ROOT.$objConfig->dir_dynamic);
			define('ROOT_XML'		,SYS_ROOT.$objConfig->dir_xml);
			define('ROOT_RSS'		,SYS_ROOT.$objConfig->dir_rss);
		
			define('WEB_UPLOAD'		,SYS_DIR.$strWebUpload);
			define('UPLOAD'			,SYS_DIR.$objConfig->dir_upload);
			define('IMAGE'			,SYS_DIR.$objConfig->dir_image);
			define('VIDEO'			,SYS_DIR.$objConfig->dir_video);
			define('TEMPLATE'		,SYS_DIR.$objConfig->dir_template);
			define('STATIC'			,SYS_DIR.$objConfig->dir_static);
			define('DYNAMIC'		,SYS_DIR.$objConfig->dir_dynamic);
			define('XML'			,SYS_DIR.$objConfig->dir_xml);
			define('RSS'			,SYS_DIR.$objConfig->dir_rss);
			
			define('HTTP'			,'http://'.URI_DOMAIN.SYS_DIR);
			define('HTTP_SITE'		,HTTP . 'site/');
			define('HTTP_CMS'		,HTTP . 'cms/');
			define('HTTP_WEB_UPLOAD',HTTP . $strWebUpload);
			define('HTTP_UPLOAD'	,HTTP . $objConfig->dir_upload);
			define('HTTP_IMAGE'		,HTTP . $objConfig->dir_image);
			define('HTTP_VIDEO'		,HTTP . $objConfig->dir_video);
			define('HTTP_STATIC'	,HTTP . $objConfig->dir_static);
			define('HTTP_DYNAMIC'	,HTTP . $objConfig->dir_dynamic);
			define('HTTP_XML'		,HTTP . $objConfig->dir_xml);
			define('HTTP_RSS'		,HTTP . $objConfig->dir_rss);
		
		
			/*********************************************************/
			/*********************************************************/
			/**********										**********/
			/**********			   CONTENT VARS				**********/
			/**********										**********/
		
			// Checks for friendly URL
			if(isset($_REQUEST[PROJECT.'_friendlyURL']) && $_REQUEST[PROJECT.'_friendlyURL'] == 1) {
				$_REQUEST[VAR_ACTION] = self::getURISegment();
				
				if(is_null($_REQUEST[VAR_ACTION])) {
					// Sets alternative names to main sessions
					switch($arrURL[1]) {
						default:
							break;
					}
		
					$strTable = str_replace('-','_',$arrURL[1]);
		
					// Gets SECTION ID
					$_REQUEST[VAR_SECTION] = $this->objModel->select('id','sec_config','','table = "' . $strTable . '"')->id;
					if(DB::isError($_REQUEST[VAR_SECTION])) $_REQUEST[VAR_SECTION] = MAIN_SECTION;
		
					// Gets LANGUAGE ID
					$mxdLanguage 	= (isset($arrURL[3]) ? $arrURL[3] : (isset($arrURL[2]) ? $arrURL[2] : 0) );
					$tempLanguage = $this->objModel->select('id','sys_language','','(acronym = "' . $mxdLanguage . '" ' . (is_numeric($mxdLanguage) ? ' OR id = "' . $mxdLanguage . '"' : '') . ') AND status = 1')->id;
		
					if(DB::isError($tempLanguage) || empty($tempLanguage)) {
						$tempLanguage = (isset($_SESSION[PROJECT][VAR_SECTION]) ? $_SESSION[PROJECT][VAR_SECTION] : MAIN_LANGUAGE);
					}
					$_SESSION[PROJECT][VAR_SECTION] = $tempLanguage;
					$strLangWhere = ' AND sys_language_id = ' . $tempLanguage;
		
					// Gets CONTENT ID
					if(isset($arrURL[2])) {
						$_REQUEST[VAR_CONTENT] = $this->objModel->select('id','ctn_' . $strTable,'','sys_permalink = "' . $arrURL[2] . '" AND ' . SYS_WHERE . $strLangWhere)->id;
						if(DB::isError($_REQUEST[VAR_CONTENT])) unset($_REQUEST[VAR_CONTENT]);
					}
		
					// If URL don't defines CONTENT ID, checks for HOME content
					if(empty($_REQUEST[VAR_CONTENT])) {
						$this->objSection = ( isset($_REQUEST[VAR_SECTION]) ? $this->objModel->getSectionConfig($_REQUEST[VAR_SECTION]) : $this->objModel->getSectionConfig(MAIN_SECTION) );
						if(isset($this->objSection) && $this->objSection->home !=$this->objSection($this->objSection->tpl_list)) {
							$_REQUEST[VAR_CONTENT] = $this->objModel->select('main.id','ctn_' . $strTable . ' AS main','rel_sec_language','(main.title = LOWER("home") OR rel_sec_language.name = main.title) AND ' . SYS_WHERE  . str_replace(' AND ',' AND rel_sec_language.',$strLangWhere))->id;
							if(DB::isError($_REQUEST[VAR_CONTENT]) || empty($_REQUEST[VAR_CONTENT])) {
								$_REQUEST[VAR_CONTENT] = $this->objModel->select('MAX(id)','ctn_' . $strTable,'',SYS_WHERE);
							}
						}
					}
		
					define('SECTION'			, $arrURL[1]);
					define('SECTION_PERMALINK'	,self::permalinkSyntax(str_replace('ctn_','',$this->objSection->table)));
				}
			} else {
				if(isset($_REQUEST[VAR_SECTION])) {
					define('SECTION'		,$_REQUEST[VAR_SECTION]);
				} elseif(!isset($inSistema)) {
					define('SECTION'		,MAIN_SECTION);
				}
				$this->objSection = $this->objModel->getSectionConfig(SECTION);
				define('SECTION_PERMALINK'	,self::permalinkSyntax(str_replace('ctn_','',$this->objSection->table)));
			}
		
			// Language
			if(isset($tempLanguage)) {
				define('LANGUAGE',$tempLanguage);
			} elseif(isset($_REQUEST[VAR_LANGUAGE])) {
				define('LANGUAGE',$_REQUEST[VAR_LANGUAGE]);
			} elseif(isset($_SESSION[PROJECT][VAR_LANGUAGE])) {
				define('LANGUAGE',$_SESSION[PROJECT][VAR_LANGUAGE]);
			} else {
				define('LANGUAGE',MAIN_LANGUAGE);
			}
			$_SESSION[PROJECT][VAR_LANGUAGE] = LANGUAGE;
		
			// Locale
			$strLocale = $this->objModel->select('acronym','sys_language','','id = "' .LANGUAGE. '";')->acronym;
			setlocale(LC_ALL,$strLocale);
			define('LOCALE',$strLocale);
		
			// Content
			if(isset($_REQUEST[VAR_CONTENT])) {
				// Checks if VAR_CONTENT and VAR_LANGUAGE values are a valid pair or if there is an equivalent pair for LANG_CONTENT
				$intContent = $this->objModel->select('id',$this->objSection->table,'','rel_sec_language_id = "' . LANGUAGE . '" AND IF(rel_sec_language_id = 1 AND lang_content IS NOT NULL,lang_content = "'.$_REQUEST[VAR_CONTENT].'",id = "'.$_REQUEST[VAR_CONTENT].'")');
				define('CONTENT',$intContent);
			} else {
				define('CONTENT',NULL);
			}
		} else {
			if(isset($_REQUEST[PROJECT.'_friendlyURL']) && $_REQUEST[PROJECT.'_friendlyURL'] == 1) {
				$_REQUEST[VAR_ACTION] = self::getURISegment();
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
		} elseif(defined('SECTION') && is_numeric(SECTION) && is_object($this->objSection)) {
			define('HOME',$this->objSection->home);
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
	}
	
	/**
	 * Replaces any non-number character with an empty char
	 * 
	 * @param	string	$strTemp	Original string
	 * 
	 * @return	string
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	static function onlyNumbers($strTemp) {
		return str_replace(self::$arrOnlyNumberChar,'',$strTemp);
	}
	
	public function permalinkSyntax() {
		
	}
	
	static function getURISegment() {
		$strURL = str_replace(array(URI_DOMAIN,LOCAL_DIR,'site/','conteudo/') ,'',$_SERVER['REQUEST_URI']);
		if(strpos($strURL,'/') !== 0) $strURL = '/' . $strURL;
		
		$arrURL = explode('/', $strURL );
		$_SESSION[PROJECT]['URI_SEGMENT'] = $arrURL;
		
		switch($arrURL[1]) {
			case 'site':
			case 'include-sistema':
				return '';
			break;
	
			case 'cms':
			case 'admin':
			case 'applr-admin':
				return (!empty($arrURL[2]) && $arrURL[2] != '?APPLR_friendlyURL=1' ? $arrURL[2] : 'login');
			break;
	
			case 'siteMap':
				return 'siteMap';
			break;
			case 'userLogin':
				return 'userLogin';
			break;
			case 'restrictedArea':
				return 'restrictedArea';
			break;
			case 'registerUser':
				return 'registerUser';
			break;
			case 'registerSuccess':
				return 'registerSuccess';
			break;
			case 'forgotPassword':
				return 'forgotPassword';
			break;
			case 'shareThisSite':
				return 'shareThisSite';
			break;
			case 'registerNews':
				return 'registerNews';
			break;
			case 'mediaGallery':
				return 'mediaGallery';
			break;
	
			case 'logoutUser':
			case 'logout':
				return 'logoutUser';
			break;
	
			case 'FB-share':
				return 'FB-share';
			break;
	
			default:
				return null;
			break;
		}
	}
	
	/**
	 * Sets Action value
	 *
	 * @param 		string 	$strValue	Action value
	 * 
	 * @return		void	
	 * 
	 * @since		2010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setAction($strValue) {
		if(!is_string($strValue) || empty($strValue))	return false;
		
		$this->strAction 	= $strValue;
	}
	
	/**
	 * Sets strCharSet value
	 *
	 * @param 		string 	$strValue	strCharSet value
	 * 
	 * @return		void	
	 * 
	 * @since		2013-01-23
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setCharSet($strValue) {
		if(!is_string($strValue) || empty($strValue))	return false;
		
		$this->strCharSet 	= $strValue;
	}
	
	/**
	 * Sets SMARTY object and configures TPL directories
	 * 
	 * @param		string	$strTemplateDir	Sets SMARTY template dir path
	 * 
	 * @return		void	
	 * 
	 * @since		2010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function setSmarty($strTemplateDir = ROOT_TEMPLATE) {
		$this->objSmarty = new smarty_ApplrSmarty($strTemplateDir);
	}
	
	/**
	 * Sets View's template name
	 *
	 * @param 		string 	$strValue	File name
	 * 
	 * @return		void	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setTemplate($strValue) {
		if(!is_string($strValue) || empty($strValue))	return false;
		if(strpos($strValue,'.htm') === false) 			$strValue 	.= '.htm';
		
		$this->strTemplate 	= $strValue;
	}
	
	/**
	 * Gets Action value
	 * 
	 * @return		string	
	 * 
	 * @since		2010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function getAction() {
		return $this->strAction;
	}
	
	/**
	 * Gets strCharSet value
	 * 
	 * @return		string	
	 * 
	 * @since		2013-01-23
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function getCharSet() {
		return $this->strCharSet;
	}
	
	/**
	 * Gets error number defined by $this->intError
	 * 
	 * @return		mixed
	 * 
	 * @since		2008-12-07
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function getError() {
		return (isset($this->intError) && is_numeric($this->intError) ? $this->intError : null);
	}
	
	/**
	 * Checks if user is authenticated; if FALSE, redirects to authUser main interface
	 * 
	 * @return		void
	 * 
	 * @since 		2009-02-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function checkAccess() {
		if(!self::checkSessionAuth()) {
			$objAuth = new authUser_Controller();
			$objAuth->renderTemplate();
			exit();
		}
	}
	
	/**
	 * Validates e-mail syntax
	 *
	 * @param		string	$strMail	E-mail address
	 * 
	 * @return		boolean	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function checkEmailSyntax($strMail) {
    	if(!is_string($strMail) || empty($strMail))	return false;
        if(!@eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $strMail)) return false;
        
        return true;
    }
	
	/**
	 * Displays system interfaces
	 * 
	 * @param 		boolean	$boolDisplay	Defines if View's template is shown on browser or set as $this->renderTemplate() return value
	 * @param 		string	$strTemplate	Sets View's template name; if empty, assume parent name
	 *
	 * @return		string
	 * 
	 * @since 		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function renderTemplate($boolDisplay = true, $strTemplate = '') {
		// Valida as variaveis de parametro
		if(!is_bool($boolDisplay) && $boolDisplay !== 0 && $boolDisplay !== 1) 			$boolDisplay 	= false;
		if(!is_string($strTemplate) || empty($strTemplate) || is_null($strTemplate))	$strTemplate 	= str_replace(array('_Controller','_controller'),'',get_class($this));
		if(!in_array(end(explode('.',$strTemplate)),self::$arrExtTPL))					$strTemplate 	.= '.html';
		
		$this->setTemplate($strTemplate);
		
		// Renderize View's template
		if($boolDisplay) {
			$this->objSmarty->display($this->strTemplate);
		} else {
			return $this->objSmarty->fetch($this->strTemplate);
		}
	}
    
    /**
     * Decode HTML entities for $strValue on a given charset
     *
     * @param 		string	$strValue			Value to convert
	 * 
	 * @return		mixed	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function decodeEntities($strValue) {
    	if(!is_string($strValue))	return false;
    	
    	return html_entity_decode($strValue,ENT_QUOTES,$this->strCharSet);
    }
    
    /**
     * Convert HTML entities for $strValue in a given charset
     *
     * @param 		string	$strValue			Value to convert
	 * 
	 * @return		mixed	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function encodeEntities($strValue) {
    	if(!is_string($strValue))	return false;
    	
    	return htmlentities($this->decodeEntities($strValue),ENT_QUOTES,$this->strCharSet);
    }
    
    /**
     * Aplies a given method for each element on a multi-dimensional array
	 * 
     * @param 		array 	$arrSource			Array or Object to be analised and updated. Returns same variable type of $arrSource
     * @param 		string 	$strMethod			Method to apply on $arrSource elements
     * @param 		object 	$objInherit			If $strMethod is defined in an external class/object, defines source
     * @param 		boolean $boolFirstIteration	Defines first iteration, for recursive analisys
     * 
	 * @return		mixed
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function multiArrayWalk(&$arrSource, $strMethod, $objInherit = null, $boolFirstIteration = true) {
    	if(!is_array($arrSource) 	&& !is_object($arrSource))							return false;
    	if(!is_string($strMethod) 	|| empty($strMethod))								return false;
		if(is_object($objInherit)	&& !method_exists($objInherit,$strMethod)) 			return false;
		if(!is_object($objInherit)	&& !method_exists($this,$strMethod)) 				return false;
		
		if(is_array($arrSource)) {
			// Instantiates result array in first iteration, used for data concatenation on next cicles
			if($boolFirstIteration) $arrReturn = array();
			
			foreach($arrSource AS $intKey => &$strData) {
				if(is_array($strData)) {
					// Executes next iteration form next dimension
					$arrReturn[$intKey] = $this->multiArrayWalk($strData, $strMethod, $objInherit, false);
				} else {
					// Runs defined method on array element
					if(function_exists($strMethod)) {
						$strMethod($strData);
					} elseif(is_object($objInherit)) {
						$arrReturn[$intKey] = $objInherit->$strMethod($strData);
					} else {
						$arrReturn[$intKey] = $this->$strMethod($strData);
					}
				}
			}
		} else {
			// Instantiates result object in first iteration, used for data concatenation on next cicles
			if($boolFirstIteration) $arrReturn = null;
			
			foreach($arrSource AS $intKey => &$strData) {
				if(is_array($strData)) {
					// Executes next iteration form next dimension
					$arrReturn->$intKey = $this->multiArrayWalk($strData, $strMethod, $objInherit, false);
				} else {
					// Runs defined method on array element
					if(function_exists($strMethod)) {
						$strMethod($strData);
					} elseif(is_object($objInherit)) {
						$arrReturn->$intKey = $objInherit->$strMethod($strData);
					} else {
						$arrReturn->$intKey = $this->$strMethod($strData);
					}
				}
			}
		}
		
		return $arrReturn;
	}
	
    /**
     * Decode HTML entities for every element of $arrData, recursivelly
     *
     * @param 		array 	$arrSource			Array to be analised and updated
     * 
	 * @return		void	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function multiArrayDecodeEntities(&$arrData) { 
    	if((!is_array($arrData) && !is_object($arrData)) || sizeof($arrData) == 0)	return false;
    	
   		$arrData = $this->multiArrayWalk($arrData,'decodeEntities');
    }
    
    /**
     * Encode HTML entities for every element of $arrData, recursivelly
     *
     * @param 		array 	$arrSource			Array to be analised and updated
     * 
	 * @return		void
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function multiArrayEncodeEntities(&$arrData) { 
    	if((!is_array($arrData) && !is_object($arrData)) || sizeof($arrData) == 0)	return false;
    	
   		$arrData = $this->multiArrayWalk($arrData,'encodeEntities');
    }
    
	/**
	 * Replaces htmlentities special characters for their respective voel in a given string
	 *
	 * @param		string	$strValue			String to be analised and updated
	 *
	 * @return		string
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function replaceSpecialChars($strValue) {
    	if(!is_string($strValue) || empty($strValue))	return false;
    	
		// Special Characters array
		$arrSpecialChar = array('&aacute;', '&agrave;', '&acirc;', '&atilde;', '&auml;', '&eacute;', '&egrave;', '&ecirc;', '&euml;', '&iacute;', '&igrave;', '&icirc;', '&iuml;', '&oacute;', '&ograve;', '&ocirc;', '&otilde;', '&ouml', '&uacute;', '&ugrave;', '&ucirc;', '&uuml;', '&ccedil;',
								'&Aacute;', '&Agrave;', '&Acirc;', '&Atilde;', '&Auml;', '&Eacute;', '&Egrave;', '&Ecirc;', '&Euml;', '&Iacute;', '&Igrave;', '&Icirc;', '&Iuml;', '&Oacute;', '&Ograve;', '&Ocirc;', '&Otilde;', '&Ouml', '&Uacute;', '&Ugrave;', '&Ucirc;', '&Uuml;', '&Ccedil;');
							
		// Special Characters respective voels array
		$arrVoels  		= array('a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c',
								'A', 'A', 'A', 'A', 'A', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'C');
		
		return str_replace($arrSpecialChar,$arrVoels,$this->encodeEntities($strValue));
	}
	
	/**
	 * Replaces quotes and slashes for SQL Injection safety
	 *
	 * @param 		string $strValue	String to be analised
	 * 
	 * @return		string
	 * 
	 * @since		2008-12-15
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function replaceQuoteAndSlash($strValue) { 
	    $strValue = trim($strValue);
	    
	    $strValue = str_replace(array("\\",'\"',"\'","'",'"'),array('\\\\','\\\"',"\\\'","\'",'\"'),$strValue);
	    
	    return $strValue;
	}
	
	/**
	 * Unreplaces quotes and slashes for SQL Injection safety
	 *
	 * @param 		string $strValue	String to be analised
	 * 
	 * @return		string
	 * 
	 * @since		2008-12-15
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function unreplaceQuoteAndSlash($strValue) { 
	    $strValue = trim($strValue);
	    
	    $strValue = str_replace(array('\\\\','\\\"',"\\\'","\'",'\"'),array("\\",'\"',"\'","'",'"'),$strValue);
	    
		return $strValue;
	}
	
	/**
	 * Executes $this->replaceQuoteAndSlash() for superglobal variables
	 * 
	 * @return		void
	 * 
	 * @since		2008-12-15
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function secureGlobals() { 
	    foreach ($_REQUEST 	AS $intKey => &$strValue) {
	    	if(!is_array($strValue)) {
	        	$_REQUEST[$intKey] 	= self::replaceQuoteAndSlash($strValue);
	    	} else {
	    		$this->multiArrayWalk($strValue, 'replaceQuoteAndSlash');
	    	}
	    }
	    foreach ($_POST 	AS $intKey => &$strValue) {
	    	if(!is_array($strValue)) {
	        	$_POST[$intKey] 	= self::replaceQuoteAndSlash($strValue);
	    	} else {
	    		$this->multiArrayWalk($strValue, 'replaceQuoteAndSlash');
	    	}
	    }
	    foreach ($_GET 		AS $intKey => &$strValue) {
	    	if(!is_array($strValue)) {
	        	$_GET[$intKey] 		= self::replaceQuoteAndSlash($strValue);
	    	} else {
	    		$this->multiArrayWalk($strValue, 'replaceQuoteAndSlash');
	    	}
	    }
	    /*
	    foreach ($_SESSION 	AS $intKey => $strValue) {
	    	if(!is_array($strValue)) {
	        	$_SESSION[$intKey] 	= self::replaceQuoteAndSlash($strValue);
	    	} else {
	    		$this->multiArrayWalk($strValue, 'replaceQuoteAndSlash');
	    	}
	    }
	    */
	}
	
	/**
	 * Executes $this->unreplaceQuoteAndSlash() for superglobal variables
	 * 
	 * @return		void
	 * 
	 * @since		2008-12-15
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function unsecureGlobals() { 
	    foreach ($_REQUEST 	AS $intKey => $strValue) {
	    	if(!is_array($strValue)) {
	        	$_REQUEST[$intKey] 	= self::unreplaceQuoteAndSlash($strValue);
	    	} else {
	    		$this->multiArrayWalk($strValue, 'unreplaceQuoteAndSlash');
	    	}
	    }
	    foreach ($_POST 	AS $intKey => $strValue) {
	    	if(!is_array($strValue)) {
	        	$_POST[$intKey] 	= self::unreplaceQuoteAndSlash($strValue);
	    	} else {
	    		$this->multiArrayWalk($strValue, 'unreplaceQuoteAndSlash');
	    	}
	    }
	    foreach ($_GET 		AS $intKey => $strValue) {
	    	if(!is_array($strValue)) {
	        	$_GET[$intKey] 		= self::unreplaceQuoteAndSlash($strValue);
	    	} else {
	    		$this->multiArrayWalk($strValue, 'unreplaceQuoteAndSlash');
	    	}
	    }
	    /*
	    foreach ($_SESSION 	AS $intKey => $strValue) {
	    	if(!is_array($strValue)) {
	        	$_SESSION[$intKey] 	= self::unreplaceQuoteAndSlash($strValue);
	    	} else {
	    		$this->multiArrayWalk($strValue, 'unreplaceQuoteAndSlash');
	    	}
	    }
	    */
	}
	
	/**
	 * Escapes special characters against XSS attacks
	 *
	 * @param 		string $strData	String to escape
	 * 
	 * @return 		string
	 * 
	 * @since 		2009-02-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function escapeXSS($strData) {
		if((!is_string($strData) && !is_numeric($strData)) || empty($strData))	return false;
		
		return strip_tags(str_replace(self::$arrSpecialChar,'',self::replaceQuoteAndSlash($strData)));
	}
	
	/**
	 * Checks the occurence of any $this->arrSpecialChar in each $arrData element
	 * 
	 * @param		array	$arrData	Array of values to validate
	 * 
	 * @return		boolean
	 * 
	 * @since 		2009-02-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function checkXSSchar($arrData) {
		if(!is_array($arrData) 	|| count($arrData) 	== 0)								return false;
		
		foreach($arrData AS $strData) {
			foreach($this->arrSpecialChar AS $strChar) {
				if(strpos($strData,$strChar) !== false) return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Validates an array of parameters against an array of data type definition
	 * 
	 * @param		array	$arrValues			Array of values, where fieldName => fieldValue
	 * @param		array	$arrDataType		Array of data type definition, where fieldName => dataType
	 * @param		boolean	$boolKeepOtherData	Defines if not-defined $arrValues keys must be preserved
	 * 
	 * @return		mixed
	 * 
	 * @since 		2009-02-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function validateParamsArray(&$arrValues,$arrDataType,$boolKeepOtherData = true) {
		if(!is_array($arrValues))															return false;
		if(!is_array($arrDataType) 	|| count($arrDataType) 	== 0)							return false;
		
		// Validates $arrParams data
		foreach($arrDataType AS $strKey => $strDataType) {
			if(!array_key_exists($strKey,$arrValues)) 										$arrValues[$strKey] = null;
			
			switch(strtolower($strDataType)) {
				case 'numeric':
					if(!is_numeric($arrValues[$strKey])) 									return $strKey;
				break;
				
				case 'numeric_empty':
					if(!is_numeric($arrValues[$strKey]) && !empty($arrValues[$strKey])) 	return $strKey;
				break;
				
				case 'numeric_clearchar':
					if(!is_numeric($this->escapeXSS($arrValues[$strKey]))) 					return $strKey;
				break;
				
				case 'numeric_clearchar_empty':
					if(	!is_numeric($this->escapeXSS($arrValues[$strKey]))	&&
						!empty($arrValues[$strKey])) 										return $strKey;
				break;
				
				case 'string':
					if(!is_string($arrValues[$strKey])) 									return $strKey;
				break;
				
				case 'string_notempty':
					if(!is_string($arrValues[$strKey]) || empty($arrValues[$strKey])) 		return $strKey;
				break;
				
				case 'date':
					if(!checkdate(date('m',strtotime($arrValues[$strKey])),date('d',strtotime($arrValues[$strKey])),date('Y',strtotime($arrValues[$strKey])))) return $strKey;
				break;
				
				case 'email':
					if(empty($arrValues[$strKey]) || !$this->checkEmailSyntax($arrValues[$strKey])) return $strKey;
				break;
				
				case 'email_notempty':
					if(!$this->checkEmailSyntax($arrValues[$strKey]))						return $strKey;
				break;
				
				case 'boolean':
					if(	!is_bool($arrValues[$strKey]) 	&&
						$arrValues[$strKey] !== 1 		&& $arrValues[$strKey] !== 0	&&
						$arrValues[$strKey] !== '1' 	&& $arrValues[$strKey] !== '0')		return $strKey;
				break;
				
				case 'array':
					if(!is_array($arrValues[$strKey])) 										return $strKey;
				break;
				
				default:
				break;
			}
		}
		
		if(!$boolKeepOtherData) {
			$arrValues = array_intersect_key($arrValues,$arrDataType);
		}
		
		return true;
	}

	/**
	 * Checks if a specific record exists in database
	 * 
	 * @param	string	$strTable			DB::Attribute's name
	 * @param	string	$strField			DB::Entity's name
	 * @param	string	$strWhere			DB::Query WHERE statement
	 * @param	boolean	$boolReturnValue	Defines wether method returns BOOLEAN or DB::Attribute_VALUE
	 * 
	 * @return	mixed
	 * 
	 * @since 	2009-04-28
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function recordExists($strField,$strTable,$strWhere = '1',$boolReturnValue = false) {
		if(!is_string($strField)		|| empty($strField))	return false;
		if(!is_string($strTable)		|| empty($strTable))	return false;
		if(!is_string($strWhere)		|| empty($strWhere))	return false;
		if(!is_bool($boolReturnValue) 	&& $boolReturnValue !== 1 && $boolReturnValue !== 0)	return false;
		
		// Instantiates model class
		$strModelClass 	= str_replace('_Controller','_Model',get_class($this));
		if(!is_object($this->objModel)) $this->objModel	= new $strModelClass();
		
		return $this->objModel->recordExists($strField,$strTable,$strWhere,$boolReturnValue);
	}
	
	/**
	 * Sets TPL paging variables according to $intTotal
	 * 
	 * @param	integer	$intTotal			Total number of records to page
	 * @param	integer	$intLimit			List limit
	 * @param	integer	$intPage			Actual page on paging
	 * @param	integer	$intListPages		Number of pages for paging group
	 * 
	 * @return	boolean
	 * 
	 * @since 	2009-07-21
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setPagingVariables($intTotal, $intLimit = 10, $intPage = 1, $intListPages = 10) {
		// Verify method attributes
		if(	!is_numeric($intTotal)		|| $intTotal 		<= 0	||
			!is_numeric($intLimit)		|| $intLimit 		<= 0	||
			!is_numeric($intPage)		|| $intPage 		<= 0	||
			!is_numeric($intListPages)	|| $intListPages 	<= 0	) {
			return false;
		}
		
		$intTotalPages 	= ceil($intTotal / $intLimit);
		
		$intStart 		= ceil($intPage / $intListPages);
		if($intStart > 1) $intStart = $intStart * $intListPages - $intListPages + 1;
		$intEnd 		= ($intTotalPages < ($intStart + $intListPages) ? $intTotalPages - $intStart + 1 : $intListPages);
		
		$intNextPage	= ($intPage + 1 > $intTotalPages ? 0 : $intPage + 1);
		$intPrevPage	= ($intPage == 1 ? 0 : $intPage - 1);
		$intNextGroup	= ($intStart + $intListPages > $intTotalPages ? 0 : $intStart + $intListPages);
		$intPrevGroup	= ($intStart - $intListPages < 0 ? 0 : $intStart - $intListPages);
		
		$intOffSet 		= ($intPage * $intLimit) - $intLimit;
		
		$this->objSmarty->assign('intTotal',$intTotal);
		$this->objSmarty->assign('intLimit',($intOffSet + $intLimit));
		$this->objSmarty->assign('intOffSet',($intOffSet + 1));
		$this->objSmarty->assign('intPage',$intPage);
		$this->objSmarty->assign('intTotalPages',$intTotalPages);
		$this->objSmarty->assign('arrPaging',array_fill($intStart,$intEnd,true));
		$this->objSmarty->assign('intNextPage',$intNextPage);
		$this->objSmarty->assign('intPrevPage',$intPrevPage);
		$this->objSmarty->assign('intNextGroup',$intNextGroup);
		$this->objSmarty->assign('intPrevGroup',$intPrevGroup);
		
		return true;
	}
	
	/**
	 * Sets XLS headers and send to browser
	 * 
	 * @param	string $strFileName File's name to download
	 * 
	 * return	void
	 * 
	 * @since 	2013-01-16
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setXLSHeader($strFileName) {
		@header("Content-type:application/vnd.ms-excel");
		@header("Expires: 0");
		@header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		@header("Pragma: no-cache");
		@header("Content-Disposition:attachment; filename=".$strFileName);
	}

}