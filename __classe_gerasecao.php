<?php
include_once 'mensagens.php';
include_once 'funcao_termos.php';
include_once 'classe_smarty.php';
include_once ROOT.'site/include-sistema/sys/class/getid3/getid3.php';

function fnBuscaCampos($idSecao, $prefixo = '', $sufixo = '') { // Cria o array com os campos dinmicos
	global $conexao;
	
	$query = "SELECT
				  bt_campo
			   FROM
				  tb_secao_estrutura
			   WHERE
				  tb_secao_bn_id = '".$idSecao."'
			   UNION
			   SELECT
				  bt_campo
			   FROM
				  tb_secao_relacionamento
			   WHERE
				  tb_secao_bn_id = '".$idSecao."';";
	$rs	= $conexao->query($query);
	if(!DB::isError($rs)) {
		$arrCampos = array();
		while($ob = $rs->fetchRow()) {
			$arrCampos[] = $ob->bt_campo . ' AS ' . $prefixo . $ob->bt_campo . $sufixo;
			$arrTemp = explode('_',$ob->bt_campo);
			if(end($arrTemp) == 'img') {
				$arrCampos[] = $ob->bt_campo . '_thumbnail AS ' . $prefixo . $ob->bt_campo . '_thumbnail' . $sufixo;
			}
			unset($arrTemp);
		}
		unset($query,$rs,$ob);
		return $arrCampos;
	} else {
		return false;
	}
}

function fnBuscaRelacionamentos($tabela,$strSecao) { // Cria o array com as tabelas relacionadas  tabela principal e os campos de tais tabelas
	global $conexao;
	
	$query =  "SELECT
					tb_secao_bn_id,
					bn_secao,
					bt_tabela,
					bt_campo
				FROM
					tb_secao_relacionamento
				WHERE
					tb_secao_bn_id = '".$strSecao."'
				GROUP BY
					bt_campo"; /* WHERE ~ OR bn_secao = '".$strSecao."' */
	$rs	= $conexao->query($query);
	if(!DB::isError($rs)) {
		$arrCamposRel = array();
		while($ob = $rs->fetchRow()) {
			// Define o nome da tabela relacionada
			#$strTabelas = substr($ob->bt_tabela,6,strlen($ob->bt_tabela)-6);
			$arrTabelas = explode('_tb_',str_replace('tb_rel_tb_','',$ob->bt_tabela));
			
			if($ob->tb_secao_bn_id == $ob->bn_secao) {
				$arrTabelas[0] = str_replace('_parent','',$arrTabelas[0]);
				$arrTabelas[1] = str_replace('_child','',$arrTabelas[1]);
			}
			
			if('tb_conteudo_'.$arrTabelas[0] == $tabela) {
				$strTabelas = 'tb_conteudo_'.$arrTabelas[1];
			}
			if('tb_conteudo_'.$arrTabelas[1] == $tabela) {
				$strTabelas = 'tb_conteudo_'.$arrTabelas[0];
			}
			
			// Define o ID da seo relacionada
			if($ob->tb_secao_bn_id == $strSecao) {
				$strSecaoRel = $ob->bn_secao;
			} elseif($ob->bn_secao == $strSecao) {
				$strSecaoRel = $ob->tb_secao_bn_id;
			}
			
			// Seta o prefixo para o ALIAS dos campos da tabela relacionada
			//$strPrefixo = str_replace('tb_conteudo_','tb_',$strTabelas).'_';
			
			$arrCamposRel[$ob->bt_campo]['id']	 = $strSecaoRel;
			$arrCamposRel[$ob->bt_campo]['tabela'] = $strTabelas;
			$arrCamposRel[$ob->bt_campo]['tabelaRel'] = $ob->bt_tabela;
			$arrCamposRel[$ob->bt_campo]['campos'] = fnBuscaCampos($strSecaoRel);
			
			/**********************************************************************/
			/**********************************************************************/
			/***																***/
			/*** FALTA BUSCAR OS CAMPOS DE RELACIONAMENTO DA TABELA RELACIONADA ***/
			/***																***/
			/**********************************************************************/
			/**********************************************************************/
		}
		unset($query,$rs,$ob);
		
		return $arrCamposRel;
	} else {
		return false;
	}
}

function fnSetaRefinamento($refinamento, $strTabela, $strConteudo) { // Cria a string de refinamento da busca
	if(!empty($strConteudo) && $strConteudo > 0) {
		$strConteudo = $strTabela.'.bn_id = "'.$strConteudo.'"';
	} else {
		$strConteudo = '';
	}
	
	if($strConteudo != '' && $refinamento != '') {
		$refinamento .= ' AND '.$strConteudo;
	} elseif($strConteudo != '' && $refinamento == '') {
		$refinamento = $strConteudo;
	}
	
	return $refinamento;
}

function fnGroup($group) { // Define o agrupamento da query
	if($group != '') {
		if(preg_match('/^\s*?GROUP BY.*$/',$group)){
			$strGroup = " ".$group;
		} else {
			$strGroup = " GROUP BY ".$group;
		}
		return $strGroup;
	} else {
		return;
	}
}

$strURL = '';
function fnSetaLinkURL($array,$controle = 0) {
	global $strURL;
	
	if(!is_array($array)) {
		$array = array($array);
	}
	
	foreach($array AS $chave => $campo) {
		if(!is_array($campo)) {
			if(isset($_REQUEST[$chave]) && ( (is_numeric($_REQUEST[$campo]) && $_REQUEST[$campo] > 0) || (is_string($_REQUEST[$campo]) && $_REQUEST[$campo] != '') )) {
				if($controle == 0) {
					$strURL .= '&'.$chave.'=1';
				}
				$strURL .= '&'.$campo.'='.$_REQUEST[$campo];
			}
		} elseif(isset($_REQUEST[$chave])) {
			$strURL .= '&'.$chave.'=1';
			fnSetaLinkURL($campo,1);
		}
	}
}

