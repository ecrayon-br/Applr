<?php
class Model {
	
	protected 	$_dbType	= DB_TYPE;
	protected 	$_hostName	= DB_HOST;
	protected 	$_userName	= DB_USER;
	protected 	$_password	= DB_PASSWORD;
	protected 	$_dbName	= DB_NAME;
	
	protected 	$_boolDebug = false;
	
	public  	$objConn;
	
	public		$boolConnStatus;
	public		$intError;
	
	/**
	 * Class constructor
	 * 
	 * @param 	mixed	$intConnection	Client's connection ID
	 * 
	 * @since	2013-01-18
	 * @author	Diego Flores <diegotf [at] gmail dot com>
	 * 
	**/
	public function __construct($intConnection = null) {
		$this->boolConnStatus = $this->setConnection($intConnection);
		
		if($this->boolConnStatus) {
			$this->objConn->loadModule('Extended');
			$this->objConn->query('SET group_concat_max_len = 1024000;');
		}
	}
	
	/**
	 * Creates DB connection
	 * 
	 * @param 	mixed	$intConnection	Client's connection ID
	 * 
	 * @return	boolean
	 *
	 * @author	Diego Flores <diegotf [at] gmail dot com>
	 * @since	2013-01-18
	**/
	public function setConnection($intConnection = null) {
		if(!is_null($intConnection) && !is_numeric($intConnection))	return false;
		
		$arrDSN			= 	array(
								'phptype'	=> $this->_dbType,
								'hostspec'	=> $this->_hostName,
								'database'	=> $this->_dbName,
								'username'	=> $this->_userName,
								'password'	=> $this->_password,
								'new_link'	=> true
							);
		
		$arrOptions		= array	(
		    					'debug'       	=> 2,
		    					'portability' 	=> MDB2_PORTABILITY_ALL,
								'persistent'	=> true,
								'seqcol_name'	=> 'id',
								'seqname_format'=> '%s'
								);
		
		$this->objConn 	=& PEAR_ApplrDB::singleton($arrDSN,$arrOptions);
		
		if($this->_boolDebug) { echo '<pre>'; var_dump($this->objConn); echo '</pre>'; }
		
		if (MDB2::isError($this->objConn)) {
			define('ERROR_MSG','Error on Model::setConnection(): ' . $this->objConn->getMessage());
			return false;
		}
		
		$this->setFetchMode('OBJECT');
		
		return true;
	}
	
	/**
	 * Sets connections vars
	 *
	 * @param	string	$strTemp	Value to be assigned
	 * 
	 * @return	void
	 *
	 * @author	Diego Flores
	 * @since	2007-06-04
	**/
	public function setDBType($strTemp) {
		// Validates attribute
		if(!is_string($strTemp) || empty($strTemp))	return false;
		
		$this->_dbType		= $strTemp;
	}
	
	public function setHostName($strTemp) {
		// Validates attribute
		if(!is_string($strTemp) || empty($strTemp))	return false;
		
		$this->_hostName	= $strTemp;
	}
	
	public function setUserName($strTemp) {
		// Validates attribute
		if(!is_string($strTemp) || empty($strTemp))	return false;
		
		$this->_userName	= $strTemp;
	}
	
	public function setPassword($strTemp) {
		// Validates attribute
		if(!is_string($strTemp) || empty($strTemp))	return false;
		
		$this->_password	= $strTemp;
	}
	
	public function setDBName($strTemp) {
		// Validates attribute
		if(!is_string($strTemp) || empty($strTemp))	return false;
		
		$this->_dbName		= $strTemp;
	}
	
	private function setDebug($boolDebug) {
		// Validates attribute
		if(!is_bool($boolDebug) && $boolDebug !== 0 && $boolDebug !== 1)	$boolDebug = false;
		
		$this->_boolDebug = $boolDebug;
	}
	
	/**
	 * Returns connection vars values
	 *
	 * @return	string
	 * 
	 * @author	Diego Flores
	 * @since	2007-10-10
	**/
	public function getDBType() {
		return $this->_dbType;
	}
	
