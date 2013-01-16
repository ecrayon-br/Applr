<?php
include_once 'DB.php';

class Model {
	
	protected 	$_dbType	= DB_TYPE;
	protected 	$_hostName	= DB_HOST;
	protected 	$_userName	= DB_USER;
	protected 	$_password	= DB_PASSWORD;
	protected 	$_dbName	= DB_NAME;
	
	protected 	$_boolDebug = false;
	
	protected  	$objConn;
	
	public		$boolConnStatus;
	public		$intError;
	
	/**
	 * Inicializador da classe, realiza a conexao ao DB de acordo com o ID recebido por parametro
	 * 
	 * @author	Diego Flores <diegotf [at] gmail dot com>
	 * @since	2007-06-04
	 * 
	 * @todo 	Validar fetch_mode em $this->setFetchMode
	 * @todo	Reconstruir metodos $this->getClientConnection, $this->insert, $this->select, $this->update, $this->replace, $this->delete
	**/
	public function __construct() {
		$this->boolConnStatus = $this->setConnection();
	}
	
	/**
	 * Realiza e verifica a conexao com o DB
	 * 
	 * @param 	mixed	$intConnection	ID da conexao do cliente
	 * 
	 * @return	boolean
	 *
	 * @author	Diego Flores <diegotf [at] gmail dot com>
	 * @since	2008-01-29
	**/
	public function setConnection($intConnection = null) {
		if(!is_null($intConnection) && !is_numeric($intConnection))	return false;
		
		$strDSN			= "$this->_dbType://$this->_userName:$this->_password@$this->_hostName/$this->_dbName";
		$arrOptions		= array	(
		    					'debug'       => 2,
		    					'portability' => DB_PORTABILITY_ALL,
								);
		$this->objConn 	=& DB::connect($strDSN,$arrOptions);
		
		#var_dump($this->objConn);
		
		if (PEAR::isError($this->objConn)) return false;
		
		$this->setFetchMode();
		
		return true;
	}
	
	/**
	 * Seta as variaveis de conexao
	 *
	 * @param	string	$strTemp	Valor atribuido a variavel interna
	 * 
	 * @return	void
	 *
	 * @author	Diego Flores
	 * @since	2007-06-04
	**/
	public function setDBType($strTemp) {
		// Valida os argumentos do metodo
		if(!is_string($strTemp) || empty($strTemp))	return false;
		
		$this->_dbType		= $strTemp;
	}
	
	public function setHostName($strTemp) {
		// Valida os argumentos do metodo
		if(!is_string($strTemp) || empty($strTemp))	return false;
		
		$this->_hostName	= $strTemp;
	}
	
	public function setUserName($strTemp) {
		// Valida os argumentos do metodo
		if(!is_string($strTemp) || empty($strTemp))	return false;
		
		$this->_userName	= $strTemp;
	}
	
	public function setPassword($strTemp) {
		// Valida os argumentos do metodo
		if(!is_string($strTemp) || empty($strTemp))	return false;
		
		$this->_password	= $strTemp;
	}
	
	public function setDBName($strTemp) {
		// Valida os argumentos do metodo
		if(!is_string($strTemp) || empty($strTemp))	return false;
		
		$this->_dbName		= $strTemp;
	}
	
	/**
	 * Retorna o valor as variaveis de conexao
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
	 * Define o fetchMode do objeto de conexao
	 *
	 * @param	string	$mxdFetch	Defines DB_FETCHMODE constant
	 * 
	 * @return	void
	 *
	 * @author	Diego Flores <diegotf [at] gmail dot com>
	 * @since	2008-01-29
	**/
	public function setFetchMode($mxdFetch = 0) {
		if(!is_object($this->objConn))							return false;
		if(!is_string($mxdFetch) && !is_numeric($mxdFetch))		return false;
		if(is_string($mxdFetch))								$mxdFetch = strtoupper($mxdFetch);
		
		// Seta o FETCH_MODE
		switch($mxdFetch) {
			case 'OBJ':
			case 0:
			default:
				$this->objConn->setFetchMode(DB_FETCHMODE_OBJECT);
			break;
			
			case 'ORDERED':
			case 1:
				$this->objConn->setFetchMode(DB_FETCHMODE_ORDERED);
			break;
			
			case 'ASSOC':
			case 2:
				$this->objConn->setFetchMode(DB_FETCHMODE_ASSOC);
			break;
		}
	}
	