function fnPaginacao($objConfig,$total, $itens = 5, $itensLista = 15, $strLink = '', $strRW = '&lt;', $strFW = '&gt;', $strRWPg = '...', $strFWPg = '...', $boolJS = 0,$arrPagingVars = array()) { // Cria o array da paginao array('linkRW' => (string), 'strPG' => (string), 'linkFW' => (string));
	global $strURL;
	
	// Faz as verificações de formato dos parâmetros
	
	if(is_object($objConfig)) {
		$intSecao = $objConfig->bn_id;
	} elseif(is_numeric($objConfig)) {
		$intSecao = $objConfig;
	} else {
		return false;
	}
	if(!is_numeric($total) || $total == 0) {
		return false;
	}
	if(is_null($itens) || is_null($itensLista)) {
		return false;
	}
	
	// Define as variáveis para a URL
	$arrStringPaginacao = array(
							'intType' 						=> 'intType',
							'ib_busca' 						=> 'ib_busca',
							'it_busca' 						=> 'it_busca',
							'bt_busca' 						=> 'bt_busca',
							'bn_regiao' 					=> 'bn_regiao',
							'bn_hotelquali' 				=> 'bn_hotelquali',
							'tb_noticia_categoria_bn_id'	=> 'tb_noticia_categoria_bn_id',
							'tb_produto_categoria_bn_id'	=> 'tb_produto_categoria_bn_id',
							'tb_servico_subgrupo_bn_id'		=> 'tb_servico_subgrupo_bn_id',
							'tb_na_cidade_categoria_bn_id'	=> 'tb_na_cidade_categoria_bn_id',
							'tb_shopping_localidade_bn_id'	=> 'tb_shopping_localidade_bn_id',
							'tb_lancheteria_cozinha_bn_id'	=> 'tb_lancheteria_cozinha_bn_id',
							'tb_restaurante_cozinha_bn_id'	=> 'tb_restaurante_cozinha_bn_id',
							'it_campo'						=> 'it_campo',
							'ib_busca_edicao' 				=> 'in_busca_edicao',
							'ib_busca_nome' 				=> 'it_busca_nome',
							'ib_busca_ativo' 				=> 'in_busca_ativo',
							'tb_imagem_categoria_bn_id'		=> 'tb_imagem_categoria_bn_id',
							'it_nome' 						=> 'it_nome',
							'bd_criacao_dia'				=> 'bd_criacao_dia',
							'bd_criacao_mes' 				=> 'bd_criacao_mes',
							'bd_criacao_ano'				=> 'bd_criacao_ano',
							'ib_busca_publicacao' 			=> array(
															'id_busca_inicio_publicacao_ano' => 'id_busca_inicio_publicacao_ano',
															'id_busca_inicio_publicacao_mes' => 'id_busca_inicio_publicacao_mes',
															'id_busca_inicio_publicacao_dia' => 'id_busca_inicio_publicacao_dia',
															'id_busca_fim_publicacao_ano' 	 => 'id_busca_fim_publicacao_ano',
															'id_busca_fim_publicacao_mes' 	 => 'id_busca_fim_publicacao_mes',
															'id_busca_fim_publicacao_dia' 	 => 'id_busca_fim_publicacao_dia'
															),
							'ib_busca_expiracao' 	=> array(
															'id_busca_inicio_expiracao_ano'  => 'id_busca_inicio_expiracao_ano',
															'id_busca_inicio_expiracao_mes'  => 'id_busca_inicio_expiracao_mes',
															'id_busca_inicio_expiracao_dia'  => 'id_busca_inicio_expiracao_dia',
															'id_busca_fim_expiracao_ano' 	 => 'id_busca_fim_expiracao_ano',
															'id_busca_fim_expiracao_mes' 	 => 'id_busca_fim_expiracao_mes',
															'id_busca_fim_expiracao_dia' 	 => 'id_busca_fim_expiracao_dia'
															)
						  );
	$arrStringPaginacao = array_merge($arrStringPaginacao,$arrPagingVars);
	
	fnSetaLinkURL($arrStringPaginacao,(strpos($_SERVER['PHP_SELF'],'/site/') !== false ? 1 : 0));
	
	// Define o valor da lista de paginacao atual
	$strPg = ceil(PAGINA / $itens);
	
	// Define o número total de páginas
	if($total > $itensLista && $itensLista > 0) {
		$totalPag = ceil($total / $itensLista);
	} else {
		$totalPag = 1;
	}
	
	if($totalPag > $itens && $itens > 0) {
		$totalLista = ceil($totalPag / $itens);
	} else {
		$totalLista = 1;
	}
	
	$str   = '';
	
	$fim    = $itens * $strPg;
	if($fim > $totalPag) { $fim = $totalPag; }
	$inicio = ($fim - $itens) + 1;
	if($inicio <= 0) { $inicio = 1; }
	
	$strIndex = ($_REQUEST['PUBLICA_friendlyURL'] == 1 && is_object($objConfig) ? HTTP.fnSintaxePermalink(str_replace('tb_conteudo_','',$objConfig->bt_tabela)).'/?' : HTTP_DINAMICO.'index.php?');
/*
echo 'pagina: '.PAGINA.'<br>';
echo 'itens: '.$itens.'<br>';
echo 'strPg: '.$strPg.'<br>';
echo 'total: '.$total.'<br>';
echo 'itensLista: '.$itensLista.'<br>';
echo 'totalPag: '.$totalPag.'<br>';
echo 'totalLista: '.$totalLista.'<br>';
echo 'inicio: '.$inicio.'<br>';
echo 'fim: '.$fim.'<br>';
*/
	for($i = $inicio; $i <= $fim; $i++) {
		if($strLink != '') {
			$link = str_replace('TOTAL_PG' ,$total		,$strLink);
			$link = str_replace('TOTAL'    ,$itensLista	,$link);
			
			$link = str_replace('IN_SECAO' ,IN_SECAO	,$link);
			$link = str_replace('SECAO'    ,$intSecao	,$link);
			$link = str_replace('IN_IDIOMA',IN_IDIOMA	,$link);
			$link = str_replace('IDIOMA'   ,IDIOMA		,$link);
			$link = str_replace('IN_PAGINA',IN_PAGINA	,$link);
			$link = str_replace('PAGINA'   ,$i			,$link);
		} else {
			if($intSecao > 0) {
				$link = $strIndex . ($_REQUEST['PUBLICA_friendlyURL'] == 1 ? '' : IN_SECAO.'='.$intSecao.'&'.IN_IDIOMA.'='.IDIOMA.'&') . IN_PAGINA.'='.$i;
			} else {
				$link = $strIndex . IN_PAGINA.'='.$i;
			}
		}
		if($i != PAGINA) {
			$str .= '<li><a href="'.$link.$strURL.'">'.$i.'</a></li>';
		} else {
			$str .= '<li class="active"><a href="'.$link.$strURL.'">'.$i.'</a></li>';
		}
		unset($link);
		
		$inicio++;
	}
	
	// Cria o link próximas, anteriores e avançar paginação
	if($boolJS == 0 && $itens > 0 && $itensLista > 0) {
		if($strLink != '') {
			$link = str_replace('TOTAL_PG' ,$total		,$strLink);
			$link = str_replace('TOTAL'    ,$itensLista	,$link);
			
			$link = str_replace('IN_SECAO' ,IN_SECAO	,$link);
			$link = str_replace('SECAO'    ,$intSecao	,$link);
			$link = str_replace('IN_IDIOMA',IN_IDIOMA	,$link);
			$link = str_replace('IDIOMA'   ,IDIOMA		,$link);
			$link = str_replace('IN_PAGINA',IN_PAGINA	,$link);
			
			$linkFW = str_replace('PAGINA' ,(PAGINA + 1),$link);
			$linkRW = str_replace('PAGINA' ,(PAGINA	- 1),$link);
			
			$linkFWPg = str_replace('PAGINA',(($strPg + 1) * $itens - $itens + 1),$link);
			$linkRWPg = str_replace('PAGINA',(($strPg - 1) * $itens - $itens + 1),$link);
			unset($link);
		} else {
			if($intSecao > 0) {
				if((PAGINA + 1) <= $totalPag) {
					$linkFW = $strIndex . ($_REQUEST['PUBLICA_friendlyURL'] == 1 ? '' : IN_SECAO.'='.$intSecao.'&'.IN_IDIOMA.'='.IDIOMA.'&') . IN_PAGINA.'='.(PAGINA + 1);
				} else {
					$linkFW = '';
				}
				
				if((PAGINA - 1) > 0) {
					$linkRW = $strIndex . ($_REQUEST['PUBLICA_friendlyURL'] == 1 ? '' : IN_SECAO.'='.$intSecao.'&'.IN_IDIOMA.'='.IDIOMA.'&') . IN_PAGINA.'='.(PAGINA - 1);
				} else {
					$linkRW = '';
				}
				
				$linkFWPg = $strIndex . ($_REQUEST['PUBLICA_friendlyURL'] == 1 ? '' : IN_SECAO.'='.$intSecao.'&'.IN_IDIOMA.'='.IDIOMA.'&') . IN_PAGINA.'='.(($strPg + 1) * $itens - $itens + 1);
				$linkRWPg = $strIndex . ($_REQUEST['PUBLICA_friendlyURL'] == 1 ? '' : IN_SECAO.'='.$intSecao.'&'.IN_IDIOMA.'='.IDIOMA.'&') . IN_PAGINA.'='.(($strPg - 1) * $itens - $itens + 1);
			} else {
				$linkFW = $strIndex . IN_PAGINA.'='.(PAGINA + 1);
				$linkRW = $strIndex . IN_PAGINA.'='.(PAGINA - 1);
				
				$linkFWPg = $strIndex . IN_PAGINA.'='.(($strPg + 1) * $itens - $itens + 1);
				$linkRWPg = $strIndex . IN_PAGINA.'='.(($strPg - 1) * $itens - $itens + 1);
			}
		}
		if($inicio > $itens) {
			if((($strPg + 1) * $itens - $itens + 1) <= $totalPag) {
				$str .= '<li class="marginRight5"><a href="'.$linkFWPg.$strURL.'">'.$strFWPg.'</a></li>';
			}
			
			if($strPg > 1) {
				$str  = '<li class="marginLeft5"><a href="'.$linkRWPg.$strURL.'">'.$strRWPg.'</a></li>'.$str;
			}
			unset($strPg);
		}
		
		if(PAGINA < $totalPag) {
			$linkFW = '<li class="marginRight5"><a href="'.$linkFW.$strURL.'">'.$strFW.'</a></li>';
		} else {
			$linkFW	= '';
		}
		
		if(PAGINA > 1) {
			$linkRW	= '<li class="marginLeft5"><a href="'.$linkRW.$strURL.'">'.$strRW.'</a></li>';
		} else {
			$linkRW	= '';
		}
	} elseif($boolJS == 1) {
		if($inicio > $itens) {
			$str .= ' <span id="pg_avancar_paginacao"><a href="javascript:fnPaginacaoLista(1);">'.$strFWPg.'</a></span>';
			$str  = ' <span id="pg_avancar_paginacao"><a href="javascript:fnPaginacaoLista(0);">'.$strRWPg.'</a></span>'.$str;
		}
		$linkFW   = '<span id="pg_avancar_lista"><a href="javascript:fnPaginacaoLista(1);">'.$strFW.'</a></span>';
		$linkRW   = '<span id="pg_voltar_lista"><a href="javascript:fnPaginacaoLista(0);"> '.$strRW.'</a></span>';
	} else {
		$str = '';
		$linkFW = '';
		$linkRW = '';
	}
	
	return  array(
				'intPagina' 		=> PAGINA,
				'intTotalPaginas' 	=> $totalPag,
				'linkFW' 			=> $linkFW,
				'str'  	 			=> $str,
				'linkRW' 			=> $linkRW,
				'total'	 			=> $total,
				'itens'	 			=> $itens,
				'lista'	 			=> $itensLista,
				'strURL' 			=> $strURL,
				'itensInicio' 		=> (PAGINA * $itensLista) - $itensLista + 1,
				'itensFim' 			=> (PAGINA * $itensLista)
			);
}

