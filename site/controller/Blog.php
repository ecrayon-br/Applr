<?php
class Blog_controller extends Main_controller {
	
	private $isLead;
	
	protected $intSecID = 10;
	
	/**
	 * Class constructor
	 *
	 * @return	void
	 *
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($boolRenderTemplate = true,$strTemplate = '',$intContentID = 0) {
		parent::__construct(false,$this->intSecID,$intContentID);
		
		// Checks if IS LEAD cookie exists
		if(!isset($_COOKIE[PROJECT . '_isLead'])) {
			$this->isLead = false;
		} else {
			$this->isLead = true;
		}
		$this->objSmarty->assign('isLead',$this->isLead);
		
		// If content is defined, checks for PRIVATE param
		if($this->intContentID && !$this->isLead && $this->objData->private_bool) {
			// Gets related HOTSPOT
			$intHotspotContent 	= $this->objModel->recordExists('id', 'aet_fl_destino AS hotspot JOIN sec_rel_aet_fl_destino_rel_aet_fl_blog AS rel','hotspot.id = rel.parent_id AND rel.child_id = ' . $this->intContentID,true);
			$objHotspot 		= new Hotspot_controller(true,'hotspot.html',$intHotspotContent);
		} else {
			$this->renderTemplate($strTemplate);
		}
	}
}
?>
