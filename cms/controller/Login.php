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
		
		$this->objAuth = new authUser_Controller();
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
	public function auth($strLogin,$strPwd) {
		if($this->objAuth->authUser($strLogin,$strPwd)) {
			header("Location: " . HTTP_CMS . "config");
			exit();
		} else {
			$this->objSmarty->assign('ERROR_MSG','There was an error in authentication! Please try again!');
			$this->renderTemplate();
		}
	}
}
?>