function fnExibeSecao($strResultado, $strSecao, $strConteudo, $strIdioma, $arrCampos = array(), $strRefinamento = '', $strGroup = 'bn_id', $strLimit = 0, $strJoins = '', $boolJoins = 1, $boolRel = 1, $qtdPaginacao = 5, $qtdLista = null, $strLink = '', $strRW = 'anterior', $strFW = 'pr&oacute;ximo', $strRWPg = '...', $strFWPg = '...', $strTPL = '', $boolStrTPL = 0, $boolHome = '', $strOrderBy = '') { // Exibe a seo setada
	// Criao array com os resultados da seo
	$array  = fnGeraSecao($strSecao,$strConteudo,$strIdioma,$arrCampos,$strRefinamento,$strGroup,$strLimit,$strJoins,$boolJoins,$boolRel,$boolHome,1,1,$strOrderBy);
	
	// Exibe ou retorna o TPL interpretado
	fnInterpretaSecao($array,$strSecao,$strConteudo,$strIdioma,$boolStrTPL,$strResultado,$qtdPaginacao,(!is_numeric($qtdLista) ? (isset($array['CONFIG']->bn_lista) ? $array['CONFIG']->bn_lista : 10) : $qtdLista),$strLink,$strRW,$strFW,$strRWPg,$strFWPg,$strTPL,$boolHome);
}

function fnSetaLink($strSecao, $strConteudo, $valor, $obConfig, $campo = '') { 	// Cria o link de conteúdo
	global $conexao;
	
	if(strpos($valor,'.')) {										// MATÉRIA e URL
		// Define o target do link
		if(strpos($obConfig->bn_link,'window.open') === false) {
			$str = '" target="'.$obConfig->bn_link;
		}
		
		if(is_numeric($valor)) {									// MATÉRIA
			$arr = explode('.',$valor);
			$obConfigRel   = fnConfigSecao($arr[0]);
			
			// Define o target do link
			if(strpos($obConfigRel->bn_link,'window.open') === false) {
				$str = '" target="'.$obConfigRel->bn_link;
			}
			
			// Cria a URL do link
			switch($obConfigRel->bb_estatico) {
				case 1:
					if($obConfigRel->bb_home == 1) {
						$valor = ESTATICO.$obConfigRel->bt_arquivo.'.htm';
					} else {
						$valor = ESTATICO.reset(explode('_',$obConfigRel->bt_arquivo)).'/'.$obConfigRel->bt_arquivo.'_'.$arr[1].'.htm';
					}
				break;
				
				case 0:
				default:
					$valor = DINAMICO.'index.php?'.IN_SECAO.'='.$arr[0].'&'.IN_IDIOMA.'='.IDIOMA.'&'.IN_CONTEUDO.'='.$arr[1];
				break;
			}
		}
		
		// Define a função javascript para window.open ou concatena a configuração de target
		if(!isset($str)) {
			$valor = "javascript:popupJanela('".$valor."','materia','".$obConfigRel->bn_link_altura."','".$obConfigRel->bn_link_largura."');";
		} else {
			$valor .= $str;
		}
	} elseif( (is_numeric($valor) && $valor > 0) || (reset(explode('_',$valor)) == 'video') ) {			// GALERIA IMAGENS / VIDEOS
		$valor = fnSetaLinkGaleria($obConfig->bb_estatico,$valor);
	}
	
	return $valor;
}

function fnSetaLinkGaleria($boolEstatico,$strValor) {
	switch($boolEstatico) {
		case 1:
			$strValor = ESTATICO."/galeria/galeria".(reset(explode('_',$strValor)) == 'video' ? $strValor : '_'.$strValor).".htm";
		break;
		
		case 0:
		default:
			$strValor = DINAMICO."galeria.php?".IN_GALERIA."=".$strValor;
		break;
	}
	$strValor = "javascript:popupJanela('".$strValor."','galeria','".GALLERY_HEIGHT."','".GALLERY_WIDTH."');";
	
	return $strValor;
}

function fnLimit($strLimit,$obConfigLista) {
	if($obConfigLista == 0) $obConfigLista = 1;
	if(is_numeric($strLimit) && $strLimit == 0) {
		if(PAGINA > 1) {
			$strOffSet 	= (PAGINA - 1) * $obConfigLista;
		} else {
			$strOffSet 	= 0;
		}
		$strLimit		= ' LIMIT '.$strOffSet.','.$obConfigLista.';';
	} elseif(is_numeric($strLimit) && $strLimit > 0) {
		// Seta limite simples, com OFFSET ZERO
		$strLimit		= ' LIMIT 0,'.$strLimit.';';
	} elseif(is_string($strLimit) && substr_count($strLimit,',') == 1) {
		// Seta limite com OFFSET definido pelo usuário
		$arrLimit		= explode(',',$strLimit);
		$strLimit	= ' LIMIT '.intval($arrLimit[0]).','.intval($arrLimit[1]).';';
		unset($arrLimit);
	} else {
		$strLimit 		= '';
	}
	return $strLimit;
}

function fnInfoImg($strImagem,$strCampo) {
	global $conexao;
	
	$query	= "SELECT bn_id, bt_nome, bt_autor, bt_legenda FROM tb_imagem WHERE bt_arquivo = '".$strImagem."' OR bt_thumbnail = '".$strImagem."';";
	$rs		= $conexao->query($query);
	if(!DB::isError($rs)) {
		$arrTemp	= array();
		if($ob	= $rs->fetchRow()) {
			$strLegenda 			= $strCampo.'_legenda';
			$strAutor				= $strCampo.'_autor';
			$strNome				= $strCampo.'_nome';
			$arrTemp[$strLegenda]	= $ob->bt_legenda;
			$arrTemp[$strAutor]		= $ob->bt_autor;
			$arrTemp[$strNome]		= $ob->bt_nome;
		}
		
		return $arrTemp;
	} else {
		return false;
	}
}

