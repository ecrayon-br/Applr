<?php
class authUser_Model extends Model {
	
	/**
	 * Class constructor
	 * 
	 * @return		void
	 * 
	 * @subpackage 	authUser
	 * @since 		2008-12-07
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Verifies login/password in database and authenticates user. If TRUE, returns Data Object; if FALSE, returns boolean.
	 *
	 * @param 		string	$strLogin	Username
	 * @param 		string	$strPwd		Password
	 * @param		mixed	$userField	Specifies username field in DB::entity; if NULL, sets bt_login
	 * @param		boolean	$boolMD5	Defines wether password is MD5 encrypted or not
	 * @param		boolean	$authCode	Defines wether user is identified by system's authCode or not
	 * 
	 * @return 		mixed
	 * 
	 * @subpackage 	authUser
	 * @since 		2008-12-15
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function authUser($strLogin,$strPwd,$userField = null,$boolMD5 = false,$authCode = false) {
		// Verify method attributes
		if(	!is_string($strLogin)	|| empty($strLogin)	||
			!is_string($strPwd)		|| empty($strPwd) 	){
			return false;
		}
		if(!is_bool($boolMD5) 	&& $boolMD5 	!== 0 && $boolMD5 	!== 1)	$boolMD5 	= false;
		if(!is_bool($authCode) 	&& $authCode 	!== 0 && $authCode 	!== 1)	$authCode 	= false;
		
		// Escapes password string
		if($boolMD5) {
			$strPwd = md5(authUser_Controller::replaceQuoteAndSlash($strPwd));
		} else {
			$strPwd = authUser_Controller::replaceQuoteAndSlash($strPwd);
		}
		
		$strQuery	= 'SELECT *, '.($authCode ? 'authcode' : 'NULL AS authcode').' FROM usr_data WHERE '.(!is_null($userField) ? $userField : 'username').' = "'.authUser_Controller::replaceQuoteAndSlash($strLogin).'" AND password = "'.$strPwd.'" '.($authCode ? 'AND authcode_true = 1' : '').';';
		$objQuery	= $this->executeQuery($strQuery);
		$objQuery	= $objQuery->fetchRow();
		
		if(isset($objQuery->id) && is_numeric($objQuery->id)) {
			return $objQuery;
		} else {
			return false;
		}
	}
}
?>
