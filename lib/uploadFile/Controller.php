<?php
class uploadFile_Controller extends Controller {
	
	public		$intError;
	public		$strPath;
	
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
	 * @param		string	$strFilter		File type filter
	 * @param 		string	$strPrefix		New file's name prefix
	 * @param 		integer	$intMaxSize		File's max size
	 * @param		string	$strPath		System files path
	 * 
	 * @return		void
	 *
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function __construct($strIndex,$strDirectory,$strFilter = '',$strPrefix = null,$intMaxSize = 1000000000,$strPath = SYS_ROOT) {
		if(empty($strIndex) || !is_string($strIndex))						return false;
		if(empty($strDirectory) || !is_string($strDirectory) || (!is_dir($strDirectory) && !is_dir($strPath . $strDirectory)))	$strDirectory = ROOT_UPLOAD;
		if(empty($strPrefix) && !is_null($strPrefix))						$strPrefix = date('YmdHis') . '_';
		if(!is_numeric($intMaxSize) || $intMaxSize <= 0)					$intMaxSize = 1000000000;
		if(empty($strPath) || !is_string($strPath) || !is_dir($strPath))	$strPath = SYS_ROOT;
		
		// Runs parent class constructor
		parent::__construct();
		
		$this->setMaxSize($intMaxSize);
		$this->setPath($strPath);
		$this->setDirectory($strPath . str_replace($strPath,'',$strDirectory),false);
		$this->setPrefix($strPrefix);
		$this->setFilter($strFilter);
		
		$this->setFile($strIndex);
	}
	
