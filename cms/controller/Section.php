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
	private		$arrLangList	= array();
	private		$arrTPLTypeList	= array();
	private		$arrTPLList		= array();
	
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
		
		// Gets TEMPLATES list
		$this->arrTPLList	= $this->objModel->select(array('id','name'),'sys_template',array(),array(),array(),array(),0,null,'All');
		
		// Gets TEMPLATES TYPE list
		$this->arrTPLTypeList	= $this->objModel->select(array('id','name'),'sys_template_type',array(),array(),array(),array(),0,null,'All');
		
		// Gets LANGUAGES list
		$this->arrLangList	= $this->objModel->select(array('id','name'),'sys_language',array(),array(),array(),array(),0,null,'All');
		
		// Gets FOLDER list
		$this->arrFldList	= $this->objModel->select(array('id','name'),'sys_folder',array(),array(),array(),array(),0,null,'All');
		
		// Gets SECTION list
		$arrTempSec			= $this->objModel->select(array('id','name','sys_folder_id'),'sec_config',array(),array('sec_config.id <> ' . $this->intSecID),array(),array(),0,null,'All');
		foreach($arrTempSec AS $intKey => $objTemp) {
			$this->arrSecList[$objTemp->sys_folder_id][] = $objTemp;
		}
		$this->objSmarty->assign('arrFld',$this->arrFldList);
		$this->objSmarty->assign('arrSec',$this->arrSecList);
		$this->objSmarty->assign('arrLang',$this->arrLangList);
		$this->objSmarty->assign('arrTPL',$this->arrTPLList);
		$this->objSmarty->assign('arrTPLType',$this->arrTPLTypeList);
		
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
		
		// Sets TEMPLATE var
		$arrInsertTPL	= array();
		foreach($_POST['template'] AS $intType => $intTPL) {
			if(!empty($intTPL)) {
				$arrInsertTPL[] = 	array(
						'sys_template_type_id'	=> $intType,
						'sys_template_id'		=> $intTPL
				);
			}
		}
		
		// Sets LANGUAGE var
		$arrInsertLang	= array();
		foreach($_POST['name'] AS $intLang => $strName) {
			if(!empty($strName)) {
				$arrInsertLang[] = 	array(
									'sys_language_id'	=> $intLang,
									'name'				=> $strName
								);
			}
		}
		$_POST['name']	= reset($arrInsertLang)['name']; 
		
		// Sets sys vars
		if(!isset($_POST['autothumb_h']) || empty($_POST['autothumb_h'])) 	$_POST['autothumb_h'] = null;
		if(!isset($_POST['autothumb_w']) || empty($_POST['autothumb_w'])) 	$_POST['autothumb_w'] = null;
		if(!is_null($_POST['autothumb_h']) || !is_null($_POST['autothumb_w'])) $_POST['autothumb'] = 1; else $_POST['autothumb'] = 0; 
		if(!isset($_POST['id']) || empty($_POST['id']) || $_POST['id'] <= 0) {
			$_POST['permalink'] = Controller::permalinkSyntax($_POST['name']);
		} else {
			$_POST['permalink'] = $this->objModel->recordExists('permalink',$this->strTable,'id = ' . $_POST['id'],true);
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
			
			// Saves LANGUAGE REL data
			$this->objModel->delete('rel_sec_language','sec_config_id = ' . $this->objData->id);
			foreach($arrInsertLang AS &$arrTmp) {
				$arrTmp['sec_config_id'] = $this->objData->id;
			}
			$this->objModel->insert('rel_sec_language', $arrInsertLang);
				
			// Saves TEMPLATE REL data
			$this->objModel->delete('rel_sec_template','sec_config_id = ' . $this->objData->id);
			foreach($arrInsertTPL AS &$arrTmp) {
				$arrTmp['sec_config_id'] = $this->objData->id;
			}
			$this->objModel->insert('rel_sec_template', $arrInsertTPL);
					
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
	protected function _create($intID = 0) {
		if($intID > 0) {
			$this->objData = $this->objModelCRUD->getData($intID);
			
			$arrLang	= array();
			$objLang 	= $this->objModel->select(array('sys_language.id','rel_sec_language.name'),array('sys_language','rel_sec_language'),array(),array('rel_sec_language.sys_language_id = sys_language.id','rel_sec_language.sec_config_id = ' . $intID),array(),array(),0,null,'All');
			foreach($objLang AS $objTmp) {
				$arrLang[$objTmp->id] = $objTmp;
			}
			unset($objLang);
			$this->objData->language = $arrLang;
			
			$arrTPL	= array();
			$objTPL 	= $this->objModel->select(array('rel_sec_template.sys_template_type_id','rel_sec_template.sys_template_id'),array('rel_sec_template'),array(),array('rel_sec_template.sec_config_id = ' . $intID),array(),array(),0,null,'All');
			foreach($objTPL AS $objTmp) {
				$arrTPL[$objTmp->sys_template_type_id] = $objTmp;
			}
			unset($objTPL);
			$this->objData->template = $arrTPL;
			
			$this->objSmarty->assign('objData',$this->objData);
		}
		
		$this->renderTemplate(true,$this->strModule . '_form.html');
	}
}