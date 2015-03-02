<?php
class Config_controller extends CRUD_Controller {
	/**
	 * 
	 * ATENTION!
	 * 
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 * 
	 */
	protected	$strTable		= 'config';
	protected	$arrTable		= array('config','project');
	
	protected	$arrFieldType	= array(
										'project_name' 					=> 'string',
										'project_description' 			=> 'string_empty',
										'project_start_date_Day' 		=> 'numeric',
										'project_start_date_Month' 		=> 'numeric',
										'project_start_date_Year' 		=> 'numeric',
										'project_logo_upload'			=> 'string_empty',
										'config_paging_limit' 			=> 'numeric',
										'config_rss_group' 				=> 'numeric_empty',
										'config_rss_limit' 				=> 'numeric_empty',
										'config_dir_upload' 			=> 'string',
										'config_dir_image' 				=> 'string',
										'config_dir_video' 				=> 'string',
										'config_dir_template' 			=> 'string',
										'config_dir_static' 			=> 'string',
										'config_dir_dynamic' 			=> 'string',
										'config_dir_xml' 				=> 'string',
										'config_dir_rss' 				=> 'string',
										'config_mail_auth' 				=> 'boolean',
										'config_mail_auth_host' 		=> 'string_empty',
										'config_mail_auth_user' 		=> 'string_empty',
										'config_mail_auth_password' 	=> 'string_empty',
										'config_mail_sys' 				=> 'email',
										'config_mail_public' 			=> 'email_empty',
										'config_mail_contact' 			=> 'email_empty',
										'config_mail_user' 				=> 'email_empty'
									);
	protected	$arrOrderList	= array('config.project_id');
	protected	$arrGroupList	= array('config.project_id');
	protected	$arrFieldData	= array(
										'project.name',
										'project.description',
										'project.logo_upload',
										'project.start_date',
										'config.*'
									);
	protected	$arrWhereData	= array(
										'project.id = config.project_id',
										'project.id = {id}'
									);
	protected	$arrOrderData	= array('config.project_id');
	protected	$arrGroupData	= array('config.project_id');

	public function __construct($boolRenderTemplate = true) {
		parent::__construct($boolRenderTemplate,true,CMS_ROOT_TEMPLATE);
	}
	
	/**
	 * Get CONFIG and PROJECT data
	 *
	 * @return	void
	 *
	 * @since 	2013-02-09
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function _read() {
		$this->objData	= $this->objModel->getData($this->intProjectID);
		$this->objData->logo_upload = SYS_DIR . $this->objData->logo_upload;
		
		$this->objSmarty->assign('objData',$this->objData);
		$this->renderTemplate();
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
	public function add() {
		$this->unsecureGlobals();
		
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
		if(isset($_FILES['project_logo_upload']['name'])) {
			$_POST['project_logo_upload'] = '';
			$objData->project['project_logo_upload'] = '';
		}
		#echo '<pre>'; var_dump($objData->config); die();
		$arrOrgData	= array_merge($objData->project,$objData->config);
		#echo '<pre>'; var_dump($arrOrgData); die();
		
		if(($mxdValidate = $this->validateParamsArray($_POST,$this->arrFieldType,false)) === true) {
			// Setups field's data
			#echo '<pre>'; var_dump($objData->project); die();
			$objManager						= new manageContent_Controller();
			$objData->project				= $objManager->setupFieldSufyx($objData->project,array_keys($objData->project),1);
			$objData->config				= $objManager->setupFieldSufyx($objData->config,array_keys($objData->config),1);
			$objData->project->logo_upload 	= $objData->project->project_logo_upload;
			unset($objData->project->project_logo_upload);
			
			#echo '<pre>'; var_dump($objData->project); die();
			
			// Updates PROJECT
			if($this->objModel->update('project',$objData->project,'id = ' . $this->intProjectID) !== false) {
				if($this->objModel->update('config',$objData->config,'project_id = ' . $this->intProjectID) !== false) {
					$this->objSmarty->assign('ALERT_MSG','Data updated successfully!');
					$this->objData	= $this->objModel->getData($this->intProjectID);
				} else {
					$this->objSmarty->assign('ERROR_MSG','There was an error while trying to update CONFIG data! Please try again!');
					$this->objData	= (object) $arrOrgData;
				}
			} else {
				$this->objSmarty->assign('ERROR_MSG','There was an error while trying to update PROJECT data! Please try again!');
				$this->objData	= (object) $arrOrgData;
			}
		} else {
			$this->objSmarty->assign('ERROR_MSG','There was an error while validating "' . $mxdValidate . '" data! Please try again!');
			$this->objData				= (object) $arrOrgData;
			$this->objData->start_date 	= $this->objData->start_date_Year . '-' . $this->objData->start_date_Month . '-' . $this->objData->start_date_Day;
		}
		if(!empty($this->objData->logo_upload)) $this->objData->logo_upload = SYS_DIR . $this->objData->logo_upload;
		$this->objSmarty->assign('objData',$this->objData);
		
		$this->secureGlobals();
		
		$this->renderTemplate();
	}
}
?>