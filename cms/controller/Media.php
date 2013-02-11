<?php
class Media_controller extends CRUD_controller {	
	/**
	 * 
	 * ATENTION!
	 * 
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 * 
	 */
	protected	$strTable		= 'media_data';
	protected	$arrTable		= array('media_data','media_gallery');
	
	protected	$arrFieldType	= array(
										'id'				=> 'numeric_empty',
										'media_gallery_id'	=> 'numeric',
										'usr_data_id'		=> 'numeric',
										'mediatype'			=> 'numeric',
										'name'				=> 'string_notempty',
										'author'			=> 'string',
										'label'				=> 'string',
										'filepath'			=> 'string_notempty',
										'filepath_thumbnail'=> 'string',
										'filepath_streaming'=> 'string'
									);
		
	protected	$arrFieldList	= array('media_data.*','media_gallery.name AS media_gallery_name');
	protected	$arrWhereList	= array('media_gallery.id = media_data.media_gallery_id');
	
	protected	$arrFieldData	= array('media_data.*','media_gallery.name AS media_gallery_name','media_gallery.autothumb','media_gallery.autothumb_h','media_gallery.autothumb_w');
	protected	$arrWhereData	= array('media_gallery.id = media_data.media_gallery_id','media_data.id = {id}');
	
	private		$arrGallList	= array();
	
	/**
	 * Class constructor
	 *
	 * @param	boolean	$boolRenderTemplate	Defines whether to show default's interface
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($boolRenderTemplate = true) {
		parent::__construct(false);
		
		// Gets GALLERY list
		$this->arrGallList	= (array) $this->objModel->select(array('id','name'),'media_gallery',array(),array(),array(),array(),0,null,'Col');
		$this->objSmarty->assign('arrGall',$this->arrGallList);
	
		// Shows default interface
		if($boolRenderTemplate) $this->_read();
	}
	
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
		
		/*
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
				if(@mkdir(ROOT_IMAGE . $strDirName,0755)) {
					$strDirName = DIR_IMAGE . $strDirName;
				}
			break;
		
			// Video
			case 1:
				if(@mkdir(ROOT_VIDEO . $strDirName,0755)) {
					$strDirName = DIR_VIDEO . $strDirName;
				}
			break;
		
			// Upload
			case 0:
				if(@mkdir(ROOT_UPLOAD . $strDirName,0755)) {
					$strDirName = DIR_UPLOAD . $strDirName;
				}
			break;
		}
		if(!is_dir(SYS_ROOT . $strDirName)) {
			$strDirName = DIR_UPLOAD . 'common';
		}
		$_POST['dirpath'] = $strDirName;
		
		if(!isset($_POST['autothumb_h']) || empty($_POST['autothumb_h'])) 	$_POST['autothumb_h'] = null;
		if(!isset($_POST['autothumb_w']) || empty($_POST['autothumb_w'])) 	$_POST['autothumb_w'] = null;
		if(!is_null($_POST['autothumb_h']) || !is_null($_POST['autothumb_w'])) $_POST['autothumb'] = 1; else $_POST['autothumb'] = 0; 
		*/
		
		$this->_update($_POST);
		
		$this->secureGlobals();
	}
}