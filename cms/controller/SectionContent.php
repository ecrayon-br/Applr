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
		parent::__construct();
		
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
		#echo '<pre>'; var_dump($this->intSecID); die();
		
		// Sets Section vars
		$this->setSection($this->intSecID);
		$this->objSection	= $this->objModel->getSectionConfig($this->intSecID);
		$this->objSmarty->assign('objSection',$this->objSection);
		
		// Gets Section Fields list
		$this->getFieldList();
		$this->objSmarty->assign('objStruct',$this->objStruct);
		#echo '<pre>'; print_r($this->objStruct); die();
		#echo '<pre>'; print_r($this->arrRelContent); die();
		
		$this->setFieldType();
		#echo '<pre>'; print_r($this->objStruct); die();
		
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
		
		/*
		// Sets FCKEditor object class
		$this->objFCK = new FCKeditor('APPLR');
		$this->objFCK->BasePath = HTTP . 'lib/FCKeditor/';
		$this->objFCK->ToolbarSet = 'APPLR';
		$this->objFCK->Height = '400';
		$this->objSmarty->assign('objFCK',$this->objFCK);
		*/
		
		// Shows default interface
		if($boolRenderTemplate) $this->_read();
	}
	
	private function setFieldType() {
		// Sets $arrFieldType
		foreach($this->objStruct AS $objField) {
			switch($objField->fieldtype) {
				case 'clob':
				case 'text|fixed':
				case 'text':
				case 'blob':
				case 'char':
				case 'varchar':
				case 'enum':
					$strType = ($objField->suffix == 'mail' ? 'email' : 'string');
					break;
		
				case 'boolean':
					$strType = 'boolean';
					break;
		
				case 'integer':
				case 'decimal':
				case 'float':
				case 'tinyint':
				case 'bigint':
				case 'double':
				case 'year':
					$strType = 'numeric_clearchar';
					break;
		
				case '0':
				case '1':
					$strType 				= 'numeric_clearchar';
					$this->arrRelContent[] 	= clone $objField;
					break;
		
				case '2':
					$strType 				= 'array';
					$this->arrRelContent[] 	= clone $objField;
					break;
		
				case 'time':
					$strType = '';
					break;
		
				case 'timestamp':
				case 'datetime':
				case 'date':
					$strType = 'date';
					break;
			}
				
			if($objField->mandatory == 0) $strType .= '_empty';
				
			$this->arrFieldType[$objField->field_name] = $strType;
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
	public function _create($intID = 0) {
		if($intID > 0) {
			$this->objRawData = $this->objModel->getData($intID);
			 
			foreach($this->arrRelContent AS $objRel) {
				if($objRel->type == 2) {
					$this->objRawData->{$objRel->field_name} = json_decode($this->objRawData->{$objRel->field_name});
				}
			}
			$this->setupFieldSufyx($this->objRawData,array_keys((array) $this->objRawData),1);
			#echo '<pre>'; print_r($this->objPrintData); die();
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
	 */
	public function update($intID = 0) {
		if(!is_numeric($intID) || $intID <= 0) { 
			$intID = intval($_SESSION[self::$strProjectName]['URI_SEGMENT'][5]); 
		}
		
		if(!is_numeric($intID) || $intID <= 0) {
			$this->objSmarty->assign('ALERT_MSG','You must choose an item to update!');
			$this->_read(); exit();
		}
		
		$intRel = 0;
		foreach($this->arrRelContent AS $objRel) {
			$arrFields = explode('_rel_',str_replace(array('sec_rel_','_parent','_child'),'',$objRel->table_name));		// sec_rel_ mysec_parent mysec_child
			$this->objModel->arrFieldData[]	= 'CONCAT("[",GROUP_CONCAT(DISTINCT CONCAT("{\"id\":\"",rel_ctn_0.id,"\",\"value\":\"",rel_ctn_0.' . $objRel->field_rel. ',"\"}") SEPARATOR ","),"]") AS ' . $objRel->field_name; 
			$this->objModel->arrJoinData[]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $this->objModel->strTable . '.id';
			$this->objModel->arrJoinData[]	= 'LEFT JOIN sec_ctn_' . $arrFields[1] . ' AS rel_ctn_' . $intRel . ' ON rel_tbl_' . $intRel . '.child_id = rel_ctn_' . $intRel . '.id';
			$intRel++;
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
		$objData->lang_content					= 1;
		$objData->date_create					= date('YmdHis');
		
		// If INSERT, defines PERMALINK; if UPDATE, keeps previous content
		if(empty($objData->id)) {
			$objData->permalink	= $this->permalinkSyntax($objData->name);
		} else {
			$objData->permalink = $this->objModel->recordExists('permalink',$this->strTable,'id = "' . $objData->id . '"',true);
		}
		
		#echo '<pre>'; print_r($objData); die();
		
		$this->objData	= null;
		$this->objData	= $this->setupFieldSufyx($objData,array_keys((array) $objData),1);
		
		#echo '<pre>'; print_r($this->objData); die();
		
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
		
		#echo '<pre>'; print_r($this->objData); die();
		
		// Insert / Updates data
		$this->_update((array) $this->objData,false);
		
		// Formats data array to screen
		$this->objData	= $this->setupFieldSufyx($this->objData,array_keys((array) $this->objData),2);
		
		// Shows Section's form template
		if(empty($objData->id)) {
			$this->insert();
		} else {
			$this->update($objData->id);
		}
		
		$this->secureGlobals();
	}
}