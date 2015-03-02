<?php
class Main_controller extends Controller {
	
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
		parent::__construct(false,CMS_ROOT_TEMPLATE);
		
		$this->objSection_Menu = new Section_controller(false);
		
		$this->objSmarty->assign('objHierarchy',$this->objSection_Menu->objSmarty->getTemplateVars('objHierarchy'));
		
		if($boolRenderTemplate) $this->renderTemplate();
	}
}