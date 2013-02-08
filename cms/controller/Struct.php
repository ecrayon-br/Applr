<?php
class Struct_controller extends Main_controller {
	protected 	$objModel;
	
	public		$objData;
	
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
		
		if(!DEBUG) authUser_Controller::isLoggedIn(true,'Login.html');
		
		$this->objModel	= new Struct_model();
		
		if($boolRenderTemplate) {
			$this->renderTemplate();
		}
	}
	
	/**
	 * Inserts / Updates sec_struct data
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function add() {
		$this->unsecureGlobals();
		$arrFieldType	= 	array(
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
		
		if($this->validateParamsArray($_POST,$arrFieldType,false)) {
			if(($intID = $this->objModel->replace('sec_struct',$_POST)) !== false) {
				$_POST['id']	= $intID;
				$this->objSmarty->assign('ALERT_MSG','Data added successfully!');
			} else {
				$this->objSmarty->assign('ERROR_MSG','There was an error while trying to add data! Please try again!');
			}
		} else {
			$this->objSmarty->assign('ERROR_MSG','There was an error while validating sent data! Please try again!');
		}
		
		$this->objData	= (object) $_POST;
		$this->objSmarty->assign('objData',$this->objData);
		
		$this->renderTemplate();
		$this->secureGlobals();
	}
}