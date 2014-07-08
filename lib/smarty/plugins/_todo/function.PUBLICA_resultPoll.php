<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}
include_once 'function.PUBLICA_showPoll.php';

// Busca as respostas relacionadas a enquete
function fnEnqueteResultado($intId) {
	global $conexao;
	
	// Busca o total geral de respostas para a enquete
	$query	= " SELECT COUNT(tb_enquete_resposta_bn_id) FROM tb_enquete_resultado WHERE tb_enquete_bn_id = '".$intId."';";
	$intTotal = $conexao->getOne($query);
	unset($query);
	
	// Busca o total de respostas para cada opcao da enquete
	$query	= "	SELECT 
					tb_enquete_resposta.bn_id 																			AS indice,
					tb_enquete_resposta.bn_id,
					tb_enquete_resposta.bt_nome,
					tb_enquete_resposta.bt_arquivo,
					tb_enquete_resposta.bt_arquivo_thumbnail,
                    COUNT(tb_enquete_resultado.tb_enquete_resposta_bn_id) 												AS total,
					ROUND( ( COUNT(tb_enquete_resultado.tb_enquete_resposta_bn_id)  * 100  / ".$intTotal.") ,0) 	AS resultado

				FROM 
					tb_enquete_resposta
					LEFT JOIN tb_enquete_resultado ON tb_enquete_resultado.tb_enquete_bn_id = tb_enquete_resposta.tb_enquete_bn_id and tb_enquete_resultado.tb_enquete_resposta_bn_id 	= tb_enquete_resposta.bn_id 
				WHERE
					tb_enquete_resposta.tb_enquete_bn_id 			= '".$intId."'
				GROUP BY 
					tb_enquete_resposta.bn_id
				ORDER BY
					resultado DESC;";
	$arr	= $conexao->getAssoc($query,false,NULL,DB_FETCHMODE_ASSOC,false);
	$arr['total'] = $intTotal;
	unset($query);
	
	if(!DB::isError($arr)) {
		return $arr;
	} else {
		return false;
	}
}

// Exibe a enquete de conteudo
function smarty_function_PUBLICA_resultPoll($params, &$smarty) {
	global $conexao;
	
	// Verifica a validade do parametro
	if(!is_numeric($params['var'])) {
        return false;
    }
    
	// Busca as configuracoes do destaque
	$arrE	= fnEnqueteInfo($params['var'],$smarty);
	
	if($arrE !== false && is_numeric($arrE['bn_template_resultado']) && $arrE['bn_template_resultado'] > 0) {
		// Busca o nome do arquivo TPL relacionado
		$query	= "	SELECT bt_arquivo FROM tb_template WHERE bn_id = '".$arrE['bn_template_resultado']."';";
		$strT	= $conexao->getOne($query);
		unset($query);
		
		// Busca o array das secoes relacionadas ao destaque
		$arr	= fnEnqueteResultado($arrE['bn_id']);
		
		if($arr !== false && !DB::isError($strT)) {
			// Seta as variaveis do SMARTY
			$smarty->assign('intTotal',$arr['total']);
			unset($arr['total']);
			
			$smarty->assign('arrPollQuestion',$arrE);
			$smarty->assign('arrPollResult',$arr);
			/*
			echo '<pre>';
			print_r($arrE);
			print_r($arr);
			die();
			*/
			$smarty->display($strT);
		} else {
			return;
		}
	} else {
		return;
	}
}
?>