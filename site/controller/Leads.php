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
	public function __construct($arrWhere = '') {
		if(!empty($arrWhere) && is_string($arrWhere)) $arrWhere = array($arrWhere);
		
		parent::__construct(false,$this->intSecID,0,false);
		
		// Sets RELATIONSHIP query params
		foreach($this->objRelField AS $intRel => $objRel) {
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
		$this->objModel->arrJoinData[]	= $this->objModel->arrJoinList[]	= 'LEFT JOIN sec_rel_aet_fl_email_report_rel_aet_fl_email AS rel_tbl_email_report ON rel_tbl_email_report.parent_id = aet_fl_email_report.id';
		
		// Sets specific WHERE params
		if(!empty($arrWhere)) {
			$this->objModel->arrWhereData[] = $this->objModel->arrWhereList[] = implode(' AND ',$arrWhere);
		} 
		
		// Gets content
		$this->getSectionContent();
	}
}
?>
