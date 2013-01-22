<?php
class uploadFile_Controller extends Controller {
	
	public		$intError;
	
	private		$arrFile;
	private		$strFilter;
	private		$intMaxSize;
	private		$strDirectory;
	private		$strPrefix;
    
	/**
	 * Class constructor. Instantiates class object; sets $intMaxSize, $strDirectory and $strPrefix values.
	 * 
	 * @param		string	$strIndex		$_FILES index
	 * @param 		string	$strDirectory	Directory path relative to DOCUMENT_ROOT
	 * @param 		string	$strPrefix		New file's name prefix
	 * @param 		integer	$intMaxSize		File's max size
	 * 
	 * @return		void
	 *
	 * @subpackage 	Upload
	 * @since		2007-11-05
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 * @todo 		Validate method parameters
	 * 				Validate MIME-TYPE on $this->validateFile()
	 * 
	 */
	public function __construct($strIndex = '',$strDirectory = '',$strPrefix = null,$intMaxSize = 1000000000) {
		// Runs parent class constructor
		parent::__construct(false);
		
		$this->setMaxSize($intMaxSize);
		$this->setDirectory(PATH.str_replace(PATH,'',$strDirectory),false);
		$this->setPrefix((is_null($strPrefix) ? date('YmdHis').'_' : $strPrefix));
		
		if(!empty($strIndex)) $this->setFile($strIndex);
	}
	
