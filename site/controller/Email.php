<?php
class Email_controller extends Main_Controller {
	
	protected $intSecID = 7;
	
	/**
	 * Class constructor
	 *
	 * @return	void
	 *
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($intCampaignID = 0) {
		// Sets CAMPAIGN ID
		if(empty($intCampaignID) || !is_numeric($intCampaignID)) return false; else $this->strWhere = 'aet_fl_email.sequence_bool = 1 AND rel_tbl_0.child_id = ' . $intCampaignID;
		
		parent::__construct(false,$this->intSecID,0,false);

		// Sets sys_template CONTENT JOIN SQL
		$this->objModel->arrFieldList[]	= $this->objModel->arrFieldData[]	= 'sys_template.filename AS mail_tpl_filename';
		$this->objModel->arrJoinList[]	= $this->objModel->arrJoinData[]	= 'LEFT JOIN sys_template ON sys_template.status = 1 AND sys_template.id = aet_fl_email.mail_tpl';
		
		// Gets content
		$this->getSectionContent();
	}
}
?>
