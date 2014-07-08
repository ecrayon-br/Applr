<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
	include_once '../../cms/include-sistema/classe_gerasecao.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
	include_once '../../../cms/include-sistema/classe_gerasecao.php';
}

// Busca as configuracoes do destaque
function fnDestaqueInfo($strTag,&$smarty) {
	global $conexao;
	
	$query = "	SELECT
					bn_id,
					
					bt_ordem,
					bn_link,
					bn_link_altura,
					bn_link_largura,
					
					bn_limite_secao,
					bn_limite_tpl,
					bn_template
				FROM
					tb_destaque,
					tb_destaque_secao
				WHERE
					tb_destaque_secao.tb_destaque_bn_id = tb_destaque.bn_id						AND
					tb_destaque_secao.tb_idioma_bn_id 	= 1										AND
					tb_destaque.bt_tag					= '".$strTag."'							AND
					tb_destaque.bb_ativo				= 1;";
	$rs		= $conexao->query($query);
	if(!DB::isError($rs)) {
		if($rs->numRows() > 0) {
			$ob = $rs->fetchRow();
			unset($query,$rs);
			
			return $ob;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

// Busca as secoes relacionadas ao ID do destaque
function fnDestaqueSecao($intId) {
	global $conexao;
	
	$query	= "	SELECT
					tb_idioma_bn_id,
					tb_secao_bn_id
				FROM
					tb_destaque_secao
				WHERE
					tb_destaque_bn_id = '".$intId."';";
	$arr	= $conexao->getAssoc($query,false,NULL,DB_FETCHMODE_ORDERED,true);
	unset($query);
	
	if(!DB::isError($arr)) {
		return $arr;
	} else {
		return false;
	}
}

// Busca o conteudo das secoes relacionadas ao ID do destaque
function fnDestaqueConteudo($intDestaque) {
	global $conexao;
	
	$query	= "	SELECT
					tb_secao_bn_id,
					bn_conteudo
				FROM
					tb_destaque_conteudo
				WHERE
					tb_destaque_bn_id 	= '".$intDestaque."';";
	$arr	= $conexao->getAssoc($query,false,NULL,DB_FETCHMODE_ORDERED,true);
	unset($query);
	
	if(!DB::isError($arr)) {
		return $arr;
	} else {
		return false;
	}
}

// Exibe o destaque de conteudo
function smarty_function_PUBLICA_hotspot($params, &$smarty) {
	global $conexao;
	
	// Verifica a validade do parametro
	if(!is_string($params['var']) || $params['var'] == '') {
        return false;
    } else {
		$strTag = $params['var'];
	}
	
	// Verifica a definicao de campos extras
	if(isset($params['field'])) {
		if(!is_array($params['field'])) $params['field'] = explode(',',$params['field']);
	} else {
		$params['field'] = array();
	}
	
	// Verifica a definicao de joins
	if(isset($params['join'])) {
		if(is_array($params['join']))	$params['join'] = implode(' ',$params['join']);
	} else {
		$params['join'] = '';
	}
	
	// Verifica a definicao de refinamentos para a busca
	if(isset($params['where'])) {
		if(is_array($params['where'])) $params['where'] = implode(' AND ',$params['where']);
		$params['where'] = trim($params['where']);
		if(strpos($params['where'],'AND ') !== 0) {
			$params['where'] = 'AND '.$params['where'];
		}
	} else {
		$params['where'] = '';
	}
	
	// Verifica a definicao do boolean boolJoins
	if(!isset($params['boolJoins']) || (isset($params['boolJoins']) && $params['boolJoins'] != 1 && $params['boolJoins'] != 0)) $params['boolJoins'] = 1;
	
	// Verifica a definicao do boolean boolRel
	if(!isset($params['boolRel']) || (isset($params['boolRel']) && $params['boolRel'] != 1 && $params['boolRel'] != 0)) 		$params['boolRel'] = 1;
	
	// Busca as configuracoes do destaque
	$ob	= fnDestaqueInfo($strTag,$smarty);
	
	if($ob !== false && is_numeric($ob->bn_template) && $ob->bn_template > 0) {
		// Busca o nome do arquivo TPL relacionado
		$query	= "	SELECT bt_arquivo FROM tb_template WHERE bn_id = '".$ob->bn_template."';";
		$strT	= $conexao->getOne($query);
		unset($query);
		
		// Busca o array das secoes relacionadas ao destaque
		$arr	= fnDestaqueSecao($ob->bn_id);
		
		// Busca os conteudos em destaque das secoes relacionadas
		$arrB	= fnDestaqueConteudo($ob->bn_id);
		
		if($arr !== false && $arrB !== false && !DB::isError($strT)) {
			// Popula o array de conteudos
			$arrD = array();
			foreach($arr AS $intIdioma => $arrSecao) {
				foreach($arrSecao AS $intSecao) {
					if(isset($arrB[$intSecao]) && count($arrB[$intSecao]) > 0) {
						// Busca as informacoes da secao
						$obSecao = fnConfigSecao($intSecao);
						
						// Busca os conteudos da secao
						$_REQUEST['debug'] = null;
						$arrC = fnGeraSecao($intSecao,NULL,$intIdioma,$params['field'],$obSecao->bt_tabela.'.bn_id IN ('.implode(',',$arrB[$intSecao]).') '.$params['where'],'bn_id',$ob->bn_limite_secao,$params['join'],$params['boolJoins'],$params['boolRel']);
						fnLimpaArray($arrC);
						$arrD = array_merge($arrD,$arrC);
					}
				}
			}
			
			if(count($arrD) > 0) {
				// Limita o array de conteudo ao limite de itens do destaque
				$arrD	= array_slice($arrD,0,$ob->bn_limite_tpl);
				
				// Seta as variaveis do SMARTY
				$smarty->assign('hotSpot_arrData',$arrD);
				//echo '<pre>'; print_r($arrD); die();
				$smarty->display($strT);
			} else {
				return;
			}
		} else {
			return;
		}
	} else {
		return;
	}
}
?>
