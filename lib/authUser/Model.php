<?php
class authUser_Model extends Model {
	
	/**
	 * Class constructor
	 * 
	 * @return		void
	 * 
	 * @since 		2013-01-22
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Verifies login/password in database and authenticates user. If TRUE, returns Data Object; if FALSE, returns boolean.
	 *
	 * @param 		string	$strLogin		Username
	 * @param 		string	$strPwd			Password
	 * @param		mixed	$strUserField	Specifies username field in DB::entity; if NULL, sets default field name
	 * @param		boolean	$boolMD5		Defines wether password is MD5 encrypted or not
	 * @param		boolean	$boolAuthCode	Defines wether user is identified by system's authCode or not
	 * 
	 * @return 		mixed
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
		if(!is_bool($boolMD5) 		&& $boolMD5 		!== 0 && $boolMD5 		!== 1)	$boolMD5 		= false;
		if(!is_bool($boolAuthCode) 	&& $boolAuthCode 	!== 0 && $boolAuthCode 	!== 1)	$boolAuthCode 	= false;
		
		// Defines whether use MD5 algorithm
		if($boolMD5) {
			$strPwd = $this->objConn->quote(md5($strPwd));
		} else {
			$strPwd = $this->objConn->quote($strPwd);
		}
		
		// Sets FIELDS and WHERE statement
		$arrAuthData = array(
						(!is_null($strUserField) ? $strUserField : 'username') . ' = ' . $this->objConn->quote($strLogin),
						'password = ' . $strPwd
						);
		if($boolAuthCode) {
			$arrFields		= array('*');
			$arrAuthData[]	= 'authcode_true = 1';
		} else {
			$arrFields		= array('*','NULL AS authcode');
		}
		
		// Queries DB
		$objQuery = $this->select($arrFields,'usr_data',array(),$arrAuthData);
		if($objQuery !== false) $objQuery = reset($objQuery);
		
		// If record exists, returns data object 
		if(isset($objQuery->id) && is_numeric($objQuery->id)) {
			return $objQuery;
		} else {
			return false;
		}
	}
}
?>
