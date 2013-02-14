<?php
class manageDB_Controller extends Controller {
	protected	$objModel;
	
	public function __construct() {
		parent::__construct();
		
		$this->objModel = new manageDB_Model();
	}
	
	/**
	 * Creates new database and set it to work with
	 * 
	 * @param	string	$strValue	Database name
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-02-13
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function createDatabase($strValue) {
		if(!is_string($strValue) || empty($strValue)) return false;
		
		if($this->objModel->objConn->createDatabase($strValue)) {
			$this->objModel->objConn->setDatabase($strValue);
			
			return true;
		}
		return false;
	}
	
	/**
	 * Creates Table with default fields `id` (integer) and `name` (string[255])
	 * 
	 * @param	string	$strValue		Table name
	 * @param	array	$arrDataDef		Table fields data definition
	 * @param	array	$arrTableOpt	Specifics DBMS options
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-02-13
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function createTable($strValue,$arrDataDef,$arrTableOpt = array()) {
		if(!is_string($strValue) 	|| empty($strValue)) 	return false;
		if(!is_array($arrDataDef) 	|| empty($arrDataDef)) 	return false;
		
		// Checks default fields 
		if(!isset($arrDataDef['id']) || !is_array($arrDataDef['id']) || !isset($arrDataDef['id']['type']) || $arrDataDef['id']['type'] != 'integer') {
			$arrDataDef['id'] = array(
									'type'		=> 'integer',
									'unsigned'	=> 1,
									'notnull'	=> 1
								);
		}
		if(!isset($arrDataDef['name']) || !is_array($arrDataDef['name']) || !isset($arrDataDef['name']['type']) || $arrDataDef['name']['type'] != 'text') {
			$arrDataDef['name'] = array(
									'type'		=> 'text',
									'length'	=> 255,
									'notnull'	=> 1
								);
		}
		
		// Sets specifics DBMS options
		if(empty($arrTableOpt)) {
			switch(DB_TYPE) {
				case 'mysql':
				case 'mysqli':
				default:
					$arrTableOpt = 	array(
										'charset' => 'utf8',
										'collate' => 'utf8_unicode_ci',
										'type'    => 'innodb'
									);
				break;
			}
		}
		
		if($this->objModel->objConn->createTable($strValue,$arrDataDef,$arrTableOpt)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Creates PRIMARY KEY attribute on $strValue TABLE
	 *
	 * @param	string	$strValue		Table name
	 * @param	string	$strField		Field name
	 *
	 * @return	boolean
	 *
	 * @since 	2013-02-13
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function setPrimaryKey($strValue,$strField = 'id') {
		if(!is_string($strValue) 	|| empty($strValue)) 	return false;
		if(!is_string($strField) 	|| empty($strField)) 	$strField = 'id';
		
		$arrKeyDef	=	array (
							'primary'	=> true,
							'fields'	=> array ('id' => array())
						);
		if($this->objModel->objConn->createConstraint($strValue, $strValue . '_PK', $arrKeyDef)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Creates UNIQUE KEY $strField attribute on $strValue TABLE
	 *
	 * @param	string	$strValue		Table name
	 * @param	string	$strField		Field name
	 *
	 * @return	boolean
	 *
	 * @since 	2013-02-13
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function setUniqueKey($strValue,$strField) {
		if(!is_string($strValue) 	|| empty($strValue)) 	return false;
		if(!is_string($strField) 	|| empty($strField)) 	return false;
		
		$arrKeyDef	=	array (
							'unique'	=> true,
							'fields'	=> array ($strField => array())
						);
		if($this->objModel->objConn->createConstraint($strValue, $strField . '_Unq', $arrKeyDef)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Creates FOREIGN KEY attribute on $strMainTable TABLE reffering $strRelatedTable
	 *
	 * @param 	string	$strMainTable		Main table name
	 * @param 	string	$strRelatedTable	Related table name
	 * @param 	array 	$arrMainKey			Main key array definition, array( fieldname => array( ['sorting' => ascending|descending], ['position' => integer] )), one for each field covered
	 * @param 	array 	$arrReferedKey		Related key array definition, array( fieldname => array( ['sorting' => ascending|descending], ['position' => integer] )), one for each referenced field
	 * @param 	string 	$strOnDelete		FK ON DELETE action
	 * @param 	string 	$strOnUpdate		FK ON UPDATE action
	 * @param 	string 	$strMatch			FK MATCH parameter
	 *
	 * @return	boolean
	 *
	 * @since 	2013-02-13
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function setForeignKey($strMainTable,$strRelatedTable,$arrMainKey = array('id' => array()),$arrReferedKey = array('id' => array()),$strOnDelete = 'CASCADE',$strOnUpdate = 'CASCADE',$strMatch = 'FULL') {
		if(!is_string($strMainTable) 	|| empty($strMainTable)) 	return false;
		if(!is_string($strRelatedTable) || empty($strRelatedTable)) return false;
		
		// Defines Keys array
		if(!is_array($arrMainKey) || empty($arrMainKey)) {
			$arrMainKey = array('id' => array());
		}
		if(!is_array($arrReferedKey) || empty($arrReferedKey)) {
			$arrReferedKey = array('id' => array());
		}
		
		// Defines OnDelete and OnUpdate actions
		$arrOnActions = array('CASCADE','RESTRICT','SET NULL','SET DEFAULT','NO ACTION');
		$strOnDelete = strtoupper($strOnDelete);
		if(!is_string($strOnDelete) || empty($strOnDelete) || !in_array($strOnDelete,$arrOnActions)) $strOnDelete = 'CASCADE';
		$strOnUpdate = strtoupper($strOnUpdate);
		if(!is_string($strOnUpdate) || empty($strOnUpdate) || !in_array($strOnUpdate,$arrOnActions)) $strOnUpdate = 'CASCADE';
		
		// Defines Match parameter
		$arrMatchActions = array('SIMPLE','PARTIAL','FULL');
		$strMatch = strtoupper($strMatch);
		if(!is_string($strMatch) || empty($strMatch) || !in_array($strMatch,$arrOnActions)) $strMatch = 'FULL';
		
		
		$arrKeyDef =	array (
							'primary'		=> 	false,
							'unique'		=> 	false,
							'foreign'		=> 	true,
							'check'			=> 	false,
							'fields'		=> 	$arrMainKey,
							'references' 	=> 	array(
													'table'				=> $strRelatedTable,
													'fields' 			=> $arrReferedKey,
													'deferrable' 		=> false,
													'initiallydeferred' => false,
													'onupdate' 			=> $strOnUpdate,
													'ondelete' 			=> $strOnDelete,
													'match' 			=> $strMatch
												)
						);
		if($this->objModel->objConn->createConstraint($strMainTable, $strMainTable . '_' . $strRelatedTable . '_FK', $arrKeyDef)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Sets $strField as INDEX on $strValue TABLE
	 *
	 * @param	string	$strValue		Table name
	 * @param	string	$strField		Field name
	 * @param	array	$arrIndexDef	Index definitions ('sorting' => ascending|descending, 'length' => integer)
	 *
	 * @return	boolean
	 *
	 * @since 	2013-02-13
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function setIndex($strValue,$strField,$arrIndexDef = array('sorting' => 'descending')) {
		if(!is_string($strValue) 	|| empty($strValue)) 	return false;
		if(!is_string($strField) 	|| empty($strField)) 	return false;

		// Defines Index definitions array
		if(!is_array($arrIndexDef) || empty($arrIndexDef)) {
			$arrIndexDef = array('sorting' => 'descending');
		}
		
		$arrKeyDef	=	array ( 'fields' => array ( $strField => $arrIndexDef ) );
		if($this->objModel->objConn->createIndex($strValue, $strField . '_Idx', $arrIndexDef)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Creates SEQUENCE / AUTO_INCREMENT attribute on $strName table
	 * 
	 * @param	string	$strValue		Table name
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-02-13
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function createSequence($strValue) {
		if(!is_string($strValue) 	|| empty($strValue)) 	return false;
		
		if($this->objModel->objConn->createSequence($strValue,1)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Alters table element
	 *
	 * @param	string	$strValue		Table name
	 * @param	array	$arrAlterParams	Array definition for each ALTER action, such as 
											$arrAlterParams['add'] 		= array(name => array( 'type' => 'text', 'length' => 20 ));
											$arrAlterParams['remove'] 	= array(name => array());
											$arrAlterParams['change'] 	= array(name => array( 'length' => '20', 'definition' => array( 'type' => 'text', 'length' => 20 ) ) );
											$arrAlterParams['rename'] 	= array(name => array('name' => newname, 'definition' => array( 'type' => 'text', 'length' => 1, 'default' => 'M' ) ) );
	 * @param	boolean	$boolCheck		If true, no changes will be made, but only a check if the proposed changes are feasible for the specific table and RDBMS
	 *
	 * @return	boolean
	 *
	 * @since 	2013-02-13
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function alter($strTable,$arrAlterParams = array(), $boolCheck = false) {
		if(!is_string($strTable) || empty($strTable)) 					return false;
		if(!is_bool($boolCheck) && $boolCheck != 0 && $boolCheck != 1)	$boolCheck = false;
		if(!is_array($arrAlterParams) || empty($arrAlterParams)) 		return false;
		
		if(!isset($arrAlterParams['add'])) 		$arrAlterParams['add']		= array();
		if(!isset($arrAlterParams['remove'])) 	$arrAlterParams['remove'] 	= array();
		if(!isset($arrAlterParams['change'])) 	$arrAlterParams['change'] 	= array();
		if(!isset($arrAlterParams['rename'])) 	$arrAlterParams['rename'] 	= array();
		
		// Sets altering array params
		$strNewTableName	= $strTable;
		$arrChangesDef 		= 	array(
								'name'		=> $strNewTableName,
								'add'		=> $arrAlterParams['add'],
								'remove'	=> $arrAlterParams['remove'],
								'change'	=> $arrAlterParams['change'],
								'rename'	=> $arrAlterParams['rename']
							);
		
		if($this->objModel->objConn->alterTable($strTable,$arrChangesDef,$boolCheck)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Drops database element
	 * 
	 * @param	string	$strMethod	Method name
	 * @param	string	$strValue	Element name
	 * @param	string	$strArgs	Arguments, in case of dropConstraint or dropIndex
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-02-13
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function drop($strMethod,$strValue,$strArgs) {
		if(!is_string($strMethod) 	|| empty($strValue)) 	return false;
		if(!is_string($strValue) 	|| empty($strValue)) 	return false;
		if(!is_string($strArgs) 	|| empty($strArgs)) 	return false;
		
		$arrMethod	= array('Sequence','Constraint','Index','Table','Database');
		if(!in_array($strMethod,$arrMethod)) return false;
		
		$strMethod	= 'drop' . ucfirst(strtolower($strMethod));
		if($strMethod == 'dropConstraint' || $strMethod == 'dropIndex') {
			if(strtoupper($strArgs) == 'PRIMARY') $strArgs = 'PRIMARY';
			$mxdReturn	= $this->objModel->objConn->$strMethod($strValue,$strArgs,($strArgs == 'PRIMARY' ? true : false));
		} else {
			$mxdReturn	= $this->objModel->objConn->$strMethod($strValue);
		}
		
		if($mxdReturn) {
			return true;
		}
		return false;
	}
}
?>