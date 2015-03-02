<?php
class getFile_Controller extends Controller {
	public		$objSection;
	
	protected	$strDirPath;
		
	private		$intContent;
	
	/**
	 * Class constructor
	 *
	 * @param	integer	$intSection	Section ID
	 * @param	integer	$intContent	Content ID
	 *
	 * @return	boolean
	 *
	 * @since	2013-01-22
	 * @author	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($intSection,$intContent) {
		parent::__construct();
		
		if($intSection != SECTION_ID) {
			$this->objSection = $this->objModel->getSectionConfig($intSection);
		} else {
			$this->objSection = parent::$objSection;
		}
		
		$this->intContent	= $intContent;
		$this->strDirPath	= ROOT_UPLOAD . str_replace('ctn_','',$this->objSection->table) . '/';
	}
	
	/**
	 * Defines Content ID
	 * 
	 * @param	integer	$intTemp	Content ID
	 * 
	 * @return	boolean
	 * 
	 * @since	2013-01-22
	 * @author	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setContentId($intTemp) {
		if(empty($intContent) || !is_numeric($intContent)) return false;
		
		$this->intContent = $intTemp;
		
		return true;
	}
	
	/**
	 * Gets filename from DB and, if file exists, dump it to the browser
	 * 
	 * @param	string	$strField	Field name in DB that carries filename registry
	 * 
	 * @return	mixed
	 * 
	 * @since	2013-01-22
	 * @author	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function getFile($strField) {
		if(empty($strField) || !is_string($strField)) return false;
		
		if( ($strFile = $this->objModel->recordExists($strField,$this->objSection->table,str_replace('#table#',$this->objSection->table.'.',SYS_WHERE) . ' AND sys_language_id = ' . LANGUAGE . ' AND content_id = ' . $this->intContent,true)) !== false && is_file($this->strDirPath . $strFile)) {
			// Send the necessary headers to browser
			header("Content-Type: application/unknown");
			header("Content-Disposition: filename=" . $strFile);
			
			// open the file for reading and start dumping it to the browser
			if($fp = fopen($strDir.$strFile, "r")) {
				while(!feof($fp)) {
					echo  fgets($fp);
				}
				// close the file
				fclose($fp);
			}
		} else {
			return false;
		}
	}
}
?>