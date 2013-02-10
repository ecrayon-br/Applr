<?php
class Template_controller extends CRUD_controller {
	/**
	 * 
	 * ATENTION!
	 * 
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 * 
	 */
	protected	$strTable		= 'sys_template';
	protected	$arrTable		= array('sys_template');
	
	protected	$arrFieldType	= array(
										'id'			=> 'numeric_empty',
										'usr_data_id'	=> 'numeric',
										'name'			=> 'string_notempty',
										'filename'		=> 'string',
										'status'		=> 'boolean'
									);
		
	protected	$arrFieldList	= array('sys_template.*');
	protected	$arrWhereList	= array('sys_template.status = 1');
	
	protected	$arrFieldData	= array('sys_template.*');
	protected	$arrWhereData	= array('sys_template.id = {id}');
	
	private		$arrFileList	= array();
	
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
		parent::__construct(false);
		
		// Read ROOT_TEMPLATE files and create Smarty variable to SELECT box
		$this->arrFileList = array();
		if($objHandle = opendir(ROOT_TEMPLATE)) {
			while (false !== ($strFile = readdir($objHandle))) {
				if(is_file(ROOT_TEMPLATE.$strFile)) $this->arrFileList[] = $strFile;
			}
			closedir($objHandle);
		}
		$arrTemplate		= (array) $this->objModel->select('filename',$this->strTable,array(),$this->strTable . '.status = 1',array(),array(),0,null,'Col');
		$this->arrFileList	= array_diff($this->arrFileList,$arrTemplate);
		
		$this->objSmarty->assign('arrFile',$this->arrFileList);
		
		// Shows default interface
		if($boolRenderTemplate) $this->_read();
	}
	
	/**
	 * Shows INSERT / UPDATE form interface
	 *
	 * @param	integer	$intID			Content ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function _create($intID = 0) {
		if($intID > 0) {
			$this->objData = $this->objModelCRUD->getData($intID);
			
			$this->objData->path	= $this->objData->filename;
			$this->objData->html	= file_get_contents(ROOT_TEMPLATE . $this->objData->filename);
			
			$this->objSmarty->assign('objData',$this->objData);
		}
		
		$this->renderTemplate(true,$this->strModule . '_form.html');
	}
	
	/**
	 * @see CRUD_controller::delete()
	 */
	public function delete() {
		parent::delete(0);
	}
	
	/**
	 * Inserts / Updates data
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function add() {
		$this->unsecureGlobals();
		
		// Sets original data object
		$this->objData	= (object) $_POST;
		$this->objSmarty->assign('objData',$this->objData);
		
		// If "File to associate" is selected
		if(!empty($_POST['path']) && empty($_POST['html'])) {
			$this->objData->filename = $this->objData->path = $_POST['filename'] = $_POST['path'];
			$this->_update($_POST);

		// If HTML field is filled
		} elseif(!empty($_POST['html'])) {
			// If UPDATE, assumes old filename
			$strFileName = (!empty($_POST['filename']) ? $_POST['filename'] : date('YmdHis') . '-' . Controller::permalinkSyntax($_POST['name']) . '.html');
			
			// Put file contents and save data
			if(file_put_contents(ROOT_TEMPLATE . $strFileName,$_POST['html'],LOCK_EX)) {
				$this->objData->filename = $this->objData->path = $_POST['filename'] = $strFileName;
				
				$this->_update($_POST);
			} else {
				$this->objSmarty->assign('ERROR_MSG','There was an error while trying to create template file! Please try again!');
				$this->_create();
			}
		} else {
			$this->objSmarty->assign('ERROR_MSG','You must choose a file to associate or write your own HTML code!');
			$this->_create();
		}
		
		$this->secureGlobals();
	}
}