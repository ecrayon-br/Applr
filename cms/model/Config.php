<?php
class Config_model extends Model {
	
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
	 * Gets PROJECT data and CONFIG
	 * 
	 * @param	integer	$intProject	Project ID
	 *
	 * @return	MDB2_Data_Object
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function getData($intProject) {
		if(!is_numeric($intProject) || $intProject <= 0) return false;
		
		$arrFields = array(
						'project.name',
						'project.description',
						'project.logo_upload',
						'project.start_date',
						'config.*'
					);
		
		$objReturn = $this->select($arrFields,array('project','config'),array(),'project.id = config.project_id AND project.id = ' . $intProject);
		
		if($objReturn === false) {
			return false;
		} else {
			return reset($objReturn);
		}
	}
}
?>