<?php
class TemplateType_controller extends CRUD_Controller {
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
	protected	$strTable		= 'sys_template_type';
	protected	$arrTable		= array('sys_template_type');
	
	protected	$arrFieldType	= array(
										'id'		=> 'numeric_empty',
										'name'		=> 'string',
										'status'	=> 'boolean'
									);
		
	protected	$arrFieldList	= array('sys_template_type.*');
	
	protected	$arrFieldData	= array('sys_template_type.*');
	protected	$arrWhereData	= array('sys_template_type.id = {id}');

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