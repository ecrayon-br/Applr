<?php
class Folder_controller extends CRUD_controller {
	/**
	 * 
	 * ATENTION!
	 * 
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 * 
	 */
	protected	$strTable		= 'sys_folder';
	protected	$arrTable		= array('sys_folder');
	
	protected	$arrFieldType	= array(
										'id'			=> 'numeric_empty',
										'usr_data_id'	=> 'numeric',
										'name'			=> 'string_notempty',
										'icon_filepath'	=> 'string',
										'sys_filepath'	=> 'string',
										'status'		=> 'boolean'
									);
		
	protected	$arrFieldList	= array('sys_folder.*');
	protected	$arrWhereList	= array('sys_folder.status = 1');
	
	protected	$arrFieldData	= array('sys_folder.*');
	protected	$arrWhereData	= array('sys_folder.id = {id}');
	
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
		
		$this->_update($_POST);
		
		$this->secureGlobals();
	}
}