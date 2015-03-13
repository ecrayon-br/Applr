<?php
class Campanhas_controller extends Main_Controller {

	private $tryConfirm = 0;
	
	protected $intSecID = 6;
	protected $objMail;
	protected $objList;
	
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
		parent::__construct($boolRenderTemplate,$this->intSecID,$intContentID);
	}
	
	public function send() {
		if(empty($_SESSION[PROJECT]['URI_SEGMENT'][3])) return false;
		
		// CAMPANHA
		// Gets URI SEGMENT specific and formats content
		$contentControl = false;
		foreach($this->objData AS $intKey => $objData) {
			if($objData->permalink == $_SESSION[PROJECT]['URI_SEGMENT'][3]) {
				$this->objData = clone $objData;
				$this->objData->rel_produto = reset($this->objData->rel_produto);
				$this->objData->rel_email_config = reset($this->objData->rel_email_config);
				$contentControl = true;
				break;
			}
		}
		if(!$contentControl) return false;
		
		// MENSAGENS e AGENDAMENTOS
		// Gets EMAILS content
		$this->objMail = new Email_controller($this->objData->id);
		
		// Sets Email Report object
		$this->objMailReport = new EmailReport_controller();
		
		// Iterate through all e-mails on this campaign
		$intPrevMailID	= 0;
		foreach($this->objMail->objData AS $intMailKey => $objMail) {
			// Checks if e-mail templates exists
			if(!$this->objSmarty->templateExists($objMail->mail_tpl_filename)) { $intPrevMailID = $objMail->id; continue; }

			// LISTA
			// Gets user's address list
			switch($objMail->timesheet_int) {
				case 0:
				case null:
					$this->objList = new Leads_controller('rel_tbl_0.child_id is null');
				break;
				
				default:
					$this->objList = new Leads_controller('rel_ctn_0.read_bool = 1 AND rel_tbl_email_report.child_id = ' . $intPrevMailID . ' AND datediff(now(),rel_ctn_0.date_expire) = ' . $objMail->timesheet_int);
				break;
			}
			
			// Sets previous e-mail ID
			$intPrevMailID = $objMail->id;
			
			// A/B Test
			if(!empty($objMail->subject_02)) {
				#echo '<pre>'; print_r($objMail);
				#echo '<pre>'; print_r($this->objList->objData); die();
				
				// Clones objList and shuffles
				$objList	= $this->objList->objData;
				shuffle(shuffle($objList));
				
				// Defines A/B slice
				$intSlice	= ceil(count($this->objList->objData) * 0.1);
				$objList_A	= array_slice($objList,0,$intSlice);
				$objList_B	= array_slice($objList,($intSlice*-1));
				
				/*
				echo '<pre>';
				print_r($objList);
				echo '<hr />';
				print_r($objList_A);
				echo '<hr />';
				print_r($objList_B);
				die();
				*/
				
				// Sends message to full A_list
				$this->sendMailToList($objMail,$objList_A,$objMail->subject);
				
				// Sends message to full B_list
				$this->sendMailToList($objMail,$objList_B,$objMail->subject_02);
				
			// Normal
			} else {
				// Sends message to full leads list
				$this->sendMailToList($objMail);
			}
			
			
		}
		
	}
	
	private function sendMailToList($objMail,$objList = null,$strSubject = '') {
		if(empty($objMail)) return false;
		if(!is_array($objList) && !is_object($objList)) $objList = $this->objList->objData;
		if(empty($objList)) return false;
		if(empty($strSubject)) $strSubject = $objMail->subject;
		
		// Sets objSendMail
		$objSendMail	= new sendMail_Controller();
		
		// Creates message blocks
		$intMsg 	= 0;
		$intBlock	= 0;
		$intSend	= 0;
		foreach($objList AS $objLead) {
			if($intMsg < $this->objData->rel_email_config->block_int) {
					
				/**
				 * @todo performs multi-thread
				 */
				if($this->objData->rel_email_config->conn_int == 1) {
					// Sets E-mail content
					/**
					 * @todo get 2nd level relationship data
					 */
					$objMailContent = null;
					$objMailContent->md5		= md5($objMail->id . '-' . $objLead->id);
					$objMailContent->name 		= $objLead->name;
					$objMailContent->first_name = $objLead->first_name;
					$objMailContent->url		= urlencode($this->objModel->select('CONCAT("'.HTTP.'","blog/",aet_fl_blog.permalink) AS url', 'aet_fl_email', array('JOIN sec_rel_aet_fl_email_rel_aet_fl_destino AS rel_1 ON rel_1.parent_id = aet_fl_email.id','JOIN sec_rel_aet_fl_destino_rel_aet_fl_blog AS rel_2 ON rel_2.parent_id = rel_1.child_id','JOIN aet_fl_blog ON aet_fl_blog.id = rel_2.child_id'),'aet_fl_email.id = ' . $objMail->id,array(),array(),0,null,'One'));
		
					// Sends e-mail - if error, retries `$this->objData->error_int` times
					do {
						// Delays error resend
						if($intSend > 0) sleep($this->objData->rel_email_config->delay_error_int);
							
						/**
						 * @todo set_time_limit
						*/
							
						// Send it
						$objSend = $objSendMail->sendMessage(array($objLead->name => $objLead->first_name),$strSubject, ROOT_TEMPLATE.$objMail->mail_tpl_filename ,$objMailContent);
							
						// If send succeed, inserts report
						if($objSend) {
							$arrReportData = array(
									'name' 		=> date('Ymd') . ' | ' . $objLead->name . ' | ' . $objMail->name,
									'rel_leads' => $objLead->id,
									'read_bool' => 0,
									'click_bool'=> 0,
									'rel_email'	=> $objMail->id
							);
							$arrReportData['permalink'] = $this->permalinkSyntax($arrReportData['name']);
							
							$this->objMailReport->insert($arrReportData);
						}
							
						$intSend++;
					} while($objSend !== true && $intSend < $this->objData->error_int);
		
				}
					
				$intMsg++;
			} else {
				$intMsg = 0;
				$intBlock++;
					
				// Delays message blocks
				if($intBlock > 0) sleep($this->objData->rel_email_config->delay_block_int);
			}
		}
	}

	public function confirm() {
		if(empty($_REQUEST['md5'])) 	return false;

		$strMD5 		= $_REQUEST['md5'];
		$boolDisplay 	= (!empty($_REQUEST['display']) ? true : false);
		$strURL 		= (!empty($_REQUEST['redirect-uri']) ? urldecode($_REQUEST['redirect-uri']) : '');
		
		if( $this->objModel->update('aet_fl_email_report',array('active' => 1, 'date_expire' => date('Ymd')),'MD5(CONCAT(rel_email,"-",rel_leads)) = "'. $strMD5 .'"') !== false ) {
			if(!$boolDisplay) {
				header("Location: " . $strURL);
			} else {
				header('Content-type:image/jpeg'); echo file_get_contents(HTTP . 'icone.jpg');
				exit();
			}
		} else {
			// Retries confirmation
			if($this->tryConfirm <= 3) {
				$this->confirm();
			} else {
				if(!$boolDisplay) {
					header("Location: " . HTTP . '/?error=2');
					exit();
				} else {
					header('Content-type:image/jpeg'); echo file_get_contents(HTTP . 'icone.jpg');
					exit();
				}
			}
		}
	}
}
?>
