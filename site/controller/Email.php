<?php
class Email_controller extends Main_controller {
	
	protected 	$intSecID = 7;
	
	private 	$boolSendMailUnsubscribe = false;
	
	/**
	 * Class constructor
	 *
	 * @return	void
	 *
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($intCampaignID = 0,$boolGetContent = false) {
		// Sets CAMPAIGN ID
		if($boolGetContent && !empty($intCampaignID) && is_numeric($intCampaignID)) $this->strWhere = 'aet_fl_email.sequence_bool = 1 AND rel_tbl_0.child_id = ' . $intCampaignID;
		
		parent::__construct(false,$this->intSecID,0,false);

		// Sets sys_template CONTENT JOIN SQL
		/**
		 * @todo set PROJECT_ID
		 */
		$this->objModel->arrFieldList[]	= $this->objModel->arrFieldData[]	= 'sys_template.filename AS mail_tpl_filename';
		$this->objModel->arrJoinList[]	= $this->objModel->arrJoinData[]	= 'LEFT JOIN sys_template ON sys_template.status = 1 AND sys_template.id = aet_fl_email.mail_tpl';
		$this->objModel->arrJoinList[]	= $this->objModel->arrJoinData[]	= 'LEFT JOIN sec_rel_aet_fl_destino_rel_aet_fl_avatar AS rel_hotspot ON rel_hotspot.child_id = rel_tbl_3.child_id';
		$this->objModel->arrOrderList	= $this->objModel->arrOrderData		= array('aet_fl_email.timesheet_int ASC, aet_fl_email.previous_msgstatus ASC');
		
		// Gets content
		if($boolGetContent) $this->getSectionContent();
	}
	
	/**
	 * Unsubscribes LEAD from EMAIL LIST
	 *
	 * @return	void
	 *
	 * @since 	2015-04-15
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function unsubscribe() {
		// Updates LEAD status
		if(!empty($_SESSION[PROJECT]['URI_SEGMENT'][3]) && $this->objModel->update('aet_fl_leads',array('deleted' => 1),'MD5(name) = "' . $_SESSION[PROJECT]['URI_SEGMENT'][3] . '"')) {
			// Unsets cookies
			setcookie(PROJECT . '_isLead',true,1,'/');
			setcookie(PROJECT . '_confirmLead',true,1,'/');
			
			$this->objSmarty->assign('ALERT_MSG','Muito Obrigado!<br /><br />Seu e-mail foi descadastrado com sucesso.');
		} else {
			$this->objSmarty->assign('ERROR_MSG','Ocorreu um erro ao descadastrar.<br />Por favor, tente novamente.');
		}
		
		// Sends e-mail with offer
		if($this->boolSendMailUnsubscribe) {
			// Gets user data
			$objUser = $this->objModel->select('id,name,first_name','aet_fl_leads',array(),'MD5(name) = "' . $_SESSION[PROJECT]['URI_SEGMENT'][3] . '"',array(),array(),0,null,'Row');
			
			// Sends message
			if($objUser) {
				$objMail = new sendMail_Controller();
				$objMail->sendMessage(array($objUser->name => $objUser->first_name),'ASSUNTO DA MENSAGEM DE DESCADASTRAMENTO', ROOT_TEMPLATE.'email-unsubscribe-offer.html',$objUser);
			}
		}
		
		// Renders TPL
		$this->renderTemplate('email-list-unsubscribe.html');
	}
}
?>
