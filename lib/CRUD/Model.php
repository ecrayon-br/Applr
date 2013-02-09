<?php
class CRUD_Model extends Model {	
	public		$arrTable		= array();
	public		$arrFieldType	= array();
	public		$arrFieldList	= array();
	public		$arrWhereList	= array();
	public		$arrFieldData	= array();
	public		$arrWhereData	= array();
	
	public function __construct($arrTable = '',$arrFieldList= array(),$arrWhereList= array(),$arrFieldType= array(),$arrFieldData= array(),$arrWhereData= array()) {
		if(!is_array($arrTable) 	|| empty($arrTable)) 		$arrTable 		= array();
		if(!is_array($arrFieldList) || empty($arrFieldList)) 	$arrFieldList 	= array();
		if(!is_array($arrWhereList) || empty($arrWhereList)) 	$arrWhereList 	= array();
		if(!is_array($arrFieldType) || empty($arrFieldType)) 	$arrFieldType 	= array();
		if(!is_array($arrFieldData) || empty($arrFieldData)) 	$arrFieldData 	= array();
		if(!is_array($arrWhereData) || empty($arrWhereData)) 	$arrWhereData 	= array();
		
		parent::__construct();
		
		$this->arrTable 	= $arrTable;
		$this->arrFieldType	= $arrFieldType;
		$this->arrFieldList	= $arrFieldList;
		$this->arrWhereList	= $arrWhereList;
		$this->arrFieldData	= $arrFieldData;
		$this->arrWhereData	= $arrWhereData;
	}
	
	/**
	 * Gets specific STRUCT register data
	 * 
	 * @param	integer	$intID	Struct register ID
	 *
	 * @return	MDB2_Data_Object
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function getData($intID) {
		if(!is_numeric($intID) || $intID <= 0) return false;
		
		$objReturn = $this->select($this->arrFieldData,$this->arrTable,array(),str_replace('{id}',$intID,implode(' AND ',$this->arrWhereData)));
		
		if($objReturn === false) {
			return false;
		} else {
			return reset($objReturn);
		}
	}
	
	/**
	 * Gets all registers
	 *
	 * @return	MDB2_Data_Object
	 *
	 * @since 	2013-02-09
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function getList() {
		$objReturn = $this->select($this->arrFieldList,$this->arrTable,array(),$this->arrWhereList);
		
		if($objReturn === false) {
			return false;
		} else {
			return $objReturn;
		}
	}
}
?>