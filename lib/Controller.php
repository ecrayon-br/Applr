<?php
class Controller {
	
	static 		$arrSpecialChar	= array('/','*',"'",'=','-','#',';','<','>','+','%','.',' ');
	
	public		$intError;
	public		$objSmarty;
	
	protected	$strClientName	= CLIENT;
	protected	$strAction		= '';
	protected	$objModel;
	
	private		$strTemplate;
	
	/**
	 * Class constructor
	 *
	 * @param 		boolean	$boolRenderView	Defines if View's template is renderized on $this->__construct() or heir method call
	 *
	 * @return		void
	 * 
	 * @since 		2010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 * @todo 		Check PHP updates on referencial variables
	 * 				Define charset on $this->multiArrayDecodeEntities()
	 * 				Implement /cms/include-sistema/variaveis.php on $this->__construct()
	 * 				Implement template name automatically on $this->renderTemplate()
	 * 				Implement internationalization
	 * 
	 * 
	 */
	public function __construct($boolRenderView = false) {
		if(!is_bool($boolRenderView) && $boolRenderView !== 1 && $boolRenderView !== 0)	$boolRenderView = false;
		
		@session_start();
		@set_time_limit(0);
		
		// Aplies security for superglobal variables
		$this->secureGlobals();
		
		// Instantiates SMARTY object
		//$this->setSmarty();
		
		// Renderize View's template
		if($boolRenderView) $this->renderTemplate();
	}
	
	public function permalinkSyntax() {
		
	}
	
	public function getSectionConfig() {
		
	}
	
	public function getURISegment() {
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
				return 'cms';
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
	 * @since		20010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setAction($strValue) {
		if(!is_string($strValue) || empty($strValue))	return false;
		
		$this->strAction 	= $strValue;
	}
	
	/**
	 * Sets SMARTY object and configures TPL directories
	 * 
	 * @return		void	
	 * 
	 * @since		20010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function setSmarty() {
		// Creates SMARTY object
		$this->objSmarty 				= new Smarty();
		$this->objSmarty->template_dir 	= ROOT_TPL;
		$this->objSmarty->config_dir	= ROOT_TPL . 'configs/';
		$this->objSmarty->compile_dir	= ROOT_TPL . 'templates_c/';
		$this->objSmarty->cache_dir		= ROOT_TPL . 'cache/';
		$this->objSmarty->caching 		= false;
		$this->objSmarty->clear_all_cache();
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
	 * @since		20010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function getAction() {
		return $this->strAction;
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
	 * Checks if SESSION belongs to authenticated user or if it's been hijacked by comparing REMOTE_ADDR & FINGERPRINT MD5 hash
	 * 
	 * @return		boolean
	 * 
	 * @since 		2009-02-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	protected function checkSessionAuth() {
		if(!isset($_SESSION[$this->strClientName]['remoteAddrAuth']) || !isset($_SESSION[$this->strClientName]['fingerprintAuth']) || !isset($_SESSION[$this->strClientName]['serverNameAuth']) || $_SESSION[$this->strClientName]['serverNameAuth'] !== $_SERVER['SERVER_NAME'] || (md5($_SERVER['REMOTE_ADDR']) !== $_SESSION[$this->strClientName]['remoteAddrAuth'] || md5($this->strClientName.'_AUTHSYS_'.$_SESSION[$this->strClientName]['user']) !== $_SESSION[$this->strClientName]['fingerprintAuth'])) {
			return false;
		} else {
			return true;
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
		if(!is_string($strTemplate) || empty($strTemplate) || is_null($strTemplate))	$strTemplate 	= str_replace('_Controller','',get_class($this));
		if(strpos($strTemplate,'.htm') === false) 										$strTemplate 	.= '.htm';
		
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
     * @param 		string	$strCharset			Charset used in convertion
	 * 
	 * @return		mixed	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function decodeEntities($strValue,$strCharset = 'UTF-8') {
    	if(!is_string($strValue))							return false;
    	if(!is_string($strCharset) 	|| empty($strCharset))	return false;
    	
    	return html_entity_decode($strValue,ENT_QUOTES,$strCharset);
    }
    
    /**
     * Convert HTML entities for $strValue in a given charset
     *
     * @param 		string	$strValue			Value to convert
     * @param 		string	$strCharset			Charset used in convertion
	 * 
	 * @return		mixed	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function encodeEntities($strValue,$strCharset = 'UTF-8') {
    	if(!is_string($strValue))							return false;
    	if(!is_string($strCharset) 	|| empty($strCharset))	return false;
    	
    	return htmlentities($this->decodeEntities($strValue),ENT_QUOTES,$strCharset);
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
					$arrReturn[$intKey] = $this->multiArrayWalk(&$strData, $strMethod, $objInherit, false);
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
					$arrReturn->$intKey = $this->multiArrayWalk(&$strData, $strMethod, $objInherit, false);
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
	    
	    $strValue = str_replace("\\",'\\\\'	,$strValue);
		$strValue = str_replace("'","\'"	,$strValue);
		$strValue = str_replace('"','\"'	,$strValue);
		$strValue = str_replace('\"','\\"'	,$strValue);
		$strValue = str_replace("\'","\\'"	,$strValue);
	    
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
	    
	    $strValue = str_replace('\\\\',"\\"	,$strValue);
		$strValue = str_replace("\'","'"	,$strValue);
		$strValue = str_replace('\"','"'	,$strValue);
		$strValue = str_replace('\\"','\"'	,$strValue);
		$strValue = str_replace("\\'","\'"	,$strValue);
	    
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
	    foreach ($_REQUEST 	AS $intKey => $strValue) {
	        $_REQUEST[$intKey] 	= self::replaceQuoteAndSlash($strValue);
	    }
	    foreach ($_POST 	AS $intKey => $strValue) {
	        $_POST[$intKey] 	= self::replaceQuoteAndSlash($strValue);
	    }
	    foreach ($_GET 		AS $intKey => $strValue) {
	        $_GET[$intKey] 		= self::replaceQuoteAndSlash($strValue);
	    }
	    /*
	    foreach ($_SESSION 	AS $intKey => $strValue) {
	        $_SESSION[$intKey] 	= self::replaceQuoteAndSlash($strValue);
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
	 * @return		boolean
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
			if(!array_key_exists($strKey,$arrValues)) 										return $strKey;
			
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
					if(!checkdate(date('m',strtotime($arrValues[$strKey])),date('d',strtotime($arrValues[$strKey])),date('Y',strtotime($arrValues[$strKey])))) return false;
				break;
				
				case 'email':
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