function fnGeraSecao($strSecao, $strConteudo, $strIdioma, $arrCamposXTRA = array(), $strRefinamento = '', $strGroup = 'bn_id', $strLimit = 0, $strJoins = '', $boolJoins = 1, $boolRel = 1, $boolHome = '', $boolInfoImg = 1, $boolControle = 1, $strOrderBy = '') { // Busca os contedos da seo e retorna o RECORDSET para a lista ou o OBJECT para o contedo
	global $conexao;
	
	// Define o ID de SEÇÃO
	if(!is_numeric($strSecao) || $strSecao == 0) {
		return false;
	}
	
	// Define o ID de CONTEUDO
	if(!is_numeric($strConteudo) && !is_null($strConteudo)) {
		return false;
	}
	
	// Define o ID de IDIOMA
	if(!is_numeric($strIdioma) || $strIdioma == 0) {
		return false;
	}
	
	// Busca as configuraes da seo
	$obConfig = fnConfigSecao($strSecao);
	if(!is_object($obConfig)) {
		return false;
	}
	
	// Define o boolean $strHome
	if(!is_numeric($boolHome) || (is_numeric($boolHome) && $boolHome > 1)) { 
		$boolHome = $obConfig->bb_home;
	} else {
		$obConfig->bb_home = $boolHome;
	}
	if(!defined('HOME')) {
		define('HOME',$boolHome);
	}
	
	// Define o ID de conteudo da HOME
	/*
	if($boolHome == 1 && is_null($strConteudo)) {
		$strConteudo = fnBuscaId($obConfig->bt_tabela,0,'LIMIT 0,1','','bd_publicacao');
	}
	*/
	if(!defined('CONTEUDO')) {
		define('CONTEUDO',$strConteudo);
	}
	
	// Cria array de campos dinamicos
	$arrCampos = fnBuscaCampos($strSecao);
	if(!is_array($arrCampos)) {
		return false;
	}
	
	// Cria a string de refinamento da busca
	$strRefinamentoStd 		= str_replace('bb_delete'		,$obConfig->bt_tabela.'.bb_delete'		,REFINAMENTO);
	if($boolControle == 1) {
		$strRefinamentoStd 	= str_replace('bb_ativo'		,$obConfig->bt_tabela.'.bb_ativo'		,$strRefinamentoStd);
		$strRefinamentoStd 	= str_replace('bd_publicacao'	,$obConfig->bt_tabela.'.bd_publicacao'	,$strRefinamentoStd);
		$strRefinamentoStd 	= str_replace('bd_expiracao' 	,$obConfig->bt_tabela.'.bd_expiracao'	,$strRefinamentoStd);
	}
	$strRefinamentoPg = fnSetaRefinamento($strRefinamento, $obConfig->bt_tabela,0);
	$strRefinamentoPg = (strlen($strRefinamentoPg) > 0  ? $strRefinamentoPg . ' AND ' : ' ')
					 .(PREVIEW == 0 ? $strRefinamentoStd.' AND ' : $obConfig->bt_tabela.'.bb_delete = 0 AND ')
					 .$obConfig->bt_tabela.".tb_idioma_bn_id = '".$strIdioma."'";
	
	$strRefinamento = fnSetaRefinamento($strRefinamento, $obConfig->bt_tabela, $strConteudo);
	$strRefinamento = (strlen($strRefinamento) > 0  ? $strRefinamento . ' AND ' : ' ')
					 .(PREVIEW == 0 ? $strRefinamentoStd.' AND ' : $obConfig->bt_tabela.'.bb_delete = 0 AND ')
					 .$obConfig->bt_tabela.".tb_idioma_bn_id = '".$strIdioma."'";
		
	// Define o agrupamento da seo
	$strGroup = fnGroup($strGroup);
	
	// Seta a string LIMIT
	$strLimitQuery = fnLimit($strLimit,$obConfig->bn_lista);
	
	// Cria array dos campos de relacionamento
	$arrRelacionamentos = fnBuscaRelacionamentos($obConfig->bt_tabela,$strSecao);
	
	// Cria array das tabelas de relacionamento
	$arrRelacionamentosTable = array();
	foreach($arrRelacionamentos AS $bt_campo => $arrRelTemp) {
		$arrRelacionamentosTable[$arrRelTemp['tabelaRel']] = $arrRelTemp;
	}
	
	// Define o nome do campo de relacionamento do ID PRINCIPAL
	$strCampo			= str_replace('tb_conteudo_','tb_',$obConfig->bt_tabela.'_bn_id');
	
	if($boolJoins == 1) {
		// Cria string de tabelas de relacionamento
		if(is_array($arrRelacionamentosTable) && count($arrRelacionamentosTable) > 0) {
			$arrTabelas			= array_keys($arrRelacionamentosTable);
			$arrJoins			= array();
			foreach($arrTabelas AS $tabelaRel) {
				$arrJoins[]		= ' LEFT JOIN '.$tabelaRel.' ON '.$tabelaRel.'.'.$strCampo.' = '.$obConfig->bt_tabela.'.bn_id ';
				
				$strSufix		= end(explode('_tb_',$tabelaRel));
				if($strSufix == 'usuario' && $obConfig->bb_publico == 0) {
					$strRefinamento .= ' AND '.$tabelaRel.'.tb_usuario_bn_id = "'.$_SESSION['PUBLICA']['id'].'"';
				}
			}
			$strJoins			= implode(" \n",$arrJoins) . $strJoins;
			unset($arrTabelas,$arrJoins);
		}
	}
	
	// Monta a query dinâmica para o conteúdo e executa a consulta
	$query  ="SELECT DISTINCT
				".$obConfig->bt_tabela.".bn_id,
				".$obConfig->bt_tabela.".bn_materia,
				".$obConfig->bt_tabela.".bd_publicacao,
				".$obConfig->bt_tabela.".permalink,
				".$obConfig->bt_tabela.".seo_description,
				".$obConfig->bt_tabela.".seo_keyword,
				".(count($arrCamposXTRA) > 0 ? implode(', ',$arrCamposXTRA).',' : '')
				 .$obConfig->bt_tabela.".".implode(", ".$obConfig->bt_tabela.".",$arrCampos)."
			  FROM
			  	".$obConfig->bt_tabela
				 .$strJoins."
			  WHERE "
				 .$strRefinamento
				 .$strGroup."
			  ORDER BY
			  	".(empty($strOrderBy) ? $obConfig->bt_ordem : $strOrderBy)
				 .$strLimitQuery;
	
	// Caso a variável $_REQUEST['debug'] esteja setada e seja igual a "2", exibe o array completo em tela
	if(isset($_REQUEST['debug']) && $_REQUEST['debug'] == 0) {
		echo '<pre>'.$query.'</pre>';
	}
	
	$rs	= $conexao->query($query);
	if(is_numeric($strLimit)) {
		// Monta a query para a paginação e executa a consulta
		$queryP = "SELECT
					".$obConfig->bt_tabela.".bn_id
				  FROM
					".$obConfig->bt_tabela
					 .$strJoins."
				  WHERE "
					 .$strRefinamento
					 .$strGroup.";";
		$rsP	= $conexao->query($queryP);
		$total  = $rsP->numRows();
		unset($queryP,$rsP);
	} else {
		$total = $rs->numRows();
	}	
	// Monta o array associativo com o resultado da busca de conteúdo
	$arrResultado = array();
	
	while($ob = $rs->fetchRow()) {
		$arrResultado[$ob->bn_id]['bn_id'] 			= $ob->bn_id;
		$arrResultado[$ob->bn_id]['bn_materia']		= $ob->bn_materia;
		$arrResultado[$ob->bn_id]['bd_publicacao']	= $ob->bd_publicacao;
		$arrResultado[$ob->bn_id]['permalink'] 		= $ob->permalink;
		$arrResultado[$ob->bn_id]['url_permalink'] 	= HTTP.str_replace('tb_conteudo_','',$obConfig->bt_tabela).'/'.$ob->permalink;
		$arrResultado[$ob->bn_id]['seo_description']= $ob->seo_description;
		$arrResultado[$ob->bn_id]['seo_keyword']	= $ob->seo_keyword;
		$arrResultado[$ob->bn_id]['link']			= fnSetaLink($strSecao, $ob->bn_id, $strSecao.'.'.$ob->bn_id, $obConfig);
		
		$arrCampos = array_merge($arrCampos,$arrCamposXTRA);
		//echo '<pre>'; print_r($arrCampos); echo '</pre>';
		foreach($arrCampos AS $campo) {
			$intCountMedia = 0;
			$str  	 = trim(end(explode('AS',$campo)));
			$strTipo = end(explode('_',$str));
			switch($strTipo) {
				case 'galeriavideo':
					if($intCountMedia == 0 && $ob->$str != '' && $ob->$str > 0) $intCountMedia = $conexao->getOne('SELECT COUNT(bn_id) FROM tb_video WHERE tb_video_categoria_bn_id = '.$ob->$str.';');
				case 'galeriaimg':
					if($strTipo == 'galeriaimg' && $intCountMedia == 0 && $ob->$str != '' && $ob->$str > 0) $intCountMedia = $conexao->getOne('SELECT COUNT(bn_id) FROM tb_imagem WHERE tb_imagem_categoria_bn_id = '.$ob->$str.';');
					#echo 'SELECT COUNT(bn_id) FROM tb_imagem WHERE tb_imagem_categoria_bn_id = '.$ob->$str.';<br>';
					// Cria campo com a URL para utilizacao em TPL
					$valor			= fnSetaLink($strSecao, $ob->bn_id, $ob->$str, $obConfig, $str);
					$arrResultado[$ob->bn_id][$str.'_url']		= $valor;
					$arrResultado[$ob->bn_id][$str.'_count']	= $intCountMedia;
					
					// Define o valor do campo como o ID da galeria
					$valor			= $ob->$str;
				break;
				
				case 'rel':
					$valor			= fnSetaLink($strSecao, $ob->bn_id, $ob->$str, $obConfig, $str);
				break;
				
				case 'video':
					$queryV 		= "SELECT bt_download, bt_url FROM tb_video WHERE bn_id = '".$ob->$str."';";
					$rsV			= $conexao->query($queryV);
					$obV			= $rsV->fetchRow();
					$valor			= $obV->bt_download;
					$arrResultado[$ob->bn_id][$str.'_streaming']	= $obV->bt_url;
					unset($queryV,$rsV,$obV);
					if(DB::isError($valor)) {
						$valor		= '';
					}
					unset($queryR,$rsR);
				break;
				
				case 'img':
					if($boolInfoImg == 1) {
						$arrInfoImg = fnInfoImg($ob->$str,$str);
						$arrResultado[$ob->bn_id] = array_merge($arrResultado[$ob->bn_id],$arrInfoImg);
					}
				case 'thumbnail':
					if($ob->$str != '') {
						$valor		= IMAGEM.$ob->$str;
					} else {
						$valor		= '';
					}
				break;
				
				case 'upload':
					if($ob->$str != '') {
						$strSectionDir = (str_replace('tb_conteudo_','',$obConfig->bt_tabela).'/');
						
						if(file_exists( ROOT_UPLOAD . $strSectionDir . $ob->$str) || file_exists( ROOT_WEB_UPLOAD . $strSectionDir . $ob->$str) || file_exists( ROOT_IMAGEM . $strSectionDir . $ob->$str )) {
							$valor		= $strSectionDir . $ob->$str;
						} else {
							$valor		= '';
						}
					} else {
						$valor		= '';
					}
				break;
				
				case 'fone':
					$valor			= ($ob->$str != '' && $ob->$str != 0 ? '('.substr($ob->$str,0,2).') '.substr($ob->$str,2,(strlen($ob->$str) - 6)).'-'.substr($ob->$str,6,(strlen($ob->$str) - 6)) : '');
				break;
				
				case 'cep':
					$valor			= ($ob->$str != '' && $ob->$str != 0 ? substr($ob->$str,0,5).'-'.substr($ob->$str,5,3) : '');
				break;
				
				case 'richtext':
					$valor	= str_replace('../../site/arquivos/imagens/',HTTP_IMAGEM,$ob->$str);
				break;
				
				default:
					$valor			= $ob->$str;
				break;
			}
			$arrResultado[$ob->bn_id][$str]	= $valor;
		}
	}
	
	// Cria o array temporrio para a busca de registros relacionados
	$arrId = array_keys($arrResultado);
	
	// Caso a variável $_REQUEST['debug'] esteja setada e seja igual a "2", exibe o array completo em tela
	if(isset($_REQUEST['debug']) && $_REQUEST['debug'] == 1) {
		echo '<pre>';
		print_r($arrResultado);
		echo '</pre>';
	}
	
	// Busca os registros relacionados ao registro principal
	if(is_array($arrRelacionamentos) && count($arrRelacionamentos) > 0 && $boolRel == 1) {
		// Define o refinamento manual
		if(!is_numeric($strConteudo) && count($arrId) > 0) {
			$strRefinamentoRel = " AND rel.".$strCampo." IN (".implode(',',$arrId).")";
		} elseif($strConteudo > 0) {
			$strRefinamentoRel = " AND rel.".$strCampo." = '".$strConteudo."'";
			$arrId = array($strConteudo);
		} else {
			$strRefinamentoRel = '';
		}
		
		if($strRefinamentoRel != '') {
			foreach($arrRelacionamentos AS $strFieldRel => $arrRel) {
				// Carrega as configurações da seção relacionada
				$obConfigRel = fnConfigSecao($arrRel['id']);
				
				// Define o nome do campo de relacionamento do ID RELACIONADO
				$strRel = str_replace('tb_conteudo_','tb_',$arrRel['tabela']);
				
				// Busca o conteudo das seções relacionadas
				$query = "SELECT DISTINCT
							rel.".$strCampo."				AS id_principal,
							tabela.bn_id 					AS bn_id,
							tabela.bd_publicacao			AS bd_publicacao,
							tabela.permalink				AS permalink,
							tabela.".implode(', tabela.',$arrRel['campos'])."
						  FROM
							".$arrRel['tabelaRel']."		AS rel,
							".$arrRel['tabela']."			AS tabela
						  WHERE
						  	".REFINAMENTO."					AND
							rel.".$strRel."_bn_id			=  tabela.bn_id
							".(isset($arrResultado[$strConteudo][$strFieldRel]) && intval($arrResultado[$strConteudo][$strFieldRel]) > 0 ? " AND tabela.bn_id	= '".$arrResultado[$strConteudo][$strFieldRel]."'" : "")."
							".$strRefinamentoRel."
						  ORDER BY
							id_principal, ".$obConfigRel->bt_ordem.";";
				
				$arrData  = $conexao->getAssoc($query,false,array(),DB_FETCHMODE_OBJECT,true);
				if(!DB::isError($arrData)) {
					foreach($arrId AS $intIDRel) {
						if(isset($_REQUEST['debug']) && $_REQUEST['debug'] == 4) {
							echo $query.'<br><br>';
							echo '$arrData: <br>';
							echo '<pre>'; print_r($arrData); echo '</pre>';
							echo '<br><br>';
							echo '$strFieldRel: '.$strFieldRel.'<br>';
							echo '$intIDRel: '.$intIDRel.'<br>';
							echo '$arrResultado[$intIDRel][$strFieldRel]: <br>';
							echo $arrResultado[$intIDRel][$strFieldRel];
							echo '<br><br>';
							echo '---------------------------------------<br>';
						}
						
						$arrDataRel								= (isset($arrData[$intIDRel]) ? $arrData[$intIDRel] : null);
						$intDataID								= (isset($arrResultado[$intIDRel][$strFieldRel]) ? $arrResultado[$intIDRel][$strFieldRel] : null);
						$arrResultado[$intIDRel][$strFieldRel]	= array();
						if(is_array($arrDataRel) && count($arrDataRel) > 0) {
							$i	= 0;
							foreach($arrDataRel AS $z => $ob) {
								if(is_null($intDataID) || empty($intDataID) || intval($intDataID) == 0 || (is_numeric(intval($intDataID)) && intval($intDataID) == $ob->bn_id)) {
									$arrResultado[$ob->id_principal][$strFieldRel][$i]['bn_id']			= $ob->bn_id;
									$arrResultado[$ob->id_principal][$strFieldRel][$i]['bd_publicacao']	= $ob->bd_publicacao;
									$arrResultado[$ob->id_principal][$strFieldRel][$i]['permalink']		= $ob->permalink;
									$arrResultado[$ob->id_principal][$strFieldRel][$i]['url_permalink']	= HTTP.str_replace('tb_conteudo_','',$arrRel['tabela']).'/'.$ob->permalink;
									$arrResultado[$ob->id_principal][$strFieldRel][$i]['link']			= fnSetaLink($arrRel['id'], $ob->bn_id, $arrRel['id'].'.'.$ob->bn_id, $obConfigRel);
									
									// Adiciona os campos dinmicos ao array de resultado
									foreach($arrRel['campos'] AS $campo) {
										$arrTemp = explode(' AS ',$campo);
										$str  	 = end($arrTemp);
										unset($arrTemp);
										
										$arrTemp = explode('_',$str);
										$suf  	 = end($arrTemp);
										unset($arrTemp);
										
										switch($suf) {
											case 'rel':
												$valor		  = fnSetaLink($arrRel['id'], $ob->bn_id, $ob->$str, $obConfigRel);
											break;
											
											case 'img':
												if($boolInfoImg == 1) {
													$arrInfoImg = fnInfoImg($ob->$str,$str);
													$arrResultado[$ob->id_principal][$strFieldRel][$i] = array_merge($arrResultado[$ob->id_principal][$strFieldRel][$i],$arrInfoImg);
												}
											case 'thumbnail':
												if($ob->$str != '') {
													$valor    = IMAGEM.$ob->$str;
												} else {
													$valor	  = '';
												}
											break;
											
											case 'upload':
												if($ob->$str != '') {
													if(file_exists(ROOT_UPLOAD.$ob->$str)) {
														$valor		= UPLOAD.$ob->$str;
													} elseif(file_exists(ROOT_IMAGEM.$ob->$str)) {
														$valor		= IMAGEM.$ob->$str;
													} else {
														$valor		= '';
													}
												} else {
													$valor		= '';
												}
											break;
											
											default:
												$valor		  = $ob->$str;
											break;
										}
										$arrResultado[$ob->id_principal][$strFieldRel][$i][$str] = $valor;
									}
									$i++;
								}
							}
						}
					}
				} else {
					return false;
				}
				unset($query,$rs,$ob,$strCampoId,$strCampoData);
			}
		}
	}
	
	// Busca o caminho do arquivo TPL
	if(is_numeric($strConteudo) && $strConteudo > 0) {
		$strTemplate = (!empty($obConfig->bn_template_conteudo) ? $obConfig->bn_template_conteudo : ($boolHome == 1 ? $obConfig->bn_template_home : $obConfig->bn_template_lista));
	} elseif(BUSCA != '' || BUSCA == 1) {
		$strTemplate = (!empty($obConfig->bn_template_lista) ? $obConfig->bn_template_lista : ($boolHome == 1 ? $obConfig->bn_template_home : $obConfig->bn_template_conteudo));
	} elseif($boolHome == 1) {
		$strTemplate = $obConfig->bn_template_home;
	} else {
		$strTemplate = $obConfig->bn_template_lista;
	}
	
	if(is_numeric($strTemplate) && $strTemplate > 0) {
		$query = 'SELECT
					bt_arquivo
				  FROM
					tb_template
				  WHERE
					bn_id = "'.$strTemplate.'";';
		$strTemplate = $conexao->getOne($query);
		unset($query);
		
		if(DB::isError($strTemplate) || is_numeric($strTemplate) || $strTemplate == '') {
			return false;
		} else {
			$arrTemplate = explode('/',$strTemplate);
			$strTemplate = end($arrTemplate);
		}
	}

	// Executa a função nativa do PHP html_entity_decode() em todos os elementos do array $arrResultado
	array_walk($arrResultado,'fnHTMLEntityDecode');
	
	// Caso a variável $_REQUEST['debug'] esteja setada e seja igual a "2", exibe o array completo em tela
	if(isset($_REQUEST['debug']) && $_REQUEST['debug'] == 2) {
		echo '<pre>';
		print_r($arrResultado);
		echo '</pre>';
	}
	
	// Busca os IDs imediatamente antes e depois do CONTEUDO para paginacao direta
	if(is_numeric($strConteudo) && $strConteudo > 0) {
		$queryPg = "SELECT
					".$obConfig->bt_tabela.".bn_id
				  FROM
					".$obConfig->bt_tabela
					 .$strJoins."
				  WHERE "
					 .$strRefinamentoPg
					 .$strGroup.";";
		$rsPg	= $conexao->getCol($queryPg);
		
		// Sets control variables
		$intLength = count($rsPg);
		$intKey = reset(array_keys($rsPg,$strConteudo));
		
		// Sets RW key
		$intRW = $intKey - 1;
		if($intKey == 0) $intRW = $intLength - 1;
		$intRW = $rsPg[$intRW];
		
		// Sets FW key
		$intFW = $intKey + 1;
		if($intFW == $intLength) $intFW = 0;
		$intFW = $rsPg[$intFW];
		
		$arrResultado['intRW'] 	= $intRW;
		$arrResultado['intFW'] 	= $intFW;
	}

	// Adiciona o caminho do TPL no array de resultado
	$arrResultado['template'] 		= $strTemplate;
	
	// Adiciona as variáveis de configuração no array de resultado
	$arrResultado['lista'] 		  	= $obConfig->bn_lista;
	$arrResultado['arquivo']	  	= $obConfig->bt_arquivo;
	$arrResultado['link']		  	= $obConfig->bn_link;
	$arrResultado['link_altura']  	= $obConfig->bn_link_altura;
	$arrResultado['link_largura'] 	= $obConfig->bn_link_largura;
	
	// Adiciona a variável de controle da paginação
	$arrResultado['total'] 		  	= $total;

	// Adiciona as configurações da seção
	$arrResultado['CONFIG']			= $obConfig;
	
	// Caso a variável $_REQUEST['debug'] esteja setada e seja igual a "3", exibe o array completo em tela
	if(isset($_REQUEST['debug']) && $_REQUEST['debug'] == 3) {
		echo '<pre>';
		print_r($arrResultado);
		echo '</pre>';
	}
	
	// Retorna o array de resultado
	return $arrResultado;
}

