<?php
class Hotspot_controller extends Main_Controller {
	private $tryCookie = 0;
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
		
		$this->renderTemplate();
	}
	
	/**
	 * Inserts LEAD e-mail in database and sends confirmation message
	 *
	 * @return	void
	 *
	 * @since 	2015-03-03
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function save() {
		// Sets SECTION as LEADS
		$objUser 	= new Main_Controller(false,4);
		$strWhere	= 'name = "'.$_POST['name'].'" AND ' . str_replace('aet_fl_leads.active = 1 AND ','',$objUser->strWhere);
		
		if(!$this->objModel->recordExists('id', 'aet_fl_leads',$strWhere)) {
			// Inserts content
			$objReturn = $objUser->insertMainContent($_POST);
		} else {
			// Formats email info
			$objUser				= null;
			$objUser->objData		= null;
			$objUser->objData->name	= $_POST['name'];
			
			$objReturn = $this->objModel->recordExists('id', 'aet_fl_leads', 'active = 0 AND '. $strWhere);
		}
		
		if($objReturn) {
			// Sends confirmation e-mail
			$objMail = new sendMail_Controller();
			if($objMail->sendMessage(array($objUser->objData->name => $objUser->objData->name),'Confirme seu email','<a href="' . HTTP . SECTION_SEGMENT . '/confirm/'.md5($objUser->objData->name).'">Clique aqui</a> para confirmar sua inscrição!')) {
				echo 'Enviei e-mail!<br />';
			} else {
				echo 'Erro no e-mail!<br />';
			}
		}
	}
	
	/**
	 * Confirms LEAD e-mail and sets control cookie
	 *
	 * @return	void
	 *
	 * @since 	2015-03-03
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function confirm() {
		// IF cookie doesnt exists
		if(isset($_COOKIE[PROJECT . '_isLead']) || $_COOKIE[PROJECT . '_isLead'] == true || empty($_SESSION[PROJECT]['URI_SEGMENT'][3])) {
			header("Location: " . HTTP . SECTION_SEGMENT);
			exit();
		} else {
			// Sets cookie
			$intExpire = time()+60*60*24*365;
			if(setcookie(PROJECT . '_isLead',true,$intExpire,'/')) {
				
				// Updates lead status
				if($this->objModel->update('aet_fl_leads',array('active' => 1),'MD5(name) = "' . $_SESSION[PROJECT]['URI_SEGMENT'][3] . '"')) {
					header("Location: " . HTTP . SECTION_SEGMENT);
					exit();
				} else {
					setcookie(PROJECT . '_isLead',false,1);
				}
			} else {
				
				// Retries set cookie
				if($this->tryCookie <= 3) {
					$this->confirm();
				} else {
					header("Location: " . HTTP . SECTION_SEGMENT . '/?error=1');
					exit();
				}
			}
		}
	}
}
?>
