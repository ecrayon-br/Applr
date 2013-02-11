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
		$this->arrGallList	= (array) $this->objModel->select(array('id','name','dirpath'),'media_gallery',array(),array(),array(),array(),0,null,'All');
		
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
		
		// Instantiats UPLOAD class
		switch($_POST['mediatype']) {
			// IMAGE
			case 2:
			default:
				// Setups MEDIA GALLERY id and dir_path
				$arrGall	= (!empty($_POST['media_gallery_id']) ? explode('|',$_POST['media_gallery_id']) : array(1,DIR_IMAGE . 'common'));
			break;
		
				// VIDEO
			case 1:
				// Setups MEDIA GALLERY id and dir_path
				$arrGall	= (!empty($_POST['media_gallery_id']) ? explode('|',$_POST['media_gallery_id']) : array(1,DIR_VIDEO . 'common'));
			break;
		
				// UPLOAD
			case 0:
				// Setups MEDIA GALLERY id and dir_path
				$arrGall	= (!empty($_POST['media_gallery_id']) ? explode('|',$_POST['media_gallery_id']) : array(1,DIR_UPLOAD . 'common'));
			break;
		}
		
		// Instantiates uploadFile class
		$objUpload = new uploadFile_Controller('applr_tmp',SYS_ROOT . $arrGall[1]);
		
		// Sets MAIN FILE URL or uploads user file
		if(!empty($_POST['file_url'])) {
			$_POST['filepath'] = $_POST['file_url'];
		} else {
			$objUpload->setFile('file_upload');
			$_POST['filepath'] = $objUpload->uploadFile();
		}
		
		// Sets STREAMING FILE URL or uploads user file
		if(!empty($_POST['streaming_url'])) {
			$_POST['filepath_streaming'] = $_POST['streaming_url'];
		} else {
			$objUpload->setFile('streaming_upload');
			$_POST['filepath_streaming'] = $objUpload->uploadFile();
		}
		
		// Sets THUMBNAIL FILE URL or uploads user file
		if(!empty($_POST['thumb_url'])) {
			$_POST['filepath_thumbnail'] = $_POST['thumb_url'];
		} else {
			$objUpload->setFile('thumb_upload');
			$_POST['filepath_thumbnail'] = $objUpload->uploadFile();
		}
		
		// Sets sys vars
		if(!isset($_POST['media_gallery_id']) || empty($_POST['media_gallery_id'])) {
			$_POST['media_gallery_id'] = 1;
		} elseif(!is_numeric($_POST['media_gallery_id']) || $_POST['media_gallery_id'] <= 0) $_POST['media_gallery_id'] = 1;
		if(!is_numeric($_POST['mediatype']) || $_POST['mediatype'] < 0 || $_POST['mediatype'] > 2) $_POST['mediatype'] = 2;
		
		/*
		
		if(!isset($_POST['autothumb_h']) || empty($_POST['autothumb_h'])) 	$_POST['autothumb_h'] = null;
		if(!isset($_POST['autothumb_w']) || empty($_POST['autothumb_w'])) 	$_POST['autothumb_w'] = null;
		if(!is_null($_POST['autothumb_h']) || !is_null($_POST['autothumb_w'])) $_POST['autothumb'] = 1; else $_POST['autothumb'] = 0; 
		*/
		
		$this->_update($_POST);
		
		$this->secureGlobals();
	}
}