	public function getHostName() {
		return $this->_hostName;
	}
	
	public function getUserName() {
		return $this->_userName;
	}
	
	public function getPassword() {
		return $this->_password;
	}
	
	public function getDBName() {
		return $this->_dbName;
	}
	
	/**
	 * Defines result queries FETCHMODE 
	 *
	 * @param	string	$mxdFetch	Defines fetchmode constant
	 * 
	 * @return	void
	 *
	 * @author	Diego Flores <diegotf [at] gmail dot com>
	 * @since	2008-01-29
	**/
	public function setFetchMode($mxdFetch = 0)  {
		if(!is_object($this->objConn))							return false;
		if(!is_string($mxdFetch) && !is_numeric($mxdFetch))		return false;
		if(is_string($mxdFetch))								$mxdFetch = strtoupper($mxdFetch);
		
		switch($mxdFetch) {
			case 'MDB2_FETCHMODE_OBJECT':
			case 'OBJ':
			case 'OBJECT':
			case 0:
				$this->objConn->setFetchMode(MDB2_FETCHMODE_OBJECT);
			break;
			
			case 'MDB2_FETCHMODE_ORDERED ':
			case 'ORDERED':
			case 1:
			default:
				$this->objConn->setFetchMode(MDB2_FETCHMODE_ORDERED);
			break;
			
			case 'MDB2_FETCHMODE_ASSOC':
			case 'ASSOC':
			case 2:
				$this->objConn->setFetchMode(MDB2_FETCHMODE_ASSOC);
			break;
		}
	}
	
