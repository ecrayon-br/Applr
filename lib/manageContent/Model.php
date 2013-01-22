<?php
class manageContent_Model extends Model {
	
	/**
	 * Executes parent::construct and instantiates connection
	 * 
	 * @return	void
	 * 
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Get SECTION fields data
	 * 
	 * @param	integer	$intSection	SECTION id
	 *
	 * @return	MDB2_Data_Object
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function getSectionFields($intSection) {
		if(empty($intSection) || !is_numeric($intSection)) return false;
		
		return $this->objModel->select('field_name AS field','rel_sec_struct',array(),'sec_config_id = ' . $intSection);
	}
	
	/**
	 * Get RELATIONSHIP fields data
	 * 
	 * @param	integer	$intSection	SECTION id
	 *
	 * @return	MDB2_Data_Object
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function getRelSectionFields($intSection) {
		if(empty($intSection) || !is_numeric($intSection)) return false;
		
		return $this->objModel->select(array('field_name AS field','table','IF(parent_id = ' . $intSection . ',1,0) AS parent'),'rel_sec_sec',array(),'parent_id = ' . $intSection . ' OR child_id = ' . $intSection);
	}
}