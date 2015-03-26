<?php
class Main_controller extends manageContent_Controller {
	
	public $strPermalink;
	public $strWhere;
	public $intContentID;
	
	protected $strTPL;
	
	/**
	 * Class constructor
	 *
	 * @return	void
	 *
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 * @todo Check $this->objRelField and $this->objRelContent CONCAT/GROUP_CONCAT
	 *
	 */
	public function __construct($boolRenderTemplate = true,$intSecID = 0,$intContentID = 0,$boolGetContent = true,$checkAuth = false,$strTemplateDir = ROOT_TEMPLATE) {
		parent::__construct($intSecID,$checkAuth,$strTemplateDir);
		
		if(!empty($_REQUEST['clear'])) setcookie(PROJECT . '_isLead',true,1,'/');
		
		// Sets Section vars
		if(empty($intSecID)) $intSecID = SECTION;
		$this->setSection($intSecID);
		
		// Gets Section Fields list
		$this->getFieldList();
		$this->setFieldType();
		
		// Set Content Var
		if(empty($intContentID)) $this->intContentID = CONTENT; else $this->intContentID = $intContentID; 
		
		// Sets default fields
		$this->objField[] = 'lang_content';
		$this->objField[] = 'usr_data_id';
		$this->objField[] = 'sys_language_id';
		$this->objField[] = 'date_create';
		$this->objField[] = 'active';
		
		// Setups SYS_WHERE syntax
		$this->strWhere = (!empty($this->strWhere) ? $this->strWhere . ' AND ' : '') . str_replace('#table#',$this->objSection->table_name.'.',SYS_WHERE);
		
		// Sets Content List vars
		$this->objModel->strTable		= $this->objSection->table_name;
		$this->objModel->arrTable		= array($this->objSection->table_name);
		$this->objModel->arrFieldType	= $this->arrFieldType;
		
		$this->objModel->arrFieldList	= array($this->objModel->strTable . '.*');
		$this->objModel->arrJoinList	= array();
		$this->objModel->arrWhereList	= array($this->strWhere);
		$this->objModel->arrOrderList	= array($this->objModel->strTable . '.date_publish DESC');
		$this->objModel->arrGroupList	= array($this->objModel->strTable . '.id');
		
		$this->objModel->arrFieldData	= array($this->objModel->strTable . '.*');
		$this->objModel->arrJoinData	= array();
		$this->objModel->arrWhereData	= array($this->strWhere . ' AND ' . $this->objModel->strTable . '.id = {id}');
		$this->objModel->arrOrderData	= array($this->objModel->strTable . '.date_publish DESC');
		$this->objModel->arrGroupData	= array($this->objModel->strTable . '.id');
		
		// Set Relationship SQL vars
		$intRel = 0;
		foreach($this->objRelField AS $intRel => $objRel) {
			if($objRel->parent) {
				$arrTables = explode('_rel_',str_replace(array('sec_rel_','_parent','_child'),'',$objRel->table_name));
				$strTable = ($objRel->parent ? $arrTables[1] : $arrTables[0]);
					
				$arrChildField = explode(',',$objRel->child_fields);
				foreach($arrChildField AS &$strField) {
					if($strField != 'permalink') {
						$strField = '\"' . $strField . '\":\"",IF(' . $strTable . '.' . $strField. ' IS NULL,"",' . $strTable . '.' . $strField. '),"\"';
					} else {
						$strAttrPermalink = 'IF(' . $strTable . '.' . $strField. ' IS NULL,"", CONCAT("' . $objRel->child_section_permalink . '/",' . $strTable . '.' . $strField. ') )';
						$strField = '\"' . $strField . '\":\"",' . $strAttrPermalink . ',"\"';
					}
				}
				$arrChildField[] = '\"url_permalink\":\"", CONCAT("' . HTTP . '",' . $strAttrPermalink . '),"\"';
					
				if($objRel->parent) {
					$this->objModel->arrFieldData[$objRel->field_name]	= $this->objModel->arrFieldList[$objRel->field_name]	= 'CONCAT("[",GROUP_CONCAT(DISTINCT CONCAT("{\"id\":\"",' . $strTable . '.id,"\",\"value\":\"",' . $strTable . '.' . $objRel->field_rel. ',"\",' . implode(',',$arrChildField) . '}") SEPARATOR ","),"]") AS ' . $objRel->field_name;
					$this->objModel->arrJoinData['rel_tbl_' . $intRel]	= $this->objModel->arrJoinList['rel_tbl_' . $intRel]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $this->objModel->strTable . '.id';
					$this->objModel->arrJoinData[$arrTables[1]]	= $this->objModel->arrJoinList[$arrTables[1]]	= 'LEFT JOIN ' . $arrTables[1] . ' AS ' . $strTable . ' ON rel_tbl_' . $intRel . '.child_id = ' . $strTable . '.id';
				} else {
					$this->objModel->arrJoinData['rel_tbl_' . $intRel]	= $this->objModel->arrJoinList['rel_tbl_' . $intRel]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.child_id = ' . $this->objModel->strTable . '.id';
					$this->objModel->arrJoinData[$arrTables[0]]	= $this->objModel->arrJoinList[$arrTables[0]]	= 'LEFT JOIN ' . $arrTables[0] . ' AS ' . $strTable . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $strTable . '.id';
				}
			}
		}
		/*
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
			$this->objModel->arrJoinData[$arrTables[1]]	= $this->objModel->arrJoinList[$arrTables[1]]	= 'LEFT JOIN ' . $arrTables[1] . ' AS ' . $arrTables[1] . ' ON rel_tbl_' . $intRel . '.child_id = ' . $arrTables[1] . '.id';
				
			$intRel++;
		}
		*/
		if($boolGetContent) $this->getSectionContent();
		
		if($boolRenderTemplate) $this->renderTemplate();
	}
	
