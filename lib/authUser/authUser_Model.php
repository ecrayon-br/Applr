<?php
include_once '../../../cms/include-sistema/config_banco.php';

class authUser_Model {
		
	protected 	$_dbType	= DB_TYPE;
	protected 	$_hostName	= DB_HOST;
	protected 	$_userName	= DB_USER;
	protected 	$_password	= DB_PASSWORD;
	protected 	$_dbName	= DATABASE;
	protected  	$objConn;
	
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
		$this->setConnection();
	}
	
	/**
	 * Connects with DB
	 * 
	 * @return	boolean
	 *
	 * @author	Diego Flores <diegotf [at] gmail dot com>
	 * @since	2008-01-29
	**/
	public function setConnection() {
		$strDSN			= "$this->_dbType://$this->_userName:$this->_password@$this->_hostName/$this->_dbName";
		$arrOptions		= array	(
		    					'debug'       => 2,
		    					'portability' => DB_PORTABILITY_ALL,
								);
		$this->objConn 	=& DB::connect($strDSN,$arrOptions);
		
		if (PEAR::isError($this->objConn)) return false;
		
		$this->setFetchMode();
		
		return true;
	}
	
	/**
	 * Defines DB_FETCHMODE constant
	 *
	 * @param	string	$mxdFetch	Defines DB_FETCHMODE constant
	 * 
	 * @return	void
	 *
	 * @author	Diego Flores <diegotf [at] gmail dot com>
	 * @since	2008-01-29
	**/
	public function setFetchMode($mxdFetch = 0) {
		if(!is_object($this->objConn))							return false;
		if(!is_string($mxdFetch) && !is_numeric($mxdFetch))		return false;
		if(is_string($mxdFetch))								$mxdFetch = strtoupper($mxdFetch);
		
		// Seta o FETCH_MODE
		switch($mxdFetch) {
			case 'OBJ':
			case 0:
			default:
				$this->objConn->setFetchMode(DB_FETCHMODE_OBJECT);
			break;
			
			case 'ORDERED':
			case 1:
				$this->objConn->setFetchMode(DB_FETCHMODE_ORDERED);
			break;
			
			case 'ASSOC':
			case 2:
				$this->objConn->setFetchMode(DB_FETCHMODE_ASSOC);
			break;
		}
	}
	
	/**
	 * Executes query on database
	 *
	 * @param 		string $strQuery	Query to execute
	 * 
	 * @return 		DB::object
	 * 
	 * @since 		2008-12-07
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	protected function executeQuery($strQuery) {
		if(!is_string($strQuery)|| empty($strQuery))	return false;
		
		return $this->objConn->query($strQuery);
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
		
		$strQuery	= 'SELECT *, '.($authCode ? 'bn_authcode' : 'NULL AS bn_authcode').' FROM tb_conteudo_usuario WHERE '.(!is_null($userField) ? $userField : 'bt_login').' = "'.authUser_Controller::replaceQuoteAndSlash($strLogin).'" AND bt_senha = "'.$strPwd.'" '.($authCode ? 'AND bb_preenchido_simnao = 1' : '').';';
		$objQuery	= $this->executeQuery($strQuery);
		$objQuery	= $objQuery->fetchRow();
		
		if(isset($objQuery->bn_id) && is_numeric($objQuery->bn_id)) {
			return $objQuery;
		} else {
			return false;
		}
	}
}
?>
