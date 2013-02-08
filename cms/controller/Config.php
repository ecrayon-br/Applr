<?php
class Config_controller extends Main_controller {
	
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
		parent::__construct(false);
		
		if(!DEBUG) authUser_Controller::isLoggedIn(true,'Login.html');
		
		if($boolRenderTemplate) $this->renderTemplate();
	}
	
	public function update() {
		// Setups main data objects
		$objData->project 	= array();
		$objData->config	= array();
		
		foreach($_POST AS $strKey => $mxdValue) {
			$tmpPrefix = reset(explode('_',$strKey));
			
			if($tmpPrefix == 'project') {
				$objData->project[str_replace($tmpPrefix . '_','',$strKey)]	= $mxdValue;
			} else {
				$objData->config[str_replace($tmpPrefix . '_','',$strKey)]	= $mxdValue;
			}
		}
		
		// Setups field's data
		$objManager			= new manageContent_Controller();
		$objData->project	= $objManager->setupFieldSufyx($objData->project,array_keys($objData->project),1);
		$objData->config	= $objManager->setupFieldSufyx($objData->config,array_keys($objData->config),1);
		
		// Updates PROJECT
		if($this->objModel->update('project',$objData->project,'id = 1') !== false) {
			if($this->objModel->update('config',$objData->config,'id = 1') !== false) {
				die('Success!');
			} else {
				die(ERROR_MSG);
			}
		} else {
			die(ERROR_MSG);
		}
		
		$this->renderTemplate();
	}
}
?>