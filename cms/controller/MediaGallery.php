<?php
class MediaGallery_controller extends CRUD_Controller {	
	/**
	 * 
	 * ATENTION!
	 * 
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 * 
	 */
		/**
		 * @todo set PROJECT_ID
		 */
	protected	$strTable		= 'media_gallery';
	protected	$arrTable		= array('media_gallery');
	
	protected	$arrFieldType	= array(
										'id'			=> 'numeric_empty',
										'usr_data_id'	=> 'numeric_empty',
										'sec_config_id'	=> 'numeric_empty',
										'public'		=> 'numeric',
										'name'			=> 'string',
										'mediatype'		=> 'numeric',
										'description'	=> 'string_empty',
										'dirpath'		=> 'string_empty',
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
		parent::__construct($boolRenderTemplate,true,CMS_ROOT_TEMPLATE);
	
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
		/**
		 * @todo set PROJECT_ID
		 */
		$this->arrSecList	= $this->objModel->select(array('id','name'),'sec_config',array(),array(),array(),array(),0,null,'All');
		
		$this->objSmarty->assign('arrDir',$this->arrDirList);
		$this->objSmarty->assign('arrSec',$this->arrSecList);
		
		// Shows default interface
		if($boolRenderTemplate) $this->_read();
	}
	
	/**
	 * @see CRUD_Controller::delete()
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
	
	/**
	 * Shows CONTENT interface
	 * 
	 * @param	integer	$intID	Content ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
		/**
		 * @todo set PROJECT_ID
		 */
	public function gallery($intID = 0,$strTPL = '') {
		if(!is_numeric($intID) || $intID <= 0) { 
			$intID = intval($_SESSION[self::$strProjectName]['URI_SEGMENT'][4]); 
		}
		if(!is_numeric($intID) || $intID <= 0) {
			$this->objSmarty->assign('ALERT_MSG','You must choose an item to show!');
			$this->_read(); exit();
		}
		if(!is_string($strTPL) || empty($strTPL)) $strTPL = $this->strModule . '_gallery.html';

		$this->objModel->arrFieldList	= array('media_gallery.*','media_data.id AS media_id','media_data.mediatype AS media_mediatype','media_data.name AS media_name','media_data.author AS media_author','media_data.label AS media_label','CONCAT("'.HTTP.'",media_data.filepath) AS media_filepath','CONCAT("'.HTTP.'",media_data.filepath_thumbnail) AS media_thumbnail','CONCAT("'.HTTP.'",media_data.filepath_streaming) AS media_streaming');
		$this->objModel->arrJoinList	= array('LEFT JOIN media_data ON media_data.media_gallery_id = media_gallery.id');
		$this->objModel->arrWhereList	= array('media_gallery.id = ' . $intID);
		$this->objModel->arrOrderList	= array('media_gallery.name ASC');
		$this->objModel->arrGroupList	= array('media_data.id');

		if($intID > 0) {
			$this->objData = $this->objModel->getList();
			$this->objSmarty->assign('objData',$this->objData);
		}
		
		#echo '<pre>'; var_dump($this->objData);
		$this->renderTemplate(true,$strTPL);
	}
}