<?php
class Login_controller extends Main_controller {
	private $objAuth;

	/**
	 * Class constructor
	 *
	 * @param	boolean	$boolRenderTemplate	Defines whether to show default's interface
	 *
	 * @return	void
	 *
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($boolRenderTemplate = true) {
		parent::__construct($boolRenderTemplate);
	}
	
	/**
	 * Authenticates user
	 *
	 * @param 		string	$strLogin		Username
	 * @param 		string	$strPwd			Password
	 *
	 * @return 		boolean
	 *
	 * @since 		2013-01-22
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function auth($strLogin = '',$strPwd = '') {
		// Verify method attributes
		if(!is_string($strLogin) 	|| empty($strLogin)) $strLogin 	= $_REQUEST['username'];
		if(!is_string($strPwd)		|| empty($strPwd))	 $strPwd	= $_REQUEST['password'];
		if(!is_string($strLogin) 	|| empty($strLogin)) return false;
		if(!is_string($strPwd)		|| empty($strPwd))	 return false;

		$this->objAuth = new authUser_Controller();
		
		if($this->objAuth->authUser($strLogin,$strPwd)) {
			header("Location: " . HTTP_CMS . "main");
			exit();
		} else {
			$this->objSmarty->assign('ERROR_MSG','There was an error in authentication! Please try again!');
			$this->renderTemplate();
		}
	}
	
	/**
	 * Exits Applr system
	 *
	 * @return 		void
	 *
	 * @since 		2013-02-09
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function out() {
		authUser_Controller::logoutUser();
		parent::__construct(true);
	}
}
?>