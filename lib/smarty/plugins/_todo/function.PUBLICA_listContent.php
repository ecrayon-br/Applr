<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
	include_once '../../cms/include-sistema/classe_gerasecao.php';
	include_once '../../cms/include-sistema/funcao_termos.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
	include_once '../../../cms/include-sistema/classe_gerasecao.php';
	include_once '../../../cms/include-sistema/funcao_termos.php';
}

/**
 * Prints Publica listing content
 *
 * @param	array	$params	Variables defined at SMARTY tag; possible values describes at the function scope
 * @param	object	$smarty	SMARTY object
 * @return	void
 */
function smarty_function_PUBLICA_listContent($params, &$smarty) {
	global $conexao;
	
	// Defines SECTION ID
	if(!isset($params['sectionId']) || !is_numeric($params['sectionId'])) {
		$params['sectionId'] = $smarty->_tpl_vars['SECAO'];
	}
	if(is_null($params['sectionId']) || !is_numeric($params['sectionId'])) return;
	
	// Gets SECTION configs
	$objConfig	= fnConfigSecao($params['sectionId']);
	
	// Defines default variables
	if(!isset($params['returnVarSufix']))	{ $params['returnVarSufix']	= false;						}
	if(!isset($params['tableField']))		{ $params['tableField']	= '';								}
	if(!isset($params['tableName']))		{ $params['tableName']	= $objConfig->bt_tabela;			}
	if(!isset($params['tableJoin']))		{ $params['tableJoin']	= '';								}
	if(!isset($params['tableOrder']))		{ $params['tableOrder']	= $objConfig->bt_ordem;				}
	if(!isset($params['tableLimit']))		{ $params['tableLimit']	= $objConfig->bn_lista;				}
	if(!isset($params['tplBox']))			{ $params['tplBox']		= 'imageGalleryBox.htm';			}
	if(!isset($params['tableWhere']))		{ $params['tableWhere']	= '';								}
	if(!empty($params['tableWhere']))		{ $params['tableWhere']	= ' AND '.$params['tableWhere'];	}
	if(!isset($params['tableGroup']))		{ $params['tableGroup']	= '';								}
	
	// Sets valid content control WHERE statement
	$strValidStat 	= str_replace('bb_delete'		,$params['tableName'].'.bb_delete'		,REFINAMENTO);
	$strValidStat 	= str_replace('bb_ativo'		,$params['tableName'].'.bb_ativo'		,$strValidStat);
	$strValidStat 	= str_replace('bd_publicacao'	,$params['tableName'].'.bd_publicacao'	,$strValidStat);
	$strValidStat 	= str_replace('bd_expiracao' 	,$params['tableName'].'.bd_expiracao'	,$strValidStat);
	
	// Defines user's filter
	if(isset($params['attributeName']) && !empty($params['attributeName']) && isset($params['attributeValue']) && $params['attributeValue'] > 0) {
		$params['tableWhere']	.= ' AND '.$params['attributeName'].' = "'.$params['attributeValue'].'"';
	}
	
	// Defines content relationship filter
	if(isset($params['allRelationshipFilter']) && $params['allRelationshipFilter'] == 1) {
		// Sets entity-relation variables
		$params['tableRelName']	= fnBuscaRelacionamentos($params['tableName'],$params['sectionId']); //reset($conexao->getAssoc('SELECT tb_secao_bn_id, bt_tabela FROM tb_secao_relacionamento WHERE tb_secao_bn_id = "'.$params['sectionId'].'";',false,array(),DB_FETCHMODE_ASSOC,true));
		#echo '<pre>'; print_r($params['tableRelName']);
		$strTableRel		= '';
		$strTableFieldRel	= '';
		foreach($params['tableRelName'] AS $strIndexRel => $arrTableRel) {
			$strTableFieldRel	.= ', rel_' . $strIndexRel .'.*';
			
			$arrRelFields		= explode('_tb_',str_replace('tb_rel_','',$arrTableRel['tabelaRel']));
			$arrRelFields[1]	= 'tb_' . $arrRelFields[1];
			$strTableRel		.= ' LEFT JOIN '.$arrTableRel['tabelaRel'].' AS rel_' . $strIndexRel . ' ON '.$params['tableName'].'.bn_id = rel_'.$strIndexRel.'.'.$arrRelFields[0].'_bn_id ';
		}
		$strTableRel		.= $params['tableJoin'];
		
		// Sets SQL query string
		$strQuery = 'SELECT '.$params['tableName'].'.* '.$strTableFieldRel.' '.($params['tableField'] != '' ? ','.$params['tableField'] : '').' FROM '.$params['tableName'].' '.$strTableRel.' WHERE '.str_replace('&lt;','>',$strValidStat).$params['tableWhere'].' AND '.$params['tableName'].'.tb_idioma_bn_id = "'.$smarty->_tpl_vars['IDIOMA'].'"'.($params['tableGroup'] != '' ? ' GROUP BY '.$params['tableGroup'] : '').' ORDER BY '.$params['tableOrder'].($params['tableLimit'] > 0 ? ' LIMIT '.$params['tableLimit'] : '').';';
	} elseif(isset($params['contentRelationshipFilter']) && $params['contentRelationshipFilter'] == 1 && $params['sectionId'] != $smarty->_tpl_vars['SECAO']) {
		// Sets entity-relation variables
		$params['tableRelName']	= $conexao->getOne('SELECT bt_tabela FROM tb_secao_relacionamento WHERE tb_secao_bn_id = "'.$params['sectionId'].'" AND bn_secao = "'.$smarty->_tpl_vars['SECAO'].'";');
		
		// Sets SQL query string
		$strQuery = 'SELECT '.$params['tableName'].'.* '.($params['tableField'] != '' ? ','.$params['tableField'] : '').' FROM '.$params['tableName'].' JOIN '.$params['tableRelName'].' AS tableRel '.$params['tableJoin'].' WHERE '.$strValidStat.$params['tableWhere'].' AND '.$params['tableName'].'.tb_idioma_bn_id = "'.$smarty->_tpl_vars['IDIOMA'].'" AND '.$params['tableName'].'.bn_id = tableRel.'.str_replace('tb_conteudo_','tb_',$params['tableName']).'_bn_id '.(isset($params['contentId']) && $params['contentId'] > 0 ? ' AND tableRel.tb_'.end(explode('_tb_',$params['tableRelName'])).'_bn_id = "'.$params['contentId'].'"' : '').($params['tableGroup'] != '' ? ' GROUP BY '.$params['tableGroup'] : '').' ORDER BY '.$params['tableOrder'].($params['tableLimit'] > 0 ? ' LIMIT '.$params['tableLimit'] : '').';';
	} else {
		// Sets SQL query string
		$strQuery = 'SELECT '.$params['tableName'].'.* '.($params['tableField'] != '' ? ','.$params['tableField'] : '').' FROM '.$params['tableName'].' '.$params['tableJoin'].' WHERE '.$strValidStat.$params['tableWhere'].' AND '.$params['tableName'].'.tb_idioma_bn_id = "'.$smarty->_tpl_vars['IDIOMA'].'"'.($params['tableGroup'] != '' ? ' GROUP BY '.$params['tableGroup'] : '').' ORDER BY '.$params['tableOrder'].($params['tableLimit'] > 0 ? ' LIMIT '.$params['tableLimit'] : '').';';
	}
	
	// Do SQL search
	$arrQuery = $conexao->getAll($strQuery,array(),DB_FETCHMODE_ASSOC);
	if($params['debug'] === 0) { echo '<pre>'; echo $strQuery; echo '</pre>'; }
	
	if(DB::isError($arrQuery)) return;
	#echo '<pre>'; echo $strQuery; var_dump($arrQuery); echo '</pre>';
	if(isset($params['allRelationshipFilter']) && $params['allRelationshipFilter'] == 1) {
		foreach($arrQuery AS $intIndex => &$arrData) {
			foreach($arrData AS $strKey => &$mxdValue) {
				if(end(explode('_',$strKey)) == 'rel') {
					$mxdValue = fnSetaLink($params['sectionId'], $arrData['bn_id'], $mxdValue, $objConfig, $strKey);
				}
			}
			
			foreach($params['tableRelName'] AS $strIndexRel => $arrTableRel) {
				// Gets related section config parameters
				$obConfigRel = fnConfigSecao($arrTableRel['id']);
				
				// Defines relatioship field's name
				$arrRelFields		= explode('_tb_',str_replace('tb_rel_','',$arrTableRel['tabelaRel']));
				$arrRelFields[1]	= 'tb_' . $arrRelFields[1];
				$strParentFieldRel 	= $arrRelFields[0].'_bn_id'; //str_replace('tb_conteudo_','tb_',$params['tableName']).'_bn_id';
				$strChildFieldRel 	= $arrRelFields[1].'_bn_id'; //str_replace('tb_conteudo_','tb_',$arrTableRel['tabela']).'_bn_id';
				
				// var_dump($strChildFieldRel);
				//if(!empty($arrData[$strParentFieldRel])) {
					// Gets DB related content
					$query = "SELECT DISTINCT
								rel.".$strParentFieldRel."				AS id_principal,
								tabela.bn_id 					AS bn_id,
								tabela.bd_publicacao			AS bd_publicacao,
								tabela.permalink				AS permalink,
								tabela.".implode(', tabela.',$arrTableRel['campos'])."
							  FROM
								".$arrTableRel['tabelaRel']."		AS rel,
								".$arrTableRel['tabela']."			AS tabela
							  WHERE
							  	".REFINAMENTO."													AND
								rel.".$strChildFieldRel."	=  tabela.bn_id 					AND
								".(isset($arrData[$strChildFieldRel]) && intval($arrData[$strChildFieldRel]) > 0 ? " tabela.bn_id	= '".$arrData[$strChildFieldRel]."' AND " : "")."
								rel.".$strParentFieldRel."	=  ".$arrData['bn_id']."
								
							  ORDER BY
								id_principal, ".$obConfigRel->bt_ordem.";";
					$arrRelData  = $conexao->getAssoc($query,false,array(),DB_FETCHMODE_ASSOC,true);
					
					foreach($arrRelData AS $intKey => &$mxdData) {
						foreach($mxdData AS $intKeyData => &$tmpData) {
							$tmpData['url_permalink'] = HTTP.str_replace('tb_conteudo_','',$arrTableRel['tabela']).'/'.$tmpData['permalink'];
						}
					}
				
					// Redefines main array content
					$arrData[$strIndexRel] = (is_array($arrRelData) && count($arrRelData) > 0 ? $arrRelData[$arrData['bn_id']] : array());
				//} else {
					//$arrData[$strIndexRel] = array();
				//}
			}
		}
	} else {
		foreach($arrQuery AS $intIndex => &$arrData) {
			foreach($arrData AS $strKey => &$mxdValue) {
				if(end(explode('_',$strKey)) == 'rel') {
					$mxdValue = fnSetaLink($params['sectionId'], $arrData['bn_id'], $mxdValue, $objConfig, $strKey);
				}
			}
		}
	}
	
	if($params['debug'] == 3) { echo '<pre>'; print_r($arrQuery); echo '</pre>'; }
	
	// Assigns SMARTY data variable
	$smarty->assign('listContent_arrData'.($params['returnVarSufix'] ? '_'.$params['sectionId'] : ''),$arrQuery);
	$smarty->assign('listContent_arrParams'.($params['returnVarSufix'] ? '_'.$params['sectionId'] : ''),$params);
	
	// Sets translation terms
	fnTermos($smarty);
	
	// Check wheter TPL file exists; if false, checks for loop string or return empty
	if(file_exists($smarty->template_dir.$params['tplBox'])) {
		// Displays TPL result
		$smarty->display($params['tplBox']);
	} elseif(is_string($params['loop']) && !empty($params['loop'])) {
		foreach($arrQuery AS $intKey => &$arrPrintData) {
			// Defines link path
			$strLink	= $smarty->_tpl_vars['DINAMICO'].'index.php?'.IN_SECAO.'='.$params['sectionId'].'&'.IN_CONTEUDO.'='.$arrPrintData['bn_id'];
			
			// Replaces loop string variables
			$strReturn	= $params['loop'];
			
			foreach($arrPrintData AS $strKey => &$mxdValue) {
				$strReturn = str_replace('#'.$strKey.'#',$mxdValue,$strReturn);
			}
			
			$strReturn	= str_replace('#ITERATION#',$intKey);
			$strReturn	= str_replace('#HTTP#',HTTP,$strReturn);
			$strReturn	= str_replace('#DINAMICO#',DINAMICO,$strReturn);
			$strReturn	= str_replace('#IMAGEM#',IMAGEM,$strReturn);
			
			$strReturn	= str_replace('#IN_SECAO#',IN_SECAO,$strReturn);
			$strReturn	= str_replace('#IN_IDIOMA#',IN_IDIOMA,$strReturn);
			$strReturn	= str_replace('#IN_CONTEUDO#',IN_CONTEUDO,$strReturn);
			$strReturn	= str_replace('#IB_BUSCA#',IB_BUSCA,$strReturn);
			$strReturn	= str_replace('#BUSCA#',BUSCA,$strReturn);
			
			$strReturn	= str_replace('#LINK#',$strLink,$strReturn);
			$strReturn	= str_replace('#url_permalink#',HTTP.str_replace('tb_conteudo_','',$params['tableName']).'/'.$arrPrintData['permalink'],$strReturn);
			
			echo $strReturn."\n";
		}
	} else { return; }
}
?>
