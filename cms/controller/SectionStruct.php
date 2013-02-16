<?php
class SectionStruct_controller extends Section_controller {	
	/**
	 * 
	 * ATENTION!
	 * 
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 * 
	 */
	protected	$strTable		= 'sec_config';
	protected	$arrTable		= array('sec_config','rel_sec_struct','sec_config_order','sec_struct');
	
	protected	$arrFieldType	= array(
										'sec_config_id'		=> 'numeric',
										'name'				=> 'string_notempty',
										'field_name'		=> 'string_notempty',
										'tooltip'			=> 'string',
										'mandatory'			=> 'boolean',
										'admin'				=> 'boolean'
									);
		
	protected	$arrFieldList	= array('sec_config.*','sec_parent.name AS sec_parent_name');
	protected	$arrJoinList	= array('LEFT JOIN sec_config AS sec_parent ON sec_config.parent = sec_parent.id');
	protected	$arrWhereList	= array(
										'rel_sec_struct.sec_config_id = sec_config.id',
										'rel_sec_struct.id = sec_config_order.field_id',
										'rel_sec_struct.sec_struct_id = sec_struct.id'
										);
	
	protected	$arrFieldData	= array('rel_sec_struct.*','sec_config_order.field_order','sec_config_order.type','sec_struct.name AS sec_struct_name');
	protected	$arrJoinData	= array();
	protected	$arrWhereData	= array(
										'rel_sec_struct.sec_config_id = sec_config.id',
										'rel_sec_struct.id = sec_config_order.field_id',
										'rel_sec_struct.sec_struct_id = sec_struct.id'
										);
	protected	$arrGroupData	= array('rel_sec_struct.id');
	
	/*
	protected	$arrFieldData	= array('sec_config.*','sec_parent.name AS sec_parent_name','sys_folder.name AS sys_folder_name');
	protected	$arrJoinData	= array(
										'LEFT JOIN sec_config AS sec_parent ON sec_config.parent = sec_parent.id',
										'LEFT JOIN sys_folder ON sec_config.sys_folder_id = sys_folder.id'
										);
	protected	$arrWhereData	= array('sec_config.id = {id}');
	*/
	
	private		$arrStruct		= array();
	private		$strSecTable;
	
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
		
		// Gets STRUCT list
		$arrStruct	= $this->objModel->select(array('sec_struct.*'),'sec_struct',array(),array(),array(),array(),0,null,'All');
		foreach($arrStruct AS $objTemp) {
			$this->arrStruct[$objTemp->id] = clone $objTemp;
		}
		
		// Gets SECTION list
		$this->arrSecList	= array();
		$arrTempSec			= $this->objModel->select(array('id','name','sys_folder_id','table_name'),'sec_config',array(),array(),array(),array(),0,null,'All');
		foreach($arrTempSec AS $intKey => $objTemp) {
			$objTemp->fields = $this->objModel->select(array('id','name'),'rel_sec_struct',array(),array('sec_config_id = ' . $objTemp->id),array(),array(),0,null,'All');
			$this->arrSecList[$objTemp->sys_folder_id][$objTemp->id] = $objTemp;
			
			if($objTemp->id == $this->intSecID) {
				$this->strSecTable = $objTemp->table_name;
			}
		}
		$this->objSmarty->assign('arrSec',$this->arrSecList);
		$this->objSmarty->assign('arrStruct',$this->arrStruct);
		
		// Shows default interface
		if($boolRenderTemplate || !is_numeric($this->intSecID) || $this->intSecID <= 0) $this->_read();
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
		
		// Sets DYNAMIC field data
		if(isset($_POST['field']) && $_POST['field'] == 1) {
			// Sets main insert table
			$strTable			= 'rel_sec_struct';
			
			// Sets field name syntax
			$_POST['field_name'] = str_replace('-','_', Controller::permalinkSyntax( $_POST['field_name'] . (string) $this->arrStruct[$_POST['sec_struct_id']]->suffix ) );
			
			// Sets specific field data to validate
			$this->arrFieldType	= 	array_merge($this->arrFieldType,array(
																		'sec_struct_id'		=> 'numeric'
																	)
									);
		// Sets RELATIONSHIP field data
		} elseif(isset($_POST['field']) && $_POST['field'] == 2) {
			// Sets main insert table
			$strTable			= 'rel_sec_sec';
			
			// Sets specific field data to validate
			$this->arrFieldType	= 	array_merge($this->arrFieldType,array(
																		'child_id'		=> 'numeric',
																		'field_rel'		=> 'numeric',
																		'field_type'	=> 'numeric'
																	)
									);
		}
		
