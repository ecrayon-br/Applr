<?php
class Campanhas_controller extends Main_Controller {

	private $tryConfirm = 0;
	
	protected $intSecID = 6;
	protected $objMail;
	protected $objList;
	
	protected $strErrorMail = 'diegotf@gmail.com';
	protected $discardErrors = false;
	
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
	
	/**
	 * 
	 * @todo Test CRONTAB setup
	 * @todo Implement CRONTAB delete
	 */
	public function send($strPermalink = '',$boolSetTask = true) {
		if(empty($strPermalink)) $strPermalink = $_SESSION[PROJECT]['URI_SEGMENT'][3];
		if(empty($strPermalink)) return false;
		
		// CAMPANHA
		// Gets URI SEGMENT specific and formats content
		$contentControl = false;
		foreach($this->objData AS $intKey => $objData) {
			if($objData->permalink == $strPermalink) {
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
		
		// Iterates through all e-mails on this campaign
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
					$strWhere = 'rel_ctn_0.read_bool = 1 AND rel_tbl_email_report.child_id = ' . $intPrevMailID . ' AND datediff(now(),rel_ctn_0.date_expire) = ' . $objMail->timesheet_int . (!is_null($objAB_Result) ? ' AND aet_fl_leads.id NOT IN (SELECT DISTINCT rel_leads FROM aet_fl_email_report WHERE rel_email = ' . $objMail->id . ')' : ''); 
					$this->objList = new Leads_controller($strWhere);
				break;
			}
			
			// Sets previous e-mail ID
			$intPrevMailID = $objMail->id;
			
			// If there's no destinatary, gets next mail iteration
			if(empty($this->objList->objData)) continue;
			
			
			// A/B Test
			// Gets AB_Test result, if exists
			$objAB_Result = $this->getAB_Result($objMail->id);
			if(!empty($objMail->subject_02)) {
				
				// Sets Scheduled Job Action
				$strActionFile 	= 'cronjob-send-ab_test-result-mail.php';
				$strAction		= SYS_ROOT . $strActionFile . ' permalink=' . $strPermalink;
				
				// If e-mails must be send to AB Testing List
				if(is_null($objAB_Result)) {

					// Clones objList and shuffles
					$objList			= $this->objList->objData;
					shuffle(shuffle($objList));
					
					// Defines A/B slice
					$intSlice	= ceil(count($this->objList->objData) * 0.1);
					$objList_A	= array_slice($objList,0,$intSlice);
					$objList_B	= array_slice($objList,($intSlice*-1));
					
					// Sends message to full A_list
					$this->sendMailToList($objMail,$objList_A,$objMail->subject,1);
					
					// Sends message to full B_list
					if(count($objList) > 1) $this->sendMailToList($objMail,$objList_B,$objMail->subject_02,2);
					
					if($boolSetTask) {
						// Gets php.exe path
						$objFinder 	= new FileFinder();
						$strPath	= $objFinder->find();
							
						// Sets Scheduled Job Time
						$intTime	= time() + 300;
							
						// Register crontab / Task Scheduler action
						$strOS = strtoupper(substr(PHP_OS, 0, 3));
						switch($strOS) {
							case 'WIN':
								// Sets Task Scheduler job
								$objService = new X_Scheduler_Ms_Service();
								$objService->scheduleCommand($strPath, $intTime, 1, 300, $strAction);
								$objService->register(PROJECT . ' - AB Test - ' . $objMail->id);
								break;
					
							default:
								// Sets CronTab job
								$strCronJob = date('i H d m',$intTime); #(date('i') + 2) . ' ' . (date('H')) . date(' d m ') . (date(' N') == 7 ? 0 : date('N'));
								$objCronTab = new CrontabManager();
								$objJob		= $objCronTab->newJob();
								$objJob->on($strCronJob)->doJob('MAILTO=' . $this->$strErrorMail)->doJob($strPath . ' ' . $strAction . ($this->discardErrors ? ' >/dev/null 2>&1' : ''),null,true);
								$objCronTab->save();
								break;
						}
					}

				// If e-mail has already been sent to A/B Testing List, sends it to remaining recipients
				} elseif($objAB_Result->is_read == 1) {
					
					// Sends message to full list
					$this->sendMailToList($objMail,$this->objList->objData,($objAB_Result->subject_int == 1 ? $objMail->subject : $objMail->subject_02),$objAB_Result->subject_int);
					
					// Deletes task
					$strOS = strtoupper(substr(PHP_OS, 0, 3));
					switch($strOS) {
						case 'WIN':
							// Deletes Task Scheduler job
							$objService = new X_Scheduler_Ms_Service();
							$objService->deleteTask(PROJECT . ' - AB Test - ' . $objMail->id);
						break;
						
						default:
							/*
							// Reads all crontab jobs
							exec('crontab -l',$arrJobs);
							
							// Removes AB_Test Job from list
							$arrKeepJobs = array();
							foreach($arrJobs AS $strJob) {
								if(strpos($strJob,$strActionFile) === false) {
									$arrKeepJobs[] = $strJob;
								}
							}
							*/
						break;
					}
				
				}
				
			// Normal
			} else {
				// Sends message to full leads list
				$this->sendMailToList($objMail);
			}
			
			
		}
		
	}
	
	private function getAB_Result($intMail) {
		if(empty($intMail) || !is_numeric($intMail)) return false;
		
		$objResult = $this->objModel->select(array('rel_email','subject_int','SUM(read_bool) AS is_read','SUM(click_bool) AS is_click'),'aet_fl_email_report',array(),'rel_email = ' . $intMail,'is_read DESC, is_click DESC, subject_int ASC','subject_int',0,null,'Row');
		
		return $objResult;
	}
	
	private function sendMailToList($objMail,$objList = null,$strSubject = '',$intSubject = 1) {
		if(empty($objMail)) return false;
		if(!is_array($objList) && !is_object($objList)) $objList = $this->objList->objData;
		if(empty($objList)) return false;
		if(empty($strSubject)) $strSubject = $objMail->subject;
		if($intSubject != 1 && $intSubject != 2) $intSubject = 1;
		
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
									'name' 			=> date('Ymd') . ' | ' . $objLead->name . ' | ' . $objMail->name . ' | ' . $strSubject,
									'rel_leads' 	=> $objLead->id,
									'read_bool' 	=> 0,
									'click_bool'	=> 0,
									'rel_email'		=> $objMail->id,
									'subject_int'	=> $intSubject
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
		
		$arrUpdateData	= array(
							'active' 		=> 1,
							'date_expire' 	=> date('Ymd'), # . (date('d') - 1), #date('Ymd')
							'read_bool'		=> 1,
							'click_bool'	=> intval(!$boolDisplay)
							);
		
		if( $this->objModel->update('aet_fl_email_report',$arrUpdateData,'MD5(CONCAT(rel_email,"-",rel_leads)) = "'. $strMD5 .'"') !== false ) {
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
