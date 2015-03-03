<?php
class Destino_controller extends Main_Controller {


	/**
	 * Class constructor
	 *
	 * @return	void
	 *
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($boolRenderTemplate = true) {
		parent::__construct($boolRenderTemplate);
		
		// Checks if is_user cookie exists
		if(!isset($_COOKIE['is_user'])) {
			$this->objSmarty->assign('is_user',false);
		} else {
			$this->objSmarty->assign('is_user',true);
		}
	}
	
	public function save() {
		$objUser = new Main_Controller(false,4);
		
		if($objUser->insertMainContent($_POST)) {
			echo 'Salvei!<br />';
		} else {
			echo 'Erro!<br />';
		}
	}
}
?>