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
		if(!empty($intSecID)) {
			$this->intSecID	= intval($intSecID);
			$strSection		= $this->objModel->recordExists('permalink','sec_config','id = "' . $this->intSecID . '"',true);
		} else {
			$strSection		= (!in_array($_SESSION[self::$strProjectName]['URI_SEGMENT'][3],array('update','insert','add','list')) ? $_SESSION[self::$strProjectName]['URI_SEGMENT'][3] : $_SESSION[self::$strProjectName]['URI_SEGMENT'][4]);
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
	public function _create($intID = 0) {
		if($intID > 0) {
			$this->objRawData = $this->objModel->getData($intID);
			#echo '<pre>'; print_r($this->objRawData);
			foreach($this->arrRelContent AS $objRel) {
				if($objRel->type == 2) {
					$this->objRawData->{$objRel->field_name} = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $this->objRawData->{$objRel->field_name}));
				}
			}
			#echo '<pre>'; print_r($this->objRawData);
			$this->objPrintData = $this->setupFieldSufyx($this->objRawData,array_keys((array) $this->objRawData),2);
			#echo '<pre>'; print_r($this->objPrintData);
			$this->objSmarty->assign('objData',$this->objPrintData);
			
		}
		
		$this->renderTemplate(true,$this->strModule . '_form.html');
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
	public function update($intID = 0) {
		if(!is_numeric($intID) || $intID <= 0) { 
			$intID = intval($_SESSION[self::$strProjectName]['URI_SEGMENT'][5]); 
		}
		
		if(!is_numeric($intID) || $intID <= 0) {
			$this->objSmarty->assign('ALERT_MSG','You must choose an item to update!');
			$this->_read(); exit();
		}
		
		foreach($this->arrRelContent AS $intRel => $objRel) {
			$arrChildField = explode(',',$objRel->child_fields);
			foreach($arrChildField AS &$strField) {
				$strField = '\"' . $strField . '\":\"",IF(rel_ctn_' . $intRel . '.' . $strField. ' IS NULL,"",rel_ctn_' . $intRel . '.' . $strField. '),"\"';
			}
			
			$arrTables = explode('_rel_',str_replace(array('sec_rel_','_parent','_child'),'',$objRel->table_name));
			
			$this->objModel->arrFieldData[]	= $this->objModel->arrFieldList[]	= 'CONCAT("[",GROUP_CONCAT(DISTINCT CONCAT("{\"id\":\"",rel_ctn_' . $intRel . '.id,"\",\"value\":\"",rel_ctn_' . $intRel . '.' . $objRel->field_rel. ',"\",' . implode(',',$arrChildField) . '}") SEPARATOR ","),"]") AS ' . $objRel->field_name;
			$this->objModel->arrJoinData[]	= $this->objModel->arrJoinList[]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $this->objModel->strTable . '.id';
			$this->objModel->arrJoinData[]	= $this->objModel->arrJoinList[]	= 'LEFT JOIN ' . $arrTables[1] . ' AS rel_ctn_' . $intRel . ' ON rel_tbl_' . $intRel . '.child_id = rel_ctn_' . $intRel . '.id';
		}
		
		$this->_create($intID);
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
		
		$objData								= (object) $_POST;
		$objData->lang_content					= 0;
		$objData->date_create					= date('YmdHis');
		
		// If INSERT, defines PERMALINK; if UPDATE, keeps previous content
		if(empty($objData->id)) {
			$objData->permalink	= $this->permalinkSyntax($objData->name);
		} else {
			$objData->permalink = $this->objModel->recordExists('permalink',$this->strTable,'id = "' . $objData->id . '"',true);
		}

		$this->objData	= $this->setupFieldSufyx($objData,array_keys((array) $objData),1);
		
		if(empty($this->objData->sys_language_id))	$this->objData->sys_language_id	= $objData->sys_language_id	= LANGUAGE;
		if(empty($this->objData->date_publish))	{
			$this->objData->date_publish	= date('YmdHis');
		} else {
			$this->objData->date_publish	.= ' ' . $this->objData->time_publish;
		}
		if(empty($this->objData->date_expire))	{
			$this->objData->date_expire		= '00000000000000';
		} else {
			$this->objData->date_expire		.= ' ' . $this->objData->time_expire;
		}
		
		// Insert / Updates data
		$this->_update((array) $this->objData,false);
		
		// Formats data array to screen
		$this->objData	= $this->setupFieldSufyx($this->objData,array_keys((array) $this->objData),2);
		#echo '<pre>'; print_r($this->objData); die();
		// Shows Section's form template
		if(empty($objData->id)) {
			$this->insert();
		} else {
			$this->update($objData->id);
		}
		
		$this->secureGlobals();
	}
}