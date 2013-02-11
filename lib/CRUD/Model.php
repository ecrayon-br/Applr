<?php
class CRUD_Model extends Model {	
	public	$arrTable		= array();
	public	$arrFieldType	= array();
	public	$arrFieldList	= array();
	public	$arrJoinList	= array();
	public	$arrWhereList	= array();
	public	$arrOrderList	= array();
	public	$arrGroupList	= array();
	public	$arrFieldData	= array();
	public	$arrJoinData	= array();
	public	$arrWhereData	= array();
	public	$arrOrderData	= array();
	public	$arrGroupData	= array();
	
	public function __construct($arrTable = '',$arrFieldType= array(),$arrFieldList= array(),$arrJoinList= array(),$arrWhereList= array(),$arrOrderList= array(),$arrGroupList= array(),$arrFieldData= array(),$arrJoinData= array(),$arrWhereData= array(),$arrOrderData= array(),$arrGroupData= array()) {
		if(!is_array($arrTable) 	|| empty($arrTable)) 		$arrTable 		= array();
		if(!is_array($arrFieldType) || empty($arrFieldType)) 	$arrFieldType 	= array();
		
		if(!is_array($arrFieldList) || empty($arrFieldList)) 	$arrFieldList 	= array();
		if(!is_array($arrJoinList) 	|| empty($arrJoinList)) 	$arrJoinList 	= array();
		if(!is_array($arrWhereList) || empty($arrWhereList)) 	$arrWhereList 	= array();
		if(!is_array($arrOrderList) || empty($arrOrderList)) 	$arrOrderList 	= array();
		if(count($arrOrderList) == 0)			 				$arrOrderList 	= array(reset($arrTable) . '.id ASC');
		if(!is_array($arrGroupList) || empty($arrGroupList)) 	$arrGroupList 	= array();
		if(count($arrGroupList) == 0)			 				$arrGroupList 	= array(reset($arrTable) . '.id');
		
		if(!is_array($arrFieldData) || empty($arrFieldData)) 	$arrFieldData 	= array();
		if(!is_array($arrJoinData) 	|| empty($arrJoinData)) 	$arrJoinData 	= array();
		if(!is_array($arrWhereData) || empty($arrWhereData)) 	$arrWhereData 	= array();
		if(!is_array($arrOrderData) || empty($arrOrderData)) 	$arrOrderData 	= array();
		if(count($arrOrderData) == 0)			 				$arrOrderData 	= array(reset($arrTable) . '.id ASC');
		if(!is_array($arrGroupData) || empty($arrGroupData)) 	$arrGroupData 	= array();
		if(count($arrGroupData) == 0)			 				$arrGroupData 	= array(reset($arrTable) . '.id');
		
		parent::__construct();
		
		$this->arrTable 	= $arrTable;
		$this->arrFieldType	= $arrFieldType;
		
		$this->arrFieldList	= $arrFieldList;
		$this->arrJoinList	= $arrJoinList;
		$this->arrWhereList	= $arrWhereList;
		$this->arrOrderList	= $arrOrderList;
		$this->arrGroupList	= $arrGroupList;
		
		$this->arrFieldData	= $arrFieldData;
		$this->arrJoinData	= $arrJoinData;
		$this->arrWhereData	= $arrWhereData;
		$this->arrOrderData	= $arrOrderData;
		$this->arrGroupData	= $arrGroupData;
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
		
		$objReturn = $this->select($this->arrFieldData,$this->arrTable,$this->arrJoinData, str_replace('{id}',$intID,implode(' AND ',$this->arrWhereData)), $this->arrOrderData,$this->arrGroupData);
		
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
		$objReturn = $this->select($this->arrFieldList,$this->arrTable,$this->arrJoinList,$this->arrWhereList,$this->arrOrderList,$this->arrGroupList);
		
		if($objReturn === false) {
			return false;
		} else {
			return $objReturn;
		}
	}
}
?>