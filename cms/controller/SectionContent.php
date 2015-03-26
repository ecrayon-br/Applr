<?php
class SectionContent_controller extends manageContent_Controller {
	private		$strSecTable;

	protected	$arrFieldType = array();
	
	public		$objFCK;
	
	/**
	 * Class constructor
	 *
	 * @param	boolean	$boolRenderTemplate	Defines whether to show default's interface
	 * @param	integer	$intSecID	Defines SECTION ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	
	public function __construct($boolRenderTemplate = true,$intSecID = 0) {
		parent::__construct($intSecID,true,CMS_ROOT_TEMPLATE);
		
		// Gets Section ID from Section Permalink
		/**
		 * @todo set PROJECT_ID
		 * @todo review if/else block below
		 */
		if(!empty($intSecID)) {
			$this->intSecID	= intval($intSecID);
			$strSection		= $this->objModel->recordExists('permalink','sec_config','id = "' . $this->intSecID . '"',true);
		} else {
			$strSection		= (!in_array($_SESSION[self::$strProjectName]['URI_SEGMENT'][3],array('update','insert','add','list','duplicate')) ? $_SESSION[self::$strProjectName]['URI_SEGMENT'][3] : $_SESSION[self::$strProjectName]['URI_SEGMENT'][4]);
			$this->intSecID	= intval($this->objModel->recordExists('id','sec_config','permalink = "' . $strSection . '"',true));
			
			if(empty($this->intSecID)) {
				$this->intSecID	= intval($this->objModel->recordExists('id','sec_config','id = "' . $strSection . '"',true));
			}
			if(empty($_SESSION[self::$strProjectName]['URI_SEGMENT'][3]) || empty($this->intSecID)) {
				$this->objSmarty->assign('ALERT_MSG','There was an error retrieving Section\'s data!');
			}
		}
		$this->objSmarty->assign('strPermalink',$strSection);
		
		// Sets Section vars
		$this->setSection($this->intSecID);
		$this->objSection	= $this->objModel->getSectionConfig($this->intSecID);
		$this->objSmarty->assign('objSection',$this->objSection);
		
		// Gets Section Fields list
		$this->getFieldList();
		$this->objSmarty->assign('objStruct',$this->objStruct);
		
		$this->setFieldType();
		
		// Sets Content List vars
		$this->objModel->strTable		= $this->objSection->table_name;
		$this->objModel->arrTable		= array($this->objSection->table_name);
		$this->objModel->arrFieldType	= $this->arrFieldType;
		
		$this->objModel->arrFieldList	= array('*');
		$this->objModel->arrJoinList	= array();
		$this->objModel->arrWhereList	= array('deleted = 0');
		$this->objModel->arrOrderList	= array('date_publish DESC');
		$this->objModel->arrGroupList	= array('id');

		$this->objModel->arrFieldData	= array($this->objModel->strTable . '.*');
		$this->objModel->arrJoinData	= array();
		$this->objModel->arrWhereData	= array($this->objModel->strTable . '.id = {id}');
		$this->objModel->arrOrderData	= array($this->objModel->strTable . '.date_publish DESC');
		$this->objModel->arrGroupData	= array($this->objModel->strTable . '.id');
		
