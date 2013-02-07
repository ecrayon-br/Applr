<?php
class Main_controller extends Controller {
	
	public function __construct($boolRenderTemplate = true) {
		parent::__construct();
		
		$this->objSmarty->setTemplateDir(SYS_ROOT . 'cms/views/');
		
		if($boolRenderTemplate) $this->renderTemplate();
	}
}