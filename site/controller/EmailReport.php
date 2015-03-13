<?php
class EmailReport_controller extends Main_Controller {
	
	protected $intSecID = 9;
	
	/**
	 * Class constructor
	 *
	 * @return	void
	 *
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct() {
		parent::__construct(false,$this->intSecID,0,false);
	}
}
?>
