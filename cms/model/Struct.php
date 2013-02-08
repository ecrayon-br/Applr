<?php
class Struct_model extends Model {
	
	/**
	 * Class constructor
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct() {
		parent::__construct();
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
		
		$arrFields = array(
						'sec_struct.*'
					);
		
		$objReturn = $this->select($arrFields,'sec_struct',array(),'sec_struct.id = ' . $intID);
		
		if($objReturn === false) {
			return false;
		} else {
			return reset($objReturn);
		}
	}
}
?>