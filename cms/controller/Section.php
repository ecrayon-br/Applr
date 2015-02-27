<?php
class Section_controller extends CRUD_Controller {	
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
										'sys_sec_type_id'	=> 'numeric',
										'name'				=> 'string',
										'permalink'			=> 'string',
										'table_name'		=> 'string',
										'website'			=> 'boolean',
										'user_edit'			=> 'boolean',
										'public'			=> 'boolean',
										'home'				=> 'boolean',
										'static'			=> 'boolean',
										'static_filename'	=> 'string_empty',
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
		
	protected	$arrFieldList	= array('sec_config.*','sec_parent.name AS sec_parent_name','sys_folder.name AS sys_folder_name','sys_sec_type.name AS sys_sec_type_name','sys_sec_type.prefix AS sys_sec_type_prefix');
	protected	$arrJoinList	= array(
										'LEFT JOIN sec_config AS sec_parent ON sec_config.parent = sec_parent.id',
										'LEFT JOIN sys_folder ON sec_config.sys_folder_id = sys_folder.id',
										'LEFT JOIN sys_sec_type ON sec_config.sys_sec_type_id = sys_sec_type.id'
										);
	protected	$arrOrderList	= array('sys_folder_id ASC, parent ASC, name ASC');
	
	protected	$arrFieldData	= array('sec_config.*','sec_parent.name AS sec_parent_name','sys_folder.name AS sys_folder_name','sys_sec_type.name AS sys_sec_type_name','sys_sec_type.prefix AS sys_sec_type_prefix');
	protected	$arrJoinData	= array(
										'LEFT JOIN sec_config AS sec_parent ON sec_config.parent = sec_parent.id',
										'LEFT JOIN sys_folder ON sec_config.sys_folder_id = sys_folder.id',
										'LEFT JOIN sys_sec_type ON sec_config.sys_sec_type_id = sys_sec_type.id'
										);
	protected	$arrWhereData	= array('sec_config.id = {id}');
	
	protected	$intSecID		= 0;
	protected	$arrTypeList	= array();
	protected	$arrFldList		= array();
	#protected	$arrSecList		= array();
	protected	$arrLangList	= array();
	protected	$arrTPLTypeList	= array();
	protected	$arrTPLList		= array();
	
	protected	$objManage;
	protected	$objSectionData;
	protected	$arrInsertLang;
	protected	$arrInsertTPL;
	protected	$objHierarchy;
	
