<?php
include SMARTY_DIR . 'Smarty.class.php';

class smarty_ApplrSmarty extends Smarty{
	public function __construct() {
		parent::__construct();
		
		$this->setTemplateDir(ROOT_TEMPLATE);
		$this->setCompileDir(SMARTY_DIR . 'templates_c/');
		$this->setConfigDir(SMARTY_DIR . 'configs/');
		$this->setCacheDir(SMARTY_DIR . 'cache/');
		
		$this->debugging = false;
	}
} 