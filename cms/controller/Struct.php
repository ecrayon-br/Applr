<?php
class Struct_controller extends CRUD_Controller {
	/**
	 * 
	 * ATENTION!
	 * 
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 * 
	 */
	protected	$strTable		= 'sec_struct';
	protected	$arrTable		= array('sec_struct');
	
	protected	$arrFieldType	= array(
										'id'			=> 'numeric_empty',
										'name'			=> 'string',
										'html'			=> 'string',
										'suffix'		=> 'string_empty',
										'status'		=> 'boolean',
										'fieldtype'		=> 'string',
										'length'		=> 'numeric_empty',
										'is_unsigned'	=> 'boolean',
										'notnull'		=> 'boolean',
										'default_value'	=> 'string_empty'
									);
		
	protected	$arrFieldList	= array('sec_struct.*');
	protected	$arrOrderList	= array('sec_struct.name ASC');
	
	protected	$arrFieldData	= array('sec_struct.*');
	protected	$arrWhereData	= array('sec_struct.id = {id}');
	
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