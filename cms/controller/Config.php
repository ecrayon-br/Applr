<?php
class Config_controller extends Main_controller {
	protected 	$objModel;
	
	public		$objData;
	
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
		
		$this->objModel	= new Config_model();
		
		$this->objData	= $this->objModel->getData($this->intProjectID);
		
		if($boolRenderTemplate) {
			$this->objSmarty->assign('objData',$this->objData);
			$this->renderTemplate();
		}
	}
	
	/**
	 * Updates CONFIG and PROJECT data and prints resulting interface
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
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
		$arrOrgData	= array_merge($objData->project,$objData->config);
		
		// Setups field's data
		$objManager			= new manageContent_Controller();
		$objData->project	= $objManager->setupFieldSufyx($objData->project,array_keys($objData->project),1);
		$objData->config	= $objManager->setupFieldSufyx($objData->config,array_keys($objData->config),1);
		
		// Updates PROJECT
		if($this->objModel->update('project',$objData->project,'id = ' . $this->intProjectID) !== false) {
			if($this->objModel->update('config',$objData->config,'project_id = ' . $this->intProjectID) !== false) {
				$this->objSmarty->assign('ALERT_MSG','Data updated successfully!');
				$this->objData	= $this->objModel->getData($this->intProjectID);
			} else {
				$this->objSmarty->assign('ERROR_MSG','There was an error while trying to update data! Please try again!');
				$this->objData	= (object) $arrOrgData;
			}
		} else {
			$this->objSmarty->assign('ERROR_MSG','There was an error while trying to update data! Please try again!');
			$this->objData	= (object) $arrOrgData;
		}
		
		$this->objSmarty->assign('objData',$this->objData);
		$this->renderTemplate();
	}
}
?>