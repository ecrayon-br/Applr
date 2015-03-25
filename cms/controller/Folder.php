<?php
class Folder_controller extends CRUD_Controller {
	/**
	 * 
	 * ATENTION!
	 * 
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 * 
	 */
		/**
		 * @todo set PROJECT_ID
		 */
	protected	$strTable		= 'sys_folder';
	protected	$arrTable		= array('sys_folder');
	
	protected	$arrFieldType	= array(
										'id'			=> 'numeric_empty',
										'usr_data_id'	=> 'numeric_empty',
										'name'			=> 'string',
										'icon_filepath'	=> 'string_empty',
										'sys_filepath'	=> 'string_empty',
										'status'		=> 'boolean'
									);
		
	protected	$arrFieldList	= array('sys_folder.*');
	
	protected	$arrFieldData	= array('sys_folder.*');
	protected	$arrWhereData	= array('sys_folder.id = {id}');

	public function __construct($boolRenderTemplate = true) {
		parent::__construct($boolRenderTemplate,true,CMS_ROOT_TEMPLATE);
	}
	
	/**
	 * @see CRUD_Controller::delete()
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
		
		$this->_update($_POST);
		
		$this->secureGlobals();
	}
}