<?php
class Leads_controller extends Main_controller {
	
	protected $intSecID = 4;
	
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
	public function __construct($arrWhere = '',$boolGetContent = true) {
		// Sets specific WHERE params
		if(!empty($arrWhere) && is_string($arrWhere)) $arrWhere = array($arrWhere);
		if(!empty($arrWhere)) $this->strWhere = implode(' AND ',$arrWhere);
		
		parent::__construct(false,$this->intSecID,0,false);
		
		/*
		// Sets RELATIONSHIP query params
		$intRel = 0;
		foreach($this->objRelField AS $intRel => $objRel) {
			$arrTables		= explode('_rel_',str_replace(array('sec_rel_','_parent','_child'),'',$objRel->table_name));
			$strTable		= ($objRel->parent ? $arrTables[1] : $arrTables[0]); 
			$strTableAlias	= ($objRel->parent ? (strpos($objRel->table_name,'_parent') && strpos($objRel->table_name,'_child') ? $strTable . '_child' : $strTable) : (strpos($objRel->table_name,'_parent') && strpos($objRel->table_name,'_child') ? $strTable . '_parent' : $strTable)); 

			$arrChildField = explode(',',$objRel->child_fields);
			foreach($arrChildField AS &$strField) {
				if($strField != 'permalink') {
					$strField = '\"' . $strField . '\":\"",IF(' . $strTableAlias . '.' . $strField. ' IS NULL,"",' . $strTableAlias . '.' . $strField. '),"\"';
				} else {
					$strAttrPermalink = 'IF(' . $strTableAlias . '.' . $strField. ' IS NULL,"", CONCAT("' . $objRel->child_section_permalink . '/",' . $strTableAlias . '.' . $strField. ') )';
					$strField = '\"' . $strField . '\":\"",' . $strAttrPermalink . ',"\"';
				}
			}
			$arrChildField[] = '\"url_permalink\":\"", CONCAT("' . HTTP . '",' . $strAttrPermalink . '),"\"';
			
			if($objRel->parent) {
				$this->objModel->arrFieldData[$objRel->field_name]	= $this->objModel->arrFieldList[$objRel->field_name]	= 'CONCAT("[",GROUP_CONCAT(DISTINCT CONCAT("{\"id\":\"",' . $strTableAlias . '.id,"\",\"value\":\"",' . $strTableAlias . '.' . $objRel->field_rel. ',"\",' . implode(',',$arrChildField) . '}") SEPARATOR ","),"]") AS ' . $objRel->field_name;
				$this->objModel->arrJoinData['rel_tbl_' . $intRel]	= $this->objModel->arrJoinList['rel_tbl_' . $intRel]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $this->objModel->strTable . '.id';
				$this->objModel->arrJoinData[$strTableAlias]		= $this->objModel->arrJoinList[$strTableAlias]			= 'LEFT JOIN ' . $strTable . ' AS ' . $strTableAlias . ' ON rel_tbl_' . $intRel . '.child_id = ' . $strTableAlias . '.id';
			} else {
				$this->objModel->arrJoinData['rel_tbl_' . $intRel]	= $this->objModel->arrJoinList['rel_tbl_' . $intRel]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.child_id = ' . $this->objModel->strTable . '.id';
				$this->objModel->arrJoinData[$strTableAlias]		= $this->objModel->arrJoinList[$strTableAlias]			= 'LEFT JOIN ' . $strTable . ' AS ' . $strTableAlias . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $strTableAlias . '.id';
			}
			
			$intRel++;
		}
		*/
		
		// Adds specifics REL_QUERY instructions
		/**
		 * @todo set PROJECT_ID
		 */
		$this->objModel->arrJoinData[]	= $this->objModel->arrJoinList[]	= 'LEFT JOIN sec_rel_aet_fl_email_report_rel_aet_fl_email AS rel_tbl_email_report ON rel_tbl_email_report.parent_id = aet_fl_email_report.id';

		// Gets content
		if($boolGetContent) $this->getSectionContent();
	}
}
?>
