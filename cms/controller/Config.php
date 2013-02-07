<?php
class Config_controller extends Main_controller {
	
	public function __construct($boolRenderTemplate = true) {
		parent::__construct($boolRenderTemplate);
	}
	
	public function update() {
		echo 'UPDATE!';
	}
}
?>