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
										'is_default'	=> 'boolean',
										'autothumb'		=> 'boolean',
										'autothumb_h'	=> 'numeric_empty',
										'autothumb_w'	=> 'numeric_empty',
										'status'		=> 'boolean'
									);
		
	protected	$arrFieldList	= array('media_gallery.*','COUNT(media_data.id) AS files_count');
	protected	$arrJoinList	= array('LEFT JOIN media_data ON media_data.media_gallery_id = media_gallery.id');
	protected	$arrWhereList	= array('media_gallery.is_default = 0');
	protected	$arrOrderList	= array('media_gallery.name ASC');
	protected	$arrGroupList	= array('media_gallery.id');
	
	protected	$arrFieldData	= array('media_gallery.*');
	protected	$arrJoinData	= array();
	protected	$arrWhereData	= array('media_gallery.id = {id}');
	protected	$arrOrderData	= array('media_gallery.name ASC');
	protected	$arrGroupData	= array('media_gallery.id');
	
	private		$arrDirList		= array();
	private		$arrSecList		= array();
	
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
	
		// Read ROOT_IMAGE files and create Smarty variable to SELECT box
		$this->arrDirList = array();
		if($objHandle = opendir(ROOT_IMAGE)) {
			while (false !== ($strFile = readdir($objHandle))) {
				if(is_dir(ROOT_IMAGE.$strFile) && $strFile != '.' && $strFile != '..') $this->arrDirList[] = DIR_IMAGE . $strFile;
			}
			closedir($objHandle);
		}
		if($objHandle = opendir(ROOT_VIDEO)) {
			while (false !== ($strFile = readdir($objHandle))) {
				if(is_dir(ROOT_VIDEO.$strFile) && $strFile != '.' && $strFile != '..') $this->arrDirList[] = DIR_VIDEO . $strFile;
			}
			closedir($objHandle);
		}
		if($objHandle = opendir(ROOT_UPLOAD)) {
			while (false !== ($strFile = readdir($objHandle))) {
				if(is_dir(ROOT_UPLOAD.$strFile) && $strFile != '.' && $strFile != '..') $this->arrDirList[] = DIR_UPLOAD . $strFile;
			}
			closedir($objHandle);
		}
		$arrMediaDir		= (array) $this->objModel->select('dirpath',$this->strTable,array(),array(),array(),array(),0,null,'Col');
		$this->arrDirList	= array_diff($this->arrDirList,$arrMediaDir);
		
		// Gets SECTION list
		$this->arrSecList	= (array) $this->objModel->select(array('id','name'),'sec_config',array(),array(),array(),array(),0,null,'Col');
		
		$this->objSmarty->assign('arrDir',$this->arrDirList);
		$this->objSmarty->assign('arrSec',$this->arrSecList);
		
		// Shows default interface
		if($boolRenderTemplate) $this->_read();
	}
	
	/**
	 * Shows INSERT / UPDATE form interface
	 *
	 * @param	integer	$intID			Content ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	/*
	protected function _create($intID = 0) {
		if($intID > 0) {
			$this->objData = $this->objModelCRUD->getData($intID);
			if(is_numeric($objData->sec_config_id) && $objData->sec_config_id > 0) $this->objData->sec_config_name = $this->objModelCRUD->recordExists('name','sec_config','id = ' . $objData->sec_config_id,true);
			$this->objSmarty->assign('objData',$this->objData);
		}
	
		$this->renderTemplate(true,$this->strModule . '_form.html');
	}
	*/
	
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
		
		$this->_update($_POST);
		
		$this->secureGlobals();
	}
}