function fnHTMLEntityDecode(&$array) { // Executa a função nativa do PHP html_entity_decode() em todos os elementos do array $array
	foreach($array AS $chave => $valor) {
		if(is_string($valor)) {
			if(end(explode('_',$chave)) == 'richtext' || end(explode('_',$chave)) == 'multitext') {
				$array[$chave] = html_entity_decode($valor,ENT_QUOTES,'UTF-8');
			} else {
				$array[$chave] = nl2br( str_replace('&lt;','<', str_replace('&gt;','>',$valor) ) );
			}
		} elseif(is_array($valor)) {
			fnHTMLEntityDecode($array[$chave]);
		}
	}
}

function fnSetaParametro(&$array,$strParametro) { // Retorna o valor de $array[$strParametro] e unseta a variável no array original
	if(isset($array[$strParametro])) {
		$str = $array[$strParametro];
		unset($array[$strParametro]);
		
		return $str;
	} else { return; }
}

function fnInterpretaSecao($array,$strSecao = 0, $strConteudo = 0, $strIdioma = 0, $boolStrTPL = 0, $strResultado = 'arrResultado', $qtdPaginacao = 5, $qtdLista = 0, $strLink = 'javascript:fnSetaLista(PAGINA);', $strRW = 'anterior', $strFW = 'pr&oacute;ximo', $strRWPg = '&lt;&lt;...', $strFWPg = '...&gt;&gt;', $strTPL = '',$boolHome = 0) {
	if(is_array($array)) {
		// Define o caminho do TPL e atualiza o array de resultado
		$strTemplate = fnSetaParametro($array,'template');
		
		if((is_string($strTemplate) && $strTemplate != '') || (is_string($strTPL) && $strTPL != '')) {
			// Cria o objeto SMARTY
			$smarty  = new smartyConnect;
			
			// Cria as variveis de TERMOS
			fnTermos($smarty);
			
			// Inicializa a classe getID3 e seta a variavel smarty com a nova instancia
			$smarty->assign('objID3',new getID3());
			
			// Define a variavel SMARTY com o objeto de configuracao da secao
			$objConfig = fnSetaParametro($array,'CONFIG');
			$smarty->assign('CONFIG',$objConfig);
			
			// Define as variáveis padrão para o TPL
			$smarty->assign('RESULTADO'		,$strResultado);
			$smarty->assign('TITULO'		,TITULO);
			$smarty->assign('DIRETORIO' 	 ,DIRETORIO);
			$smarty->assign('XML'			,XML);
			$smarty->assign('ESTATICO'		,ESTATICO);
			$smarty->assign('DINAMICO'		,DINAMICO);
			$smarty->assign('IMAGEM'		,IMAGEM);
			$smarty->assign('HOME'		 	,HOME);
			$smarty->assign('PAGINA'		,PAGINA);
			$smarty->assign('GALLERY_HEIGHT',GALLERY_HEIGHT);
			$smarty->assign('GALLERY_WIDTH'	,GALLERY_WIDTH);
			
			// Define as variáveis de TPL específicas de cada seção
			$smarty->assign('SECAO'			,$strSecao);
			$smarty->assign('CONTEUDO'		,$strConteudo);
			$smarty->assign('IDIOMA'		,$strIdioma);
			$smarty->assign('ARQUIVO'		,fnSetaParametro($array,'arquivo'));
			$smarty->assign('LINK'			,fnSetaParametro($array,'link'));
			$smarty->assign('LINK_ALTURA'	 ,fnSetaParametro($array,'link_altura'));
			$smarty->assign('LINK_LARGURA'	 ,fnSetaParametro($array,'link_largura'));
			
			// Define o total de itens no array
			$total = fnSetaParametro($array,'total');
			$smarty->assign('TOTAL'	 ,$total);
				
			// Define as variáveis de paginação da seção
			if($qtdLista == 0 && $total > 1) {
				$qtdLista = $array['lista'];
			}
			$smarty->assign('LISTA'			 ,fnSetaParametro($array,'lista'));
			
			// Define o array com as variáveis de paginação
			if($total > 0) {
				$boolJS = 0;
				
				$pag = fnPaginacao($objConfig, $total, $qtdPaginacao, $qtdLista, $strLink, $strRW, $strFW, $strRWPg, $strFWPg, $boolJS);
				$pag['intRW'] = fnSetaParametro($array,'intRW');
				$pag['intFW'] = fnSetaParametro($array,'intFW');
				$smarty->assign('PAGINACAO',$pag);
			}
			
			// Define o array com o resultado da secao
			if($strResultado == '') {
				$strResultado = 'arrResultado';
			}
			
			// Monta o array de dados
			$smarty->assign($strResultado,$array); //($smarty->_tpl_vars['CONFIG']->bb_home == 1 ? reset($array) : (is_numeric($strConteudo) && isset($array[$strConteudo]) ? $array[$strConteudo] : $array)));
			
			//echo '<pre>'; print_r($array); echo '</pre>'; 
			
			// Define o valor do primeiro indice
			if(!is_numeric($strConteudo)) {
				if(isset($_REQUEST['firstId']) && !is_null($_REQUEST['firstId']) && !empty($_REQUEST['firstId']) && $_REQUEST['firstId'] > 0) {
					$smarty->assign('FIRST_ID',$_REQUEST['firstId']);
					$smarty->assign('CONTEUDO',$_REQUEST['firstId']);
				} elseif(isset($_REQUEST['intMedia']) && !is_null($_REQUEST['intMedia']) && !empty($_REQUEST['intMedia']) && $_REQUEST['intMedia'] > 0) {
					$smarty->assign('FIRST_ID',$_REQUEST['intMedia']);
					$smarty->assign('CONTEUDO',$_REQUEST['intMedia']);
				} else {
					$smarty->assign('FIRST_ID',reset(array_keys($array)));
					if(HOME == 1 && $smarty->_tpl_vars['CONFIG']->bb_home == 1) $smarty->assign('CONTEUDO',reset(array_keys($array)));
				}
			} else {
				$smarty->assign('FIRST_ID',$strConteudo);
			}
			
			if($boolStrTPL == 0) {
				// Exibe o TPL interpretado
				if($strTPL != '') {
					$smarty->display($strTPL);
				} else {
					$smarty->display($strTemplate);
				}
				unset($smarty);
			} else {
				// Retorna o TPL interpretado
				if($strTPL != '') {
					$strTPL = $smarty->fetch($strTPL);
				} else {
					$strTPL = $smarty->fetch($strTemplate);
				}
				unset($smarty);
				
				return $strTPL;
			}
		} else {
			// Caso no haja TPL setado, retorna o array de resultados
			return $array;
		}
	} else {
		return false;
	}
}

