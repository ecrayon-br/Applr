<?php
class Hotspot_controller extends Blog_controller {
	
	private $tryCookie = 0;
	private $strRedirectURI;
	private $strRedirectURI_Segment;
	
	protected $intSecID = 3;
	protected $isLead;
	protected $confirmLead;
	
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
		// Gets ContentID from $_POST
		if(empty($intContentID) && !empty($_REQUEST['hotspot-id'])) $intContentID = $_REQUEST['hotspot-id'];
		
		parent::__construct(false);
		
		// Sets $strRedirectURI
		if(!empty($_POST['redirect-uri'])) {
			$this->strRedirectURI_Segment	= str_replace('/','_',$_POST['redirect-uri']);
			$this->strRedirectURI			= HTTP . $_POST['redirect-uri'];
		} elseif(!empty($_SESSION[PROJECT]['URI_SEGMENT'][5])) {
			$this->strRedirectURI_Segment 	= str_replace('_','/',$_SESSION[PROJECT]['URI_SEGMENT'][5]);
			$this->strRedirectURI			= HTTP . $this->strRedirectURI_Segment;
		} else {
			if(is_array($this->objData)) 	$this->objData = reset($this->objData);
			$this->strRedirectURI_Segment 	= str_replace('/','_',$this->objData->rel_blog[0]->permalink);
			$this->strRedirectURI			= $this->objData->rel_blog[0]->url_permalink;
		}
		
		// Checks if user is lead
		if(empty($_REQUEST[PROJECT . '_preview'])) $this->checkLead(true);
		
		/*
		// Checks if CONFIRM LEAD cookie exists
		if(!isset($_COOKIE[PROJECT . '_confirmLead'])) {
			$this->confirmLead = false;
		} else {
			$this->confirmLead = true;
		}
		$this->objSmarty->assign('confirmLead',$this->confirmLead);
		
		// Checks if IS LEAD cookie exists
		if(!isset($_COOKIE[PROJECT . '_isLead'])) {
			$this->isLead = false;
		} else {
			$this->isLead = true;
		}
		$this->objSmarty->assign('isLead',$this->isLead);
		*/

