<?php
class Hotspot_controller extends Main_controller {
	
	private $tryCookie = 0;
	private $isLead;
	private $confirmLead;
	private $strRedirectURI;
	private $strRedirectURI_Segment;
	
	protected $intSecID = 3;
	
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
		// Gets ContentID from $_POST
		if(empty($intContentID) && !empty($_REQUEST['hotspot-id'])) $intContentID = $_REQUEST['hotspot-id'];
		
		parent::__construct(false,$this->intSecID,$intContentID);
		
		// Checks if CONFIRM LEAD cookie exists
		if(!isset($_COOKIE[PROJECT . '_confirmLead'])) {
			$this->confirmLead = false;
		} else {
			$this->confirmLead = true;
		}
		
		// Checks if IS LEAD cookie exists
		if(!isset($_COOKIE[PROJECT . '_isLead'])) {
			$this->isLead = false;
		} else {
			$this->isLead = true;
		}
		
		$this->objSmarty->assign('isLead',$this->isLead);
		$this->objSmarty->assign('confirmLead',$this->confirmLead);
		
		// Sets $strRedirectURI
		if(!empty($_POST['redirect-uri'])) {
			$this->strRedirectURI_Segment	= str_replace('/','_',$_POST['redirect-uri']);
			$this->strRedirectURI			= HTTP . $_POST['redirect-uri'];
		} elseif(!empty($_SESSION[PROJECT]['URI_SEGMENT'][4])) {
			$this->strRedirectURI_Segment 	= str_replace('_','/',$_SESSION[PROJECT]['URI_SEGMENT'][4]);
			$this->strRedirectURI			= HTTP . $this->strRedirectURI_Segment;
		} else {
			if(is_array($this->objData)) $this->objData = reset($this->objData);
			$this->strRedirectURI_Segment 	= str_replace('/','_',$this->objData->rel_blog[0]->permalink);
			$this->strRedirectURI			= $this->objData->rel_blog[0]->url_permalink;
		}
		
		// Checks if user is lead
		if(empty($_REQUEST['preview'])) $this->checkLead(true);

