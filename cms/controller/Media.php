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
				$strFilter	= 'IMAGE';
			break;
		
				// VIDEO
			case 1:
				// Setups MEDIA GALLERY id and dir_path
				$arrGall	= (!empty($_POST['media_gallery_id']) ? explode('|',$_POST['media_gallery_id']) : array(1,DIR_VIDEO . 'common'));
				$strFilter	= 'VIDEO';
			break;
		
				// UPLOAD
			case 0:
				// Setups MEDIA GALLERY id and dir_path
				$arrGall	= (!empty($_POST['media_gallery_id']) ? explode('|',$_POST['media_gallery_id']) : array(1,DIR_UPLOAD . 'common'));
				$strFilter	= '';
			break;
		}
		$_POST['media_gallery_id'] = $arrGall[0];
		
		// Instantiates uploadFile class
		$objUpload = new uploadFile_Controller('applr_tmp',SYS_ROOT . $arrGall[1]);
		
		// Sets MAIN FILE URL or uploads user file
		if(!empty($_POST['file_url'])) {
			$_POST['filepath'] = $_POST['file_url'];
		} else {
			$objUpload->setFile('file_upload');
			$objUpload->setFilter($strFilter);
			$_POST['filepath'] = $objUpload->uploadFile();
			if($_POST['filepath'] === false) $_POST['filepath'] = null;
		}
		
		if(!empty($_POST['filepath'])) {
			// Sets STREAMING FILE URL or uploads user file
			if(!empty($_POST['streaming_url'])) {
				$_POST['filepath_streaming'] = $_POST['streaming_url'];
			} elseif($objUpload->setFile('streaming_upload')) {
				$_POST['filepath_streaming'] = $objUpload->uploadFile();
				if($_POST['filepath_streaming'] === false) $_POST['filepath_streaming'] = null;
			}
			
			// Sets THUMBNAIL FILE URL or uploads user file
			$boolUDThumb = false;
			if(!empty($_POST['thumb_url'])) {
				$boolUDThumb = true;
				$_POST['filepath_thumbnail'] = $_POST['thumb_url'];
			} elseif($objUpload->setFile('thumb_upload')) {
				$boolUDThumb = true;
				$objUpload->setFilter('IMAGE');
				$_POST['filepath_thumbnail'] = $objUpload->uploadFile();
				if($_POST['filepath_thumbnail'] === false) $_POST['filepath_thumbnail'] = null;
			}
			
			// Sets sys vars
			if(!isset($_POST['media_gallery_id']) || empty($_POST['media_gallery_id'])) {
				$_POST['media_gallery_id'] = 1;
			} elseif(!is_numeric($_POST['media_gallery_id']) || $_POST['media_gallery_id'] <= 0) $_POST['media_gallery_id'] = 1;
			if(!is_numeric($_POST['mediatype']) || $_POST['mediatype'] < 0 || $_POST['mediatype'] > 2) $_POST['mediatype'] = 2;
			
			// Creates autothumb
			$arrAutoThumb = (array) $this->objModel->select(array('autothumb_h','autothumb_w'),'media_gallery',array(),array('media_gallery.autothumb = 1','media_gallery.id = ' . $_POST['media_gallery_id']),array(),array(),0,null,'Row');
			if(isset($arrAutoThumb['autothumb_h']) && isset($arrAutoThumb['autothumb_w']) && !empty($_POST['filepath'])) {
				// Defines original image content and basename
				// If THUMBNAIL is user defined file
				if($boolUDThumb) {
					$strFileName	= basename($_POST['filepath_thumbnail']);
					$strOrgPath		= (empty($_POST['thumb_url']) ? SYS_ROOT : '') . $_POST['filepath_thumbnail'];
					$strDestPath	= SYS_ROOT . $arrGall[1] . '/'. $strFileName;
				} else {
					$strFileName	= basename($_POST['filepath']);
					$strOrgPath		= (empty($_POST['file_url']) ? SYS_ROOT : '') . $_POST['filepath'];
					$strDestPath	= SYS_ROOT . $arrGall[1] . '/'. $strFileName;
				}
				
				$boolCreate		= true;
				if(!file_exists($strDestPath)) {
					// Creates original file
					$strFileData	= file_get_contents($strOrgPath);
					if(!file_put_contents($strDestPath,$strFileData,LOCK_EX)) {
						$strDestPath 	= $strOrgPath;
						$boolCreate		= false;
					}
				}
				
				// Creates new file, resized
				if($boolCreate) {
					$strThumbPath = $objUpload->createThumbnail($strDestPath,$arrAutoThumb['autothumb_w'],$arrAutoThumb['autothumb_h']);
					@unlink($strDestPath);
					$strDestPath = $strThumbPath;
				}
				
				// Sets variable to INSERT / UPDATE
				$_POST['filepath_thumbnail'] = (!empty($strDestPath) ? str_replace(SYS_ROOT,'',$strDestPath) : $_POST['filepath']);
			}
		}
		
		$this->_update($_POST);
		
		$this->secureGlobals();
	}
}