	/**
	 * Checks fields syntax and prepare string values with pre and post quotes. Assumes that number of columns is count(reset($arrData))
	 *
	 * @param 		array $arrData				Array with query values
	 * 
	 * @return		array
	 * 
	 * @since 		2008-12-07
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function prepareColumnData($arrData) {
		if(!is_array($arrData) || count($arrData) == 0)	return false;
		
		// Defines column quantity per row
		$intColumn	= count(reset($arrData));
		foreach($arrData AS $intRowKey => &$arrRowData) {
			$intRow	= count($arrRowData);
			
			if($intRow > 0) {
				// Completes empty fields in rows array
				if($intRow < $intColumn) {
					// Defines difference between column quantity and $arrRowData elements
					$intDiffColumn 	= $intColumn - $intRow;
					
					// Fills $arrRowData with empty values for missing elements
					for($intI = 0; $intI < $intDiffColumn; $intI++) array_push($arrRowData,'');
				}
				
				foreach($arrRowData AS $intColumnKey => &$mxdColumnData) {
					// Checks STRING syntax
					if(is_null($mxdColumnData) || strtoupper($mxdColumnData) == 'NULL') {
						$mxdColumnData = 'NULL';
					} elseif(is_string($mxdColumnData) && strtoupper($mxdColumnData) != 'NULL' && strpos($mxdColumnData,'"') !== 0 && strpos($mxdColumnData,"'") !== 0) {
						$mxdColumnData = $this->objConn->quote($mxdColumnData);
					} elseif($mxdColumnData === "") {
						$mxdColumnData = $this->objConn->quote('','text',true);
					}
				}
			} else {
				unset($arrData[$intRowKey]);
			}
		}
		
		return $arrData;
	}
	
	/**
	 * Prepare column sintax for INSERT queries
	 *
	 * @param 		array $arrData				Array with query values
	 * 
	 * @return		array
	 * 
	 * @since 		2008-12-07
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function prepareInsertRowSyntax($arrData) {
		if(!is_array($arrData) || count($arrData) == 0)	return false;
		
		// Prepares column data
		$arrData	= $this->prepareColumnData($arrData);
		
		// Prepare row syntax
		foreach($arrData AS $intRowKey => &$arrRowData) {
			$arrRowData = '('.implode(',',$arrRowData).')';
		}
		
		return $arrData;
	}
	
	/**
	 * Prepare column sintax for UPDATE queries
	 *
	 * @param 		array $arrData				Array with query values
	 * 
	 * @return		array
	 * 
	 * @since 		2009-04-28
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function prepareUpdateRowSyntax($arrData) {
		if(!is_array($arrData) || count($arrData) == 0)	return false;
		
		// Prepares column data
		$arrData	= $this->prepareColumnData($arrData);
		$arrData	= reset($arrData);
		
		// Prepare row syntax
		foreach($arrData AS $strRowKey => &$strValue) {
			$strValue = $strRowKey . ' = ' . $strValue;
		}
		
		return $arrData;
	}
	
	
	/**
	 * Sets INSERT/REPLACE/UPDATE data syntax
	 *
	 * @param 		array 	$arrData			Array data, where key is DB field and value is query value
	 * @param		boolean	$boolPreserveKeys	Defines whether result array must preserve original keys or get ordered indexes
	 *
	 * @return		array
	 *
	 * @since 		2013-02-08
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	private function setDataSyntax(&$arrData,$boolPreserveKeys = true) {
		foreach($arrData AS $mxdColumnKey => &$mxdColumnData) {
			// Checks STRING syntax
			if(is_null($mxdColumnData) || strtoupper($mxdColumnData) == 'NULL') {
				$mxdColumnData = null;
			} elseif(is_string($mxdColumnData) && strpos($mxdColumnData,'"') !== 0 && strpos($mxdColumnData,"'") !== 0) {
				$mxdColumnData = htmlentities($mxdColumnData,ENT_QUOTES,'UTF-8',false); //$this->objConn->quote(htmlentities($mxdColumnData,ENT_QUOTES,'UTF-8'));
			} elseif($mxdColumnData === "") {
				$mxdColumnData = ''; //$this->objConn->quote('','text',true);
			}
		}
		if(!$boolPreserveKeys) $arrData = array_values($arrData);
	}
	
	/**
	 * Treats UTF-8 and HTML SPECIAL CHARS
	 *
	 * @param 		array 	$arrData			Array data, where key is DB field and value is query value
	 * @param		boolean	$boolPreserveKeys	Defines whether result array must preserve original keys or get ordered indexes
	 * 
	 * @return		array
	 * 
	 * @since 		2013-01-23
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	private function prepareDataSyntax(&$arrData,$boolPreserveKeys = true) {
		if(!is_array($arrData) || count($arrData) == 0)	return false;
		
		if(is_array(reset($arrData))) {
			$intColumn = count($arrData[0]);
			
			foreach($arrData AS $mxdColumnKey => &$mxdColumnData) {
				$this->setDataSyntax($mxdColumnData,$boolPreserveKeys);
			}
		} else {
			$intColumn = count($arrData);
						
			$this->setDataSyntax($arrData,$boolPreserveKeys);
		}
		
		return $arrData;
	}
	
	/**
	 * Prepares INSERT query syntax
	 *
	 * @param 		string 	$strTable			Table name
	 * @param 		array 	$arrField			Column values
	 * @param 		boolean $boolInsertMode		Defines wether INSERT ( 1 ) or REPLACE ( 0 )
	 * 
	 * @return 		string
	 * 
	 * @since 		2008-12-07
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	protected function prepareInsertQuery($strTable,$arrField,$boolInsertMode = true) {
		if(!is_string($strTable)|| empty($strTable))									return false;
		if(!is_array($arrField) || count($arrField) == 0)								return false;
		if(!is_bool($boolInsertMode) && $boolInsertMode !== 1 && $boolInsertMode !== 0)	return false;
		
		// Gets array with DB::entity field name values 
		$arrField = array_keys(reset($arrField));
		
		if($boolInsertMode) {
			// Prepare query using MDB2::autoPrepare
			$objQuery	= $this->objConn->extended->autoPrepare($strTable,$arrField,MDB2_AUTOQUERY_INSERT);
		} else {
			// Prepare query using MDB2::prepare
			$objQuery = $this->objConn->extended->buildManipSQL($strTable,$arrField,MDB2_AUTOQUERY_INSERT);
			$objQuery = $this->objConn->prepare( str_replace('INSERT INTO','REPLACE INTO',$objQuery) );
		}
		
		if(MDB2::isError($objQuery)) {
			define('ERROR_MSG','Error on Model::prepareInsertQuery(): ' . $objQuery->getMessage());
			return false;
		} else {
			return $objQuery;
		}
	}
	
	/**
	 * Prepares UPDATE query syntax
	 *
	 * @param 		string 	$strTable			Table name
	 * @param 		array 	$arrField			Column values
	 * 
	 * @return 		string
	 * 
	 * @since 		2008-12-07
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	/*
	protected function prepareUpdateQuery($strTable,$arrField,$strWhere = '1') {
		if(!is_string($strTable)|| empty($strTable))		return false;
		if(!is_array($arrField) || count($arrField) == 0)	return false;
		if(is_array($strWhere)) 							$strWhere 	= implode(' AND ',$strWhere);
		
		// Treats UTF-8 and HTML SPECIAL CHARS
		$this->prepareDataSyntax($arrField);
		
		// Prepare query using MDB2::autoPrepare
		$objQuery	= $this->objConn->extended->autoPrepare($strTable,array_keys($arrField),MDB2_AUTOQUERY_UPDATE,$strWhere);
		if(MDB2::isError($objQuery)) {
			define('ERROR_MSG','Error on Model::prepareUpdateQuery(): ' . $objQuery->getMessage());
			return false;
		} else {
			return $objQuery;
		}
	}
	*/
	
