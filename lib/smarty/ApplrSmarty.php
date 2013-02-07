<?php
include SMARTY_DIR . 'Smarty.class.php';

class smarty_ApplrSmarty extends Smarty{
	
	/**
	 * Class constructor, instantiates SMARTY lib
	 * 
	 * @param	string	$strTemplateDir	SMARTY template dir path
	 * 
	 * @return	void
	 * 
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function __construct($strTemplateDir = ROOT_TEMPLATE) {
		parent::__construct();
		
		$this->setTemplateDir($strTemplateDir);
		$this->setCompileDir(SMARTY_DIR . 'templates_c/');
		$this->setConfigDir(SMARTY_DIR . 'configs/');
		$this->setCacheDir(SMARTY_DIR . 'cache/');
		
		$this->debugging = false;
		
		//$this->clear_all_cache();
	}
} 