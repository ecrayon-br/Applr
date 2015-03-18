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
			$arrChildField = explode(',',$objRel->child_fields);
			foreach($arrChildField AS &$strField) {
				$strField = '\"' . $strField . '\":\"",IF(rel_ctn_' . $intRel . '.' . $strField. ' IS NULL,"",rel_ctn_' . $intRel . '.' . $strField. '),"\"';
			}
			
			$arrTables = explode('_rel_',str_replace(array('sec_rel_','_parent','_child'),'',$objRel->table_name));
			
			if($objRel->parent) {
				$this->objModel->arrJoinData[]	= $this->objModel->arrJoinList[]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $this->objModel->strTable . '.id';
				$this->objModel->arrJoinData[]	= $this->objModel->arrJoinList[]	= 'LEFT JOIN ' . $arrTables[1] . ' AS rel_ctn_' . $intRel . ' ON rel_tbl_' . $intRel . '.child_id = rel_ctn_' . $intRel . '.id';
				$this->objModel->arrFieldData[]	= $this->objModel->arrFieldList[]	= 'CONCAT("[",GROUP_CONCAT(DISTINCT CONCAT("{\"id\":\"",rel_ctn_' . $intRel . '.id,"\",\"value\":\"",rel_ctn_' . $intRel . '.' . $objRel->field_rel. ',"\",' . implode(',',$arrChildField) . '}") SEPARATOR ","),"]") AS ' . $objRel->field_name;
			} else {
				$this->objModel->arrJoinData[]	= $this->objModel->arrJoinList[]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.child_id = ' . $this->objModel->strTable . '.id';
				$this->objModel->arrJoinData[]	= $this->objModel->arrJoinList[]	= 'LEFT JOIN ' . $arrTables[0] . ' AS rel_ctn_' . $intRel . ' ON rel_tbl_' . $intRel . '.parent_id = rel_ctn_' . $intRel . '.id';
			}
		}
		$this->objModel->arrJoinData[]	= $this->objModel->arrJoinList[]	= 'LEFT JOIN sec_rel_aet_fl_email_report_rel_aet_fl_email AS rel_tbl_email_report ON rel_tbl_email_report.parent_id = rel_ctn_0.id';
		
		// Sets specific WHERE params
		if(!empty($arrWhere)) {
			$this->objModel->arrWhereData[] = $this->objModel->arrWhereList[] = implode(' AND ',$arrWhere);
		} 
		
		// Gets content
		$this->getSectionContent();
	}
}
?>