	/**
	 * Class constructor
	 *
	 * @param	boolean	$boolRenderTemplate	Defines whether to show default's interface
	 * @param	integer	$intSecID			Section's ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($boolRenderTemplate = true,$intSecID = 0) {
		parent::__construct(false);
		
		// Sets editing Section ID
		if(!empty($intSecID)) {
			$this->intSecID		= $intSecID;
		} elseif(empty($this->intSecID)) {
			$this->intSecID		= intval($_SESSION[self::$strProjectName]['URI_SEGMENT'][4]);
			if(!$this->intSecID) $this->intSecID = intval($_POST['sec_config_id']);
		}
		
		// Gets TEMPLATES list
		$this->arrTPLList	= $this->objModel->select(array('id','name'),'sys_template',array(),array(),array(),array(),0,null,'All');
		
		// Gets TEMPLATES TYPE list
		$this->arrTPLTypeList	= $this->objModel->select(array('id','name'),'sys_template_type',array(),array(),array(),array(),0,null,'All');
		
		// Gets LANGUAGES list
		$this->arrLangList	= $this->objModel->select(array('id','name'),'sys_language',array(),array(),array(),array(),0,null,'All');
		
		// Gets SECTION TYPE list
		$this->arrTypeList	= $this->objModel->select(array('id','name','prefix'),'sys_sec_type',array(),array(),array(),array(),0,null,'All');
		
		// Gets FOLDER list
		$this->arrFldList	= $this->objModel->select(array('id','name'),'sys_folder',array(),array(),array(),array(),0,null,'All');
		
		// Gets SECTION list
		$this->getHierarchy();
		#$this->arrSecList	= $this->getHierarchy();
		
		// Sets SMARTY vars
		$this->objSmarty->assign('intSecID',$this->intSecID);
		$this->objSmarty->assign('arrType',$this->arrTypeList);
		$this->objSmarty->assign('arrFld',$this->arrFldList);
		#$this->objSmarty->assign('arrSec',$this->arrSecList);
		$this->objSmarty->assign('arrLang',$this->arrLangList);
		$this->objSmarty->assign('arrTPL',$this->arrTPLList);
		$this->objSmarty->assign('arrTPLType',$this->arrTPLTypeList);
		
		// Sets Applr::manageDB object
		$this->objManage	= new manageDB_Controller();
		
		// Shows default interface
		if($boolRenderTemplate) $this->renderTemplate();
	}
	
	/**
	 * Gets SECTION HIERARCHY and setups $this->objHierarchy
	 *
	 * @return	void
	 *
	 * @since 	2015-02-25
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function getHierarchy() {
		$this->objHierarchy = array();
		
		// Gets DB sections
		$arrContent	= $this->content();

		foreach($arrContent AS $intKey => $objSection) {
			$this->searchHierarchy($this->objHierarchy,$objSection);
		}
		#echo '<pre>'; print_r($this->objHierarchy);
		$this->objSmarty->assign('objHierarchy',$this->objHierarchy);
	}
	
	/**
	 * Searcher for hierarchy position to $objNeedle->parent on $arrHaystack
	 *
	 * @return	void
	 *
	 * @since 	2015-02-25
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function searchHierarchy(&$arrHaystack,$objNeedle,$boolRecursive = false) {
		if(!$boolRecursive && empty($objNeedle->parent)) $objNeedle->parent = 0;
		if(!$boolRecursive && $objNeedle->parent == 0) {
			$arrHaystack[$objNeedle->id] = $objNeedle;
			$arrHaystack[$objNeedle->id]->child = array();
		}
		
		foreach($arrHaystack AS $intKey => &$objTemp) {
			if($objNeedle->parent == $intKey) {
				$objTemp->child[$objNeedle->id] = $objNeedle;
				$objTemp->child[$objNeedle->id]->child = array();
			} else {
				$this->searchHierarchy($objTemp->child,$objNeedle,true);
			}
		}
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
		
		// Sets data object
		$this->objSectionData = (object) $_POST;
		
		// Sets ORDER BY var
		if(empty($this->objSectionData->orderby)) {
			$this->objSectionData->orderby = $this->objSectionData->applr_orderby;
		}
		
		// Sets MAIN NAME
		$this->objSectionData->name = reset(array_diff_key($this->objSectionData->name,array_keys($this->objSectionData->name,'')));
		
		// Sets sys vars
		if(!isset($this->objSectionData->autothumb_h) || empty($this->objSectionData->autothumb_h)) 	$this->objSectionData->autothumb_h = null;
		if(!isset($this->objSectionData->autothumb_w) || empty($this->objSectionData->autothumb_w)) 	$this->objSectionData->autothumb_w = null;
		if(!is_null($this->objSectionData->autothumb_h) || !is_null($this->objSectionData->autothumb_w)) $this->objSectionData->autothumb = 1; else $this->objSectionData->autothumb = 0; 
		if(!isset($this->objSectionData->id) || empty($this->objSectionData->id) || $this->objSectionData->id <= 0) {
			$this->objSectionData->permalink = Controller::permalinkSyntax($this->objSectionData->name);
		} else {
			$this->objSectionData->permalink = $this->objModel->recordExists('permalink',$this->strTable,'id = ' . $this->objSectionData->id,true);
		}
		
		// Sets Hierarchy variables
		$arrHierarchy = explode('|',$this->objSectionData->hierarchy);
		if(!isset($arrHierarchy[1]) || empty($arrHierarchy[1]) || !is_numeric($arrHierarchy[1]) || $arrHierarchy[1] <= 0) {
			$this->objSectionData->parent = null;
			$this->objSectionData->sys_folder_id = $arrHierarchy[0];
		}  else{
			$this->objSectionData->parent = $arrHierarchy[1];
			$this->objSectionData->sys_folder_id = $arrHierarchy[0];
		}
		$this->objData->hierarchy 	= (!is_null($this->objSectionData->sys_folder_id) ? $this->objSectionData->sys_folder_id : 0) . '|' . (!is_null($this->objSectionData->parent) ? $this->objSectionData->parent : 0);
		
		// Sets Content Type variables
		$arrType = explode('|',$this->objSectionData->sys_sec_type_id);
		if(!isset($arrType[0]) || empty($arrType[0]) || !is_numeric($arrType[0]) || $arrType[0] <= 1) {
			$strTblPrefix							= 'data';
			$this->objSectionData->sys_sec_type_id 	= 1;
		}  else {
			$strTblPrefix							= $arrType[1];
			$this->objSectionData->sys_sec_type_id 	= $arrType[0];
		}
		$this->objData->sys_sec_type_id 		= $this->objSectionData->sys_sec_type_id;
		
		// Sets table name preffix
		$this->objSectionData->table_name		= $strTblPrefix . '_' . str_replace(array('-',$strTblPrefix),array('_',''), Controller::permalinkSyntax($strTblPrefix . $this->objSectionData->table_name) );
		
		// CASE INSERT
		if(empty($this->objSectionData->id)) {
			$this->_createSection();
		
			// Shows interface
			$this->_create();
		// CASE UPDATE
		} else {
			$this->_updateSection();
		
			// Shows interface
			$this->_create($this->objData->id);
		}
		
		$this->secureGlobals();
	}
	
	/**
	 * Creates SECTION TABLE and setups `name` MAIN ATTRIBUTE, MEDIA GALLERY, MULTI-LANGUAGE and TEMPLATE configs
	 *
	 * @return	void
	 *
	 * @since 	2015-02-21
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function _createSection() {
		// Creates section table
		if($this->createSectionTable($this->objSectionData->table_name)) {
			// Saves data
			if($this->_update($this->objSectionData,false)) {
				// Insert rel_sec_struct for `name` field
				if( ($intStructID = $this->objModel->insert('rel_sec_struct',array('sec_config_id' => $this->objData->id, 'sec_struct_id' => 1, 'field_name' => 'name', 'name' => 'Name', 'tooltip' => '', 'mandatory' => 1, 'admin' => 0),true)) !== false) {
					
					$this->objModel->insert('sec_config_order',array('field_id' => $intStructID, 'sec_config_id' => $this->objData->id, 'field_order' => 1, 'type' => 1));
	
					$this->_setMediaGallery();
	
					$this->_setLanguage_Template_Configs();
						
				} else {
					$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save struct data! Please try again!');
				}
			} else {
				$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save section data! Please try again!');
			}
		} else {
			$this->objSmarty->assign('ERROR_MSG','There was an error while trying to create section\'s table! Please try again!');
		}
	}
	
	/**
	 * Updates SECTION TABLE, MULTI-LANGUAGE and TEMPLATE configs
	 *
	 * @return	void
	 *
	 * @since 	2015-02-21
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function _updateSection() {
		// Saves data
		if($this->_update($this->objSectionData,false)) {
			$this->_setLanguage_Template_Configs();
		} else {
			$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save section data! Please try again!');
		}
	}
	
	/**
	 * Creates MEDIA GALLERY directories and DBs setup
	 *
	 * @return	void
	 *
	 * @since 	2015-02-21
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function _setMediaGallery() {
		// Creates media directories and galleries
		@mkdir(ROOT_IMAGE	. $this->objSectionData->permalink,0755);
		@mkdir(ROOT_VIDEO	. $this->objSectionData->permalink,0755);
		@mkdir(ROOT_UPLOAD	. $this->objSectionData->permalink,0755);
		@mkdir(ROOT_STATIC	. $this->objSectionData->permalink,0755);
		@mkdir(ROOT_RSS		. $this->objSectionData->permalink,0755);
		@mkdir(ROOT_XML		. $this->objSectionData->permalink,0755);
	
		$arrMediaGalleryInfo	= array(
				'usr_data_id'	=> $this->intUserID,
				'sec_config_id'	=> $this->objData->id,
				'name'			=> $this->objSectionData->name,
				'is_default'	=> 1,
				'autothumb'		=> $this->objSectionData->autothumb,
				'autothumb_h'	=> $this->objSectionData->autothumb_h,
				'autothumb_w'	=> $this->objSectionData->autothumb_w,
				'status'		=> 1
		);
		$this->objModel->insert('media_gallery',array_merge($arrMediaGalleryInfo,array('mediatype' => 2, 'dirpath' => DIR_IMAGE . $this->objSectionData->permalink)));
		$this->objModel->insert('media_gallery',array_merge($arrMediaGalleryInfo,array('mediatype' => 1, 'dirpath' => DIR_VIDEO . $this->objSectionData->permalink)));
		$this->objModel->insert('media_gallery',array_merge($arrMediaGalleryInfo,array('mediatype' => 0, 'dirpath' => DIR_UPLOAD . $this->objSectionData->permalink)));
	}
	
	/**
	 * Sets MULTI-LANGUAGE and TEMPLATE SECTION's configs
	 *
	 * @return	void
	 *
	 * @since 	2015-02-21
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function _setLanguage_Template_Configs() {
		// Sets LANGUAGE var
		$this->arrInsertLang	= array();
		foreach($_POST['name'] AS $intLang => $strName) {
			if(!empty($strName)) {
				$this->arrInsertLang[] = 	array(
						'sec_config_id'		=> $this->objData->id,
						'sys_language_id'	=> $intLang,
						'name'				=> $strName
				);
			}
		}
		#$_POST['name']	= reset($this->arrInsertLang)['name']; 
		
		// Saves LANGUAGE REL data
		$this->objModel->delete('rel_sec_language','sec_config_id = ' . $this->objData->id);
		if($this->objModel->insert('rel_sec_language', $this->arrInsertLang)) {
			
			// Sets TEMPLATE var
			$this->arrInsertTPL	= array();
			foreach($this->objSectionData->template AS $intType => $intTPL) {
				if(!empty($intTPL)) {
					$this->arrInsertTPL[] = 	array(
							'sys_template_type_id'	=> $intType,
							'sys_template_id'		=> $intTPL,
							'sec_config_id'			=> $this->objData->id
					);
				}
			}
			// Saves TEMPLATE REL data
			$this->objModel->delete('rel_sec_template','sec_config_id = ' . $this->objData->id);
			if(!empty($this->arrInsertTPL)) {
				if(!$this->objModel->insert('rel_sec_template', $this->arrInsertTPL)) {
					$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save template data! Please try again!');
				}
			}
			
		} else {
			$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save language data! Please try again!');
		}
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
			// Get Section config data
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
												'seo_description'	=> array('type' => 'text', 'notnull' => 0),
												'seo_keywords'		=> array('type' => 'text', 'length' => 255, 'notnull' => 0),
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