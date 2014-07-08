<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

// Busca as configuracoes da enquete
function fnEnqueteInfo($intEnquete,&$smarty) {
	global $conexao;
	
	$query = "	SELECT
					bn_id AS indice,
					bn_id,
					bt_nome,
					bt_texto,
					bn_template_home,
					bn_template_lista,
					bn_template_conteudo,
					bn_template_resultado
				FROM
					tb_enquete
				WHERE
					".($intEnquete != '' ? " bn_id = '".$intEnquete."' 	AND " : "")."
					bn_template_conteudo 	IS NOT NULL 				AND
					bb_ativo				= 1
				ORDER BY
					bn_id DESC
				LIMIT 0,1;";
	$arr		= $conexao->getAssoc($query,false,NULL,DB_FETCHMODE_ASSOC,false);
	$arr		= reset($arr);
	if(is_array($arr)) {
		return $arr;
	} else {
		return false;
	}
}

// Busca as respostas relacionadas a enquete
function fnEnqueteResposta($intId) {
	global $conexao;
	
	$query	= "	SELECT
					bn_id AS indice,
					bn_id,
					bt_nome,
					bt_arquivo,
					bt_arquivo_thumbnail
				FROM
					tb_enquete_resposta
				WHERE
					tb_enquete_bn_id = '".$intId."';";
	$arr	= $conexao->getAssoc($query,false,NULL,DB_FETCHMODE_ASSOC,false);
	unset($query);
	
	if(!DB::isError($arr)) {
		return $arr;
	} else {
		return false;
	}
}

// Exibe a enquete de conteudo
function smarty_function_PUBLICA_showPoll($params, &$smarty) {
	global $conexao;
	
	// Verifica a validade do parametro
	if(!isset($params['var']) || !is_numeric($params['var'])) {
        $params['var'] = $conexao->getOne('SELECT MAX(bn_id) FROM tb_enquete WHERE bb_ativo = 1;');
    }
    
	// Busca as configuracoes do destaque
	$arrE	= fnEnqueteInfo($params['var'],$smarty);
	
	if($arrE !== false && is_numeric($arrE['bn_template_conteudo']) && $arrE['bn_template_conteudo'] > 0) {
		// Busca o nome do arquivo TPL relacionado
		$query	= "	SELECT bt_arquivo FROM tb_template WHERE bn_id = '".$arrE['bn_template_conteudo']."';";
		$strT	= $conexao->getOne($query);
		unset($query);
		
		// Busca o array das secoes relacionadas ao destaque
		$arr	= fnEnqueteResposta($arrE['bn_id']);
		if($arr !== false && !DB::isError($strT)) {
			// Seta as variaveis do SMARTY
			$smarty->assign('arrPollQuestion',$arrE);
			$smarty->assign('arrPollAnswer',$arr);
			$smarty->display($strT);
		} else {
			return;
		}
	} else {
		return;
	}
}
?>