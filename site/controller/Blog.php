<?php
class Blog_controller extends Main_Controller {
	private $isLead;
	
	/**
	 * Class constructor
	 *
	 * @return	void
	 *
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($boolRenderTemplate = true) {
		parent::__construct(false);
		
		// Checks if is_user cookie exists
		if(!isset($_COOKIE[PROJECT . '_isLead'])) {
			$this->isLead = false;
		} else {
			$this->isLead = true;
		}
		$this->objSmarty->assign('is_user',$this->isLead);
		
		// If content is defined, checks for PRIVATE param
		if(CONTENT && !$this->isLead && $this->objData->private_bool) {
			echo 'SHOW HOTSPOT';
		} else {
			$this->renderTemplate();
		}
	}
}
?>
