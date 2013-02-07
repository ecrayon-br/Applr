<?php
class languageTranslation_Controller extends Controller {
	protected $objModel;
	
	public function __construct() {
		parent::__construct();
		
		$this->objModel = new languageTranslator_Model();
	}
	
	static public function setTranslationVars($objData) {
		
	}
}