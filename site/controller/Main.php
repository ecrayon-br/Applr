<?php
class Main_controller extends manageContent_Controller {
	
	public $strPermalink;
	public $strWhere;
	
	private $strTPL;
	
	/**
	 * Class constructor
	 *
	 * @return	void
	 *
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($boolRenderTemplate = true,$intSecID = 0,$checkAuth = false,$strTemplateDir = ROOT_TEMPLATE) {
		
		parent::__construct($intSecID,$checkAuth,$strTemplateDir);

		if(empty($intSecID)) $intSecID = SECTION;
		
		// Sets Section vars
		$this->setSection($intSecID);
		
		// Sets default fields
		$this->objField[] = 'lang_content';
		$this->objField[] = 'usr_data_id';
		$this->objField[] = 'sys_language_id';
		$this->objField[] = 'date_create';
		$this->objField[] = 'active';
		
		// Setups SYS_WHERE syntax
		$this->strWhere = str_replace('#table#',$this->objSection->table_name.'.',SYS_WHERE);
		
		// Gets Section Fields list
		$this->getFieldList();
		$this->setFieldType();
		
		// Sets Content List vars
		$this->objModel->strTable		= $this->objSection->table_name;
		$this->objModel->arrTable		= array($this->objSection->table_name);
		$this->objModel->arrFieldType	= $this->arrFieldType;
		
		$this->objModel->arrFieldList	= array('*');
		$this->objModel->arrJoinList	= array();
		$this->objModel->arrWhereList	= array($this->strWhere);
		$this->objModel->arrOrderList	= array('date_publish DESC');
		$this->objModel->arrGroupList	= array('id');
		
		$this->objModel->arrFieldData	= array($this->objModel->strTable . '.*');
		$this->objModel->arrJoinData	= array();
		$this->objModel->arrWhereData	= array($this->strWhere . ' AND ' . $this->objModel->strTable . '.id = {id}');
		$this->objModel->arrOrderData	= array($this->objModel->strTable . '.date_publish DESC');
		$this->objModel->arrGroupData	= array($this->objModel->strTable . '.id');

		// Set Relationship SQL vars
		$intRel = 0;
		foreach($this->arrRelContent AS $objRel) {
			$arrFields = explode('_rel_',str_replace(array('sec_rel_','_parent','_child'),'',$objRel->table_name));		// sec_rel_ mysec_parent mysec_child
			$this->objModel->arrFieldData[]	= 'CONCAT("[",GROUP_CONCAT(DISTINCT CONCAT("{\"id\":\"",rel_ctn_' . $intRel . '.id,"\",\"value\":\"",rel_ctn_' . $intRel . '.' . $objRel->field_rel. ',"\"}") SEPARATOR ","),"]") AS ' . $objRel->field_name;
			$this->objModel->arrJoinData[]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $this->objModel->strTable . '.id';
			$this->objModel->arrJoinData[]	= 'LEFT JOIN ' . $arrFields[1] . ' AS rel_ctn_' . $intRel . ' ON rel_tbl_' . $intRel . '.child_id = rel_ctn_' . $intRel . '.id';
			$intRel++;
		}
		
		$this->getSectionContent();
		
		if($boolRenderTemplate) $this->renderTemplate();
	}
	
	protected function getSectionContent() {
		if(!CONTENT) {
			// Gets content
			$this->objData	= $this->content();
		
			// Formats relationship data
			foreach($this->arrRelContent AS $objRel) {
				if($objRel->type == 2) {
					foreach($this->objData AS $intDataKey => &$mxdDataContent) {
						$mxdDataContent->{$objRel->field_name} = json_decode($mxdDataContent->{$objRel->field_name});
					}
				}
			}
			// Formats display data
			$this->objData = (array) $this->setupFieldSufyx($this->objData,array_keys((array) $this->objData[0]),2,true);
		
			// Defines TPL
			$this->strTPL = (HOME == 1 ? $this->objSection->tpl_home : $this->objSection->tpl_list);
		
		} else {
			// Gets content
			$this->objData = $this->objModel->getData(CONTENT);
		
			// Formats relationship data
			foreach($this->arrRelContent AS $objRel) {
				if($objRel->type == 2) {
					$this->objData->{$objRel->field_name} = json_decode($this->objData->{$objRel->field_name});
				}
			}
		
			// Formats display data
			$this->objData = $this->setupFieldSufyx($this->objData,array_keys((array) $this->objData),2);
		
			// Defines TPL
			$this->strTPL = (HOME == 1 ? $this->objSection->tpl_home : $this->objSection->tpl_content);
		}
		
		if($_REQUEST['debug']) { echo '<pre>'; print_r($this->objData); }
	}
	
	public function renderTemplate() {
		
		
		// Sets Smarty vars
		$this->objSmarty->assign('objSection',$this->objSection);
		$this->objSmarty->assign('objData',$this->objData);
		$this->objSmarty->assign('objStruct',$this->objStruct);
		
		// Renders Template
		if(!empty($this->strTPL)) parent::renderTemplate(true,$this->strTPL);
	}

	protected function insertMainContent($mxdData) {
		if(!is_array($mxdData)) return false;
		if(empty($mxdData['lang_content'])) 	$mxdData['lang_content'] 	= 1;
		if(empty($mxdData['usr_data_id'])) 		$mxdData['usr_data_id'] 	= null;
		if(empty($mxdData['sys_language_id']))  $mxdData['sys_language_id'] = LANGUAGE;
		if(empty($mxdData['date_create']))  	$mxdData['date_create'] 	= date('YmdHis');
		if(empty($mxdData['active'])) 			$mxdData['active'] 			= 0;
		
		return parent::insertMainContent($mxdData);
	}
}