	/**
	 * Defines $_FILES specific data array
	 * 
	 * @param		string	$strIndex		$_FILES index
	 * 
	 * @return		void
	 *
	 * @subpackage 	Upload
	 * @since		2007-11-05
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setFile($strIndex) {
		if(!is_array($_FILES[$strIndex]))																						return false;
		if(!isset($_FILES[$strIndex]['name']) || !isset($_FILES[$strIndex]['tmp_name']) || !isset($_FILES[$strIndex]['type']))	return false;
		
		$this->arrFile		= $_FILES[$strIndex];
	}
	
	/**
	 * Defines filter type
	 * 
	 * @param		string	$strValue		Filter name
	 * 
	 * @return		void
	 *
	 * @subpackage 	Upload
	 * @since		2007-11-05
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setFilter($strValue) {
		if(!is_string($strValue) || empty($strValue))	return false;
		
		$this->strFilter		= $strValue;
	}
	
	/**
	 * Defines files max size 
	 * 
	 * @param		integer	$intValue		Max size value
	 * 
	 * @return		void
	 *
	 * @subpackage 	Upload
	 * @since		2007-11-05
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setMaxSize($intValue) {
		if(!is_numeric($intValue) || $intValue <= 0)	return false;
		
		$this->intMaxSize		= $intValue;
	}
	
	/**
	 * Defines file directory path
	 * 
	 * @param		string	$strValue		Directory path
	 * @param 		boolean	$boolRoot		Defines if directory path is relative to root or not
	 * 
	 * @return		void
	 *
	 * @subpackage 	Upload
	 * @since		2007-11-05
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setDirectory($strValue,$boolRoot = true) {
		if(!is_string($strValue))					return false;
		
		// Checks last charecter as /
		if(strrpos($strValue,'/') != (strlen($strValue)-1))	$strValue .= '/';
		
		$this->strDirectory	= ($boolRoot ? $this->strDirectory.$strValue : $strValue);
	}
	
	/**
	 * Definex prefix for new file
	 * 
	 * @param		string	$strValue		Prefix value
	 * 
	 * @return		void
	 *
	 * @subpackage 	Upload
	 * @since		2007-11-05
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setPrefix($strValue) {
		if(!is_string($strValue))						return false;
		
		$this->strPrefix		= $strValue;
	}
	
	/**
	 * Validates file max size.
	 * If validation fail, define $intError as 0 (zero)
	 * 
	 * @return		boolean
	 *
	 * @subpackage 	Upload
	 * @since		2007-11-05
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function validateFileSize() {
        if($this->arrFile['size'] > $this->intMaxSize) {
			$this->intError = 0;
			return false;
        }
        return true;
	}
	
	/**
	 * Validates directory path as an existing fisical directory
	 * If validation fail, define $intError as 1
	 * 
	 * @return		boolean
	 *
	 * @subpackage 	Upload
	 * @since		2007-11-05
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function validateDirectory() {
        if(!is_dir($this->strDirectory)) {
			$this->intError = 1;
			return false;
        }
        return true;
	}
	
	/**
	 * Validates new file's name against existing files in directory
	 * If validation fail, define $intError as 2
	 * 
	 * @return		boolean
	 *
	 * @subpackage 	Upload
	 * @since		2007-11-05
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function validateFileName() {
        if(file_exists($this->strDirectory.$this->strPrefix.$this->arrFile['name'])) {
			$this->intError = 2;
			return false;
        }
        return true;
	}
	
	/**
	 * Validates uploaded file against $arrFileSize, $strDirectory and $arrFileName
	 * If any validation fails, return $intError.
	 * If all validations succeed, return true.
	 *
	 * @return		mixed
	 *
	 * @subpackage 	Upload
	 * @since		2007-11-05
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function validateFile() {
		if(!isset($this->intError)) $this->validateFileSize();	// $intError = 0
		if(!isset($this->intError)) $this->validateDirectory();	// $intError = 1
		if(!isset($this->intError)) $this->validateFileName();	// $intError = 2
		
		if(isset($this->intError)) {
			return $this->intError;
		} else {
			return true;
		}
		
		/*
        if($this->strFilter=="IMAGEM") {
            if(!eregi("^image\/(jpeg|pjpeg|jpg|png|gif|bmp|x-png)$", $this->arquivo["type"])) {
                $this->erro++;
                $erro = "Arquivo em formato inválido! A imagem deve ser jpg, jpeg, bmp, gif ou png. Envie outro arquivo!";
            } 
        }
        
        if($this->strFilter=="PNG") {
            if($this->arquivo["type"] !== "image/png") { 
                $this->erro++;
                $erro = "O arquivo enviado não é uma imagem PNG! Envie outro arquivo!"; 
            }
        }
        
        if($this->strFilter=="JPG") {
            if(!eregi("^image\/(pjpeg|jpg)$", $this->arquivo["type"])) {
                $this->erro++;
                $erro = "O arquivo enviado não é uma imagem JPG! Envie outro arquivo!"; 
            }
        }
        
        if($this->strFilter=="DOCUMENTO") {
            if($this->arquivo["type"] !== "application/msword" && $this->arquivo["type"] !== "application/vnd.ms-excel" && $this->arquivo["type"] !== "application/vnd.ms-powerpoint" && $this->arquivo["type"] !== "text/html" && $this->arquivo["type"] !== "text/plain" && $this->arquivo["type"] !== "text/rtf" && $this->arquivo["type"] !== "application/pdf") { 
                $this->erro++;
                $erro = "Arquivo em formato inválido! O Documento deve ser DOC, XLS, PPT, HTM, HTML, TXT, RTF ou PDF. Envie outro arquivo!"; 
            }
        }
        
        if($this->strFilter=="GIF") {
            if($this->arquivo["type"] !== "image/gif") { 
                $this->erro++;
                $erro = "O arquivo enviado não é uma imagem GIF! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="DOC") {
            if($this->arquivo["type"] !== "application/msword") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um DOC! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="PDF") {
            if($this->arquivo["type"] !== "application/pdf") { 
                $this->erro++;
                $erro = "O arquivo enviado por voc no  um .PDF! Envie outro!"; 
            }	     
        }
        
        if($this->strFilter=="XLS") {
            if($this->arquivo["type"] !== "application/vnd.ms-excel") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um XLS! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="PPT") {
            if($this->arquivo["type"] !== "application/vnd.ms-powerpoint") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um PPT! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="PHP") {
            if($this->arquivo["type"] !== "application/x-httpd-php") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um arquivo PHP! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="PHPS") {
            if($this->arquivo["type"] !== "application/x-httpd-php-source") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um arquivo PHP source! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="JS") {
            if($this->arquivo["type"] !== "application/x-javascript") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um JS! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="SWF") {
            if($this->arquivo["type"] !== "application/x-shockwave-flash") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um SWF! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="XHTML") {
            if($this->arquivo["type"] !== "application/xhtml+xml") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um XHTML/XHT! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="XML") {
            if($this->arquivo["type"] !== "application/xml") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um XML/XSL! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="MIDI") {
            if($this->arquivo["type"] !== "audio/midi") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um MID/MIDI/KAR! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="MP3") {
            if($this->arquivo["type"] !== "audio/mpeg") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um MP3! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="WAV") {
            if($this->arquivo["type"] !== "audio/x-wav") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um WAV! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="CSS") {
            if($this->arquivo["type"] !== "text/css") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um CSS! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="HTML") {
            if($this->arquivo["type"] !== "text/html") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um HTML/HTM/SHTML! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="TXT") {
            if($this->arquivo["type"] !== "text/plain") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um TXT/ASC! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="RTF") {
            if($this->arquivo["type"] !== "text/rtf") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um RTF! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="MPEG") {
            if($this->arquivo["type"] !== "video/mpeg") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um MPEG/MPG/MPE! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="MOV") {
            if($this->arquivo["type"] !== "video/quicktime") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um MOV/QT! Envie outro arquivo!"; 
            }	     
        }
        
        if($this->strFilter=="AVI") {
            if($this->arquivo["type"] !== "video/x-msvideo") { 
                $this->erro++;
                $erro = "O arquivo enviado não é um AVI! Envie outro arquivo!"; 
            }	     
        }
        */
    }
	
	/**
	 * Uploads selected file.
	 * If file is not defined, return false.
	 * If any validation fails, return $intError.
	 * If upload succeed, return new file's name.
	 *
	 * @return		mixed
	 *
	 * @subpackage 	Upload
	 * @since		2007-11-05
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function uploadFile() {
		if(empty($this->arrFile['name'])) 	return false;
		if($this->validateFile() !== true) 	return $this->intError;
		
		// Clear all special characters on file name
		$strTempName = $this->replaceSpecialChars($this->strPrefix.$this->arrFile['name']);
		
		// Save temp file on host
		if(move_uploaded_file($this->arrFile['tmp_name'],$this->strDirectory.$strTempName)) {
			return str_replace(PATH,'',$this->strDirectory).$strTempName;
		} else {
			return $this->intError = 3;
		}
    }
}
?>