// Deleta do array principal os índices de interpretação do TPL
function fnLimpaArray(&$array) {
	unset($array['template'],$array['lista'],$array['arquivo'],$array['link'],$array['link_altura'],$array['link_largura'],$array['total'],$array['boolHome'],$array['CONFIG']);
}

// Interpreta o TPL da seção $strSecao e cria o arquivo $strArquivo.htm
function fnCriaEstatico($arrId,$strSecao,$strConteudo,$strIdioma,$strArquivo,$strTabela,$boolDiretorio = 0,$boolUpdate = 1) {
	if(!is_array($arrId) || !isset($arrId['template']) || empty($arrId['template']) || is_null($arrId['template'])) {
		return false;
	}
	
	// Atribui o TPL interpretado à variável
	$strHTML   = fnInterpretaSecao($arrId,$strSecao,$strConteudo,$strIdioma,1);
	
	// Deleta do array principal os índices de interpretação do TPL
	fnLimpaArray($arrId);

	// Cria a string com os IDs de conteúdos da seção
	$strIds	   = implode(',',array_keys($arrId));
	
	fnCriaArquivo($strArquivo,$strHTML,$strTabela,$strIds,$boolDiretorio,$boolUpdate);
	unset($strHTML,$strIds);
}

// Cria o arquivo $strArquivo.htm com o conteúdo $strHTML
function fnCriaArquivo($strArquivo,$strHTML,$strTabela,$strIds,$boolDiretorio = 0,$boolUpdate = 1) {
	global $conexao;
	
	if($boolDiretorio  == 0) {
		$strDiretorio 	= ROOT_ESTATICO;
	} else {
		$strDiretorio	= ROOT_ESTATICO.reset(explode('_',$strArquivo)).'/';
	}
	$strDiretorio 		= str_replace('//','/',$strDiretorio);
	$strArquivo		= $strDiretorio.$strArquivo.'.htm';
	if(!is_dir($strDiretorio)) { mkdir($strDiretorio,0777,true); }
	
	// Exibe as mensagens de sucesso e erro
	if(fnFile($strArquivo,$strHTML)) {
		// Atualiza boolean bb_publicar
		if($boolUpdate == 1) {
			$query = "UPDATE ".$strTabela." SET bb_publicar = 0 WHERE bn_id IN (".$strIds.");";
			$rs	   = $conexao->query($query);
		}
		
		fnLogStatic(1,$strArquivo);
		
		return true;
	} else {
		fnLogStatic(0,$strArquivo);
		
		return false;
	}
}