		// Shows default interface
		if($boolRenderTemplate) $this->_read();
	}
	
	public function duplicate() {
		// Gets parent content
		$this->update(0,false);
		
		// Setups data object
		$this->objRawData->id 	= null;
		$this->objRawData->name = 'Copy ' . date('YmdHis - ') . $this->objRawData->name; 
		foreach($this->objPrintData AS $strKey => $mxdData) {
			if(is_array($mxdData)) {
				if($this->arrRelContent[$strKey]->type == 2) {
					$this->objRawData->$strKey = reset($this->objRawData->$strKey)->id;
				} else {
					foreach($this->objRawData->$strKey AS &$objData) {
						$objData = $objData->id;
					}
				}
			}
		}
		#echo '<pre>'; print_r($this->objRawData); die();
		$this->add($this->objRawData);
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
	public function _create($intID = 0, $boolRenderTemplate = true) {
		if($intID > 0) {
			$this->objRawData = $this->objModel->getData($intID);
			
			foreach($this->arrRelContent AS $objRel) {
				if($objRel->type == 2) {
					$this->objRawData->{$objRel->field_name} = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $this->objRawData->{$objRel->field_name}));
					$this->objPrintData->{$objRel->field_name} = $this->objRawData->{$objRel->field_name};
				}
			}
			
			if(empty($this->objPrintData->id)) $this->objPrintData = $this->setupFieldSufyx($this->objRawData,array_keys((array) $this->objRawData),2);
			#echo '<pre>'; print_r($this->objPrintData);			
			$this->objSmarty->assign('objData',$this->objPrintData);
			
		}
		
		if($boolRenderTemplate) $this->renderTemplate(true,$this->strModule . '_form.html');
	}
	
	/**
	 * Shows INSERT interface
	 * 
	 * @param	integer	$intID	Content ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function insert() {
		$this->_create();
	}
	
	/**
	 * Shows UPDATE interface
	 * 
	 * @param	integer	$intID	Content ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 * @todo Check $this->objRelField and $this->objRelContent CONCAT/GROUP_CONCAT
	 *
	 */
	public function update($intID = 0,$boolRenderTemplate = true) {
		if(!is_numeric($intID) || $intID <= 0) { 
			$intID = intval($_SESSION[self::$strProjectName]['URI_SEGMENT'][5]); 
		}
		
		if(!is_numeric($intID) || $intID <= 0) {
			$this->objSmarty->assign('ALERT_MSG','You must choose an item to update!');
			$this->_read(); exit();
		}
		
		$intRel = 0;
		foreach($this->arrRelContent AS $objRel) {
			$arrTables = explode('_rel_',str_replace(array('sec_rel_','_parent','_child'),'',$objRel->table_name));
			$strTable = ($objRel->parent ? $arrTables[1] : $arrTables[0]); 
			
			$arrChildField = explode(',',$objRel->child_fields);
			foreach($arrChildField AS &$strField) {
				if($strField != 'permalink') {
					$strField = '\"' . $strField . '\":\"",IF(' . $arrTables[1] . '.' . $strField. ' IS NULL,"",' . $arrTables[1] . '.' . $strField. '),"\"';
				} else {
					$strAttrPermalink = 'IF(' . $arrTables[1] . '.' . $strField. ' IS NULL,"", CONCAT("' . $objRel->child_section_permalink . '/",' . $arrTables[1] . '.' . $strField. ') )';
					$strField = '\"' . $strField . '\":\"",' . $strAttrPermalink . ',"\"';
				}
			}
			$arrChildField[] = '\"url_permalink\":\"", CONCAT("' . HTTP . '",' . $strAttrPermalink . '),"\"';
				
			$this->objModel->arrFieldData[$objRel->field_name]	= $this->objModel->arrFieldList[$objRel->field_name]	= 'CONCAT("[",GROUP_CONCAT(DISTINCT CONCAT("{\"id\":\"",' . $arrTables[1] . '.id,"\",\"value\":\"",' . $arrTables[1] . '.' . $objRel->field_rel. ',"\",' . implode(',',$arrChildField) . '}") SEPARATOR ","),"]") AS ' . $objRel->field_name;
			$this->objModel->arrJoinData['rel_tbl_' . $intRel]	= $this->objModel->arrJoinList['rel_tbl_' . $intRel]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $this->objModel->strTable . '.id';
			$this->objModel->arrJoinData[$arrTables[1]]	= $this->objModel->arrJoinList[$arrTables[1]]	= 'LEFT JOIN ' . $arrTables[1] . ' ON rel_tbl_' . $intRel . '.child_id = ' . $arrTables[1] . '.id';
				
			$intRel++;
		}
		
		$this->_create($intID,$boolRenderTemplate);
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
	public function add($objData = null) {
		$this->unsecureGlobals();
		
		// Sets Section's DB Entity name
		$strTable		= $this->strTable;
		$arrTable		= $this->arrTable;
		$this->strTable	= $this->objSection->table_name;
		$this->arrTable	= array($this->objSection->table_name);
		
		// Sets Section's default fields
		$this->arrFieldType['id']				= 'integer_empty';
		$this->arrFieldType['lang_content']		= 'integer';
		$this->arrFieldType['usr_data_id']		= 'integer_empty';
		$this->arrFieldType['sys_language_id']	= 'integer';
		$this->arrFieldType['date_create']		= 'date';
		$this->arrFieldType['create_html']		= 'boolean';
		$this->arrFieldType['deleted']			= 'boolean';
		$this->arrFieldType['permalink']		= 'string';
		$this->arrFieldType['date_publish']		= 'date';
		$this->arrFieldType['date_expire']		= 'date_empty';
		$this->arrFieldType['active']			= 'boolean';
		$this->arrFieldType['seo_description']	= 'string_empty';
		$this->arrFieldType['seo_keywords']		= 'string_empty';
		
		$objData								= (is_null($objData) || !is_object($objData) ? (object) $_POST : $objData);
		
		$objData->lang_content					= 0;
		$objData->date_create					= date('YmdHis');
		
		// If INSERT, defines PERMALINK; if UPDATE, keeps previous content
		if(empty($objData->id)) {
			$objData->permalink	= $this->permalinkSyntax($objData->name);
		} else {
			$objData->permalink = $this->objModel->recordExists('permalink',$this->strTable,'id = "' . $objData->id . '"',true);
		}
		
		if(empty($objData->sys_language_id))	$objData->sys_language_id	= $objData->sys_language_id	= LANGUAGE;
		if(empty($objData->date_publish))	{
			$objData->date_publish	= date('YmdHis');
		} else {
			$objData->date_publish	.= ' ' . $objData->time_publish;
		}
		if(empty($objData->date_expire))	{
			$objData->date_expire		= '00000000000000';
		} else {
			$objData->date_expire		.= ' ' . $objData->time_expire;
		}

		$this->objData	= $this->setupFieldSufyx($objData,array_keys((array) $objData),1);
		
		// Insert / Updates data
		$this->_update((array) $this->objData,false);
		
		// Formats data array to screen
		$this->objPrintData	= reset($this->objPrintData);
		
		// Shows Section's form template
		if(empty($this->objPrintData->id)) {
			$this->insert();
		} else {
			$this->update($this->objPrintData->id);
		}
		
		$this->secureGlobals();
	}
}