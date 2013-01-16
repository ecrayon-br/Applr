<?php
class authUser_Controller {
	
	static 		$arrSpecialChar	= array('/','*',"'",'=','-','#',';','<','>','+','%','.',' ');
	static 		$strClientName	= 'PUBLICA';
	protected 	$objModel;
	
	/**
	 * Class constructor
	 * 
	 * @return		void
	 * 
	 * @since 		2010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function __construct() {
		include_once 'authUser_Model.php';
		
		// Instantiates model class
		if(!is_object($this->objModel)) $this->objModel	= new authUser_Model();
		
		$this->secureGlobals();
	}
	
	/**
	 * Authenticates user
	 *
	 * @param 		string	$strLogin	Username
	 * @param 		string	$strPwd		Password
	 * @param		mixed	$userField	Specifies username field in DB::entity; if NULL, sets bt_login
	 * 
	 * @return 		boolean
	 * 
	 * @since 		2010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function authUser($strLogin,$strPwd,$userField = null) {
		// Verify method attributes
		if(	!is_string($strLogin)	|| empty($strLogin)	||
			!is_string($strPwd)		|| empty($strPwd) 	){
			return false;
		}
		
		if(($objData = $this->objModel->authUser($strLogin,$strPwd,$userField)) !== false) {
			// Specialized variables 
			$_SESSION[self::$strClientName]['id']				= $objData->bn_id;
			$_SESSION[self::$strClientName]['permalink']		= $objData->permalink;
			$_SESSION[self::$strClientName]['name']				= $objData->bt_nome;
			$_SESSION[self::$strClientName]['email']			= $objData->bt_mail;
			$_SESSION['apacheCheckCode']						= $objData->bn_authcode;
			
			// Default variables to AuthUser Class - WARNING! DO NOT CHANGE ANY LINE BELOW
			$_SESSION[self::$strClientName]['auth'] 			= true;
			$_SESSION[self::$strClientName]['user'] 			= $strLogin;
			$_SESSION[self::$strClientName]['userData']			= clone $objData;
			$_SESSION[self::$strClientName]['remoteAddrAuth'] 	= md5((string) $_SERVER['REMOTE_ADDR']);
			$_SESSION[self::$strClientName]['fingerprintAuth']	= md5(self::$strClientName.'_AUTHSYS_'.$strLogin);
			$_SESSION[self::$strClientName]['serverNameAuth']	= $_SERVER['SERVER_NAME'];
			
			return true;
		} else {
			return false;
		}
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
	 * Executes private authUser method, verifies if authentication is true and displays related interface
	 * 
	 * @param 		string	$strLogin	Username
	 * @param 		string	$strPwd		Password
	 * @param		mixed	$userField	Specifies username field in DB::entity; if NULL, sets bt_login
	 * 
	 * @return 		boolean
	 * 
	 * @since 		2010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function doLogin($strLogin,$strPwd,$userField = null) {
		return $this->authUser($strLogin,$strPwd,$userField);
	}
	
	/**
	 * Destroys authenticated session
	 * 
	 * @return 		void
	 * 
	 * @since 		2010-04-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function logoutUser() {
		unset($_SESSION[self::$strClientName]);
		unset($_SESSION['apacheAuthCode']);
		unset($_SESSION['apacheCheckCode']);
	}
	
	/**
	 * Checks if user is logged in
	 * 
	 * @return 		void
	 * 
	 * @since 		2011-06-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	static public function isLoggedIn($displayTPL = false,$strTPL = 'sys/authNeeded.html') {
		@session_start();
		
		if(!$displayTPL) {
			return authUser_Controller::checkSessionAuth();
		} elseif(!authUser_Controller::checkSessionAuth()) {
			if(file_exists('../../../../cms/include-sistema/classe_smarty.php')) {
				include_once '../../../../cms/include-sistema/classe_smarty.php';
				include_once ROOT.'cms/include-sistema/funcao_termos.php';
			} elseif(file_exists('../../../cms/include-sistema/classe_smarty.php')) {
				include_once '../../../cms/include-sistema/classe_smarty.php';
				include_once ROOT.'cms/include-sistema/funcao_termos.php';
			} elseif(file_exists('../../cms/include-sistema/classe_smarty.php')) {
				include_once '../../cms/include-sistema/classe_smarty.php';
				include_once ROOT.'cms/include-sistema/funcao_termos.php';
			}
			
			// Cria o objeto SMARTY
			$smarty  = new smartyConnect;
			
			// Cria as variveis de TERMOS
			fnTermos($smarty);
			
			$smarty->assign('strMsg',$smarty->_tpl_vars['T_MSG_sem_permissao']);
			
			// Renderiza o TPL
			$smarty->display($strTPL);
			
			exit();
		} else {
			return true;
		}
	}
	
	/**
	 * Checks if SESSION belongs to authenticated user or if it's been hijacked by comparing REMOTE_ADDR & FINGERPRINT MD5 hash
	 * 
	 * @return		boolean
	 * 
	 * @subpackage 	authUser
	 * @since 		2009-02-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function checkSessionAuth() {
		if(
			!isset($_SESSION[self::$strClientName]['remoteAddrAuth']) 							|| 
			!isset($_SESSION[self::$strClientName]['fingerprintAuth']) 							|| 
			!isset($_SESSION[self::$strClientName]['serverNameAuth']) 							|| 
			$_SESSION[self::$strClientName]['serverNameAuth'] !== $_SERVER['SERVER_NAME'] 		|| 
			#md5((string) $_SERVER['REMOTE_ADDR']) !== $_SESSION[self::$strClientName]['remoteAddrAuth'] 	|| 
			md5(self::$strClientName.'_AUTHSYS_'.$_SESSION[self::$strClientName]['user']) !== $_SESSION[self::$strClientName]['fingerprintAuth']
		) {
			return false;
		} else {
			return true;
		}
	}
}
?>
