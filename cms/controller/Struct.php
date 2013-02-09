<?php
class Struct_controller extends CRUD_controller {
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
										'name'			=> 'string_notempty',
										'html'			=> 'string_notempty',
										'suffix'		=> 'string',
										'status'		=> 'boolean',
										'fieldtype'		=> 'string_notempty',
										'length'		=> 'numeric_empty',
										'is_unsigned'	=> 'boolean',
										'notnull'		=> 'boolean',
										'default_value'	=> 'string'
									);
		
	protected	$arrFieldList	= array('sec_struct.*');
	protected	$arrWhereList	= array('sec_struct.status = 1');
	
	protected	$arrFieldData	= array('sec_struct.*');
	protected	$arrWhereData	= array('sec_struct.id = {id}');
	
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