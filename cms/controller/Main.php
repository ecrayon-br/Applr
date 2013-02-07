<?php
class Main_controller extends Controller {
	
	public function __construct($boolRenderTemplate = true) {
		parent::__construct(false,SYS_ROOT . 'cms/views/');
		
		if($boolRenderTemplate) $this->renderTemplate();
	}
}