	/**
	 * Defines $_FILES specific data array
	 * 
	 * @param		string	$strIndex		$_FILES index
	 * 
	 * @return		void
	 *
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setFile($strIndex) {
		if(!is_array($_FILES[$strIndex]))																						return false;
		if(empty($_FILES[$strIndex]['name']) || empty($_FILES[$strIndex]['tmp_name']) || empty($_FILES[$strIndex]['type']))		return false;
		
		$this->arrFile		= $_FILES[$strIndex];
		
		return true;
	}
	
	/**
	 * Defines system files path
	 *
	 * @param		string	$strValue		Filter name
	 *
	 * @return		void
	 *
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function setPath($strValue) {
		if(!is_string($strValue) || empty($strValue))	$strValue = SYS_ROOT;
	
		$this->strPath		= $strValue;
	}
	
	/**
	 * Defines filter type
	 * 
	 * @param		string	$strValue		Filter name
	 * 
	 * @return		void
	 *
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setFilter($strValue) {
		if(!is_string($strValue) || empty($strValue))	$strValue = '';
		
		$this->strFilter		= strtoupper($strValue);
	}
	
	/**
	 * Defines files max size 
	 * 
	 * @param		integer	$intValue		Max size value
	 * 
	 * @return		void
	 *
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setMaxSize($intValue) {
		if(!is_numeric($intValue) || $intValue <= 0)	$intValue = 1000000000;
		
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
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setDirectory($strValue,$boolRoot = true) {
		if(!is_string($strValue))	$strValue = ROOT_UPLOAD;
		
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
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setPrefix($strValue) {
		if(!is_string($strValue) || !is_string($strValue) || empty($strValue))	$strValue = date('YmdHis') . '_';
		
		$this->strPrefix = $strValue;
	}
	
	/**
	 * Validates file max size.
	 * If validation fail, define $intError as 0 (zero)
	 * 
	 * @return		boolean
	 *
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function validateFileSize() {
        if($this->arrFile['size'] > $this->intMaxSize) {
			$this->intError = 0;
			define('ERROR_MSG','Error on uploadFile::validateFileSize!');
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
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function validateDirectory() {
        if(!is_dir($this->strDirectory)) {
			$this->intError = 1;
			define('ERROR_MSG','Error on uploadFile::validateDirectory!');
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
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function validateFileName() {
        if(file_exists($this->strDirectory.$this->strPrefix.$this->arrFile['name'])) {
			$this->intError = 2;
			define('ERROR_MSG','Error on uploadFile::validateFileName!');
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
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function validateFile() {
		if(!isset($this->intError)) $this->validateFileSize();
		if(!isset($this->intError)) $this->validateDirectory();
		if(!isset($this->intError)) $this->validateFileName();
	
		if(isset($this->intError)) {
			define('ERROR_MSG','Error on uploadFile::validateFile!');
			return $this->intError;
		}
		
		// IMAGE TYPES
        if($this->strFilter=="IMG" || $this->strFilter=="IMAGE" || $this->strFilter=="IMAGEM") {
            if(!eregi("^image\/(jpeg|pjpeg|jpg|png|gif|bmp|x-png)$", $this->arrFile["type"])) {
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not an image!');
            } 
        }
        if($this->strFilter=="PNG") {
            if($this->arrFile["type"] !== "image/png") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a PNG image!');
            }
        }
        if($this->strFilter=="JPG") {
            if(!eregi("^image\/(pjpeg|jpg)$", $this->arrFile["type"])) {
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a JPG image!');
            }
        }
        if($this->strFilter=="GIF") {
            if($this->arrFile["type"] !== "image/gif") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a GIF image!');
            }	     
        }
        
        // TEXT and DOCUMENT TYPES
        if($this->strFilter=="TEXT" || $this->strFilter=="DOCUMENT" || $this->strFilter=="DOCUMENTO") {
            if($this->arrFile["type"] !== "application/msword" && $this->arrFile["type"] !== "application/vnd.ms-excel" && $this->arrFile["type"] !== "application/vnd.ms-powerpoint" && $this->arrFile["type"] !== "text/html" && $this->arrFile["type"] !== "text/plain" && $this->arrFile["type"] !== "text/rtf" && $this->arrFile["type"] !== "application/pdf") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a document!');
            }
        }
        if($this->strFilter=="DOC") {
            if($this->arrFile["type"] !== "application/msword") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a DOC file!');
            }	     
        }
        if($this->strFilter=="PDF") {
            if($this->arrFile["type"] !== "application/pdf") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a PDF file!');
            }	     
        }
        if($this->strFilter=="XLS") {
            if($this->arrFile["type"] !== "application/vnd.ms-excel") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a XLS file!');
            }	     
        }
        if($this->strFilter=="PPT") {
            if($this->arrFile["type"] !== "application/vnd.ms-powerpoint") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a PPT file!');
            }	     
        }
        if($this->strFilter=="TXT") {
            if($this->arrFile["type"] !== "text/plain") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a TXT/ASCII file!');
            }	     
        }
        if($this->strFilter=="RTF") {
            if($this->arrFile["type"] !== "text/rtf") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a RTF file!');
            }	     
        }
        
        // WEB SCRIPT TYPES
        if($this->strFilter=="PHP") {
            if($this->arrFile["type"] !== "application/x-httpd-php") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a PHP file!');
            }	     
        }
        if($this->strFilter=="PHPS") {
            if($this->arrFile["type"] !== "application/x-httpd-php-source") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a PHP Source file!'); 
            }	     
        }
        if($this->strFilter=="JS" || $this->strFilter=="JAVASCRIPT") {
            if($this->arrFile["type"] !== "application/x-javascript") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a JS file!');
            }	     
        }
        if($this->strFilter=="CSS") {
            if($this->arrFile["type"] !== "text/css") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a CSS file!');
            }	     
        }
        if($this->strFilter=="HTML") {
            if($this->arrFile["type"] !== "text/html") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a HTML/HTM/SHTML file!');
            }	     
        }
        if($this->strFilter=="XHTML") {
            if($this->arrFile["type"] !== "application/xhtml+xml") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a XHTML/XHTM file!');
            }	     
        }
        if($this->strFilter=="XML") {
            if($this->arrFile["type"] !== "application/xml") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a XML/XSL file!');
            }	     
        }
        if($this->strFilter=="SWF" || $this->strFilter=="FLASH" || $this->strFilter=="FLA") {
            if($this->arrFile["type"] !== "application/x-shockwave-flash") { 
                $this->intError = 3;
                $erro = "O arquivo enviado não é um SWF! Envie outro arquivo!"; 
            }	     
        }
        
        // MEDIA TYPES
        if($this->strFilter=="MIDI") {
            if($this->arrFile["type"] !== "audio/midi") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a MIDI file!');
            }	     
        }
        if($this->strFilter=="MP3") {
            if($this->arrFile["type"] !== "audio/mpeg") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a MP3 file!');
            }	     
        }
        if($this->strFilter=="WAV") {
            if($this->arrFile["type"] !== "audio/x-wav") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a WAV file!');
            }	     
        }
        
        if($this->strFilter=="MPEG") {
            if($this->arrFile["type"] !== "video/mpeg") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a MPEG/MPG/MPE file!');
            }	     
        }
        if($this->strFilter=="MOV") {
            if($this->arrFile["type"] !== "video/quicktime") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a MOV/QT file!');
            }	     
        }
        if($this->strFilter=="AVI") {
            if($this->arrFile["type"] !== "video/x-msvideo") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a AVI file!');
            }	     
        }
        if($this->strFilter=="VIDEO") {
            if($this->arrFile["type"] !== "video/x-msvideo" && $this->arrFile["type"] !== "video/quicktime" && $this->arrFile["type"] !== "video/mpeg") { 
                $this->intError = 3;
                define('ERROR_MSG','Error on uploadFile::validateFile - Uploaded file is not a AVI file!');
            }	     
        }
        
        if($this->intError == 3) return false;
        
        return true;
    }
	
	/**
	 * Uploads selected file.
	 * If file is not defined, return false.
	 * If any validation fails, return $intError.
	 * If upload succeed, return new file's name.
	 *
	 * @return		mixed
	 *
	 * @since		2013-01-23
	 * @author		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
    public function uploadFile() { 
		if(empty($this->arrFile['name'])) 	return false;
		if($this->validateFile() !== true) 	return false;
		 
		// Clear all special characters on file name
		$strTempName = $this->replaceSpecialChars($this->strPrefix.$this->arrFile['name']);
		
		// Save temp file on host
		if(($objMoveFile = move_uploaded_file($this->arrFile['tmp_name'],$this->strDirectory.$strTempName)) !== false) {
			return str_replace($this->strPath,'',$this->strDirectory).$strTempName;
		} else {
			define('ERROR_MSG','Error on uploadFile::uploadFile!');
			$this->intError = 4;
			return false;
		}
    }
    
    public function createThumbnail($strOrgPath,$intNewW = 0,$intNewH = 0,$strDestPath = '',$intQuality=80) {
    	if(!is_file($strOrgPath)) return false;
    	if($intNewW <= 0 && $intNewH <= 0) return false;
    	
    	// Defines original filename
    	$strOrgFileName = basename($strOrgPath);
    	$strOrgFileExt	= end(explode('.',$strOrgFileName));
    	
    	// Defines destination filename
    	if(!is_file($strDestPath)) {
    		$strDestFileName = md5($strOrgFileName) . ($strOrgFileExt == 'png' ? '.png' : '.jpg');
    	} else {
    		$strDestFileName = md5(basename($strDestPath)) . ($strOrgFileExt == 'png' ? '.png' : '.jpg');
    	}
    	
	    // Defines destination directory path
    	if(!empty($strDestPath) && (is_file($strDestPath) || is_dir($strDestPath))) {
    		$strDestPath = realpath(pathinfo($strDestPath,PATHINFO_DIRNAME));
    	} else {
    		$strDestPath = realpath(pathinfo($strOrgPath,PATHINFO_DIRNAME));
    	}
    	$strDestFileName = $strDestPath . DIRECTORY_SEPARATOR . $strDestFileName;
    	
    	// Creates new image object
    	if(strOrgFileExt == 'png') {
			$objImg = imagecreatefrompng($strOrgPath);
    	} else {
			$objImg = imagecreatefromjpeg($strOrgPath);
    	}
		
		// Gets original file dimensions
	    $intW = imagesx($objImg); 
	    $intH = imagesy($objImg); 
	    
	    // Defines thumbnail dimensions
	    $intThumbW = ($intNewW > 0 ? $intNewW : ($intNewH * $intW) / $intH);
	    $intThumbH = ($intNewH > 0 ? $intNewH : ($intNewW * $intH) / $intW);
	    
	    // Creates thumbnail
	    if(function_exists('imagecopyresampled')) {
	        if(function_exists('imageCreateTrueColor')) {
	            $objFile	= imageCreateTrueColor($intThumbW,$intThumbH); 
	        } else {
	            $objFile	= imagecreate($intThumbW,$intThumbH); 
	        }
	        
	        if(!($boolFile = @imagecopyresampled($objFile,$objImg,0,0,0,0,$intThumbW,$intThumbH,$intW,$intH))) { 
	            $boolFile	= @imagecopyresized($objFile,$objImg,0,0,0,0,$intThumbW,$intThumbH,$intW,$intH); 
	            
	        } 
	    } else {
	        $objFile	= imagecreate($intThumbW,$intThumbH); 
	        $boolFile 	= @imagecopyresized($objFile,$objImg,0,0,0,0,$intThumbW,$intThumbH,$intW,$intH); 
	    } 
	    if($boolFile) {
		    if($strOrgFileExt == 'png') {  
		        $boolFile = imagepng($objFile,$strDestFileName); 
		    } else { 
		        $boolFile = imagejpeg($objFile,$strDestFileName,$intQuality);
		    }
		    $strDestFileName = str_replace(array('/','\\'),'/',$strDestFileName);
		    
		    return ($boolFile ? $strDestFileName : false);
	    }
	    return false;
    }
}
?>