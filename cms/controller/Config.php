<?php
class Config_controller extends Main_controller {
	
	public function __construct($boolRenderTemplate = true) {
		parent::__construct(false);
		
		if(!DEBUG) authUser_Controller::isLoggedIn(true,'Login.html');
		
		if($boolRenderTemplate) $this->renderTemplate();
	}
	
	public function update() {
		echo 'UPDATE!';
	}
}
?>