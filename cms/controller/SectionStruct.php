<?php
class SectionStruct_controller extends Section_controller {	
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
	protected	$strTable			= 'sec_config';
	protected	$arrTable			= array('sec_config');
	
	protected	$arrFieldType		= array(
											'sec_config_id'		=> 'numeric',
											'name'				=> 'string',
											'field_name'		=> 'string',
											'tooltip'			=> 'string_empty',
											'mandatory'			=> 'boolean',
											'admin'				=> 'boolean'
										);
		
	protected	$arrFieldList		= array('sec_config.*','sec_parent.name AS sec_parent_name','sys_folder.name AS sys_folder_name');
	protected	$arrJoinList		= array(
										'LEFT JOIN sec_config AS sec_parent ON sec_config.parent = sec_parent.id',
										'JOIN rel_sec_struct',
										'JOIN sec_config_order',
										'JOIN sec_struct',
										'JOIN sys_folder'
										);
	protected	$arrWhereList		= array(
											'rel_sec_struct.sec_config_id = sec_config.id',
											'rel_sec_struct.id = sec_config_order.field_id',
											'rel_sec_struct.sec_struct_id = sec_struct.id',
											'sys_folder.id = sec_config.sys_folder_id'
											);
	protected	$arrOrderList		= array('sec_config.id');
	
	protected	$arrFieldData		= array('rel_sec_struct.*','sec_config_order.field_order','sec_config_order.type','sec_struct.name AS sec_struct_name');
	protected	$arrJoinData		= array('JOIN rel_sec_struct','JOIN sec_config_order','JOIN sec_struct');
	protected	$arrWhereData		= array(
											'rel_sec_struct.sec_config_id = sec_config.id',
											'rel_sec_struct.id = sec_config_order.field_id',
											'rel_sec_struct.sec_struct_id = sec_struct.id'
											);
	protected	$arrGroupData		= array('rel_sec_struct.id');
	protected	$arrOrderData		= array('rel_sec_struct.id');
	
	protected	$arrStruct			= array();

	private		$strSecTable;
	private		$strChildTable;
	private		$intFieldID;
	private		$objManageContent;
	
	public		$objField;
	
	/**
	 * Class constructor
	 *
	 * @param	boolean	$boolRenderTemplate	Defines whether to show default's interface
	 * @param	integer	$intSecID			Section's ID
	 * @param	integer	$intFieldID			Field's ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($boolRenderTemplate = true,$intSecID = 0,$intFieldID = 0) {
		parent::__construct(false,$intSecID);
		
		// Sets manageContent_Controller() class' object
		$this->objManageContent = new manageContent_Controller($this->intSecID);
		
		// Sets editing Field ID
		if(!empty($intFieldID)) {
			$this->intFieldID	= $intFieldID;
		} elseif(empty($this->intFieldID)) {
			$this->intFieldID	= intval($_SESSION[self::$strProjectName]['URI_SEGMENT'][5]);
			if(empty($this->intFieldID)) $this->intFieldID = intval($_POST['id']);
		}
		
		// Gets STRUCT list
		$arrStruct	= $this->objModel->select(array('sec_struct.*'),'sec_struct',array(),array(),array('sec_struct.name ASC'),array(),0,null,'All');
		foreach($arrStruct AS $objTemp) {
			$this->arrStruct[$objTemp->id] = clone $objTemp;
		}
		
		// Gets SECTION list
		/**
		 * @todo set PROJECT_ID
		 */
		$this->arrSecList	= array();
		$arrTempSec			= $this->objModel->select(array('id','name','sys_folder_id','table_name'),'sec_config',array(),array(),array(),array(),0,null,'All');
		foreach($arrTempSec AS $intKey => $objTemp) {
			$objTemp->fields = $this->objModel->select(array('id','name','field_name'),'rel_sec_struct',array(),array('sec_config_id = ' . $objTemp->id),array(),array(),0,null,'All');
			$this->arrSecList[$objTemp->sys_folder_id][$objTemp->id] = $objTemp;
			
			if($objTemp->id == $this->intSecID) {
				$this->strSecTable = $objTemp->table_name;
			}
			
			if(!empty($_POST['child_id']) && $objTemp->id == $_POST['child_id']) {
				$this->strChildTable = $objTemp->table_name;
			}
		}
		#echo '<pre>'; var_dump($this->arrSecList[1]);
		$this->objSmarty->assign('arrSec',$this->arrSecList);
		$this->objSmarty->assign('arrStruct',$this->arrStruct);
		
