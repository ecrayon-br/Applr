<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

/**
 * Prints Publica combo field content
 *
 * @param	array	$params	Variables defined at SMARTY tag; possible values describes at the function scope
 * @param	object	$smarty	SMARTY object
 * @return	void
 */
function smarty_function_PUBLICA_listComboContent($params, &$smarty) {
	global $conexao;
	
	// Defines SECTION ID
	if(!isset($params['sectionId']) || !is_numeric($params['sectionId'])) {
		$params['sectionId'] = $smarty->_tpl_vars['SECAO'];
	}
	if(is_null($params['sectionId']) || !is_numeric($params['sectionId'])) return;
	
	// Gets SECTION configs
	$objConfig	= fnConfigSecao($params['sectionId']);
	
	// Defines default variables
	if(!isset($params['fieldValue']))	{ $params['fieldValue']	= 'bn_id';							}
	if(!isset($params['fieldName']))	{ $params['fieldName']	= 'bt_nome';						}
	if(!isset($params['tableName']))	{ $params['tableName']	= $objConfig->bt_tabela;			}
	if(!isset($params['tableOrder']))	{ $params['tableOrder']	= $objConfig->bt_ordem;				}
	if(!isset($params['tableLimit']))	{ $params['tableLimit']	= $objConfig->bn_lista;				}
	if(!isset($params['tableWhere']))	{ $params['tableWhere']	= '';								}
	if(!empty($params['tableWhere']))	{ $params['tableWhere']	= ' AND '.$params['tableWhere'];	}
	
	// Defines content relationship filter
	if(isset($params['contentRelationshipFilter']) && $params['contentRelationshipFilter'] == 1 && $params['sectionId'] != $smarty->_tpl_vars['SECAO']) {
		// Sets entity-relation variables
		$params['tableRelName']	= $conexao->getOne('SELECT bt_tabela FROM tb_secao_relacionamento WHERE tb_secao_bn_id = "'.$params['sectionId'].'" AND bn_secao = "'.$smarty->_tpl_vars['SECAO'].'";');
		
		// Sets SQL query string
		$strQuery = 'SELECT * FROM '.$params['tableName'].' AS tableName JOIN '.$params['tableRelName'].' AS tableRel WHERE '.REFINAMENTO.$params['tableWhere'].' AND tb_idioma_bn_id = "'.$smarty->_tpl_vars['IDIOMA'].'" AND tableName.bn_id = tableRel.'.str_replace('tb_conteudo_','tb_',$params['tableName']).'_bn_id '.(isset($params['contentId']) && $params['contentId'] > 0 ? ' AND tableRel.tb_'.end(explode('_tb_',$params['tableRelName'])).'_bn_id = "'.$params['contentId'].'"' : '').' ORDER BY '.$params['tableOrder'].($params['tableLimit'] > 0 ? ' LIMIT '.$params['tableLimit'] : '').';';
	} else {
		// Sets SQL query string
		$strQuery = 'SELECT * FROM '.$params['tableName'].' AS tableName WHERE '.REFINAMENTO.$params['tableWhere'].' AND tb_idioma_bn_id = "'.$smarty->_tpl_vars['IDIOMA'].'"'.' ORDER BY '.$params['tableOrder'].($params['tableLimit'] > 0 ? ' LIMIT '.$params['tableLimit'] : '').';';
	}
	
	// Do SQL search	
	$arrQuery = $conexao->getAll($strQuery,array(),DB_FETCHMODE_ASSOC);
	if(DB::isError($arrQuery) || count($arrQuery) == 0) return;
	$strReturn = '';
	foreach($arrQuery AS $intKey => $arrData) {
		if($params['returnResult']) {
			$strReturn .= '<option value="'.$arrData[$params['fieldValue']].'"'.(isset($params['value']) && $params['value'] == $arrData[$params['fieldValue']] ? 'selected' : '').'>'.(isset($arrData[$params['fieldName']]) ? $arrData[$params['fieldName']] : $arrData['bt_nome'] )."</option>\n";
		} else {
			echo '<option value="'.$arrData[$params['fieldValue']].'"'.(isset($params['value']) && $params['value'] == $arrData[$params['fieldValue']] ? 'selected' : '').'>'.(isset($arrData[$params['fieldName']]) ? $arrData[$params['fieldName']] : $arrData['bt_nome'] )."</option>\n";
		}
	}
	return $strReturn;
}
?>