	/**
	 * Controla a exibicao do debug de resultado dos metodos
	 *
	 * @param	boolean	$boolDebug	Define a exibicao do debug de resultado
	 * 
	 * @return	void
	 *
	 * @author	Diego Flores
	 * @since	2007-06-04
	**/
	private function setDebug($boolDebug = false) {
		if(!is_bool($boolDebug) && $boolDebug !== 0 && $boolDebug !== 1)	$boolDebug = false;
		
		$this->_boolDebug = $boolDebug;
	}
	
	/**
	 * Checks fields syntax and prepare string values with pre and post quotes. Assumes that column quantity is count(reset($arrData))
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
		/*
		echo '>>>>> -----------------------'."\n";
		echo '>>>>> -----------------------'."\n";
		print_r($arrData);
		*/
		// Defines column quantity per row
		$intColumn	= count(reset($arrData));
		//var_dump($intColumn);
		foreach($arrData AS $intRowKey => &$arrRowData) {
			/*
			echo '>> -----------------------'."\n";
			print_r($arrRowData);
			echo '>> -----------------------'."\n";
			*/
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
					if(is_null($mxdColumnData)) {
						$mxdColumnData = 'NULL';
					} elseif(is_string($mxdColumnData) && strtoupper($mxdColumnData) != 'NULL' && strpos($mxdColumnData,'"') !== 0 && strpos($mxdColumnData,"'") !== 0) {
						$mxdColumnData = '"'.$mxdColumnData.'"';
					} elseif($mxdColumnData === "") {
						$mxdColumnData = '""';
					}
				}
			} else {
				unset($arrData[$intRowKey]);
			}
		}
		/*
		echo '>> -----------------------'."\n";
		print_r($arrData);
		echo '>>>>> -----------------------'."\n";
		echo '>>>>> -----------------------'."\n";
		*/
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
		
		// Prepare row's syntax
		$arrValue	= $this->prepareInsertRowSyntax($arrField);
		
		// Gets table attribute's name
		$arrField	= array_keys(reset($arrField));
		
		return ($boolInsertMode ? 'INSERT' : 'REPLACE').' INTO '.$strTable.' ('.implode(',',$arrField).') VALUES '.implode(',',$arrValue);
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
	protected function prepareUpdateQuery($strTable,$arrField,$strWhere = '1') {
		if(!is_string($strTable)|| empty($strTable))									return false;
		if(!is_array($arrField) || count($arrField) == 0)								return false;
		
		// Prepare row's syntax
		$arrField	= $this->prepareUpdateRowSyntax($arrField);
		
		return 'UPDATE ' . $strTable . ' SET ' . implode(',',$arrField) . ' WHERE ' . $strWhere . ';';
	}
	
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
		
		$objRS	= $this->executeQuery('SELECT ' . $strField . ' FROM ' . $strTable . ' WHERE ' . $strWhere . ';');
		$objRS	= $objRS->fetchRow();
		
		if(isset($objRS->$strField) && !is_null($objRS->$strField)) { return ($boolReturnValue ? $objRS->$strField : true); }
		return false;
	}
	
	/**
	 * Executes query on database
	 *
	 * @param 		string $strQuery	Query to execute
	 * 
	 * @return 		DB::object
	 * 
	 * @since 		2008-12-07
	 * @author 		Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	protected function executeQuery($strQuery) {
		if(!is_string($strQuery)|| empty($strQuery))	return false;
		//var_dump($this->objConn);
		//echo '<br /><br />'.$strQuery.'<br /><br />';
		return $this->objConn->query($strQuery);
	}
	
	/**
	 * Returns last auto_increment primary key value inserted on database
	 *
	 * @return	integer
	 * 
	 * @since 	2009-04-27
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function getLastInsertID() {
		return $this->objConn->getOne('SELECT LAST_INSERT_ID();');
	}
	
	/**
	 * Inserts LOG record
	 * 
	 * @param	integer		$intUser	User ID
	 * @param	integer		$intUC		Use Case ID / DB::secao_sistema.cod_secao_sistema value
	 * @param	integer		$intAction	Action code: 1 - Inserido com sucesso; 2 - Erro ao inserir; 3 - Dados inválidos ao inserir; 4 - Atualizado com sucesso; 5 - Erro ao atualizar; 6 – Dados inválidos ao atualizar; 7 - Importado com sucesso; 8 - Erro ao importar; 9 - Dados inválidos ao importar; 10 - Mês/Ano existente; 11 - Deletado com sucesso; 12 - Erro ao deletar; 13 - Visualizou
	 * @param	array		$arrParam	Optional parameters array; options defined in $this->setLog->arrTestParam
	 * 
	 * @return	boolean
	 * 
	 * @since 	2009-05-13
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 */
	public function setLog($intUser,$intUC,$intAction,$arrParam = array()) {
		if(!is_numeric($intUser) 	|| $intUser 	<= 0) return false;
		if(!is_numeric($intUC) 		|| $intUC 		<= 0) return false;
		if(!is_numeric($intAction)	|| $intAction 	<= 0) return false;
		
		$arrTestParam	= array('cod_cnpj','num_ano','num_mes','cod_tipo_indice_boh','cod_fnrh');
		foreach($arrTestParam AS $strParam) {
			if(isset($arrParam[$strParam])) {
				$arrParam[$strParam] = '"' . Controller::escapeXSS($arrParam[$strParam]) . '"';
			} else {
				$arrParam[$strParam] = 'NULL';
			}
		}
		
		// Executes query
		$objQuery = $this->executeQuery('INSERT INTO log_sistema (dat_log,cod_usuario,cod_secao_sistema,num_acao,cod_cnpj,num_ano,num_mes,cod_tipo_indice_boh,cod_fnrh) VALUES (NOW(),' . Controller::escapeXSS($intUser) . ',' . Controller::escapeXSS($intUC) . ',' . Controller::escapeXSS($intAction) . ',' . implode(',',$arrParam) . ');');
		
		// Returns boolean
		if(!DB::isError($objQuery)) {
			return true;
		} else {
			return false;
		}
	}
	
	/****************************************************/
	
	/**
	 * Seleciona dados do DB
	 *
	 * @param 	array	$arrCampo	Seta os campos selecionados
	 * @param 	array	$arrTabela	Seta as tabelas selecionadas
	 * @param 	array	$arrJoin	Seta os JOINS pertinentes a busca
	 * @param 	array	$arrWhere	Seta os filtros da busca
	 * @param 	array	$arrOrdem	Seta os parametros de ordenacao da busca
	 * @param 	array	$arrGroup	Seta os parametros de agrupamento da busca
	 * @param 	integer	$intOffSet	Seta o indice de inicio do resultado para o recordset
	 * @param 	integer	$intLimit	Seta o total de registros retornados no recordset, a partir de $intOffset
	 * @param 	string 	$strFetch 	Seta o FETCH_MODE da busca (All, Col, Pairs, Row, One)
	 *
	 * @return 	recordset
	 *
	 * @author 	Diego Flores <diegotf [at] gmail dot com>
	 * @since	2008-01-29
	**/
	public function select($arrCampo,$arrTabela,$arrJoin = array(),$arrWhere = array(),$arrOrdem = array(),$arrGroup = array(),$intOffSet = 0,$intLimit = null,$strFetch = 'All') {
		$arrFetch	= array('All' => 'getAll','One' => 'getOne');
		
		if(!is_object($this->objConn))																	return false;
		
		if(is_string($arrCampo) && !empty($arrCampo))													$arrCampo 	= array($arrCampo);
		if(!is_array($arrCampo))																		return false;
		
		if(is_string($arrTabela) && !empty($arrTabela))													$arrTabela 	= array($arrTabela);
		if(!is_array($arrTabela))																		return false;
		
		if(is_string($arrJoin)	&& !empty($arrJoin)) 													$arrJoin 	= array($arrJoin); 
		if(!is_array($arrJoin))																			return false;
		
		if(is_string($arrWhere)	&& !empty($arrWhere)) 													$arrWhere 	= array($arrWhere);
		if(!is_array($arrWhere))																		return false;
		
		if(is_string($arrOrdem)	&& !empty($arrOrdem)) 													$arrOrdem 	= array($arrOrdem);
		if(!is_array($arrOrdem))																		return false;
		
		if(is_string($arrGroup)	&& !empty($arrGroup)) 													$arrGroup 	= array($arrGroup);
		if(!is_array($arrGroup))																		return false;
		
		if(!is_numeric($intOffSet)) 																	$intOffSet 	= 0;
		if(!is_numeric($intLimit)) 																		$intLimit 	= null;
		
		if(!is_string($strFetch) || (is_string($strFetch) && !array_key_exists($strFetch,$arrFetch))) 	return $strFetch;
		
		// Monta a query
		$strQuery	= ' SELECT 
							'.implode(',',$arrCampo).' 
						FROM
							'.implode(' JOIN ',$arrTabela).' 
						'.(count($arrJoin)	> 0 ? implode(' ',$arrJoin)						: '').'
						'.(count($arrWhere) > 0 ? 'WHERE 	'.implode(' AND '	,$arrWhere)	: '').'
						'.(count($arrGroup) > 0 ? 'GROUP BY	'.implode(' , '		,$arrGroup)	: '').'
						'.(count($arrOrdem) > 0 ? 'ORDER BY '.implode(' ,'		,$arrOrdem) : '').'
						'.(is_numeric($intLimit)? 'LIMIT	'.$intOffSet.','.$intLimit		: '').';';
		
		// Executa a acao no DB
		$objRS		= $this->objConn->$arrFetch[$strFetch]($strQuery);
		
		// Exibe o debug de resultado
		if($this->_boolDebug) { echo $strQuery.'<pre>'; print_r($objRS); echo '</pre>'; }
		
		return $objRS;
	}
	
	/**
	 * insert - Insere dados no DB
	 *
	 * @param	string	$strTabela	Seta a tabela para insercao dos dados
	 * @param	array	$arrCampo	Array de pares coordenados, onde a chave identifica o nome do campo no DB e o valor correspondente identifica os dados inseridos no mesmo
	 *
	 * @return	mixed	$rs			Caso exista campo auto_increment, retorna o valor PK do ultimo registro inserido; caso contrario, se affected_rows == 1 retorna true, senao retorna false
	 *
	 * @author	Diego Flores
	 * @since	2007-06-01
	**/
	public function insert($strTabela,$arrCampo) {
		// Valida os argumentos do metodo
		if(is_array($strTabela)) 																		$strTabela = reset($strTabela);
		if(!is_string($strTabela) || empty($strTabela))													return false;
		if(empty($arrCampo)) 																			return false;
		if(!is_array($arrCampo)) 																		$arrCampo 	= array($arrCampo);
		
		// Executa a acao no DB
		$rs		= $this->objConn->insert($strTabela,$arrCampo);
		
		// Verifica a existencia de PK AUTO_INCREMENT e retorna lastInsertID || affectedRows || false
		$arr	= reset($this->objConn->describeTable($strTabela));
		if($arr['IDENTITY']) { 
			$rs	= $this->getLastInsertID();
		} elseif($rs == 1) {
			$rs	= true;
		} else { //var_dump($rs);
			$rs = false;
		}
		
		// Exibe o debug de resultado
		if($this->_boolDebug) { var_dump($rs); }
		
		return $rs;
	}
	
	
	/**
	 * Insere dados no DB, atualizando registros de mesma PK
	 *
	 * @param	string	$strTabela	Seta a tabela para insercao dos dados
	 * @param	array	$arrCampo	Array de pares coordenados, onde a chave identifica o nome do campo no DB e o valor correspondente identifica os dados inseridos no mesmo
	 *
	 * @return	mixed	$rs			Caso exista campo auto_increment, retorna o valor PK do ultimo registro inserido; caso contrario, se affected_rows == 1 retorna true, senao retorna false
	 *
	 * @author	Diego Flores
	 * @since	2007-06-01
	**/
	public function replace($strTabela,$arrCampo) {
		// Valida os argumentos do metodo
		if(is_array($strTabela)) 																		$strTabela = reset($strTabela);
		if(!is_string($strTabela) || empty($strTabela))													return false;
		if(empty($arrCampo)) 																			return false;
		if(!is_array($arrCampo)) 																		$arrCampo 	= array($arrCampo);
		
		// Verifica se o primeiro valor de $arrCampo e a chave primaria da tabela; caso FALSE, quebra a execucao do metodo
		$arr	= reset($this->objConn->describeTable($strTabela));
		if(!$arr['PRIMARY'] || $arr['COLUMN_NAME'] != key(reset($arrCampo)))							return false;
		
		// Executa a acao no DB
		$rs		= $this->objConn->query("REPLACE INTO ".$strTabela." (".implode(",",array_keys($arrCampo)).") VALUES ('".implode("','",$arrCampo)."');");
		
		// Verifica a existencia de PK AUTO_INCREMENT e retorna lastInsertID || affectedRows || false
		if($arr['IDENTITY']) { 
			$rs	= $this->objConn->lastInsertId();
		} elseif($rs == 1) {
			$rs	= true;
		} else { //var_dump($rs);
			$rs = false;
		}
		
		// Exibe o debug de resultado
		if($this->_boolDebug) { var_dump($rs); }
		
		return $rs;
	}
	
	
	/**
	 * update - Atualiza dados no DB
	 *
	 * @param	string	$strTabela	Seta a tabela para atualizacao dos dados
	 * @param	array	$arrCampo	Array de pares coordenados, onde a chave identifica o nome do campo no DB e o valor correspondente identifica os dados inseridos no mesmo
	 * @param	mixed	$strWhere	Seta os filtros para atualizacao
	 *
	 * @return	integer	$rs			affectedRows
	 *
	 * @author	Diego Flores
	 * @since	2007-06-01
	**/
	public function update($strTabela, $arrCampo, $strWhere = '') {
		
		// Valida os argumentos do metodo
		if(is_array($strTabela)) 																		$strTabela 	= reset($strTabela);
		if(!is_string($strTabela) 	|| empty($strTabela))												return false;
		if(empty($arrCampo)) 																			return false;
		if(!is_array($arrCampo)) 																		$arrCampo 	= array($arrCampo);
		if(is_array($strWhere)) 																		$strWhere 	= implode(' AND ',$strWhere);
		if(!is_string($strWhere)	&& !empty($strWhere)) 												$strWhere 	= NULL;
		
		// Executa a acao no DB
		$rs			= $this->objConn->update($strTabela,$arrCampo,$strWhere);
		
		if($this->_boolDebug) { var_dump($rs); }
		
		return $rs;
	}
	
	
	/**
	 * delete - Deleta dados no DB
	 *
	 * @param	string	$strTabela	Seta a tabela para delecao dos dados
	 * @param	array	$arrWhere	Seta os filtros para delecao
	 *
	 * @return	integer	$rs			affectedRows
	 *
	 * @author	Diego Flores
	 * @since	2007-06-01
	**/
	public function delete($strTabela,$arrWhere = array()) {
		// Valida os argumentos do metodo
		if(is_array($strTabela)) 																		$strTabela	= reset($strTabela);
		if(!is_string($strTabela) || empty($strTabela))													return false;
		if(!is_string($arrWhere)  && !is_array($arrWhere))												return false;
		if(is_array($arrWhere)) 																		$arrWhere	= implode(' AND ',$arrWhere);
		
		// Executa a acao no DB
		$rs			= $this->objConn->delete($strTabela,$arrWhere);
		
		if($this->_boolDebug) { var_dump($rs); }
		
		return $rs;
	}
}
?>