	protected function getSectionContent($strTemplate = '', $intContent = null) {
		// Checks for $intContent value; if numeric > 0, goes for it; if == 0, gets list; otherwise, gets $this->intContentID value
		if(is_null($intContent) || !is_numeric($intContent)) $intContent = $this->intContentID;
		
		// LIST
		if(!$intContent) {
			// Gets content
			$this->objData	= $this->content();
			if(!empty($this->objData)) {
				// Formats relationship data
				foreach($this->arrRelContent AS $objRel) {
					if($objRel->type == 2) {
						foreach($this->objData AS $intDataKey => &$mxdDataContent) {
							$mxdDataContent->{$objRel->field_name} = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $mxdDataContent->{$objRel->field_name}) );
						}
					}
				}
				// Formats display data
				$arrDataKeys	= array_keys((array) reset($this->objData));
				$this->objData	= (array) $this->setupFieldSufyx($this->objData,$arrDataKeys,2,true);
			}

			// Defines TPL
			$this->strTPL = (!empty($strTemplate) ? $strTemplate : (HOME == 1 ? $this->objSection->tpl_home : $this->objSection->tpl_list));
		
		// CONTENT
		} else {
			// Gets content
			$this->objData = $this->objModel->getData($intContent);
		
			if(!empty($this->objData)) {
				// Formats relationship data
				foreach($this->arrRelContent AS $objRel) {
					if($objRel->type == 2) {
						$this->objData->{$objRel->field_name} = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $this->objData->{$objRel->field_name}) );
					}
				}
				// Formats display data
				$this->objData = $this->setupFieldSufyx($this->objData,array_keys((array) $this->objData),2);
			}
			
			// Defines TPL
			$this->strTPL = (!empty($strTemplate) ? $strTemplate : (HOME == 1 ? $this->objSection->tpl_home : $this->objSection->tpl_content));
		}
		if(!empty($_REQUEST['debug'])) { echo '<pre>'; print_r($this->objData); }
	}
	
	public function renderTemplate($strTemplate = '',$boolDisplay = true) {
		if(!empty($strTemplate) && is_string($strTemplate) && $this->objSmarty->templateExists($strTemplate)) $this->strTPL = $strTemplate;
		
		// Sets Smarty vars
		$this->objSmarty->assign('objSection',$this->objSection);
		$this->objSmarty->assign('objData',$this->objData);
		$this->objSmarty->assign('objStruct',$this->objStruct);
		// Renders Template
		if(!empty($this->strTPL)) {
			if($boolDisplay) parent::renderTemplate($boolDisplay,$this->strTPL);
			else return parent::renderTemplate($boolDisplay,$this->strTPL);
		}
	}
	
	protected function fetchTemplate($strTemplate = '') {
		return $this->renderTemplate($strTemplate,false);
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
	
	public function Search($strSearch = '',$strTemplate = '') {
		if($_SESSION[PROJECT]['URI_SEGMENT'][1] != SECTION_SEGMENT) return false;
		#if(empty($strSearch)) $strSearch = $_SESSION[PROJECT]['URI_SEGMENT'][3];
		if(empty($strSearch)) $strSearch = SEARCH;
		if(empty($strSearch)) return false;
		if(empty($strTemplate)) $strTemplate = TEMPLATE_FILE;
		
		// Parses query_string search params
		parse_str($_SERVER['QUERY_STRING'],$arrParams);
		$arrParams = array_intersect_key($arrParams,array_flip($this->objField));
		
		// Defines SEARCH SQL
		$arrSearch = array();
		
		if(!empty($arrParams)) {
			#echo '<pre>'; print_r($arrParams); die();
			foreach($arrParams AS $strKey => $strValue) {
				$arrSearch[] = $strKey . ' LIKE "%' . $strValue . '%"';
			}
			$this->objModel->arrWhereList[] = implode(' AND ',$arrSearch);
			
			define('SEARCH_BY', implode(' e ',$arrParams));
		} else {
			foreach($this->objStruct AS $objField) {
				if($objField->type == 1) {
					$arrSearch[] = 'LOWER(' . $this->objSection->table_name . '.' . $objField->field_name . ') LIKE "%' . strtolower($strSearch) . '%"';
				} else {
					#print_r($objField);
					$arrChild = explode(',',$objField->child_fields);
					foreach($arrChild AS $strChild) {
						$arrSearch[] = 'LOWER(' . $objField->child_section_table_name . '.' . $strChild . ') LIKE "%' . strtolower($strSearch) . '%"';
					}
				}
			}
			$this->objModel->arrWhereList[] = '(' . implode(' OR ',$arrSearch) . ')';
			
			define('SEARCH_BY', '');
		}
		
		// Searches for term
		$this->getSectionContent($strTemplate);
		
		// Fetches template
		#var_dump($this->objData); die();
		$objResult->html = $this->fetchTemplate();
		
		// If is AJAX request
		if( $this->isAjaxRequest() ) {
			echo json_encode($objResult);
		} else {
			echo $objResult->html;
		}
	}
}