		if(($mxdValidate = $this->validateParamsArray($_POST,$this->arrFieldType,false)) === true) {
			// Gets next order index
			$intOrder	= $this->objModel->recordExists('(MAX(field_order) + 1) AS field_order','sec_config_order','sec_config_id = ' . $this->intSecID,true);
			if(!$intOrder) $intOrder = 1;
			
			// Inserts field struct data
			if(($intFieldID = $this->objModel->insert($strTable,$_POST,true)) !== false) {
				// Sets field order data array
				$arrInsertOrder	=	array(
										'field_id'		=> $intFieldID,
										'sec_config_id'	=> $_POST['sec_config_id'],
										'field_order'	=> $intOrder,
										'type'			=> 1
									);
				
				// Inserts field order data
				if($this->objModel->insert('sec_config_order',$arrInsertOrder,true) !== false) {
					// Creates new field in section's table
					if(!$this->addField($_POST,($strTable == 'rel_sec_struct' ? true : false))) {
						$this->objModel->delete('rel_sec_struct','id = ' . $intFieldID);
						$this->objModel->delete('sec_config_order','field_id = ' . $intFieldID);
						$this->objSmarty->assign('ERROR_MSG','There was an error while trying to create "' . $_POST['name'] . '" field in database! Please try again!');
					}
				} else {
					$this->objModel->delete('rel_sec_struct','id = ' . $intFieldID);
					$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save "' . $_POST['name'] . '" order data! Please try again!');
				}
			} else {
				$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save "' . $_POST['name'] . '" data! Please try again!');
			}
		} else {
			$this->objSmarty->assign('ERROR_MSG','There was an error while validating "' . $mxdValidate . '" data! Please try again!');
		}
		
		// Shows interface
		$this->_create();
		
		$this->secureGlobals();
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
	protected function _create() {
		if(is_numeric($this->intSecID) && $this->intSecID > 0) {
			$this->objModel->arrFieldList	= $this->arrFieldData;
			$this->objModel->arrJoinList	= $this->arrJoinData;
			$this->objModel->arrWhereList	= $this->arrWhereData;
			$this->objModel->arrWhereList[]	= 'sec_config.id = ' . $this->intSecID;
			$this->objModel->arrGroupList	= $this->arrGroupData;
			
			$this->objData = $this->objModel->getList();
			$this->objSmarty->assign('objData',$this->objData);
			
			$this->renderTemplate(true,$this->strModule . '_form.html');
		} else {
			// Shows list interface
			$this->objSmarty->assign('ALERT_MSG','You must choose an item to update!');
			$this->_read();
		}
	}
	
	/**
	 * Creates new Section field
	 *
	 * @param	array	$arrData		New field info; mandatory data is array('field_name' => string, 'sec_struct_id' => integer).
										RDBMS struct data from $this->arrStruct[$arrData['sec_struct_id']]; if $arrData['sec_struct_id'] is not set, assumes single text line
	 *
	 * @return	boolean
	 *
	 * @since 	2013-02-15
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function addField($arrData,$boolStruct = true) {
		if(!is_array($arrData) || !isset($arrData['sec_struct_id']) || !isset($this->arrStruct[$arrData['sec_struct_id']])) $arrData['sec_struct_id'] = 1;
		if(!is_string($arrData['field_name']) || empty($arrData['field_name'])) return false;
		
		// Sets RDBMS struct array
		$arrStruct['type'] 		= $this->arrStruct[$arrData['sec_struct_id']]->fieldtype;
		$arrStruct['unsigned'] 	= $this->arrStruct[$arrData['sec_struct_id']]->is_unsigned;
		$arrStruct['null'] 		= $this->arrStruct[$arrData['sec_struct_id']]->notnull;
		$arrStruct['default'] 	= $this->arrStruct[$arrData['sec_struct_id']]->default_value;
		if($this->arrStruct[$arrData['sec_struct_id']]->length > 0) {
			$arrStruct['length']= $this->arrStruct[$arrData['sec_struct_id']]->length;
		}
		
		$arrDefaultFields 		= array('add' =>array($arrData['field_name'] => $arrStruct));
		
		switch($boolStruct) {
			// rel_sec_struct
			case true:
			default:
				return $this->objManage->alterTable($this->strSecTable,$arrDefaultFields);
			break;
			
			// rel_sec_sec
			case false:
			break;
		} return false;
	}
}