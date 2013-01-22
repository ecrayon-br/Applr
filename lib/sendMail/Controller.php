<?php
class sendMail_Controller extends Controller {
	
	public 		$arrRecipient		= array();
	public		$strSubject			= '';
	public 		$strHTML			= '';
	public 		$strEncoding		= 'UTF-8';
	
	private 	$strHost			= 'mail.ecrayon.com.br';
	private		$boolHostAuth		= false;
	private 	$strHostAuthUser	= 'contato@ecrayon.com.br';
	private 	$strHostAuthPwd		= '';
	private 	$strSenderMail 		= 'contato@ecrayon.com.br';
	private		$strSenderName		= 'ECRAYON Tecnologia Criativa';
    
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
	 * @todo 		Define error treatment and error messages return
	 * 
	 */
	public function _construct($strEncoding = 'UTF-8') {
		if(!is_string($strEncoding) || empty($strEncoding))	return false;
		
		$this->setAuth(EMAIL_AUTH);
		$this->setHost(EMAIL_AUTH_HOST);
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
		if(!is_boolean($boolAuth) && $boolAuth !== 0 && $boolAuth !== 1)	return false;
		
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
		if(is_string($arrRcpt) && !empty($arrRcpt))			$arrRpct = array($arrRcpt => $arrRcpt);
		if(!is_array($arrRcpt) || count($arrRcpt) <= 0)		return false;
		
		$boolTo = false;
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
		if(is_file($strHTML)) $strHTML = file_get_contents($strHTML);
		
		// Replaces array of variables
		if(is_array($arrVars)) {
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
					foreach($arrTemp AS $strAddress => $strName) {
						$this->arrRecipient[$strMode][$strAddress]->name = utf8_encode($strName);
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
			foreach($this->arrRecipient['To'] AS $strAddress => &$objData) {
				$objData->status = mail($strAddress,$this->strSubject,$this->strHTML,$strHeader . "To: " . $objData->name . '<' . $objData->address . '>'."\n");
			}
			
			// Sends 'Bcc' messages
			foreach($this->arrRecipient['Bcc'] AS $strAddress => &$objData) {
				$objData->status = mail($strAddress,$this->strSubject,$this->strHTML,$strHeader . "To: " . $objData->name . '<' . $objData->address . '>'."\n");
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
								'password' 	=> $this->strHostAuthPwd
							);
			$objSMTP = PEAR_ApplrSendMail::factory('smtp',$arrParams);
			
			// Sends 'To' messages
			foreach($this->arrRecipient['To'] AS $strAddress => &$objData) {
				$arrHeaders['To']	= $objData->name . '<' . $objData->address . '>';
				$objData->status	= (PEAR::isError($objSMTP->send($strAddress, $arrHeaders, $this->strHTML)) ? false : true);
			}
			
			// Sends 'Bcc' messages
			foreach($this->arrRecipient['Bcc'] AS $strAddress => &$objData) {
				$arrHeaders['To']	= $objData->name . '<' . $objData->address . '>';
				$objData->status	= (PEAR::isError($objSMTP->send($strAddress, $arrHeaders, $this->strHTML)) ? false : true);
			}
		}
	}
}
?>