// Cria o arquivo $strArquivo com o conteúdo $strConteudo e as permissões $strCHMOD
function fnFile($strArquivo,$strConteudo,$strModo = 'w+') {
	// Abre o arquivo no modo especificado em $strModo
	$file = @fopen($strArquivo,$strModo);
	if($file !== false) {
		// Grava o TPL interpretado no arquivo
		if(!fwrite($file,$strConteudo)) {
			unset($file);
			return false;
		} else {
			// Fecha o arquivo
			fclose($file);
			unset($file);
			
			// Define as permisses de leitura do arquivo
			@chmod($strArquivo,0775);
			
			return true;
		}
	} else {
		unset($file);
		return false;
	}
}

// Cria o log de geração dos arquivos estáticos
function fnLogStatic($boolStatus, $strArquivo) {
	switch($boolStatus) {
		case 0:
			$str 	 = "Ocorreu um erro na gera&ccedil;&atilde;o do arquivo: ".$strArquivo."\n";
			$arquivo = '/home/webadmin/scripts/log.txt';
		break;
		
		case 1:
			$str 	 = "O arquivo ".$strArquivo." foi criado com sucesso!\n";
			$arquivo = '/home/webadmin/scripts/log.txt';
		break;
		
		case 2:
			$str 	 = "Tempo de execução: ".$strArquivo."\n";
			$arquivo = '/home/webadmin/scripts/tempo.txt';
		break;
		
		default:
			$str 	 = "\n";
			$arquivo = '/home/webadmin/scripts/log.txt';
		break;
	}
	fnFile($arquivo,$str,'a+');
}

