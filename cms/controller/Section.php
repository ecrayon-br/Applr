<?php
class Section_controller extends CRUD_controller {	
	/**
	 * 
	 * ATENTION!
	 * 
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 * 
	 */
	protected	$strTable		= 'sec_config';
	protected	$arrTable		= array('sec_config');
	
	protected	$arrFieldType	= array(
										'id'				=> 'numeric_empty',
										'parent'			=> 'numeric_empty',
										'sys_folder_id'		=> 'numeric_empty',
										'name'				=> 'string_notempty',
										'permalink'			=> 'string_notempty',
										'table_name'		=> 'string_notempty',
										'website'			=> 'boolean',
										'user_edit'			=> 'boolean',
										'public'			=> 'boolean',
										'home'				=> 'boolean',
										'static'			=> 'boolean',
										'static_filename'	=> 'string',
										'xml'				=> 'boolean',
										'xml_items'			=> 'numeric_empty',
										'rss'				=> 'boolean',
										'rss_items'			=> 'numeric_empty',
										'list_items'		=> 'numeric_empty',
										'orderby'			=> 'string',
										'search'			=> 'boolean',
										'autothumb'			=> 'boolean',
										'autothumb_h'		=> 'numeric_empty',
										'autothumb_w'		=> 'numeric_empty'
									);
		
	protected	$arrFieldList	= array('sec_config.*','sec_parent.name AS sec_parent_name','sys_folder.name AS sys_folder_name');
	protected	$arrJoinList	= array(
										'LEFT JOIN sec_config AS sec_parent ON sec_config.parent = sec_parent.id',
										'LEFT JOIN sys_folder ON sec_config.sys_folder_id = sys_folder.id'
										);
	
	protected	$arrFieldData	= array('sec_config.*','sec_parent.name AS sec_parent_name','sys_folder.name AS sys_folder_name');
	protected	$arrJoinData	= array(
										'LEFT JOIN sec_config AS sec_parent ON sec_config.parent = sec_parent.id',
										'LEFT JOIN sys_folder ON sec_config.sys_folder_id = sys_folder.id'
										);
	protected	$arrWhereData	= array('sec_config.id = {id}');
	
	private		$intSecID		= 0;
	private		$arrFldList		= array();
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
		
		// Gets FOLDER list
		$this->arrFldList	= $this->objModel->select(array('id','name'),'sys_folder',array(),array(),array(),array(),0,null,'All');
		
		// Gets SECTION list
		$arrTempSec			= $this->objModel->select(array('id','name','sys_folder_id'),'sec_config',array(),array('sec_config.id <> ' . $this->intSecID),array(),array(),0,null,'All');
		foreach($arrTempSec AS $intKey => $objTemp) {
			$this->arrSecList[$objTemp->sys_folder_id][] = $objTemp;
		}
		$this->objSmarty->assign('arrFld',$this->arrFldList);
		$this->objSmarty->assign('arrSec',$this->arrSecList);
		
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
		
		// Sets sys vars
		if(!isset($_POST['autothumb_h']) || empty($_POST['autothumb_h'])) 	$_POST['autothumb_h'] = null;
		if(!isset($_POST['autothumb_w']) || empty($_POST['autothumb_w'])) 	$_POST['autothumb_w'] = null;
		if(!is_null($_POST['autothumb_h']) || !is_null($_POST['autothumb_w'])) $_POST['autothumb'] = 1; else $_POST['autothumb'] = 0; 
		if(!isset($_POST['id']) || empty($_POST['id']) || $_POST['id'] <= 0) {
			$_POST['permalink'] = Controller::permalinkSyntax($_POST['name']);
		} else {
			$_POST['permalink'] = $this->objModel->recordExists('permalink',$this->strTable,'id = ' . $_POST['id']);
		}
		
		// Sets Hierarchy variables
		$arrHierarchy = explode('|',$_POST['hierarchy']);
		if(!isset($arrHierarchy[1]) || empty($arrHierarchy[1]) || !is_numeric($arrHierarchy[1]) || $arrHierarchy[1] <= 0) {
			$_POST['parent'] = null;
			$_POST['sys_folder_id'] = $arrHierarchy[0];
		}  else{
			$_POST['parent'] = $arrHierarchy[1];
			$_POST['sys_folder_id'] = null;
		}
		
		// Saves data
		if($this->_update($_POST,false)) {
			// Creates media directories and galleries
			@mkdir(ROOT_IMAGE	. $_POST['permalink'],0755);
			@mkdir(ROOT_VIDEO	. $_POST['permalink'],0755);
			@mkdir(ROOT_UPLOAD	. $_POST['permalink'],0755);
			@mkdir(ROOT_STATIC	. $_POST['permalink'],0755);
			@mkdir(ROOT_RSS		. $_POST['permalink'],0755);
			@mkdir(ROOT_XML		. $_POST['permalink'],0755);
			
			$arrMediaGalleryInfo	= array(
											'usr_data_id'	=> $this->intUserID,
											'sec_config_id'	=> $this->objData->id,
											'name'			=> $_POST['name'],
											'is_default'	=> 1,
											'autothumb'		=> $_POST['autothumb'],
											'autothumb_h'	=> $_POST['autothumb_h'],
											'autothumb_w'	=> $_POST['autothumb_w'],
											'status'		=> 1
										);
			$this->objModel->insert('media_gallery',array_merge($arrMediaGalleryInfo,array('mediatype' => 2, 'dirpath' => DIR_IMAGE . $_POST['permalink'])));
			$this->objModel->insert('media_gallery',array_merge($arrMediaGalleryInfo,array('mediatype' => 1, 'dirpath' => DIR_VIDEO . $_POST['permalink'])));
			$this->objModel->insert('media_gallery',array_merge($arrMediaGalleryInfo,array('mediatype' => 0, 'dirpath' => DIR_UPLOAD . $_POST['permalink'])));
			
			$this->objData->hierarchy = (!is_null($this->objData->sys_folder_id) ? $this->objData->sys_folder_id : 0) . '|' . (!is_null($this->objData->parent) ? $this->objData->parent : 0);
		}
		
		// Shows interface
		$this->_create($this->objData->id);
		
		$this->secureGlobals();
	}
}