	/**
	 * Checks if a specific record exists in database
	 * 
	 * @param	string	$strTable			DB::Attribute's name
	 * @param	string	$strField			DB::Entity's name
	 * @param	string	$strWhere			DB::Query WHERE statement
	 * @param	boolean	$boolReturnValue	Defines wether method returns BOOLEAN or DB::Attribute_VALUE
	 * 
	 * @return	mixed
	 * 
	 * @since 	2009-04-28
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function recordExists($strField,$strTable,$strWhere = '1',$boolReturnValue = false) {
		if(!is_string($strField)		|| empty($strField))	return false;
		if(!is_string($strTable)		|| empty($strTable))	return false;
		if(!is_string($strWhere)		|| empty($strWhere))	return false;
		if(!is_bool($boolReturnValue) 	&& $boolReturnValue !== 1 && $boolReturnValue !== 0)	return false;
		
		$objQuery	= $this->select($strField,$strTable,array(),$strWhere,array(),array(),0,1,'Row');
		$mxdReturn	= reset($objQuery);
		
		if(MDB2::isError($objQuery) || !$mxdReturn) {
			return false;
		} else {
			return ($boolReturnValue ? $mxdReturn : true);
		}
	}
	
	/**
	 * Executes query on database
	 *
	 * @param 		string $strQuery	Query to execute
	 * 
	 * @return 		If MDB2::isError, sets ERROR_MSG constant and returns false; if SELECT statement, returns MDB2_Result_object; is INSERT/DELETE/UPDATE statement, returns the number of affected rows
	 * 
	 * @since 		2013-01-18
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function executeQuery($strQuery) {
		if(!is_string($strQuery)|| empty($strQuery))	return false;
		
		if(strpos($strQuery,'SELECT') !== false) {
			$objQuery = $this->objConn->query($strQuery);
		
			if($this->_boolDebug) { echo '<pre>'; var_dump($objQuery); echo '</pre>'; }
			
			if(MDB2::isError($objQuery)) {
				define('ERROR_MSG','Error on Model::executeQuery->query(): ' . $objQuery->getMessage());
				return false;
			} else {
				return $objQuery;
			}
		} else {
			$objQuery = $this->objConn->exec($strQuery);
			
			#$this->objConn->free();
			
			if($this->_boolDebug) { echo '<pre>'; var_dump($objQuery); echo '</pre>'; }
			
			if(MDB2::isError($objQuery)) {
				define('ERROR_MSG','Error on Model::executeQuery->exec(): ' . $objQuery->getMessage());
				return false;
			} else {
				return $objQuery;
			}
		}
	}
	
	/**
	 * Returns last auto_increment primary key value inserted on database
	 *
	 * @param	string	$strTable	Table name
	 * 
	 * @return	integer
	 * 
	 * @since 	2009-04-27
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function getLastInsertID($strTable) {
		// Validates attribute
		if(!is_string($strTable) || empty($strTable))	return false;
		
		return $this->objConn->lastInsertID($strTable, 'id');
	}
	
	/**
	 * Inserts LOG record
	 * 
	 * @param	integer		$intUser	User ID
	 * @param	integer		$intSection	Section ID
	 * @param	integer		$intAction	Action code: 1 - inserted; 2 - updated; 3 - deleted; 4 - deleted permanently
	 * @param	integer		$intObject	Object code: 1 - register; 2 - section; 3 - user
	 * @param	string		$strContent	Object name
	 * 
	 * @return	boolean
	 * 
	 * @since 	2013-01-18
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setLog($intUser,$intSection,$intAction,$intObject,$strContent) {
		if(!is_numeric($intUser) 	|| $intUser 	<= 0) return false;
		if(!is_numeric($intSection) || $intSection 	<= 0) return false;
		if(!is_numeric($intAction)	|| $intAction 	<= 0 || $intAction > 4) return false;
		if(!is_numeric($intObject)	|| $intObject 	<= 0 || $intObject > 3) return false;
		if(!is_string($strContent)	|| empty($strContent))	$strContent = '';
		
		// Executes query
		/**
		 * @todo set PROJECT_ID
		 */
		$arrField = array(
						'usr_data_id' 	=> $intUser,
						'sec_config_id' => $intSection,
						'action' 		=> $intAction,
						'object' 		=> $intObject,
						'date' 			=> date('Y-m-d H:i:s'),
						'content' 		=> $strContent	
					);
		return $this->insert('sys_log',array($arrField));
	}
	
	/**
	 * Selects data content
	 *
	 * @param 	array	$arrField		Table fields
	 * @param 	array	$arrTable		Table name
	 * @param 	array	$arrJoin		JOIN statement
	 * @param 	array	$arrWhere		WHERE statement
	 * @param 	array	$arrOrderBy		ORDER BY statement
	 * @param 	array	$arrGroupBy		GROUP BY statement
	 * @param 	integer	$intOffSet		Limit OFFSET
	 * @param 	integer	$intLimit		Limit value
	 * @param 	string 	$strFetchMode 	FETCHMODE statement 
	 *
	 * @return 	If MDB2::isError, sets ERROR_MSG constant and returns false; else, returns MDB2_Result_object
	 *
	 * @since	2013-01-18
	 * @author 	Diego Flores <diegotf [at] gmail dot com>
	 * 
	**/
	public function select($arrField,$arrTable,$arrJoin = array(),$arrWhere = array(),$arrOrderBy = array(),$arrGroupBy = array(),$intOffSet = 0,$intLimit = null,$strFetchMode = 'All',$boolDebug = false) {
		$arrFetch	= array	(
							'All' 		=> 'queryAll', 'One' 		=> 'queryOne', 'Row' 		=> 'queryRow', 'Col' 		=> 'queryCol',
							'fetchAll' 	=> 'queryAll', 'fetchOne' 	=> 'queryOne', 'fetchRow' 	=> 'queryRow', 'fetchCol' 	=> 'queryCol',
							'queryAll' 	=> 'queryAll', 'queryOne' 	=> 'queryOne', 'queryRow' 	=> 'queryRow', 'queryCol' 	=> 'queryCol',
							'1' 		=> 'queryAll', '2' 			=> 'queryOne', '3' 			=> 'queryRow', '4' 			=> 'queryCol'
							);
		if(!is_object($this->objConn))																					return false;
		if(is_string($arrField) 	&& !empty($arrField))									$arrField 		= array($arrField);
		if(!is_array($arrField))																						return false;
		if(is_string($arrTable) 	&& !empty($arrTable))									$arrTable 		= array($arrTable);
		if(!is_array($arrTable))																						return false;
		if(is_string($arrJoin)) 															$arrJoin 		= array($arrJoin); 
		if(!is_array($arrJoin))																							return false;
		if(is_string($arrWhere)) 															$arrWhere 		= array($arrWhere);
		if(!is_array($arrWhere))																						return false;
		if(is_string($arrOrderBy)) 															$arrOrderBy 	= array($arrOrderBy);
		if(!is_array($arrOrderBy))																						return false;
		if(is_string($arrGroupBy)) 															$arrGroupBy 	= array($arrGroupBy);
		if(!is_array($arrGroupBy))																						return false;
		if(!is_numeric($intOffSet)) 														$intOffSet 		= 0;
		if(!is_numeric($intLimit)) 															$intLimit 		= null;
		if(!is_string($strFetchMode) || (is_string($strFetchMode) && !array_key_exists($strFetchMode,$arrFetch))) 		return $strFetchMode;
		
		// Sets query syntax
		$strQuery	= ' SELECT 
							'.implode(',',$arrField).' 
						FROM
							'.implode(' JOIN ',$arrTable).' 
						'.(count($arrJoin)	> 0 	? implode(' ',$arrJoin)							: '').'
						'.(count($arrWhere) > 0 	? 'WHERE 	'.implode(' AND '	,$arrWhere)		: '').'
						'.(count($arrGroupBy) > 0 	? 'GROUP BY	'.implode(' , '		,$arrGroupBy)	: '').'
						'.(count($arrOrderBy) > 0 	? 'ORDER BY '.implode(' ,'		,$arrOrderBy) 	: '');
		
		// Sets LIMIT
		$this->objConn->setLimit($intLimit,$intOffSet);
		
		// Executes query
		$objQuery	= $this->objConn->$arrFetch[$strFetchMode]($strQuery);
		
		if($this->_boolDebug || $boolDebug) { echo '<pre>'; print_r($strQuery); echo '<hr />'; var_dump($objQuery); echo '</pre>'; }
		
		if(MDB2::isError($objQuery)) { #echo '<pre>'; var_dump($objQuery);
			define('ERROR_MSG',"Error on Model::select->$arrFetch[$strFetchMode](): " . $objQuery->getMessage());
			return false;
		} else {
			return $objQuery;
		}
	}
	
	/**
	 * Inserts data
	 *
	 * @param	string	$strTable		Table name
	 * @param	array	$arrField		Associative array with field_name => field_value
	 * @param	boolean	$boolReturnId	Defines whether function returns MDB2_OK or MDB2::getLastInsertID()
	 *
	 * @return	mixed
	 *
	 * @since	2013-01-18
	 * @author 	Diego Flores <diegotf [at] gmail dot com>
	 * 
	**/
	public function insert($strTable,$arrField,$boolReturnId = false) {
		if(is_array($strTable)) 						$strTable = reset($strTable);
		if(!is_string($strTable) || empty($strTable))	return false;
		if(empty($arrField)) 							return false;
		if(!is_array($arrField[0])) 					$arrField 	= array($arrField);
		
		// Prepares and execute query
		$objQuery = $this->prepareInsertQuery($strTable,$arrField);
		
		if($objQuery !== false) {
			// Treats UTF-8 and HTML SPECIAL CHARS
			$this->prepareDataSyntax($arrField,false);
			
			$objQuery = $this->objConn->extended->executeMultiple($objQuery,$arrField);
			#$this->objConn->free();
			
			if(MDB2::isError($objQuery)) {
				define('ERROR_MSG','Error on Model::insert->executeMultiple(): ' . $objQuery->getMessage());
				return false;
			} else {
				return ($boolReturnId ? $this->getLastInsertID($strTable) : $objQuery);
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Replaces data
	 *
	 * @param	string	$strTable	Table name
	 * @param	array	$arrField	Multi-dimension associative array with [n] => ([field_name] => field_value)
	 * @param	boolean	$boolReturnId	Defines whether function returns MDB2_OK OR MDB2::getLastInsertID()
	 * @param	boolean	$forceUpdateSyntax	Defines whether query's syntax MUST BE `UPDATE...` rather then `REPLACE...`
	 *
	 * @return	mixed
	 *
	 * @since	2013-01-18
	 * @author 	Diego Flores <diegotf [at] gmail dot com>
	 * 
	**/
	public function replace($strTable,$arrField,$boolReturnId = true,$forceUpdateSyntax = false) {
		if(is_array($strTable)) 						$strTable = reset($strTable);
		if(!is_string($strTable) || empty($strTable))	return false;
		if(empty($arrField)) 							return false;
		if(!is_array(reset($arrField)))					$arrField 	= array($arrField);
		
		#echo '<pre>'; var_dump($forceUpdateSyntax); print_r($arrField); die();
		
		// UPDATE syntax
		if($forceUpdateSyntax && !empty($arrField[0]['id'])) {
			$arrField = reset($arrField);
			$strWhere = $strTable . '.id = "' . $arrField['id'] . '"';
			
			#echo '<pre>'; print_r($arrField); echo $strWhere; die();
			
			$this->update($strTable, $arrField, $strWhere);
			
		// REPLACE syntax
		} else {
			// Prepares and execute query
			$objQuery = $this->prepareInsertQuery($strTable,$arrField,false);
			
			if($objQuery !== false) {
				// Treats UTF-8 and HTML SPECIAL CHARS
				$this->prepareDataSyntax($arrField,false);
				$objQuery = $this->objConn->extended->executeMultiple($objQuery,$arrField);
				#$this->objConn->free();
				
				if(MDB2::isError($objQuery)) {
					#echo '<pre>'; var_dump($objQuery); die();
					define('ERROR_MSG','Error on Model::replace->executeMultiple(): ' . $objQuery->getMessage());
					return false;
				} else {
					return ($boolReturnId ? $this->getLastInsertID($strTable) : $objQuery);
				}
			} else {
				#echo '<pre>'; var_dump($objQuery); die();
				define('ERROR_MSG','Error on Model::replace->prepareInsertQuery(): ' . $objQuery->getMessage());
				return false;
			}
			
		}
	}
	
	/**
	 * Updates data
	 *
	 * @param	string	$strTable	Table name; if array, gets first element
	 * @param	array	$arrField	Associative array with field_name => field_value
	 * @param	mixed	$strWhere	WHERE statement
	 *
	 * @return	mixed
	 *
	 * @since	2013-01-18
	 * @author 	Diego Flores <diegotf [at] gmail dot com>
	 * 
	**/
	public function update($strTable, $arrField, $strWhere = '') {
		if(is_array($strTable)) 							$strTable = reset($strTable);
		if(!is_string($strTable) || empty($strTable))		return false;
		if(empty($arrField)) 								return false;
		if(is_object($arrField)) 							$arrField 	= (array) $arrField;
		if(!is_array($arrField)) 							$arrField 	= array($arrField);
		if(is_array($strWhere)) 							$strWhere 	= implode(' AND ',$strWhere);
		if(!is_string($strWhere)	&& !empty($strWhere)) 	$strWhere 	= '1';
		
		// Treats UTF-8 and HTML SPECIAL CHARS
		$this->prepareDataSyntax($arrField,true);
		
		// Prepares and execute query
		$objQuery = $this->objConn->extended->autoExecute($strTable,$arrField,MDB2_AUTOQUERY_UPDATE,$strWhere);
		
		if(MDB2::isError($objQuery)) {
			define('ERROR_MSG','Error on Model::update->execute(): ' . $objQuery->getMessage());
			return false;
		} else {
			return $objQuery;
		}
	}
	
	/**
	 * Deletes data
	 *
	 * @param	string	$strTable	Table name; if array, gets first element
	 * @param	mixed	$strWhere	WHERE statement
	 *
	 * @return	integer
	 *
	 * @since	2013-01-18
	 * @author 	Diego Flores <diegotf [at] gmail dot com>
	 * 
	**/
	public function delete($strTable,$strWhere = array()) {
		if(is_array($strTable)) 							$strTable = reset($strTable);
		if(!is_string($strTable) || empty($strTable))		return false;
		if(is_array($strWhere)) 							$strWhere 	= implode(' AND ',$strWhere);
		if(!is_string($strWhere)	&& !empty($strWhere)) 	$strWhere 	= '1';
		
		// Prepares and execute query
		$objQuery	= $this->objConn->extended->autoPrepare($strTable,null,MDB2_AUTOQUERY_DELETE,$strWhere);
		if(MDB2::isError($objQuery)) {
			define('ERROR_MSG','Error on Model::delete->autoPrepare(): ' . $objQuery->getMessage());
			return false;
		} else {
			$objQuery = $objQuery->execute();
			
			#$this->objConn->free();
		
			if(MDB2::isError($objQuery)) {
				define('ERROR_MSG','Error on Model::delete->execute(): ' . $objQuery->getMessage());
				return false;
			} else {
				return $objQuery;
			}
		}
	}
	
	/**
	 * Get Applr section config info
	 * 
	 * @param	mixed	$mxdSection	Section ID or PERMALINK
	 *
	 * @return 	Object
	 *
	 * @since	2013-01-18
	 * @author 	Diego Flores <diegotf [at] gmail dot com>
	 * 
	 */
	/**
	 * @todo set PROJECT_ID
	 */
	public function getSectionConfig($mxdSection) {
		if(is_numeric($mxdSection) && $mxdSection > 0) {
			$boolType = 0;
		} elseif(is_string($mxdSection) && !empty($mxdSection)) {
			$boolType = 1;
		} else {
			return false;
		}
		
		$arrFields	= array(
						'sec_config.*',
						'tpl_type_content.sys_template_id 	AS tpl_content_id',
						'tpl_type_list.sys_template_id 		AS tpl_list_id',
						'tpl_type_home.sys_template_id 		AS tpl_home_id',
						'tpl_file_content.filename 			AS tpl_content',
						'tpl_file_list.filename 			AS tpl_list',
						'tpl_file_home.filename 			AS tpl_home'
						);
		$arrJoins	= array(
						'LEFT JOIN rel_sec_template AS tpl_type_content ON tpl_type_content.sec_config_id = sec_config.id AND tpl_type_content.sys_template_type_id = 1',
						'LEFT JOIN sys_template AS tpl_file_content 	ON tpl_file_content.id = tpl_type_content.sys_template_id',
						'LEFT JOIN rel_sec_template AS tpl_type_list 	ON tpl_type_list.sec_config_id = sec_config.id AND tpl_type_list.sys_template_type_id = 2',
						'LEFT JOIN sys_template AS tpl_file_list 		ON tpl_file_list.id = tpl_type_list.sys_template_id',
						'LEFT JOIN rel_sec_template AS tpl_type_home 	ON tpl_type_home.sec_config_id = sec_config.id AND tpl_type_home.sys_template_type_id = 3',
						'LEFT JOIN sys_template AS tpl_file_home 		ON tpl_file_home.id = tpl_type_home.sys_template_id',
						);
		$objReturn 	= $this->select($arrFields,'sec_config',$arrJoins,(!$boolType ? 'sec_config.id = ' . $mxdSection : 'sec_config.permalink = "' . $mxdSection . '"')); //,array(),array(),0,null,'All',1);
		
		if(!empty($objReturn)) {
			return reset($objReturn);
		} else {
			return false;
		}
	}
}
?>