<?php
class authUser_Controller extends Controller {
	
	protected 	$objModel;
	
	/**
	 * Class constructor
	 * 
	 * @param	string	$strTemp	Client name
	 * 
	 * @return	void
	 * 
	 * @since 	2013-01-22
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function __construct($strName = '') {
		if(!is_string($strName)) return false;
		
		parent::__construct();
		
		if(!empty($strName)) $this->setProjectName($strName); else $this->setProjectName(PROJECT); 
		
		// Instantiates model class
		$this->objModel	= new authUser_Model();
	}
	
	/**
	 * Authenticates user
	 *
	 * @param 		string	$strLogin		Username
	 * @param 		string	$strPwd			Password
	 * @param		mixed	$strUserField	Specifies username field in DB::entity; if NULL, sets default field name
	 * @param		boolean	$boolMD5		Defines wether password is MD5 encrypted or not
	 * @param		boolean	$boolAuthCode	Defines wether user is identified by system's authCode or not
	 * 
	 * @return 		boolean
	 * 
	 * @since 		2013-01-22
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function authUser($strLogin,$strPwd,$strUserField = null,$boolMD5 = false,$boolAuthCode = false) {
		// Verify method attributes
		if(	!is_string($strLogin)	|| empty($strLogin)	||
			!is_string($strPwd)		|| empty($strPwd) 	){
			return false;
		}
		
		if(($objData = $this->objModel->authUser($strLogin,$strPwd,$strUserField,$boolMD5,$boolAuthCode)) !== false) {
			// Specialized variables 
			$_SESSION[self::$strProjectName]['id']				= $objData->id;
			$_SESSION[self::$strProjectName]['permalink']		= $objData->permalink;
			$_SESSION[self::$strProjectName]['name']			= $objData->name;
			$_SESSION[self::$strProjectName]['email']			= $objData->email;
			$_SESSION['apacheCheckCode']						= $objData->authcode;
			
			// Default variables to AuthUser Class - WARNING! DO NOT CHANGE ANY LINE BELOW
			$_SESSION[self::$strProjectName]['auth'] 			= true;
			$_SESSION[self::$strProjectName]['user'] 			= $strLogin;
			$_SESSION[self::$strProjectName]['userData']		= clone $objData;
			$_SESSION[self::$strProjectName]['remoteAddrAuth'] 	= md5((string) $_SERVER['REMOTE_ADDR']);
			$_SESSION[self::$strProjectName]['fingerprintAuth']	= md5(self::$strProjectName.'_AUTHSYS_'.$strLogin);
			$_SESSION[self::$strProjectName]['serverNameAuth']	= $_SERVER['SERVER_NAME'];
			
			return true;
		} else {
			
			return false;
		}
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
	static public function logoutUser() {
		unset($_SESSION[self::$strProjectName]);
		unset($_SESSION['apacheAuthCode']);
		unset($_SESSION['apacheCheckCode']);
	}
	
	/**
	 * Checks if user is logged in and, if not, shows AUTH NEEDED interface
	 * 
	 * @return 		void
	 * 
	 * @since 		2011-06-20
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	static public function isLoggedIn($displayTPL = false,$strTPL = 'sys/authNeeded.html') {
		if(!$displayTPL) {
			return authUser_Controller::checkSessionAuth();
		} elseif(!authUser_Controller::checkSessionAuth()) {	
			$objSmarty = new smarty_ApplrSmarty(SYS_ROOT . 'cms/views/');
					
			// Sets TRANSLATION terms
			languageTranslation_Controller::setTranslationVars($objSmarty);
			
			$objSmarty->assign('ERROR_MSG','You must be logged in to access Applr`s Admin!');
			
			// Shows interface
			$objSmarty->display($strTPL);
			
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
	static public function checkSessionAuth() {
		if(
			!isset($_SESSION[self::$strProjectName]['remoteAddrAuth']) 										|| 
			!isset($_SESSION[self::$strProjectName]['fingerprintAuth']) 									|| 
			!isset($_SESSION[self::$strProjectName]['serverNameAuth']) 										|| 
			$_SESSION[self::$strProjectName]['serverNameAuth'] !== $_SERVER['SERVER_NAME'] 					|| 
			/*md5((string) $_SERVER['REMOTE_ADDR']) !== $_SESSION[self::$strProjectName]['remoteAddrAuth'] 	||*/ 
			md5(self::$strProjectName.'_AUTHSYS_'.$_SESSION[self::$strProjectName]['user']) !== $_SESSION[self::$strProjectName]['fingerprintAuth']
		) {
			return false;
		} else {
			return true;
		}
	}
}
?>