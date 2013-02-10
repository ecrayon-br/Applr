<?php
class MediaGallery_controller extends CRUD_controller {	
	/**
	 * 
	 * ATENTION!
	 * 
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 * 
	 */
	protected	$strTable		= 'media_gallery';
	protected	$arrTable		= array('media_gallery');
	
	protected	$arrFieldType	= array(
										'id'			=> 'numeric_empty',
										'usr_data_id'	=> 'numeric',
										'sec_config_id'	=> 'numeric_empty',
										'name'			=> 'string_notempty',
										'mediatype'		=> 'numeric',
										'description'	=> 'string',
										'dirpath'		=> 'string',
										'public'		=> 'boolean',
										'is_default'	=> 'boolean',
										'autothumb'		=> 'boolean',
										'autothumb_h'	=> 'numeric_empty',
										'autothumb_w'	=> 'numeric_empty',
										'status'		=> 'boolean'
									);
		
	protected	$arrFieldList	= array('media_gallery.*');
	protected	$arrWhereList	= array('media_gallery.status = 1','media_gallery.is_default = 0');
	
	protected	$arrFieldData	= array('media_gallery.*');
	protected	$arrWhereData	= array('media_gallery.id = {id}');
	
	/**
	 * @see CRUD_controller::delete()
	 */
	public function delete() {
		parent::delete(0);
	}
	
	/**
	 * Inserts / Updates data
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function add() {
		$this->unsecureGlobals();
		
		// Sets sys vars
		if(!is_numeric($_POST['mediatype']) || $_POST['mediatype'] < 0 || $_POST['mediatype'] > 2) $_POST['mediatype'] = 2;
		if(!isset($_POST['is_default']) || $_POST['is_default'] != 0) 	$_POST['is_default'] = 0;
		if(!isset($_POST['dirpath']) || empty($_POST['dirpath'])) 	$_POST['dirpath'] = null;
		if(!isset($_POST['sec_config_id']) || empty($_POST['sec_config_id'])) {
			$_POST['sec_config_id'] = null;
			$_POST['public'] 		= 1;
		} elseif(!is_numeric($_POST['sec_config_id']) || $_POST['sec_config_id'] <= 0) $_POST['sec_config_id'] = null;
		
		// Associated Directory
		if(!is_null($_POST['dirpath'])) {
			$strDirName = $_POST['dirpath'];
		// Associated Section
		} elseif(!is_null($_POST['sec_config_id'])) {
			$strDirName = Controller::permalinkSyntax($this->objModel->recordExists('name','sec_config','sec_config.id = ' . $_POST['sec_config_id'],true));
		// Common Gallery
		} else {
			$strDirName = Controller::permalinkSyntax($_POST['name']);
		}
		switch($_POST['mediatype']) {
			// Image
			case 2:
			default:
				$boolDir = @mkdir(ROOT_IMAGE . $strDirName,0755);
				break;
		
				// Video
			case 1:
				$boolDir = @mkdir(ROOT_VIDEO . $strDirName,0755);
				break;
		
				// Upload
			case 0:
				$boolDir = @mkdir(ROOT_UPLOAD . $strDirName,0755);
				break;
		}
		
		if(!isset($_POST['autothumb_h']) || empty($_POST['autothumb_h'])) 	$_POST['autothumb_h'] = null;
		if(!isset($_POST['autothumb_w']) || empty($_POST['autothumb_w'])) 	$_POST['autothumb_w'] = null;
		if(!is_null($_POST['autothumb_h']) || !is_null($_POST['autothumb_w'])) $_POST['autothumb'] = 1; else $_POST['autothumb'] = 0; 
		
		$this->_update($_POST);
		
		$this->secureGlobals();
	}
}