		// Sets TPL
		if($boolRenderTemplate) $this->renderTemplate();
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
		// Checks mail syntax
		if(!$this->checkEmailSyntax($_REQUEST['name'])) {
			$objResult->status			= 0;
			$objResult->alert->color	= 'red';
			$objResult->alert->msg		= 'Endereço de e-mail inválido!';
			$objResult->redirectURI		= $this->strRedirectURI;

		// Checks if user is lead
		} elseif($this->checkLead()) {
			$objResult->status			= 2;
			$objResult->alert->color	= 'green';
			$objResult->alert->msg		= 'Seu e-mail já está cadastrado em nossa base de dados!';
			$objResult->redirectURI		= $this->strRedirectURI;
			
		} else {
			
			// Sets return object
			$objResult	= null;
			$objResult->hideResend = false;
			
			// Sets SECTION as LEADS
			$objUser 	= new Leads_controller('',false);
			$strWhere	= 'name = "'.strtolower($_REQUEST['name']).'" AND ' . str_replace('aet_fl_leads.active = 1 AND ','',$objUser->strWhere);
			
			// If e-mail doesnt exists in database
			if(!$this->objModel->recordExists('id', 'aet_fl_leads',$strWhere) && !empty($_POST)) {
				// Inserts content
				unset($_POST['redirect-uri'],$_POST['hotspot-id'],$_POST['preview']);
				$_POST['name'] = $_POST['first_name'] = strtolower($_POST['name']);
				$objReturn = $objUser->insertContent($_POST);
				
			// If e-mail exists, check if is active
			} else {
				// Formats email info
				$objUser						= null;
				$objUser->objData				= null;
				$objUser->objData->name	= $objUser->objData->first_name	= strtolower($_POST['name']);
				
				// Defines hideResend smarty var
				$this->objSmarty->assign('hideResend',true);
				$objResult->hideResend = true;
				
				$objReturn = $this->objModel->recordExists('id', 'aet_fl_leads', 'active = 0 AND '. $strWhere,1);
				
				if($objReturn) {
					$intAvatar = $this->objModel->recordExists('rel_avatar', 'aet_fl_leads', 'active = 0 AND '. $strWhere,1);
					$this->intContentID = $this->objModel->select('rel_hotspot.parent_id', 'sec_rel_aet_fl_destino_rel_aet_fl_avatar AS rel_hotspot',array('JOIN sec_rel_aet_fl_leads_rel_aet_fl_avatar AS rel_leads'),array('rel_hotspot.child_id = rel_leads.child_id AND rel_leads.parent_id = "' . $objReturn . '" AND rel_leads.child_id = "' . $intAvatar . '"'),array(),array(),0,null,'One');
				}
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
				$objMail = new Email_controller();
				$objMail->setWhere($objMail->objSection->table_name . '.sequence_bool = 1 AND ' . $objMail->objSection->table_name . '.first_bool = 1 AND rel_hotspot.parent_id = ' . $this->intContentID);
				$objMail->getSectionContent();
				$objMailContent = reset($objMail->objData);
				
				if(!empty($objMail->objData) && $this->objSmarty->templateExists($objMailContent->mail_tpl_filename)) {

					// Sets template vars
					$objMailContent->md5			= md5($objUser->objData->name);
					$objMailContent->redirect_uri	= str_replace('/','_',$objMailContent->rel_blog[0]->permalink);

					// Sets Email Report object
					$this->objMailReport = new EmailReport_controller();
					
					// Inserts mail_report data
					$arrReportData = array(
							'name' 			=> date('Ymd') . ' | ' . $objUser->objData->name . ' | ' . $objMailContent->name . ' | ' . $objMailContent->subject,
							'rel_leads' 	=> $objReturn,
							'read_bool' 	=> 0,
							'click_bool'	=> 0,
							'rel_email'		=> $objMailContent->id,
							'subject_int'	=> 1
					);
					$arrReportData['permalink'] = $this->permalinkSyntax($arrReportData['name']);
					
					if($this->objMailReport->insertContent($arrReportData)) {
				
						// Sends confirmation e-mail
						$objSendMail = new sendMail_Controller();
						
						if($objSendMail->sendMessage(array($objUser->objData->name => $objUser->objData->first_name),$objMailContent->subject, ROOT_TEMPLATE.$objMailContent->mail_tpl_filename ,$objMailContent)) {
							$objResult->status			= 1;
							$objResult->alert->color	= 'blue';
							$objResult->alert->msg		= 'Enviamos uma mensagem para o seu e-mail!<br /><br />Acesse sua caixa postal e clique no link da mensagem para visualizar o conteúdo gratuito.';
						} else {
							$objResult->status			= 0;
							$objResult->alert->color	= 'red';
							$objResult->alert->msg		= 'Não foi possível enviar o e-mail.';
						}
						
					} else {
						$objResult->status			= 0;
						$objResult->alert->color	= 'red';
						$objResult->alert->msg		= 'Não foi possível enviar o e-mail.';
					}
					
				} else {
					// Unsets CONFIRM LEAD cookie
					$intExpire = time()+60*60*24*365;
					setcookie(PROJECT . '_confirmLead',$objUser->objData->name,1,'/');
					
					// Unets LEAD cookie
					$intExpire = time()+60*60*24*365;
					setcookie(PROJECT . '_isLead',true,1,'/');
					
					$objResult->status			= 0;
					$objResult->alert->color	= 'red';
					$objResult->alert->msg		= 'Não foi possível enviar o e-mail.';
				}
				
			} else {
				// Sets LEAD cookie
				$intExpire = time()+60*60*24*365;
				setcookie(PROJECT . '_isLead',true,$intExpire,'/');
				
				$objResult->status			= 2;
				$objResult->alert->color	= 'green';
				$objResult->alert->msg		= 'Seu e-mail já está cadastrado em nossa base de dados!<br /><br />Você já pode visualizar os conteúdos exclusivos! Aproveite!';
				$objResult->redirectURI		= $this->strRedirectURI;
			}
			
		}
		
		// If is AJAX request
		if( $this->isAjaxRequest() ) {
			echo json_encode($objResult);
		} else {
			header("Location: " . $this->strRedirectURI);
			exit();
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
		// Cheks if user is lead
		$this->checkLead(true);
		
		// Checks URL params
		if(empty($_SESSION[PROJECT]['URI_SEGMENT'][3]) || empty($_SESSION[PROJECT]['URI_SEGMENT'][4])) {
			header("Location: " . $this->strRedirectURI);
			exit();
		}
		
		// Sets local vars
		$strMD5		= $_SESSION[PROJECT]['URI_SEGMENT'][4];
		$intEmail	= $_SESSION[PROJECT]['URI_SEGMENT'][3];
		
		// Gets LEADS id
		$intLead	= $this->recordExists('id', 'aet_fl_leads','MD5(name) = "' . $strMD5 . '"',true);
		if(!$intLead) { $intLead = $this->recordExists('rel_leads', 'aet_fl_email_report','MD5(CONCAT(rel_email,"-",rel_leads)) = "'. $strMD5 .'"',true); } 
		
		// Checks if LEADS-EMAIL matches
		if( ($intReport = $this->recordExists('id','aet_fl_email_report','rel_leads = ' . $intLead . ' AND rel_email = "' . $intEmail . '"',1)) !== false) {
			
			// Updates email_report AND lead status
			if(
				$this->objModel->update('aet_fl_email_report',array('active' => 1, 'date_expire' => date('Ymd'), 'read_bool' => 1, 'click_bool' => 1),'id = ' . $intReport) && 
				$this->objModel->update('aet_fl_leads',array('active' => 1),'id = ' . $intLead)
			) {
			
				// Sets IS LEAD cookie
				$intExpire = time()+60*60*24*365;
				if(setcookie(PROJECT . '_isLead',true,$intExpire,'/')) {
				
					// Unsets CONFIRM LEAD cookie
					setcookie(PROJECT . '_confirmLead',true,1,'/');
					
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
		
		header("Location: " . HTTP . $this->strRedirectURI_Segment);
		exit();
	}
}
?>