		// Sets TPL
		#$this->strTemplate = $strTemplate;
		if($boolRenderTemplate) $this->renderTemplate($strTemplate);
	}
	
	/**
	 * Checks if user is already confirmed in database
	 * 
	 * @param 	boolean $boolRedirect	Defines if returns BOOLEAN or redirects user to new page
	 * 
	 * @return	boolean
	 */
	private function checkLead($boolRedirect = false) {
		if($this->isLead) {
			if(!$boolRedirect) {
				return true;
			} elseif(!empty($this->strRedirectURI)) { 
				header("Location: " . $this->strRedirectURI);
				exit();
			} /*elseif(!empty($this->strTemplate)) {
				$this->renderTemplate($this->strTemplate);
				die();
			} */else {
				return true;
			}
		} else {
			return false;
		}
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
		// Cheks if user is lead
		if(!$this->checkLead()) {
			
			// Sets return object
			$objResult	= null;
			$objResult->hideResend = false;
			
			// Sets SECTION as LEADS
			$objUser 	= new Main_controller(false,4);
			$strWhere	= 'name = "'.$_REQUEST['name'].'" AND ' . str_replace('aet_fl_leads.active = 1 AND ','',$objUser->strWhere);
			
			// If e-mail doesnt exists in database
			if(!$this->objModel->recordExists('id', 'aet_fl_leads',$strWhere) && !empty($_POST)) {
				// Inserts content
				unset($_POST['redirect-uri'],$_POST['hotspot-id']);
				$_POST['first_name'] = $_POST['name'];
				$objReturn = $objUser->insertMainContent($_POST);
				
			// If e-mail exists, check if is active
			} else {
				// Formats email info
				$objUser						= null;
				$objUser->objData				= null;
				$objUser->objData->name			= $_REQUEST['name'];
				$objUser->objData->first_name	= $_REQUEST['name'];
				
				// Defines hideResend smarty var
				$this->objSmarty->assign('hideResend',true);
				$objResult->hideResend = true;
				
				$objReturn = $this->objModel->recordExists('id', 'aet_fl_leads', 'active = 0 AND '. $strWhere);
			}
			
			// If is new e-mail or previous unactive e-mail
			if($objReturn) {
				// Sets CONFIRM LEAD cookie
				$intExpire = time()+60*60*24*365;
				setcookie(PROJECT . '_confirmLead',$_REQUEST['name'],$intExpire,'/');
				
				// Gets e-mail template
				/**
				 * @todo set PROJECT_ID
				 */
				$objMailContent 	= $this->objModel->select(array('email.id','email.subject','email.content_richtext','sys_template.filename'), 'aet_fl_email AS email',array('JOIN sec_rel_aet_fl_email_rel_aet_fl_destino AS rel','LEFT JOIN sys_template ON sys_template.id = email.mail_tpl'),'email.id = rel.parent_id AND rel.child_id = ' . $this->intContentID,array(),array(),0,null,'Row');
				
				// Sets template vars
				if($objMailContent) {
					$objMailContent->md5			= md5($objUser->objData->name);
					$objMailContent->redirect_uri	= $this->strRedirectURI_Segment;
				}
				
				// Sends confirmation e-mail
				$objMail = new sendMail_Controller();
				if($objMailContent && $this->objSmarty->templateExists($objMailContent->filename) && $objMail->sendMessage(array($objUser->objData->name => $objUser->objData->first_name),$objMailContent->subject, ROOT_TEMPLATE.$objMailContent->filename ,$objMailContent)) {
					$objResult->status			= 1;
					$objResult->alert->color	= 'blue';
					$objResult->alert->msg		= 'Enviamos uma mensagem para o seu e-mail!<br /><br />Acesse sua caixa postal e clique no link da mensagem para visualizar o conteúdo gratuito.';
				} else {
					$objResult->status			= 0;
					$objResult->alert->color	= 'red';
					$objResult->alert->msg		= 'Não foi possível enviar o e-mail.';
				}
				#$this->renderTemplate($this->strTemplate);
			} else {
				$objResult->status			= 2;
				$objResult->alert->color	= 'green';
				$objResult->alert->msg		= 'Seu e-mail já está cadastrado em nossa base de dados!';
			}
			
		} else {
			$objResult->status			= 2;
			$objResult->alert->color	= 'green';
			$objResult->alert->msg		= 'Seu e-mail já está cadastrado em nossa base de dados!';
		}
		
		echo json_encode($objResult);
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
		// Cheks if user is lead
		$this->checkLead(true);
		
		// Checks URL params
		if(empty($_SESSION[PROJECT]['URI_SEGMENT'][3])) {
			header("Location: " . $this->strRedirectURI);
			exit();
		}
		
		// Sets IS LEAD cookie
		$intExpire = time()+60*60*24*365;
		if(setcookie(PROJECT . '_isLead',true,$intExpire,'/')) {
			
			// Unsets CONFIRM LEAD cookie
			setcookie(PROJECT . '_confirmLead',true,1,'/');
			
			// Updates lead status
			if(!$this->objModel->update('aet_fl_leads',array('active' => 1),'MD5(name) = "' . $_SESSION[PROJECT]['URI_SEGMENT'][3] . '"')) {
				
				// If error, resets cookies
				setcookie(PROJECT . '_isLead',true,1,'/');
				setcookie(PROJECT . '_confirmLead',true,$intExpire,'/');
			}
			
			header("Location: " . HTTP . $this->strRedirectURI_Segment);
			exit();
		} else {
			
			// Retries set cookie
			if($this->tryCookie <= 3) {
				$this->confirm();
			} else {
				header("Location: " . HTTP . $this->strRedirectURI_Segment . '/?error=2');
				exit();
			}
		}
	}
}
?>
