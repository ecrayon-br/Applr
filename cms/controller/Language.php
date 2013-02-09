<?php
class Language_controller extends CRUD_controller {
	/**
	 * 
	 * ATENTION!
	 * 
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 * 
	 */
	protected	$strTable		= 'sys_language';
	protected	$arrTable		= array('sys_language');
	
	protected	$arrFieldType	= array(
										'id'			=> 'numeric_empty',
										'name'			=> 'string_notempty',
										'acronym'		=> 'string_notempty',
										'status'		=> 'boolean'
									);
		
	protected	$arrFieldList	= array('sys_language.*');
	protected	$arrWhereList	= array('sys_language.status = 1');
	
	protected	$arrFieldData	= array('sys_language.*');
	protected	$arrWhereData	= array('sys_language.id = {id}');
	
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