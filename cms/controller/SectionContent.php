<?php
class SectionContent_controller extends manageContent_Controller {	
	
	protected	$intSecID;
	
	private		$strSecTable;
	private		$objStruct;
	
	/**
	 * Class constructor
	 *
	 * @param	boolean	$boolRenderTemplate	Defines whether to show default's interface
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($boolRenderTemplate = true) {
		parent::__construct();
		
		// Gets Section ID from Section Permalink
		$this->intSecID	= intval($this->objModel->recordExists('id','sec_config','permalink = "' . $_SESSION[self::$strProjectName]['URI_SEGMENT'][3] . '"',true));
		if(empty($_SESSION[self::$strProjectName]['URI_SEGMENT'][3]) || empty($this->intSecID)) {
			$this->objSmarty->assign('ALERT_MSG','There was an error retrieving Section\'s data!');
		}
		
		// Sets Section vars
		$this->setSection($this->intSecID);
		
		// Gets Section Field list
		$this->objField		= $this->getFieldList();
		
		// Shows default interface
		if($boolRenderTemplate) $this->_read();
	}
}