function fnExibeGaleria($arrGalleryID,$arrSectionID,$strType,$strResultado,$strTemplate = '',$boolStrTPL = 0,$boolEstatico = 0) {
	global $conexao;
	
	// Format GALLERY IDs array
	if(!is_array($arrGalleryID)) {
		$intTemp = intval($arrGalleryID);
		if($intTemp > 0) {
			$arrGalleryID = array($intTemp);
		} else {
			$arrGalleryID = false;
		}
	} else {
		if(count($arrGalleryID) > 0) {
			foreach($arrGalleryID AS $intKey => $intValue) {
				$arrGalleryID[$intKey] = intval($intValue);
			}
		} else {
			$arrGalleryID = false;
		}
	}
	
	// Format SECTION IDs array
	if(!is_array($arrSectionID)) {
		$intTemp = intval($arrSectionID);
		if($intTemp > 0) {
			$arrSectionID = array($intTemp);
		} else {
			$arrSectionID = false;
		}
	} else {
		if(count($arrSectionID) > 0) {
			foreach($arrSectionID AS $intKey => $intValue) {
				$arrSectionID[$intKey] = intval($intValue);
			}
		} else {
			$arrSectionID = false;
		}
	}
	
	// Checks GALLERY & SECTION consistency
	if($arrGalleryID === false && $arrSectionID === false)			return false;	
	if($strType == 'both' && $arrSectionID === false) 			return false;
	if($strType != 'image' && $strType != 'video' && $strType != 'both') 	return false;
	
	// Defines MEDIA ID to be shown at first load
	if(isset($_REQUEST['intMedia']) && !is_null($_REQUEST['intMedia']) && !empty($_REQUEST['intMedia']) && $_REQUEST['intMedia'] > 0 ) {
		$intMedia = $_REQUEST['intMedia'];
	} elseif(isset($_REQUEST['firstId']) && !is_null($_REQUEST['firstId']) && !empty($_REQUEST['firstId']) && $_REQUEST['firstId'] > 0) {
		$intMedia = $_REQUEST['firstId'];
	} else {
		$intMedia = 0;
	}
	
	switch($strType) {
		case 'image':
			// Defines GALLERY common variables
			$strFirstTable		= 'tb_imagem';
			$strSecondTable		= 'tb_video';
			$strRelTable		= 'tb_video_bn_id';
			
			// Defines MEDIA DATA DB query
			$strQuery_Gallery	= '	SELECT
								bn_id 		AS intKey,
								bn_id,
								bt_nome,
								bt_autor,
								bt_legenda,
								bt_arquivo,
								bt_thumbnail,
								'.$strFirstTable.'_categoria_bn_id
							FROM 
								'.$strFirstTable.'
							WHERE
								'.$strFirstTable.'_categoria_bn_id IN ('.($arrGalleryID !== false ? implode(',',$arrGalleryID) : 'SELECT bn_id FROM '.$strFirstTable.'_categoria WHERE tb_secao_bn_id IN ('.implode(',',$arrSectionID).')').')
							LIMIT '.LISTA_GALERIA;
			
			// Gets MEDIA DATA
			$arrResultado		= $conexao->getAssoc($strQuery_Gallery, false, null, DB_FETCHMODE_ASSOC,false);
		break;
		
		case 'video':
			// Defines GALLERY common variables
			$strFirstTable		= 'tb_video';
			$strSecondTable		= 'tb_imagem';
			$strRelTable		= 'tb_imagem_categoria_bn_id';
			
			// Defines MEDIA DATA DB query
			$strQuery_Gallery	= '	SELECT
								bn_id 		AS intKey,
								bn_id,
								bt_nome,
								bt_autor,
								bt_legenda,
								REPLACE(bt_url,"watch?v=","v/")	AS bt_arquivo,
								bt_thumbnail,
								'.$strFirstTable.'_categoria_bn_id
							FROM 
								'.$strFirstTable.'
							WHERE
								'.$strFirstTable.'_categoria_bn_id IN ('.($arrGalleryID !== false ? implode(',',$arrGalleryID) : 'SELECT bn_id FROM '.$strFirstTable.'_categoria WHERE tb_secao_bn_id IN ('.implode(',',$arrSectionID).')').')
							LIMIT '.LISTA_GALERIA;
			
			// Gets MEDIA DATA
			$arrResultado		= $conexao->getAssoc($strQuery_Gallery, false, null, DB_FETCHMODE_ASSOC,false);
		break;
		
		case 'both':
			// Defines GALLERY common variables
			$strFirstTable		= 'tb_imagem';
			$strSecondTable		= 'tb_video';
			$strRelTable		= 'tb_video_bn_id';
			
			// Defines MEDIA DATA DB query
			$strQuery_Gallery_I	= '	SELECT
								bn_id,
								bt_nome,
								bt_autor,
								bt_legenda,
								bt_arquivo,
								bt_thumbnail,
								'.$strFirstTable.'_categoria_bn_id
							FROM 
								'.$strFirstTable.'
							WHERE
								'.$strFirstTable.'_categoria_bn_id IN ('.($arrGalleryID !== false && (!isset($_REQUEST['mediaType']) || $_REQUEST['mediaType'] == 'image') ? implode(',',$arrGalleryID) : 'SELECT bn_id FROM '.$strFirstTable.'_categoria WHERE tb_secao_bn_id IN ('.implode(',',$arrSectionID).')').')
							LIMIT '.LISTA_GALERIA;
			
			// @todo	This function assumes that main gallery is IMAGE gallery; thus, only IMAGES can be shown at first load.
			//		System MUST HAVE main video gallery option
			$strQuery_Gallery_V	= '	SELECT
								bn_id,
								bt_nome,
								bt_autor,
								bt_legenda,
								REPLACE(bt_url,"watch?v=","v/")	AS bt_arquivo,
								bt_thumbnail,
								'.$strSecondTable.'_categoria_bn_id
							FROM 
								'.$strSecondTable.'
							WHERE
								'.$strSecondTable.'_categoria_bn_id IN (SELECT bn_id FROM '.$strSecondTable.'_categoria WHERE tb_secao_bn_id IN ('.implode(',',$arrSectionID).'))
							LIMIT '.LISTA_GALERIA;
			
			// Gets MEDIA DATA
			$arrResultado_I 	= $conexao->getAll($strQuery_Gallery_I, null, DB_FETCHMODE_ASSOC);
			$arrResultado_V 	= $conexao->getAll($strQuery_Gallery_V, null, DB_FETCHMODE_ASSOC);
			
			
			echo '<pre>';
			print_r($arrResultado_I);
			echo '<hr>';
			print_r($arrResultado_V);
			echo '</pre>'; die();
			
			
			// Creates RESULT ARRAY merging both MEDIA DATA
			$arrResultado		= array();
			$int_I			= count($arrResultado_I);
			$int_V			= count($arrResultado_V);
			if($int_I > 0 && $int_V == 0) {
				$arrResultado			= $arrResultado_I;
			} elseif($int_I == 0 && $int_V > 0) {
				$arrResultado			= $arrResultado_V;
			} elseif($int_I >= $int_V) {
				for($i = 0; $i < $int_I; $i++) {
					$arrResultado[] 	= $arrResultado_I[$i];
					if($i < $int_V)		$arrResultado[] = $arrResultado_V[$i];
				}
			} elseif($int_V > $int_I) {
				for($i = 0; $i < $int_V; $i++) {
					$arrResultado[] 	= $arrResultado_V[$i];
					if($i < $int_I)		$arrResultado[] = $arrResultado_I[$i];
				}
			} else { return false; }
			unset($arrResultado_I,$arrResultado_V);
	
			if(!isset($_REQUEST['mediaType']) || $_REQUEST['mediaType'] == 'image')	{
				$strType	= 'image';
			} else {
				$strType	= 'video';
				$strFirstTable		= 'tb_video';
				$strSecondTable		= 'tb_imagem';
			}
			
			// Defines $intMedia index at $arrResultado
			foreach($arrResultado AS $intKey => $arrData) {
				switch($strType) {
					case 'video':
						if($arrData['bn_id'] == $intMedia && isset($arrData['tb_video_categoria_bn_id'])) {
							$intMedia = $intKey;
							break 2;
						}
					break;
					
					case 'image':
						if($arrData['bn_id'] == $intMedia && isset($arrData['tb_imagem_categoria_bn_id'])) {
							$intMedia = $intKey;
							break 2;
						}
					break;
				}
			}
			
			echo '<pre>';
			print_r($arrResultado);
			echo '</pre>';
			
		break;
		
		default:
			return false;
		break;
	}
	
	// Gets GALLERY INFO
	$query			=  'SELECT
							'.$strFirstTable.'_categoria.bt_nome,
							'.$strFirstTable.'_categoria.bt_descricao,
							'.$strFirstTable.'_categoria.bt_imagem,
							'.$strFirstTable.'_categoria.bt_imagem_thumbnail,
							'.$strFirstTable.'_categoria.bd_modificacao,
							'.$strFirstTable.'_categoria.bn_template,
							
							tb_template.bt_arquivo				AS tb_template_bt_arquivo
						FROM
							'.$strFirstTable.'_categoria
						LEFT JOIN
							tb_template ON tb_template.bn_id = '.$strFirstTable.'_categoria.bn_template
						WHERE
							'.$strFirstTable.'_categoria.bn_id IN ('.($arrGalleryID !== false ? implode(',',$arrGalleryID) : 'SELECT bn_id FROM '.$strFirstTable.'_categoria WHERE tb_secao_bn_id IN ('.implode(',',$arrSectionID).')').');';
	$rs				= $conexao->query($query);
	
	if(!DB::isError($rs) && $rs->numRows() > 0) {
		$ob			= $rs->fetchRow();
	} else {
		return false;
	}
	unset($query,$rs);
	
	// Caso a variável $_REQUEST['debug'] esteja setada exibe o array completo em tela
	if(isset($_REQUEST['debug'])) {
		echo '<pre>';
		print_r($arrResultado);
		echo '</pre>';
	}
	
	// Creates SMARTY object
	$smarty  = new smartyConnect;
	
	// Creates MULTI-LANGUAGE variables
	fnTermos($smarty);

	// Defnes TPL COMMON variables
	$smarty->assign('TITULO'		,TITULO);
	$smarty->assign('DIRETORIO' 		,DIRETORIO);
	$smarty->assign('XML'			,XML);
	$smarty->assign('ESTATICO'		,ESTATICO);
	$smarty->assign('DINAMICO'		,DINAMICO);
	$smarty->assign('IMAGEM'		,IMAGEM);
	$smarty->assign('PAGINA'		,PAGINA);
	$smarty->assign('LISTA_GALERIA' 	,LISTA_GALERIA);
	$smarty->assign('GALLERY_HEIGHT'	,GALLERY_HEIGHT);
	$smarty->assign('GALLERY_WIDTH'		,GALLERY_WIDTH);
	$smarty->assign('GALLERY_TYPE'		,$strType);
	
	// Defines SPECIFIC SECTIONS variables
	$smarty->assign('SECAO'			,(defined('SECAO') ? SECAO : NULL));
	$smarty->assign('CONTEUDO'		,implode(',',$arrSectionID));
	$smarty->assign('IDIOMA'		,IDIOMA);
	
	// Defines PAGING TPL variable
	$smarty->assign('PAGINACAO'		,count($arrResultado));
	
	// Defines GALLERY TPL variables
	$smarty->assign('GALERIA_ID'		,implode(',',$arrGalleryID));
	$smarty->assign('GALERIA_TITULO'	,$ob->bt_nome);
	$smarty->assign('GALERIA_TEXTO'		,$ob->bt_descricao);
	$smarty->assign('GALERIA_IMAGEM'	,IMAGEM.$ob->bt_imagem);
	$smarty->assign('GALERIA_THUMBNAIL'	,IMAGEM.$ob->bt_imagem_thumbnail);
	$smarty->assign('GALERIA_DATA'		,$ob->bd_modificacao);
		
	// Defines FIRST ID variable
	if(isset($arrResultado[$intMedia])) {
		$smarty->assign('arrMedia',$arrResultado[$intMedia]);
	} else {
		$smarty->assign('arrMedia',reset($arrResultado));
	}
	
	// Assigns MEDIA DATA TPL variable
	$smarty->assign($strResultado,$arrResultado);
	
	// Gets TPL file name
	if($strTemplate == '') $strTemplate = $ob->tb_template_bt_arquivo;
	
	if($boolStrTPL == 0) {
		// Displays TPL
		$smarty->display($strTemplate);
		unset($smarty);
	} else {
		// Assign TPL contents to $strTemplate variable
		$strTemplate = $smarty->fetch($strTemplate);
		unset($smarty);
		
		return $strTemplate;
	}
}

function fnGaleriaEstatico($strGaleria = NULL) {
	global $conexao;
	
	if(is_numeric($strGaleria)) {
		$strWhere = ' tb_imagem_categoria.bn_id = "'.$strGaleria.'" AND ';
	} else {
		$strWhere = '';
	}
	
	$query  = "	SELECT DISTINCT
					tb_imagem_categoria.bn_id,
					tb_template.bt_arquivo
				FROM
					tb_imagem_categoria,
					tb_imagem,
					tb_template
				WHERE
					".$strWhere."
					tb_imagem_categoria.bn_id 	= tb_imagem.tb_imagem_categoria_bn_id AND
					tb_template.bn_id 			= tb_imagem_categoria.bn_template;";
	$rs		= $conexao->query($query);
	while($ob = $rs->fetchRow()) {
		$strGaleria = fnExibeGaleria($ob->bn_id,'image',RESULTADO,$ob->bt_arquivo,1,1);
		fnCriaArquivo('galeria_'.$ob->bn_id,$strGaleria,'','',1,0);
	}
}
?>
