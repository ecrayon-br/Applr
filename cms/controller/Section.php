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
	
	protected	$intSecID		= 0;
	protected	$arrFldList		= array();
	protected	$arrSecList		= array();
	protected	$arrLangList	= array();
	protected	$arrTPLTypeList	= array();
	protected	$arrTPLList		= array();
	
	protected	$objManage;
	
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
		
		// Sets editing Section ID
		$this->intSecID		= intval($_SESSION[self::$strProjectName]['URI_SEGMENT'][4]);
		if(!$this->intSecID) $this->intSecID = intval($_POST['sec_config_id']);
		
		// Gets TEMPLATES list
		$this->arrTPLList	= $this->objModel->select(array('id','name'),'sys_template',array(),array(),array(),array(),0,null,'All');
		
		// Gets TEMPLATES TYPE list
		$this->arrTPLTypeList	= $this->objModel->select(array('id','name'),'sys_template_type',array(),array(),array(),array(),0,null,'All');
		
		// Gets LANGUAGES list
		$this->arrLangList	= $this->objModel->select(array('id','name'),'sys_language',array(),array(),array(),array(),0,null,'All');
		
		// Gets FOLDER list
		$this->arrFldList	= $this->objModel->select(array('id','name'),'sys_folder',array(),array(),array(),array(),0,null,'All');
		
		// Gets SECTION list
		$this->arrSecList	= array();
		$arrTempSec			= $this->objModel->select(array('id','name','sys_folder_id'),'sec_config',array(),array('sec_config.id <> ' . $this->intSecID),array(),array(),0,null,'All');
		foreach($arrTempSec AS $intKey => $objTemp) {
			$this->arrSecList[$objTemp->sys_folder_id][] = $objTemp;
		}
		
		// Sets SMARTY vars
		$this->objSmarty->assign('arrFld',$this->arrFldList);
		$this->objSmarty->assign('arrSec',$this->arrSecList);
		$this->objSmarty->assign('arrLang',$this->arrLangList);
		$this->objSmarty->assign('arrTPL',$this->arrTPLList);
		$this->objSmarty->assign('arrTPLType',$this->arrTPLTypeList);
		
		// Sets Applr::manageDB object
		$this->objManage	= new manageDB_Controller();
		
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
		$this->objData->hierarchy 	= (!is_null($_POST['sys_folder_id']) ? $_POST['sys_folder_id'] : 0) . '|' . (!is_null($_POST['parent']) ? $_POST['parent'] : 0);
		
		// Sets table name preffix
		$_POST['table_name']		= 'sec_ctn_' . str_replace('-','_', Controller::permalinkSyntax($_POST['table_name']) );
		
		// Creates section table
		if($this->createSectionTable($_POST['table_name'])) {
			
			// Saves data
			if($this->_update($_POST,false)) {
				
				// Insert rel_sec_struct for `name` field
				if( ($intStructID = $this->objManage->objModel->insert('rel_sec_struct',array('sec_config_id' => $this->objData->id, 'sec_struct_id' => 1, 'field_name' => 'name', 'name' => 'Name', 'tooltip' => '', 'mandatory' => 1, 'admin' => 0),true)) !== false) {
					$this->objManage->objModel->insert('sec_config_order',array('field_id' => $intStructID, 'order' => 1, 'type' => 1));
					
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
					
					// Saves LANGUAGE REL data
					$this->objModel->delete('rel_sec_language','sec_config_id = ' . $this->objData->id);
					foreach($arrInsertLang AS &$arrTmp) {
						$arrTmp['sec_config_id'] = $this->objData->id;
					}
					if($this->objModel->insert('rel_sec_language', $arrInsertLang)) {
						
						// Saves TEMPLATE REL data
						$this->objModel->delete('rel_sec_template','sec_config_id = ' . $this->objData->id);
						if(!empty($arrInsertTPL)) {
							foreach($arrInsertTPL AS &$arrTmp) {
								$arrTmp['sec_config_id'] = $this->objData->id;
							}
							
							if(!$this->objModel->insert('rel_sec_template', $arrInsertTPL)) {
								$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save template data! Please try again!');
							}
						}
					} else {
						$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save language data! Please try again!');
					}
				} else {
					$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save struct data! Please try again!');
				}
			} else {
				$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save section data! Please try again!');
			}
		} else {
			$this->objSmarty->assign('ERROR_MSG','There was an error while trying to create section\'s table! Please try again!');
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
			$this->objData 	= $this->objModel->getData($this->intSecID);
			
			// Gets Section name for each language
			$arrLang	= array();
			$objLang 	= $this->objModel->select(array('sys_language.id','rel_sec_language.name'),array('sys_language','rel_sec_language'),array(),array('rel_sec_language.sys_language_id = sys_language.id','rel_sec_language.sec_config_id = ' . $intID),array(),array(),0,null,'All');
			foreach($objLang AS $objTmp) {
				$arrLang[$objTmp->id] = $objTmp;
			}
			unset($objLang);
			$this->objData->language = $arrLang;
			
			// Gets template related for each template type
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
	
	/**
	 * Creates Section table with default fields `id` (integer) and `name` (string[255])
	 *
	 * @param	string	$strValue		Table name
	 *
	 * @return	boolean
	 *
	 * @since 	2013-02-13
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function createSectionTable($strValue) {
		$arrDefaultFields = array('add' => array(
												'lang_content'		=> array('type' => 'integer', 'unsigned' => 0, 'notnull' => 1),
												'usr_data_id'		=> array('type' => 'integer', 'unsigned' => 0, 'notnull' => 1),
												'sys_language_id'	=> array('type' => 'integer', 'unsigned' => 0, 'notnull' => 1),
												'date_create' 		=> array('type' => 'timestamp', 'notnull' => 1),
												'date_publish' 		=> array('type' => 'timestamp', 'notnull' => 1),
												'date_expire' 		=> array('type' => 'timestamp', 'notnull' => 0),
												'create_html'		=> array('type' => 'boolean', 'notnull' => 1, 'default' => 0),
												'active'			=> array('type' => 'boolean', 'notnull' => 1, 'default' => 1),
												'deleted'			=> array('type' => 'boolean', 'notnull' => 1, 'default' => 0),
												'seo_description'	=> array('type' => 'text'),
												'seo_keywords'		=> array('type' => 'text', 'length' => 255, 'notnull' => 1),
												'permalink'			=> array('type' => 'text', 'length' => 255, 'notnull' => 1),
												'name' 				=> array('type' => 'text', 'length' => 255, 'notnull' => 1)
												)
								);
		
		if($this->objManage->createTable($strValue)) {
			if($this->objManage->alterTable($strValue,$arrDefaultFields)) {
				if($this->objManage->setForeignKey($strValue,'usr_data',array('usr_data_id' => array()))) {
					if($this->objManage->setForeignKey($strValue,'sys_language',array('sys_language_id' => array()))) {
						return true;
					} else return false;
				} else return false;
			} else return false;
		} else return false;
	}
}