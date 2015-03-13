<?php
class sendMail_Controller extends Controller {
	
	public 		$arrRecipient		= array();
	public		$strSubject			= '';
	public 		$strHTML			= '';
	public 		$strEncoding		= 'UTF-8';
	public		$objReport;
	
	private 	$strHost			= 'mail.ecrayon.com.br';
	private		$boolHostAuth		= false;
	private 	$strHostAuthUser	= 'contato@ecrayon.com.br';
	private 	$strHostAuthPwd		= '';
	private 	$strSenderMail 		= 'contato@ecrayon.com.br';
	private		$strSenderName		= 'ECRAYON Tecnologia Criativa';
	private		$intPort			= 25;
    
	/**
	 * Class constructor
	 *
	 * @param 		string 	$strEncoding	Encoding label
	 * 
	 * @return		void
	 * 
	 * @since 		2013-01-18
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function __construct($strEncoding = 'UTF-8') {
		if(!is_string($strEncoding) || empty($strEncoding))	return false;

		parent::__construct();
		
		$this->setAuth((boolean) EMAIL_AUTH);
		$this->setHost(EMAIL_AUTH_HOST);
		$this->setPort(EMAIL_AUTH_PORT);
		$this->setAuthData(EMAIL_AUTH_USER,EMAIL_AUTH_PWD);
		$this->setEncoding($strEncoding);
	}
	
    /**
	 * Defines SMTP host
	 *
	 * @param 		string 	$strHost	SMTP host address
	 * 
	 * @return		boolean	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setHost($strHost) {
		if(!is_string($strHost) || empty($strHost))	return false;
		
		$this->strHost 	= $strHost;
		
		return true;
	}
	
    /**
	 * Defines SMTP host
	 *
	 * @param 		string 	$strHost	SMTP host address
	 * 
	 * @return		boolean	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setPort($intPort) {
		if(!is_numeric($intPort) || empty($intPort))	return false;
		
		$this->intPort 	= $intPort;
		
		return true;
	}
	
    /**
	 * Defines authentication mode
	 *
	 * @param 		boolean 	$boolAuth	Authentication mode
	 * 
	 * @return		boolean	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setAuth($boolAuth) {
		if(!is_bool($boolAuth) && $boolAuth !== 0 && $boolAuth !== 1)	return false;
		
		$this->boolHostAuth 	= $boolAuth;
		
		return true;
	}
	
    /**
	 * Defines login and password for authentication
	 *
	 * @param 		string 	$strUser	Login for authentication
	 * @param 		string 	$strPwd		Password for authentication
	 * 
	 * @return		boolean	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setAuthData($strUser,$strPwd) {
		if(!$this->boolHostAuth)					return false;
		if(!is_string($strUser) || empty($strUser))	return false;
		if(!is_string($strPwd) 	|| empty($strPwd))	return false;
		
		$this->strHostAuthUser 	= $strUser;
		$this->strHostAuthPwd 	= $strPwd;
		
		return true;
	}
	
    /**
	 * Defines message charset / encoding
	 *
	 * @param 		string 	$strEncoding	Encoding label
	 * 
	 * @return		boolean	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setEncoding($strEncoding) {
		if(!is_string($strEncoding) || empty($strEncoding))	return false;
		
		$this->strEncoding 	= $strEncoding;
		
		return true;
	}
	
	/**
	 * Defines sender name and e-mail address
	 *
	 * @param 		string 	$strMail	Sender e-mail address
	 * @param 		string 	$strName	Sender name or label
	 * 
	 * @return		boolean	
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setFrom($strMail,$strName) {
		if(!is_string($strMail) || empty($strMail))	return false;
		if(!is_string($strName) || empty($strName))	return false;
		
		$this->strSenderMail 	= $strMail;
		$this->strSenderName 	= $strName;
		
		return true;
	}
	
	/**
	 * Defines e-mail recipient
	 *
	 * @param 		mixed 	$arrRcpt	Array of e-mail recipients. Array indexes defines e-mail address; indexes contents defines recipient name os label.
	 * 
	 * @return		boolean
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setRecipient($arrRcpt) {
		if(is_string($arrRcpt) && !empty($arrRcpt))					$arrRcpt = array('To' => array($arrRcpt => $arrRcpt));
		if(!empty($arrRcpt['To']) && !is_array($arrRcpt['To'])) 	return false;
		if(!empty($arrRcpt['Bcc']) && !is_array($arrRcpt['Bcc'])) 	return false;
		if(empty($arrRcpt['To']) && empty($arrRcpt['Bcc']) && (!is_array($arrRcpt) || empty($arrRcpt))) 	return false;
		
		// Resets recipients
		$this->arrRecipient['To']	= array();
		$this->arrRecipient['Bcc']	= array();
		
		// If defined "To" block
		$boolTo = false;
		if(array_key_exists('To',$arrRcpt) && is_array($arrRcpt['To'])) {
			$boolTo = true;
			
			foreach($arrRcpt['To'] AS $strAddress => $strName) {
				if($this->checkEmailSyntax($strAddress)) {
					if(!is_string($strName) || empty($strName)) 	$strName = $strAddress;
					$this->arrRecipient['To'][$strAddress]->name	= $strName;
					$this->arrRecipient['To'][$strAddress]->email	= $strAddress;
				}
			}
		}

		// If defined "Bcc" block
		$boolBcc = false;
		if(array_key_exists('Bcc',$arrRcpt) && is_array($arrRcpt['Bcc'])) {
			$boolBcc = true;
			
			foreach($arrRcpt['Bcc'] AS $strAddress => $strName) {
				if($this->checkEmailSyntax($strAddress)) {
					if(!is_string($strName) || empty($strName)) 	$strName = $strAddress;
					$this->arrRecipient['Bcc'][$strAddress]->name	= $strName;
					$this->arrRecipient['Bcc'][$strAddress]->email	= $strAddress;
				}
			}
		}
		
		// If blocks are not defined
		if(!$boolTo && !$boolBcc) {
			foreach($arrRcpt AS $strAddress => $strName) {
				if($this->checkEmailSyntax($strAddress)) {
					if(!is_string($strName) || empty($strName)) $strName = $strAddress;
					if(!$boolTo) {
						$this->arrRecipient['To'][$strAddress]->name	= $strName;
						$this->arrRecipient['To'][$strAddress]->email	= $strAddress;
						$boolTo = true;
					} else {
						$this->arrRecipient['Bcc'][$strAddress]->name	= $strName;
						$this->arrRecipient['Bcc'][$strAddress]->email	= $strAddress;
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Defines message subject
	 *
	 * @param 		string 	$strSubject	Message subject
	 * 
	 * @return		boolean
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setSubject($strSubject) {
		if(!is_string($strSubject) || empty($strSubject))	return false;
		
		$this->strSubject	= $strSubject;
		
		return true;
	}
	
	/**
	 * Defines message body
	 *
	 * @param 		string 	$strHTML	Message body or file path to message template
	 * @param 		array	$arrVars	Array of variables to be replaced on message body. Array indexes defines variable name; indexes contents defines variable content/meaning. Variables on message body must be written between `{` `}`, as in `{userName}`
	 * @param 		boolean	$boolGET	Defines if variables from superglobal $_GET  must be replaced on message body. Variables on message body must be written between `{` `}`, as in `{userName}`
	 * @param 		boolean	$boolPOST	Defines if variables from superglobal $_POST must be replaced on message body. Variables on message body must be written between `{` `}`, as in `{userName}`
	 * 
	 * @return		boolean
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setHTML($strHTML, $arrVars = null, $boolGET = false, $boolPOST = false) {
		if(!is_string($strHTML) || empty($strHTML))	return false;
		
		Controller::unsecureGlobals();
		
		// If $strHTML is a file path, get file's content
		if(is_file($strHTML)) $strHTML = utf8_decode(file_get_contents($strHTML));
		
		// Replaces array of variables
		if(is_array($arrVars) || is_object($arrVars)) {
			foreach($arrVars AS $intKey => $strVar) {
				$strHTML 	= str_replace('#'.$intKey.'#',nl2br($strVar),$strHTML);
			}
		}
		
		// Replaces $_POST variables
		if($boolPOST) {
			foreach($_POST AS $intKey => $strVar) {
				$strHTML 	= str_replace('#'.$intKey.'#',nl2br($strVar),$strHTML);
			}
		}
		
		// Replaces $_GET variables
		if($boolGET) {
			foreach($_GET AS $intKey => $strVar) {
				$strHTML 	= str_replace('#'.$intKey.'#',nl2br($strVar),$strHTML);
			}
		}
		
		// Replaces CONSTANT variables
		$arrData = get_defined_constants();
		foreach($arrData AS $intKey => $strVar) {
			$strHTML 	= str_replace('#'.$intKey.'#',nl2br($strVar),$strHTML);
		}
		
		$this->strHTML		= $strHTML;
		
		return true;
	}
	
	/**
	 * Gets message charset / encoding
	 * 
	 * @return		string
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function getEncoding() {
		return $this->strEncoding;
	}
	
	/**
	 * Gets message subject
	 * 
	 * @return		string
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function getSubject() {
		return $this->strSubject;
	}
	
	/**
	 * Gets message body
	 * 
	 * @return		string
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function getHTML() {
		return $this->strHTML;
	}
	
	/**
	 * Send message to recipients
	 *
	 * @param 		array 	$arrRcpt	Array of e-mail recipients. Array indexes defines e-mail address; indexes contents defines recipient name os label.
	 * @param 		string 	$strSubject	Message subject
	 * @param 		string 	$strHTML	Message body
	 * @param 		array 	$arrVars	Array of variables to be replaced on message body. Array indexes defines variable name; indexes contents defines variable content/meaning. Variables on message body must be written between `{` `}`, as in `{userName}`
	 * 
	 * @return 		boolean
	 * 
	 * @since		2008-12-03
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function sendMessage($arrRcpt = null, $strSubject = null, $strHTML = null, $arrVars = null) {
		// Defines message recipient
		if(!is_null($arrRcpt)) 		$this->setRecipient($arrRcpt);
		
		// Defines message subject
		if(!is_null($strSubject))	$this->setSubject($strSubject);
		
		// Defines message body
		if(!is_null($strHTML))		$this->setHTML($strHTML,$arrVars);
		
		// Convert message data due to charset / encoding
		switch(strtoupper($this->strEncoding)) {
			case 'UTF-8':
				$this->strSubject 		= utf8_encode($this->strSubject);
				$this->strHTML 			= utf8_encode($this->strHTML);
				$this->strSenderName	= utf8_encode($this->strSenderName);
				
				foreach($this->arrRecipient AS $strMode => $arrTemp) {
					foreach($arrTemp AS $strAddress => $objData) {
						$this->arrRecipient[$strMode][$strAddress]->name = utf8_encode($objData->name);
					}
				}
			break;
		
			default:
			break;
		}
		
		// If !$this->boolHostAuth, send message using PHP::mail() function
		if(!$this->boolHostAuth) {
			// Creates message header
			$strHeader  = "MIME-Version: 1.0\n";
			$strHeader .= "Content-type: text/html; charset=".$this->strEncoding."\n";
			$strHeader .= "From: ".$this->strSenderName.'<'.$this->strSenderMail.'>'."\n";
			$strHeader .= "Reply-To: ".$this->strSenderName.'<'.$this->strSenderMail.'>'."\n";
			
			// Sends 'To' messages
			foreach($this->arrRecipient['To'] AS $strAddress => $objData) {
				$this->objReport->To->$strAddress = mail($strAddress,$this->strSubject,$this->strHTML,$strHeader . "To: " . $objData->name . '<' . $objData->address . '>'."\n");
			}
			
			// Sends 'Bcc' messages
			foreach($this->arrRecipient['Bcc'] AS $strAddress => $objData) {
				$this->objReport->Bcc->$strAddress = mail($strAddress,$this->strSubject,$this->strHTML,$strHeader . "To: " . $objData->name . '<' . $objData->address . '>'."\n");
			}
		} else {
			// Sends message through SMTP authentication server
			$arrHeaders = array(	
								'From' 			=> $this->strSenderMail,
								'To'			=> '',
								'Subject'		=> $this->strSubject,
								'Date'			=> date('r'),
								'Return-Path'	=> $this->strSenderMail,
								'Reply-To' 		=> $this->strSenderMail,
								'MIME-Version' 	=> '1.0',
								'Content-type' 	=> 'text/html; charset=utf-8'
							);
			$arrParams	= array(
								'host' 		=> $this->strHost,
								'auth' 		=> true,
								'username' 	=> $this->strHostAuthUser,
								'password' 	=> $this->strHostAuthPwd,
								'persist'	=> true,
								'port'		=> $this->intPort
							);
			$objSMTP = PEAR_ApplrSendMail::factory('smtp',$arrParams);
			
			if(!PEAR::isError($objSMTP)) {
				// Sends 'To' messages
				foreach($this->arrRecipient['To'] AS $strAddress => $objData) {
					$arrHeaders['To']					= $objData->name . ' <' . $objData->email . '>';
					$objSend							= $objSMTP->send($strAddress, $arrHeaders, $this->strHTML);
					$this->objReport->To->$strAddress	= (PEAR::isError($objSend) ? false : true);
					
					if(count($this->arrRecipient['To']) == 1 && count($this->arrRecipient['Bcc']) == 0) return $this->objReport->To->$strAddress;
				}
				
				// Sends 'Bcc' messages
				foreach($this->arrRecipient['Bcc'] AS $strAddress => $objData) {
					$arrHeaders['To']					= $objData->name . ' <' . $objData->email . '>';
					$objSend							= $objSMTP->send($strAddress, $arrHeaders, $this->strHTML);
					$this->objReport->Bcc->$strAddress	= (PEAR::isError($objSend) ? false : true);
				}
			} else {
				define('ERROR_MSG','Error on $this->sendMessage!');
				return false;
			}
		}
	}
	
	/**
	 * Prints sending status report
	 * 
	 * @param	integer	$intType	Report type: 0 => 'All'; 1 => 'To' recipients; 2 => 'Bcc' recipients
	 * @param	boolean	$boolPrint	Defines whether report is printed on screen or not 
	 * 
	 * @return 	string
	 * 
	 * @since	2013-01-22
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function getReport($intType = 0,$boolPrint = true) {
		if($intType < 0 || $intType > 2) return false;
		if(!is_bool($boolPrint)) return false;
		
		$strResult = '';
		
		// 'To' recipients
		if($intType == 0 || $intType == 1) {
			$strResult .= '<h1>"To" Recipients</h1>';
			$strResult .= '<h2>Total messages: ' . count($this->objReport->To) . '<br />Sent messages: #SENT#<br />Unsent messages: #UNSENT#</h2>';
			
			$intSent	= 0;
			$intUnsent	= 0;
			foreach($this->objReport->To AS $strAddress => $boolStatus) {
				if($boolStatus) {
					$intSent++;
					$strResult .= $strAddress . ' : OK';
				} else {
					$intUnsent++;
					$strResult .= $strAddress . ' : Error';
				}
			}
			
			$strResult = str_replace(array('#SENT#','#UNSENT#'), array($intSent,$intUnsent), $strResult);
		}
		$strResult .= '<hr />';
		
		// 'Bcc' recipients
		if($intType == 0 || $intType == 2) {
			$strResult .= '<h1>"Bcc" Recipients</h1>';
			$strResult .= '<h2>Total messages: ' . count($this->objReport->Bcc) . '<br />Sent messages: #SENT#<br />Unsent messages: #UNSENT#</h2>';
		
			$intSent	= 0;
			$intUnsent	= 0;
			foreach($this->objReport->Bcc AS $strAddress => $boolStatus) {
				if($boolStatus) {
					$intSent++;
					$strResult .= $strAddress . ' : OK';
				} else {
					$intUnsent++;
					$strResult .= $strAddress . ' : Error';
				}
			}
		
			$strResult = str_replace(array('#SENT#','#UNSENT#'), array($intSent,$intUnsent), $strResult);
		}
		$strResult .= '<hr />';
		
		if($boolPrint) echo $strResult;
		
		return $strResult;
	}
}
?>