		// Shows default interface
		if($boolRenderTemplate || !is_numeric($this->intSecID) || $this->intSecID <= 0) $this->_read();
	}
	
	/**
	 * Gets Field List data and sets $this->objData according to $this->intSecID
	 * 
	 * @return	void
	 *
	 * @since 	2013-02-19
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	private function getFieldList() {
		$this->objData = $this->objManageContent->getFieldList($this->intSecID);
	}
	
	/**
	 * Gets specific Field data and sets $this->objData according to $this->intSecID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-19
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	private function getFieldData() {
		$this->objField = $this->objManageContent->getFieldData($this->intFieldID);
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
			$this->getFieldList();
			#echo '<pre>'; print_r($this->objData); die();
			$this->objSmarty->assign('objData',$this->objData);
			$this->renderTemplate(true,$this->strModule . '_form.html');
		} else {
			// Shows list interface
			$this->objSmarty->assign('ALERT_MSG','You must choose an item to update!');
			$this->_read();
		}
	}
	
	/**
	 * Deletes field
	 * 
	 * @return	void
	 * 
	 * @since 	2013-02-16
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function delete() {
		// Gets field data
		$this->getFieldData();
		
		// Relationship field
		if($this->objField->type == 2) {
			// Deletes all table records
			$this->objModel->delete($this->objField->table_name);
			
			// Drops relationship table
			$this->objManage->drop('Table',$this->objField->table_name);
			
			// Drops relationship field
			$this->objManage->alterTable($this->strSecTable,array('remove' => array($this->objField->field_name => array())));
			
			// Deletes Applr Section info
			$this->objModel->delete('rel_sec_sec','id = ' . $this->intFieldID);
			$this->objModel->delete('sec_config_order','field_id = ' . $this->intFieldID);
		// Struct field
		} else {
			$doDelete = true;
			
			// If has extra_field, deletes it
			if($this->arrStruct[$this->objField->sec_struct_id]->extra_field) {
				$strExtraFieldName = $this->objField->field_name . '_old';
				$doDelete = $this->objManage->alterTable($this->strSecTable,array('remove' => array($strExtraFieldName => array())));
			}
			
			// Drops field
			if($doDelete) {
				if($this->objManage->alterTable($this->strSecTable,array('remove' => array($this->objField->field_name => array())))) {
					// Deletes Applr Section info
					$this->objModel->delete('rel_sec_struct','id = ' . $this->intFieldID);
					$this->objModel->delete('sec_config_order','field_id = ' . $this->intFieldID);
				}
			}
		}
		
		$this->_create();
	}
	
	/**
	 * Gets specific Field data and fills Struct Builder form
	 *
	 * @return void
	 * 
	 * @since 	2013-02-16
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function edit() {
		if(!is_numeric($this->intFieldID) || empty($this->intFieldID)) {
			// Shows form interface
			$this->objSmarty->assign('ALERT_MSG','You must choose an item to update!');
			$this->_create();
			exit();
		}
		
		// Gets Field Data
		$this->getFieldData();
		$this->objSmarty->assign('objField',$this->objField);
	
		// Gets Fields List
		$this->getFieldList();
		$this->objSmarty->assign('objData',$this->objData);
		
		// Shows interface
		$this->renderTemplate(true,$this->strModule . '_form.html');
	}
	
	/**
	 * Sets field new order
	 * 
	 * @param	integer	$intMove	Moving index; i.e. -1 => moves field 1 level up
	 * 
	 * @return	void
	 */
	private function orderField($intMove = -1) {
		// Gets full field list
		$this->getFieldList();
		
		// Reorders all list items
		$i = 1;
		$arrReplace = array();
		foreach($this->objData AS $objTemp) {
			$arrReplace[($i - 1)] = array(
								'field_id'		=> $objTemp->id,
								'sec_config_id' => $this->intSecID,
								'field_order'	=> $i,
								'type'			=> $objTemp->type
							);
			
			// Sets moving indexes
			if($objTemp->id == $this->intFieldID) {
				$intOrder		= $i - 1;
				$intMoveOrder	= ($i + $intMove) - 1;
			}
			
			$i++;
		}
		
		// Move array elements according to moving indexes
		$arrReplace[$intMoveOrder]['field_order'] 	= $intOrder + 1;
		$arrReplace[$intOrder]['field_order'] 		= $intMoveOrder + 1;
		
		return $this->objModel->replace('sec_config_order',$arrReplace,false);
	}
	public function orderUp() {
		if(!$this->orderField(-1)) {
			$this->objSmarty->assign('ERROR_MSG','There was an error trying to reorder Section Fields! Please, try again!');
		}
		
		$this->_create();
	}
	public function orderDown() {
		if(!$this->orderField(+1)) {
			$this->objSmarty->assign('ERROR_MSG','There was an error trying to reorder Section Fields! Please, try again!');
		}
		
		$this->_create();
		
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
		
#echo '<pre>'; print_r($_POST); #die();
		
		// Sets DYNAMIC field data
		if(isset($_POST['field']) && $_POST['field'] == 1) {
			// Sets main insert table
			$strTable			= 'rel_sec_struct';
			$intFieldType		= 1;
			
			// Sets field name syntax
			$strSuffix				= (!empty($this->arrStruct[$_POST['sec_struct_id']]->suffix ) ? '_' . (string) $this->arrStruct[$_POST['sec_struct_id']]->suffix : '');
			$_POST['field_name'] 	= str_replace('-','_', Controller::permalinkSyntax( $_POST['field_name'] . $strSuffix ));
			
			// Sets specific field data to validate
			$this->arrFieldType['sec_struct_id']	= 'numeric';
			
			// If is editing a field
			if(!empty($_POST['id'])) {
				// Sets rel_sec_struct.id param
				$this->arrFieldType['id']	= 'numeric';
				
				// Gets original field data
				$this->getFieldData();
				
				// Clears original field name syntax
				$strSuffix				= (!empty($this->arrStruct[$this->objField->sec_struct_id]->suffix ) ? '_' . (string) $this->arrStruct[$this->objField->sec_struct_id]->suffix : '');
				if(!empty($strSuffix) && ($intPos = strrpos($_POST['field_name'],$strSuffix)) !== false) $_POST['field_name'] 	= substr_replace($_POST['field_name'],'',$intPos);
			}
			
		// Sets RELATIONSHIP field data
		} elseif(isset($_POST['field']) && $_POST['field'] == 2) {
			// Sets main insert table
			$strTable			= 'rel_sec_sec';
			$intFieldType		= 2;
			
			// Sets specific field data to validate
			$this->arrFieldType	= 	array_merge($this->arrFieldType,array(
																		'child_id'		=> 'numeric',
																		'field_rel'		=> 'string',
																		'fieldtype'		=> 'numeric',
																		'table_name'	=> 'string'
																	)
									);
			
			// Defines rel table name
		/**
		 * @todo set PROJECT_ID
		 */
			$strChildTable			= $this->objModel->recordExists('table_name','sec_config','id = ' . $_POST['child_id'],true);
			if(!empty($strChildTable)) {
				$_POST['table_name']	= 'sec_rel_' . $this->strSecTable . ($this->strSecTable == $strChildTable ? '_parent' : '') . '_rel_' . $strChildTable . ($this->strSecTable == $strChildTable ? '_child' : '');
			}
			
			// If is editing a field, sets id param
			if(!empty($_POST['id'])) {
				$this->arrFieldType['id']	= 'numeric';
				$this->getFieldData();
			}
		}
#print_r($this->objField);
		// Validate $_POST params
		if(($mxdValidate = $this->validateParamsArray($_POST,$this->arrFieldType,false)) === true) {
#var_dump($mxdValidate);
			// Inserts field struct data
			if(($intFieldID = $this->objModel->replace($strTable,$_POST,true)) !== false) {
#var_dump($intFieldID);
#var_dump($_POST['id']);
				// If creating a new field
				if(empty($_POST['id'])) {
					// Gets next order index
					$intOrder	= $this->objModel->recordExists('(MAX(field_order) + 1) AS field_order','sec_config_order','sec_config_id = ' . $this->intSecID,true);
					if(!$intOrder) $intOrder = 1;
					
					// Sets field order data array
					$arrInsertOrder	=	array(
											'field_id'		=> $intFieldID,
											'sec_config_id'	=> $_POST['sec_config_id'],
											'field_order'	=> $intOrder,
											'type'			=> $intFieldType
										);
					
					// Inserts field order data
					if($this->objModel->insert('sec_config_order',$arrInsertOrder,true) !== false) {
						
						// Creates new field in section's table
						if(!$this->addField($_POST,($strTable == 'rel_sec_struct' ? true : false))) {
							$this->objModel->delete($strTable,'id = ' . $intFieldID);
							$this->objModel->delete('sec_config_order','field_id = ' . $intFieldID);
							$this->objSmarty->assign('ERROR_MSG','There was an error while trying to create "' . $_POST['name'] . '" field in database! Please try again!');
						}
						
					} else {
						$this->objModel->delete($strTable,'id = ' . $intFieldID);
						$this->objModel->delete('rel_sec_struct','id = ' . $intFieldID);
						$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save "' . $_POST['name'] . '" order data! Please try again!');
					}
				
				// Else, if editing dynamic field
				} else { //if($strTable == 'rel_sec_struct') {
					if(!$this->alterField($_POST,$this->objField->field_name)) {
						$this->objSmarty->assign('ERROR_MSG','There was an error while trying to alter "' . $_POST['name'] . '" field in database! Please try again!');
					}
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
		
		switch($boolStruct) {
			// rel_sec_struct
			case true:
			default:
				// Sets RDBMS struct array
				$arrStruct				= array();
				$arrStruct['type'] 		= $this->arrStruct[$arrData['sec_struct_id']]->fieldtype;
				$arrStruct['unsigned'] 	= $this->arrStruct[$arrData['sec_struct_id']]->is_unsigned;
				$arrStruct['null'] 		= $this->arrStruct[$arrData['sec_struct_id']]->notnull;
				$arrStruct['default'] 	= $this->arrStruct[$arrData['sec_struct_id']]->default_value;
				if($this->arrStruct[$arrData['sec_struct_id']]->length > 0) {
					$arrStruct['length']= $this->arrStruct[$arrData['sec_struct_id']]->length;
				}
				
				$arrDefaultFields 		= array('add' =>array($arrData['field_name'] => $arrStruct));
				
				// If FIELD CONFIG defines extra_field
				if($this->arrStruct[$arrData['sec_struct_id']]->extra_field) {
					
					// Creates extra field
					$strExtraFieldName	= $arrData['field_name'] . '_old';
					$arrExtraFields		= array('add' =>array($strExtraFieldName => $arrStruct));
					if($this->objManage->alterTable($this->strSecTable,$arrExtraFields)) {
						
						// Creates main field
						if($this->objManage->alterTable($this->strSecTable,$arrDefaultFields)) {
							return true;
						} else {
							// If cannot create main field, drops extra field
							$this->objManage->alterTable($this->strSecTable,array('remove' => array($strExtraFieldName => array())));
						}
						
					} else {
						return false;
					}
					
				} else {
					// Creates main field
					return $this->objManage->alterTable($this->strSecTable,$arrDefaultFields);
				}
			break;
			
			// rel_sec_sec
			case false:
				$arrParent = array(
						'table' => $this->strSecTable,
						'field' => array(
								'name' 		=> 'parent_id',
								'reference' => 'id'
						)
				);
				
				$arrChild = array(
						'table' => $this->strChildTable,
						'field' => array(
								'name' 		=> 'child_id',
								'reference' => 'id'
						)
				);
				
				if($this->objManage->createRelationTable($arrParent,$arrChild,$arrData['table_name'])) {

					// Sets RDBMS struct array
					$arrStruct				= array();
					$arrStruct['type'] 		= 'integer';
					$arrStruct['null'] 		= 1;
					$arrStruct['default'] 	= null;
					
					$arrDefaultFields 		= array('add' =>array($arrData['field_name'] => $arrStruct));
					
					return $this->objManage->alterTable($this->strSecTable,$arrDefaultFields);
				}
			break;
		} return false;
	}
	
	/**
	 * Alters Section field
	 *
	 * @param	array	$arrData		New field info; mandatory data is array('field_name' => string, 'sec_struct_id' => integer).
										RDBMS struct data from $this->arrStruct[$arrData['sec_struct_id']]; if $arrData['sec_struct_id'] is not set, assumes single text line
	 * @param	string	$strFieldName	Original field name
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-02-15
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function alterField($arrData,$strFieldName) {
		#if(!is_array($arrData) || !isset($arrData['sec_struct_id']) || !isset($this->arrStruct[$arrData['sec_struct_id']])) $arrData['sec_struct_id'] = 1;
		if(!is_array($arrData)) return false;
		if(!is_string($arrData['field_name']) || empty($arrData['field_name'])) return false;
		if(!is_string($strFieldName) || empty($strFieldName)) return false;
		
		if(empty($this->objField)) $this->getFieldData();
		
		#echo '<pre>'; print_r($arrData); die();
		
		// If new field is RELATIONSHIP
		if(empty($arrData['sec_struct_id']) && !empty($arrData['child_id'])) {
			
			// If previous field is DYNAMIC and new one is RELATIONSHIP
			if($this->objField->type == 1) {
				$doAlter = true;

				// Sets new field temp name
				$strNewField_Name = $arrData['field_name'];
				$arrData['field_name'] .= '_APPLR_TMP';
				
				/**
				 * @TODO optimize continue / restore process
				 */
				// Creates new temp field and relationship table
				if($this->addField($arrData,false)) {
					
					// Drops previous field
					if($this->objManage->alterTable($this->strSecTable,array('remove' => array($strFieldName => array())))) {
						// If previous field type requires extra_field
						if($this->arrStruct[$this->objField->sec_struct_id]->extra_field) {
							// Drops previous extra_field
							$strExtraFieldName_Old 	= $strFieldName . '_old';
							$this->objManage->alterTable($this->strSecTable,array('remove' => array($strExtraFieldName_Old => array())));
						}
						
						// Renames temp field
						$arrStruct				= array();
						$arrStruct['name']		= $strNewField_Name;
						$arrStruct['type'] 		= 'integer';
						$arrStruct['null'] 		= 1;
						$arrStruct['default'] 	= null;
						$arrDefaultFields 		= array('rename' => array($arrData['field_name'] => array('name' => $strFieldName,'definition' => $arrStruct)));
						
						if($this->objManage->alterTable($this->strSecTable,$arrDefaultFields)) {
							// Delete rel_sec_struct record
							$this->objModel->delete('rel_sec_struct','rel_sec_struct.id = ' . $this->objField->id);
							
							// Updates sec_config_order.type
							$this->objModel->update('sec_config_order',array('type' => 2),'field_id = ' . $arrData['id']);

							/*
							 * @TODO if delete or update fails, restore ALL previous configs
							*/
							return true;
						} else {
							/*
							 * @TODO if rename temp field fails, restore ALL previous configs
							 */
							return false;
						}
						
					} else {
						// Drops relationship table
						$this->objManage->drop('Table','rel_' . $this->strSecTable . '_' . $this->strChildTable);
							
						// Drops temp field
						$this->objManage->alterTable($this->strSecTable,array('remove' => array($arrData['field_name'] => array())));
							
						return false;
					}
					
				} else {
					return false;
				}
				
			// If both, previous and new field, are RELATIONSHIP and DIFFERENT to each other 
			} elseif($arrData['child_id'] != $this->objField->child_id) {
				
				// Drops previous relationship table
				if($this->objManage->drop('Table',$this->objField->table_name)) {
					// Drops previous relationship field
					$this->objManage->alterTable($this->strSecTable,array('remove' => array($this->objField->field_name => array())));
						
					// Adds new relationship field and table
					if($this->addField($arrData,false)) {
						// Delete rel_sec_sec previous record
						$this->objModel->delete('rel_sec_sec','table_name = "' . $this->objField->table_name . '"');
				
						return true;
					} else {
						// Delete rel_sec_sec previous record
						$this->objModel->delete('rel_sec_sec','table_name = "' . $arrData['table_name'] . '"');
				
						return false;
					}
				} else {
					return false;
				}
				
			} else {
				return true;
			}
		// If new field is DYNAMIC
		} else {
			// If previous field is RELATIONSHIP and new one is DYNAMIC
			if($this->objField->type == 2) {
				// Delete rel_sec_sec record
				$this->objModel->delete('rel_sec_sec','rel_sec_sec.id = ' . $this->objField->id);
				
				// Deletes all table records
				$this->objModel->delete($this->objField->table_name);
				
				// Drops relationship table
				$this->objManage->drop('Table',$this->objField->table_name);
		
				// Updates sec_config_order.type
				$this->objModel->update('sec_config_order',array('type' => 1),'field_id = ' . $arrData['id']);
			}
			
			// Sets RDBMS struct array
			$arrStruct['name']		= $arrData['field_name'];
			$arrStruct['type'] 		= $this->arrStruct[$arrData['sec_struct_id']]->fieldtype;
			$arrStruct['unsigned'] 	= $this->arrStruct[$arrData['sec_struct_id']]->is_unsigned;
			$arrStruct['null'] 		= $this->arrStruct[$arrData['sec_struct_id']]->notnull;
			$arrStruct['default'] 	= $this->arrStruct[$arrData['sec_struct_id']]->default_value;
			if($this->arrStruct[$arrData['sec_struct_id']]->length > 0) {
				$arrStruct['length']= $this->arrStruct[$arrData['sec_struct_id']]->length;
			}
			$arrDefaultFields 		= array('rename' => array($strFieldName => array('name' => $arrData['field_name'],'definition' => $arrStruct)));
	
			// Sets RDBMS struct array for EXTRA FIELD
			$strExtraFieldName_Old 	= $strFieldName . '_old';
			$strExtraFieldName		= $arrData['field_name'] . '_old';
			$arrExtraStruct			= $arrStruct;
			$arrExtraStruct['name'] = $strExtraFieldName;
			$arrExtraFields 		= array('rename' => array($strExtraFieldName_Old => array('name' => $strExtraFieldName,'definition' => $arrStruct)));
	
			// If field type is different from previous config
			if($arrData['sec_struct_id'] != $this->objField->sec_struct_id) {
				$doAlter = true;
				
				// If previous field type requires extra_field
				if($doAlter && $this->arrStruct[$this->objField->sec_struct_id]->extra_field) {
					// Deletes previous extra_field
					$doAlter = $this->objManage->alterTable($this->strSecTable,array('remove' => array($strExtraFieldName_Old => array())));
				}
				
				// If new field type requires extra_field
				if($doAlter && $this->arrStruct[$arrData['sec_struct_id']]->extra_field) {
					// Creates new extra_field
					unset($arrExtraStruct['name']);
					$arrExtraFields	= array('add' =>array($strExtraFieldName => $arrExtraStruct));
					$doAlter		= $this->objManage->alterTable($this->strSecTable,$arrExtraFields);
				}
				
				// Alters main field
				if($doAlter) {
					return $this->objManage->alterTable($this->strSecTable,$arrDefaultFields);
				} else {
					return false;
				}
				
			} else {
				
				// If requires extra_field
				if($this->arrStruct[$arrData['sec_struct_id']]->extra_field) {
					// Alters extra_field
					if($this->objManage->alterTable($this->strSecTable,$arrExtraFields)) {
						return $this->objManage->alterTable($this->strSecTable,$arrDefaultFields);
					} else {
						return false;
					}
				} else {
					// Alters main field
					return $this->objManage->alterTable($this->strSecTable,$arrDefaultFields);
				}
				
			}
		}
	}
}