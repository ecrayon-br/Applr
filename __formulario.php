<?php
@session_start();

// Inclui os arquivos padrao do sistema
include_once '../include-sistema/banco.php';
include_once ROOT.'cms/include-sistema/funcoes_padrao.php';
include_once ROOT.'cms/include-sistema/funcao_combodata.php';
include_once ROOT.'cms/include-sistema/funcao_combohora.php';
include_once ROOT.'cms/include-sistema/mensagens.php';
include_once ROOT.'cms/include-sistema/permissoes.php';
include_once ROOT.'cms/include-sistema/classe_upload.php';
include_once ROOT.'cms/include-sistema/classe_gerasecao.php';
include_once ROOT.'cms/include-sistema/funcao_rss.php';
include_once ROOT.'cms/include-sistema/funcao_xml.php';

// Gets SECTION config data
$obSecaoConfig = fnConfigSecao(SECAO);

// Busca o primeiro idioma da seo
$queryI = "SELECT
			 tb_idioma.bn_id,
			 tb_idioma.bt_nome
		   FROM
			 tb_idioma,
			 tb_secao_idioma
		   WHERE
			 tb_secao_idioma.tb_idioma_bn_id = tb_idioma.bn_id	AND
			 tb_secao_idioma.tb_secao_bn_id	 = '".SECAO."'		AND
			 tb_idioma.bb_ativo 			 = 1
		   ORDER BY
			 tb_idioma.bn_id;";

// Funao para validaao javascript
// Recebe o objeto do recordset dos campos de tb_secao_estrutura e tb_secao_relacionamento
// Retorna STRING de validaao
function fnJavaScript($ob) {
	$strJavaScript = "\n";
	if((isset($ob->bn_tipo) && $ob->bn_tipo == 3) || (!isset($ob->bn_tipo) && !strpos($ob->bt_html,'checkbox') && !strpos($ob->bt_html,'radio'))) { 	// Campo diferente de RADIOBUTTON ou CHECKBOX
		if(end(explode('_',$ob->bt_campo)) == 'richtext' && strpos($ob->bt_html,'&lt;/textarea&gt;')) {
			$strJavaScript .= '
			if(FCKeditorAPI.GetInstance("'.$ob->bt_campo.'").GetXHTML(true) == "") {
				erro = erro + "O campo \"'.html_entity_decode($ob->bt_nome,ENT_QUOTES,'UTF-8').'\" é de preenchimento obrigatório!\n";
				
				if(foco == "" && document.form.'.$ob->bt_campo.' !== undefined) {
					var controle = false;
					FCKeditorAPI.GetInstance("'.$ob->bt_campo.'").Focus();
				}
			}';
		} elseif(end(explode('_',$ob->bt_campo)) == 'data') {
			// Obriga o preenchimento do campo DIA
			$strJavaScript .= '
			if(document.form.'.$ob->bt_campo.'_dia.value == "" || document.form.'.$ob->bt_campo.'_dia.value == "0") {
				erro = erro + "O campo \"'.html_entity_decode($ob->bt_nome,ENT_QUOTES,'UTF-8').' - Dia\" é de preenchimento obrigatório!\n";
				if(foco == "" && document.form.'.$ob->bt_campo.' !== undefined) { foco = "'.$ob->bt_campo.'_dia"; }
			}';
			
			// Obriga o preenchimento do campo MES
			$strJavaScript .= '
			if(document.form.'.$ob->bt_campo.'_mes.value == "" || document.form.'.$ob->bt_campo.'_mes.value == "0") {
				erro = erro + "O campo \"'.html_entity_decode($ob->bt_nome,ENT_QUOTES,'UTF-8').' - MÃªs\" é de preenchimento obrigatório!\n";
				if(foco == "" && document.form.'.$ob->bt_campo.' !== undefined) { foco = "'.$ob->bt_campo.'_mes"; }
			}';
			
			// Obriga o preenchimento do campo ANO
			$strJavaScript .= '
			if(document.form.'.$ob->bt_campo.'_ano.value == "" || document.form.'.$ob->bt_campo.'_ano.value == "0") {
				erro = erro + "O campo \"'.html_entity_decode($ob->bt_nome,ENT_QUOTES,'UTF-8').' - Ano\" é de preenchimento obrigatório!\n";
				if(foco == "" && document.form.'.$ob->bt_campo.' !== undefined) { foco = "'.$ob->bt_campo.'_ano"; }
			}';
		} elseif(end(explode('_',$ob->bt_campo)) == 'hora') {
			// Obriga o preenchimento do campo HORA
			$strJavaScript .= '
			if(document.form.'.$ob->bt_campo.'_hora.value == "" || document.form.'.$ob->bt_campo.'_hora.value == "0") {
				erro = erro + "O campo \"'.html_entity_decode($ob->bt_nome,ENT_QUOTES,'UTF-8').' - Hora\" é de preenchimento obrigatório!\n";
				if(foco == "" && document.form.'.$ob->bt_campo.' !== undefined) { foco = "'.$ob->bt_campo.'_dia"; }
			}';
			
			// Obriga o preenchimento do campo MINUTO
			$strJavaScript .= '
			if(document.form.'.$ob->bt_campo.'_minuto.value == "" || document.form.'.$ob->bt_campo.'_minuto.value == "0") {
				erro = erro + "O campo \"'.html_entity_decode($ob->bt_nome,ENT_QUOTES,'UTF-8').' - Minuto\" é de preenchimento obrigatório!\n";
				if(foco == "" && document.form.'.$ob->bt_campo.' !== undefined) { foco = "'.$ob->bt_campo.'_mes"; }
			}';
		} elseif(end(explode('_',$ob->bt_campo)) == 'img') {
			$str = $ob->bt_campo.'_thumbnail';
			
			// Obriga o preenchimento do campo IMAGEM
			$strJavaScript .= '
			if(document.form.'.$ob->bt_campo.'.value == "" || document.form.'.$ob->bt_campo.'.value == "0") {
				erro = erro + "O campo \"'.html_entity_decode($ob->bt_nome,ENT_QUOTES,'UTF-8').'\" é de preenchimento obrigatório!\n";
				if(foco == "" && document.form.'.$ob->bt_campo.' !== undefined) { foco = "'.$ob->bt_campo.'"; }
			}';
		} elseif(end(explode('_',$ob->bt_campo)) == 'upload') {
			$str = $ob->bt_campo.'_old';
			
			// Obriga o preenchimento do campo UPLOAD
			$strJavaScript .= '
			if((document.form.'.$str.'.value == "" || document.form.'.$str.'.value == "0") && (document.form.'.$ob->bt_campo.'.value == "" || document.form.'.$ob->bt_campo.'.value == "0")) {
				erro = erro + "O campo \"'.html_entity_decode($ob->bt_nome,ENT_QUOTES,'UTF-8').'\" é de preenchimento obrigatório!\n";
				if(foco == "" && document.form.'.$ob->bt_campo.' !== undefined) { foco = "'.$ob->bt_campo.'"; }
			}';
		} elseif(end(explode('_',$ob->bt_campo)) == 'multitext' && strpos($ob->bt_html,'&lt;/select&gt;')) {
			$strJavaScript .= '
			var totalOpt = document.form.'.$ob->bt_campo.'_opt.value;
			var strOpt	 = "";
			var erroTemp = 0;
			
			for(i = 0; i <= (totalOpt - 1); i++) {
				var strTemp	= "'.$ob->bt_campo.'_" + i;
				if(FCKeditorAPI.GetInstance(strTemp).GetXHTML(true) == "") {
					erroTemp = erroTemp + 1;
				}
			}
			if(erroTemp == totalOpt) {
				erro = erro + "O campo \"'.html_entity_decode($ob->bt_nome,ENT_QUOTES,'UTF-8').'\" é de preenchimento obrigatório!\n";
				
				if(foco == "" && document.form.'.$ob->bt_campo.' !== undefined) {
					var controle = false;
					FCKeditorAPI.GetInstance("'.$ob->bt_campo.'_0").Focus();
				}
			}';
		} else {
			$strJavaScript .= '
			if(document.form.'.$ob->bt_campo.'.value == "" || document.form.'.$ob->bt_campo.'.value == "0") {
				erro = erro + "O campo \"'.html_entity_decode($ob->bt_nome,ENT_QUOTES,'UTF-8').'\" é de preenchimento obrigatório!\n";
				if(foco == "" && document.form.'.$ob->bt_campo.' !== undefined) { foco = "'.$ob->bt_campo.'"; }
			}';
		}
	} elseif((isset($ob->bn_tipo) && $ob->bn_tipo == 2) || (isset($ob->bt_html) && strpos($ob->bt_html,'radio'))) { // Campos RADIOBUTTON
		$strJavaScript .= '
		boolControl = false;
		n = document.form.'.$ob->bt_campo.'.length;
		for(i = 0; i < n; i++) {
			if(document.form.'.$ob->bt_campo.'[i].checked == true) {
				boolControl = true;
				break;
			}
		}
		if(!boolControl) {
			erro = erro + "O campo \"'.$ob->bt_nome.'\" é de preenchimento obrigatório!\n";
			if(foco == "" && document.form.'.$ob->bt_campo.' !== undefined) { foco = "'.$ob->bt_campo.'[0]"; }
		}
		';
	} elseif((isset($ob->bn_tipo) && $ob->bn_tipo == 1) || (isset($ob->bt_html) && strpos($ob->bt_html,'checkbox'))) { // Campos RADIOBUTTON
		$strJavaScript .= '
		boolControl = false;
		n = document.form.elements.length;
		for(i = 0; i < n; i++) {
			if(document.form.elements[i].id.indexOf("'.$ob->bt_campo.'_") === 0 && document.form.elements[i].checked == true) {
				boolControl = true;
				break;
			}
		}
		if(!boolControl) {
			controle = false;
			erro = erro + "O campo \"'.$ob->bt_nome.'\" é de preenchimento obrigatório!\n";
		}
		';
	}
	$strJavaScript .= "\n";
	return $strJavaScript;
}

// Funao para MATERIAS RELACIONADAS
// Recebe $valor - registro de relacionamento no banco de dados (DOUBLE ou STRING)
// Retorna o nome do registro referente ao ID do parmetro $valor
function fnValorRelacionamento($valor) {
	global $conexao;
	
	if(is_numeric($valor)) {
		if(strpos($valor,'.')) { 		// MATRIA
			$arr 	   = explode('.',$valor);
			$idSecao   = $arr[0];
			$idMateria = $arr[1];
			unset($arr);
			
			$query     = "SELECT
							bt_tabela
						  FROM
							tb_secao
						  WHERE
							bn_id = '".$idSecao."';";
			$strTabela = $conexao->getOne($query);
			if(!DB::isError($strTabela)) {
				unset($query);
				
				$query = "SELECT
							bt_nome
						  FROM
							".$strTabela."
						  WHERE
							bn_id = '".$idMateria."';";
				$str   = $conexao->getOne($query);
				if(!DB::isError($str)) {
					return $str;
				} else {
					return;
				}
			} else {
				return;
			}
		} else {						// GALERIA
			$query = "SELECT
						bt_nome
					  FROM
						tb_imagem_categoria
					  WHERE
						bn_id = '".$valor."';";
			$str   = $conexao->getOne($query);
			if(!DB::isError($str)) {
				unset($query);
				return $str;
			} else {
				return;
			}
		}
	} else {							// URL
		return $valor;
	}
}

if(defined('SECAO')) {
	// Busca a tabela da seao indicada
	$query		= "SELECT bt_tabela FROM tb_secao WHERE bn_id = '".SECAO."';";
	$strTabela  = $conexao->getOne($query);
	if(DB::isError($strTabela)) {
		mensagem('formato_dados');
	}
	unset($query);
	
	// Cria array de TABELAS utilizadas
	$arrTabelas = array($strTabela);

	// Declara a varivel para os campos de preenchimento obrigatrio
	$strJavascript = '';
	
	// Declara a varivel para os campos RICHTEXT
	$strRichText   = '';
	
	// Cria array de CAMPOS utilizados nas aoes INCLUIR, ALTERAR e CONFIRMAR_ALTERAR
	// O ndice representa o nome do campo de formulrio
	// O valor  representa o nome do campo no banco de dados
	$arrCampos  = array(
						'bn_id'				=> 'bn_id',
						'in_materia'		=> 'bn_materia',
						IN_IDIOMA			=> 'tb_idioma_bn_id',
						'bd_publicacao' 	=> 'bd_publicacao',
						'bd_expiracao'  	=> 'bd_expiracao',
						'bb_ativo' 			=> 'bb_ativo',
						'ib_publicar'		=> 'bb_publicar',
						'permalink'  		=> 'permalink',
						'seo_description' 	=> 'seo_description',
						'seo_keyword'		=> 'seo_keyword',
						'bt_nome'			=> 'bt_nome'
						);
	$arrHtml	= array();
	
	// Busca os campos dinmicos
	$query = "SELECT
				tb_secao_estrutura.bt_nome,
				tb_secao_estrutura.bt_campo,
				tb_secao_estrutura.bb_obrigatorio,
				tb_secao_estrutura.bb_administrativo,
				tb_secao_estrutura.bt_tooltip,
				tb_estrutura.bt_html,
				tb_secao_ordem.bn_ordem
			  FROM
			  	tb_secao_estrutura,
				tb_secao_ordem,
				tb_estrutura
			  WHERE
				tb_secao_ordem.bn_tipo 					= 0 								AND
			  	tb_secao_ordem.bn_campo 				= tb_secao_estrutura.bn_id 			AND
			  	tb_secao_estrutura.tb_estrutura_bn_id	= tb_estrutura.bn_id 		 		AND
			  	tb_secao_estrutura.tb_secao_bn_id		= '".SECAO."'
			  	".(ADM_USER == 0 ? ' AND tb_secao_estrutura.bb_administrativo = 0 ' : '')."
			  ORDER BY
			  	tb_secao_ordem.bn_ordem;";
	$rs    = $conexao->query($query);
	if(!DB::isError($rs)) {
		while($ob = $rs->fetchRow()) {
			
			$arrCampos[$ob->bt_campo] 								= $ob->bt_campo;
			$arrHtml[$ob->bn_ordem][$ob->bt_campo]['html']			= $ob->bt_html;
			$arrHtml[$ob->bn_ordem][$ob->bt_campo]['nome'] 			= $ob->bt_nome;
			$arrHtml[$ob->bn_ordem][$ob->bt_campo]['campo'] 		= $ob->bt_campo;
			$arrHtml[$ob->bn_ordem][$ob->bt_campo]['obrigatorio'] 	= $ob->bb_obrigatorio;
			$arrHtml[$ob->bn_ordem][$ob->bt_campo]['administrativo']= $ob->bb_administrativo;
			$arrHtml[$ob->bn_ordem][$ob->bt_campo]['tooltip'] 		= $ob->bt_tooltip;
			
			// Caso campo LOGIN, desabilita a edi��o
			if($ob->bt_campo == 'bt_login' && ACAO == 'alterar') {
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['html'] = str_replace('maxlength','maxlength readonly',$arrHtml[$ob->bn_ordem][$ob->bt_campo]['html']);
			}
			
			// Caso seja campo IMAGEM PR-CADASTRADA, cria o campo THUMBNAIL
			if(end(explode('_',$ob->bt_campo)) == 'img') {
				$arrCampos[$ob->bt_campo.'_thumbnail'] = $ob->bt_campo.'_thumbnail';
			}
			
			// Caso seja campo RELACIONAMENTO DE CONTEDO
			if(end(explode('_',$ob->bt_campo)) == 'rel' && ACAO != 'alterar') {
				$arrCampos[$ob->bt_campo.'_id'] = $ob->bt_campo;
			}
			
			// Cria o script para a validaao dos campos obrigatrios
			if($ob->bb_obrigatorio == 1) {
				$strJavascript .= fnJavaScript($ob);
			}
			
			// Cria o script para os campos RICHTEXT
			if(end(explode('_',$ob->bt_campo)) == 'richtext' && strpos($ob->bt_html,'&lt;/textarea&gt;')) {
				$strRichText .= "
			var OB".$ob->bt_campo."		 = new FCKeditor('".$ob->bt_campo."','',400,'Publica');
			OB".$ob->bt_campo.".BasePath = sBasePath;
			OB".$ob->bt_campo.".ReplaceTextarea();\n\n";
			}
		}
	}
	unset($query,$rs,$ob);
	
	// Busca os campos de relacionamento
	$query = "SELECT
				tb_secao_relacionamento.bt_nome,
				tb_secao_relacionamento.bt_campo,
				tb_secao_relacionamento.bn_secao,
				tb_secao_relacionamento.bb_obrigatorio,
				tb_secao_relacionamento.bb_administrativo,
				tb_secao_relacionamento.bt_tooltip,
				tb_secao_relacionamento.bt_estrutura,
				tb_secao_relacionamento.bn_tipo,
				tb_secao_relacionamento.bt_tabela,
				tb_secao_ordem.bn_ordem
			  FROM
			  	tb_secao_relacionamento,
				tb_secao_ordem
			  WHERE
			  	tb_secao_ordem.bn_tipo 					= 1 							AND
				tb_secao_ordem.bn_campo 				= tb_secao_relacionamento.bn_id AND
			  	tb_secao_relacionamento.tb_secao_bn_id 	= '".SECAO."'
			  	".(ADM_USER == 0 ? ' AND tb_secao_relacionamento.bb_administrativo = 0 ' : '')."
			  ORDER BY
				tb_secao_ordem.bn_ordem;";
	$rs    = $conexao->query($query);
	if(!DB::isError($rs)) {
		while($ob = $rs->fetchRow()) {
			$arrCampos[$ob->bt_campo] 								= $ob->bt_campo;
			if($_SESSION['ib_pvtuser'] === true && strpos($ob->bt_tabela,'_tb_usuario') > 6) {
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['html'] 	 		= $_SESSION['it_usuario'].'<input type="hidden" name="" value="'.$_SESSION['in_usuario'].'">';
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['nome'] 	 		= $ob->bt_nome;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['campo'] 	 	= $ob->bt_campo;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['secao'] 	 	= $ob->bn_secao;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['tooltip'] 		= $ob->bt_tooltip;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['obrigatorio'] 	= $ob->bb_obrigatorio;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['administrativo']= $ob->bb_administrativo;
			} else {
				if(strpos($ob->bt_tabela,'_tb_municipio') > 6) {	
					$arrHtml[$ob->bn_ordem]['PUBLICA_rel_estado_municipio_'.$ob->bt_campo]['html'] 	 			= $ob->bn_tipo;
					$arrHtml[$ob->bn_ordem]['PUBLICA_rel_estado_municipio_'.$ob->bt_campo]['nome'] 	 			= 'Selecione a UF para a filtragem dos Munic&iacute;pios';
					$arrHtml[$ob->bn_ordem]['PUBLICA_rel_estado_municipio_'.$ob->bt_campo]['campo'] 	 		= $ob->bt_campo;
					$arrHtml[$ob->bn_ordem]['PUBLICA_rel_estado_municipio_'.$ob->bt_campo]['tooltip'] 			= 'Os Munic&iacute;pios abaixo ser&atilde;o filtrados de acordo com a UF selecionada!	';
					$arrHtml[$ob->bn_ordem]['PUBLICA_rel_estado_municipio_'.$ob->bt_campo]['estrutura'] 		= 'bt_nome';
					$arrHtml[$ob->bn_ordem]['PUBLICA_rel_estado_municipio_'.$ob->bt_campo]['tabela']    		= $ob->bt_tabela; //'tb_rel_tb_municipio_tb_estado';
					$arrHtml[$ob->bn_ordem]['PUBLICA_rel_estado_municipio_'.$ob->bt_campo]['obrigatorio'] 		= $ob->bb_obrigatorio;
					$arrHtml[$ob->bn_ordem]['PUBLICA_rel_estado_municipio_'.$ob->bt_campo]['administrativo']	= $ob->bb_administrativo;
				}
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['html'] 	 		= $ob->bn_tipo;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['nome'] 	 		= $ob->bt_nome;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['secao'] 	 	= $ob->bn_secao;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['campo'] 	 	= $ob->bt_campo;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['tooltip'] 		= $ob->bt_tooltip;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['estrutura'] 	= $ob->bt_estrutura;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['tabela']    	= $ob->bt_tabela;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['obrigatorio'] 	= $ob->bb_obrigatorio;
				$arrHtml[$ob->bn_ordem][$ob->bt_campo]['administrativo']= $ob->bb_administrativo;
				
				// Cria o script para a validaao dos campos obrigatrios
				if($ob->bb_obrigatorio == 1) {
					$strJavascript .= fnJavaScript($ob);
				}
			}
		}
	}
	
	// Cria array de REFINAMENTOS utilizados nas aoes ALTERAR e CONFIRMAR_ALTERAR
	if( (!isset($_REQUEST['in_materia']) || (isset($_REQUEST['in_materia']) && $_REQUEST['in_materia'] < 1)) && isset($_REQUEST['in_controle']) ) {
		$arrWhere   = array('bn_id = "'.$_REQUEST['in_controle'].'"');
	} elseif(isset($_REQUEST['in_materia'])) {
		$arrWhere	= array('tb_idioma_bn_id = "'.IDIOMA.'" AND bn_materia = "'.$_REQUEST['in_materia'].'"');
	} else {
		$arrWhere	= array();
	}
	
	/***************************************************************************************************/
	/*  A PARTIR DESSE PONTO O CDIGO PHP NAO PRECISA SER EDITADO, EXCETO NA PERSONALIZAAO DE SISTEMAS  */
	/***************************************************************************************************/
	// Cria array especfico de CAMPOS utilizados nas aoes INCLUIR e CONFIRMAR_ALTERAR
	
	$strDiretorio = str_replace('tb_conteudo_','',$arrTabelas[0]);
	
	$arrInsert  = array();
	$arrUpdate  = array();
	foreach($arrCampos AS $camposRequest => $campos) {
		if($camposRequest == 'permalink') {
			// Sets permalink string
			$strPermalink = fnSintaxePermalink($_REQUEST['bt_nome']);
			$arrCount = $conexao->getCol('SELECT permalink FROM ' . $arrTabelas[0] . ' WHERE permalink LIKE "' . $strPermalink . '%" AND tb_idioma_bn_id = "' . IDIOMA . '";');
			$intCount = 1;
			foreach($arrCount AS $strCount) {
				if(is_string($strCount) && $strCount != '') {
					$strTemp = substr_replace($strCount,'',strrpos($strCount,'-'));
					if($strPermalink == $strCount || $strPermalink == $strTemp) $intCount++;
				}
			}
			if($intCount > 1) $strPermalink .= '-'.$intCount;
			
			if(ACAO != 'incluir') {
				unset($arrCampos[$campos]);
				#$arrUpdate[$campos] = $campos.' = "'.$strPermalink.'"';
			} else {
				$arrInsert[$campos] = '"'.$strPermalink.'"';
			}
		} elseif(isset($_REQUEST[$camposRequest])) {
			// Seta os valores dos arrays padrao
			if(is_array($_REQUEST[$camposRequest])) {
				if(end(explode('_',$campos)) == 'multitext') {
					$strTemp = implode("#MULTITEXT#",$_REQUEST[$camposRequest]);
				} else {
					$strTemp = 'NULL'; //implode("\n",$_REQUEST[$camposRequest]);
				}
			} else {
				switch(end(explode('_',$campos))) {
					case 'cpf':
					case 'cnpj':
					case 'numero':
					case 'cep':
						$strTemp = fnSintaxeNumerico($_REQUEST[$camposRequest]);
					break;
					
					case 'fone':
						$strTemp = $camposRequest.'_ddd';
						$strTemp = fnSintaxeNumerico($_REQUEST[$strTemp].$_REQUEST[$camposRequest]);
					break;
					
					default:
						$strTemp = $_REQUEST[$camposRequest];
					break;
				}
			}
			
			/*
			if(function_exists('mb_convert_encoding')) {
				$arrInsert[$campos] = mb_convert_encoding($strTemp,'HTML-ENTITIES', "UTF-8");
				$arrUpdate[$campos] = $campos.' = "'.mb_convert_encoding($strTemp,'HTML-ENTITIES', "UTF-8").'"';
			} else {
			*/
				$arrInsert[$campos] = ($strTemp == 'NULL' ? $strTemp : '"'.htmlentities($strTemp,ENT_QUOTES,'UTF-8').'"');
				$arrUpdate[$campos] = $campos.' = '.($strTemp == 'NULL' ? $strTemp : '"'.htmlentities($strTemp,ENT_QUOTES,'UTF-8').'"');
			/*
			}
			*/
			unset($strTemp);
		} elseif(end(explode('_',$campos)) == 'data') {
			if(isset($_REQUEST[$camposRequest.'_dia']) && isset($_REQUEST[$camposRequest.'_mes']) && isset($_REQUEST[$camposRequest.'_ano']) && checkdate($_REQUEST[$camposRequest.'_mes'],$_REQUEST[$camposRequest.'_dia'],$_REQUEST[$camposRequest.'_ano'])) {
				$strTemp = fnSintaxeNumerico($_REQUEST[$camposRequest.'_ano'].$_REQUEST[$camposRequest.'_mes'].$_REQUEST[$camposRequest.'_dia']);
			} else {
				$strTemp = '';
			}
			$arrInsert[$campos] = '"'.$strTemp.'"';
			$arrUpdate[$campos] = $campos.' = "'.$strTemp.'"';
		} elseif(end(explode('_',$campos)) == 'hora') {
			if(isset($_REQUEST[$camposRequest.'_hora']) && isset($_REQUEST[$camposRequest.'_minuto'])) {
				$strTemp = fnSintaxeNumerico($_REQUEST[$camposRequest.'_hora'].$_REQUEST[$camposRequest.'_minuto'].'00');
			} else {
				$strTemp = '';
			}
			$arrInsert[$campos] = '"'.$strTemp.'"';
			$arrUpdate[$campos] = $campos.' = "'.$strTemp.'"';
		} elseif(isset($_FILES[$camposRequest])) {
			/*
			// Busca o diretrio padrao da seao em ediao
			$query = "SELECT bt_diretorio FROM tb_imagem_categoria WHERE tb_secao_bn_id = '".SECAO."';";
			$strDiretorio = $conexao->getOne($query);
			if(DB::isError($strDiretorio) || $strDiretorio == NULL || $strDiretorio == '') {
				$strId = buscaSecaoPai(SECAO);
				$query = "SELECT bt_diretorio FROM tb_imagem_categoria WHERE tb_secao_bn_id = '".$strId."';";
				$strDiretorio = $conexao->getOne($query);
			}
			*/
			
			// Seta os valores dos arrays padrao
			$arrInsert[$campos] = '';
			$arrUpdate[$campos] = '';
		} elseif(strpos($camposRequest,'_check')) {
			$arrInsert[$campos] = 'NULL';
			$arrUpdate[$campos] = $campos.' = NULL';
		} elseif(ACAO != 'alterar') {
			unset($arrCampos[$campos]);
		}
	}
	$arrCampos = array_unique($arrCampos);
	
	// Realiza as aoes INCLUIR, ALTERAR e CONFIRMAR_ALTERAR
	switch(ACAO) {
		case 'incluir':
			// Verifica duplicidade de login
			if(SECAO == 1) {
				$strLogin = $conexao->getOne('SELECT bn_id FROM tb_conteudo_usuario WHERE bt_mail = ' . $arrInsert['bt_mail'] . ' AND ' . REFINAMENTO . ';');
				if($strLogin > 0 || $strLogin != '') { mensagem('login_existente'); break; }
			}
			
			// Seta as variveis para data de publicaao e expiraao da matria
			$arrCampos['bd_publicacao'] = 'bd_publicacao';
			$arrCampos['bd_expiracao']	= 'bd_expiracao';
			
			$arrInsert['bd_publicacao'] = $_REQUEST['bd_publicacao_ano'].$_REQUEST['bd_publicacao_mes'].$_REQUEST['bd_publicacao_dia'].$_REQUEST['bd_publicacao_hora'].$_REQUEST['bd_publicacao_minuto'].'00';
			
			if(isset($_REQUEST['ib_expiracao']) && $_REQUEST['ib_expiracao'] == 1) {
				$arrInsert['bd_expiracao'] 	 = '00000000000000';
			} else {
				$arrInsert['bd_expiracao'] 	 = $_REQUEST['bd_expiracao_ano'].$_REQUEST['bd_expiracao_mes'].$_REQUEST['bd_expiracao_dia'].$_REQUEST['bd_expiracao_hora'].$_REQUEST['bd_expiracao_minuto'].'59';
			}
			
			/*
			// Seta as variveis de log
			$arrCampos['bn_usuario_criacao'] = 'bn_usuario_criacao';
			$arrCampos['bd_cricacao']		 = 'bd_criacao';
			$arrInsert['bn_usuario_criacao'] = $_SESSION['in_usuario'];
			$arrInsert['bd_criacao']		 = date('YmdHis');
			unset($arrCampos['bd_modificacao'],$arrInsert['bd_modificacao'],$arrCampos['bn_usuario_modificacao'],$arrInsert['bn_usuario_modificacao']);
			*/
			
			// Seta as variaveis para o cadastro de PERFIL e USUARIO autores do registro
			$arrCampos['tb_perfil_bn_id'] 	= 'tb_perfil_bn_id';
			$arrInsert['tb_perfil_bn_id'] 	= $_SESSION['in_perfil'];
			
			$arrCampos['tb_usuario_bn_id'] 	= 'tb_usuario_bn_id';			
			$arrInsert['tb_usuario_bn_id'] 	= $_SESSION['in_usuario'];
			
			// Percorre os campos do formulrio dinmico em busca de campos para UPLOAD de arquivos
			if(isset($_FILES) && count($_FILES) > 0) {
				$arrKeys  = array_keys($_FILES);
				foreach($arrKeys AS $keys) {
					if(strpos($keys,'_upload') !== false && $_FILES[$keys]['name'] != '') {
						// Faz o upload do arquivo setado
						$arrInsert[$keys] = fnUploadSecao($keys,$strDiretorio,10000000,'','',1,$obSecaoConfig->bb_publico);
						if($arrInsert[$keys] === false) {
							mensagem('insert_erro');
							break 2;
						} else {
							$arrInsert[$keys] = '"'.$arrInsert[$keys].'"';
							//fnRenameArquivo($arrInsert[$keys]);
						}
					} else {
						unset($arrCampos[$keys],$arrInsert[$keys]);
					}
				}
			}
			
			if(!isset($_REQUEST['in_materia']) || (isset($_REQUEST['in_materia']) && $_REQUEST['in_materia'] == 0)) {
				// Busca o maior valor do ndice BN_MATERIA e incrementa em 1
				$queryM = "SELECT MAX(bn_id) FROM ".$arrTabelas[0].";";
				$strM	= $conexao->getOne($queryM);
				if(is_numeric($strM)) {
					$arrInsert['bn_materia'] = $strM + 1;
				} else {
					$arrInsert['bn_materia'] = 1;
				}
				$_REQUEST['in_materia'] = $arrInsert['bn_materia'];
			} else {
				$arrInsert['bn_materia'] = $_REQUEST['in_materia'];
			}
			
			$rsI	= $conexao->query($queryI);
			if(!DB::isError($rs)) {
				while($obI = $rsI->fetchRow()) {
					$arrInsert['tb_idioma_bn_id'] = $obI->bn_id;
					
					// Insere os dados
					$query = 'INSERT INTO
								' .$arrTabelas[0].'
								('.implode(', ',$arrCampos) .')
							  VALUES
								('.implode(', ',$arrInsert) .');';
					$rs    = $conexao->query($query);
					if(!DB::isError($rs)) {
						unset($query,$rs);
						
						// Busca o ID do ultimo registro
						$query 	  = "SELECT MAX(bn_id) FROM ".$arrTabelas[0]." WHERE bt_nome = ".$arrInsert['bt_nome']." AND bd_publicacao = ".$arrInsert['bd_publicacao']." AND bn_materia = ".$arrInsert['bn_materia']." AND tb_idioma_bn_id = ".$obI->bn_id.";";
						$ultimoId = $conexao->getOne($query);
						
						if(DB::isError($ultimoId)) { 
							mensagem('insert_erro');
							break;
						} else {
							// Busca o nome da seao
							$query = "SELECT bt_nome FROM tb_secao_idioma WHERE tb_secao_bn_id = '".SECAO."' AND tb_idioma_bn_id = '".$obI->bn_id."';";
							$strSecao = $conexao->getOne($query);
							unset($query);
							
							// Grava o log do registro
							fnLog(1,1,$_SESSION['in_usuario'],$arrInsert['bt_nome'],$strSecao);
							
							// Cria o array de refinamentos para a busca dos dados
							$arrWhere = array('bn_id = "'.$ultimoId.'"');
						}
						unset($query);
						
						// Busca os campos das tabelas de relacionamento
						$query = "SELECT
									bt_campo,
									bt_tabela
								  FROM
								  	tb_secao_relacionamento
								  WHERE
								  	tb_secao_bn_id = '".SECAO."'
			  						".(ADM_USER == 0 ? ' AND tb_secao_relacionamento.bb_administrativo = 0 ' : '').";";
						$rs	   = $conexao->query($query);
						
						while($ob = $rs->fetchRow()) {
							// Define a varivel temporria para os contedos de tabelas relacionadas
							if(!empty($_REQUEST[$ob->bt_campo])) {
								$temp = (!is_array($_REQUEST[$ob->bt_campo]) ? array($_REQUEST[$ob->bt_campo]) : $_REQUEST[$ob->bt_campo]);
								
								// Insere os contedos relacionados
								foreach($temp AS $valor) {
									$queryR = "INSERT
												".$ob->bt_tabela."
											  VALUES
												(
												'".$ultimoId."',
												'".$valor."'
												);";
									$rsR    = $conexao->query($queryR);
									if(DB::isError($rsR)) { 
										mensagem('insert_erro');
										break 2;
									}
									unset($queryR,$rsR);
								}
							}
						}
						unset($query,$rs,$ob);
						
						// Insere os registros de DESTAQUE
						if(isset($_REQUEST['in_destaque']) && !$_SESSION['ib_pvtuser']) {
							foreach($_REQUEST['in_destaque'] AS $tb_destaque_bn_id) {
								$queryD	= "	INSERT INTO
												tb_destaque_conteudo
												(
												tb_destaque_bn_id,
												tb_secao_bn_id,
												bn_conteudo
												)
											VALUES
												(
												'".$tb_destaque_bn_id."',
												'".SECAO."',
												'".$ultimoId."'
												);";
								$rsD	= $conexao->query($queryD);
							}
						}
						
						fnEstatico(SECAO,$obI->bn_id,$ultimoId,1);
						
						fnRSS(SECAO,$obI->bn_id);
						fnXML(SECAO,$obI->bn_id);
						
						if($obI->bn_id == 1) {
							// Envia usuario e senha por email para o novo registro
							if(SECAO == 1) {
								// BuscaPAIS
								$strPais 		= '';
								if(isset($_POST['bn_pais']) 	&& is_numeric($_POST['bn_pais'])) {
									$strPais	= $conexao->getOne('SELECT bt_nome FROM tb_conteudo_pais WHERE bn_id = '.$_POST['bn_pais'].';');
									if(DB::isError($strPais))	$strPais = '';
								}
								
								// Busca o nome da CIDADE
								$strCidade 		= '';
								if(isset($_POST['bn_municipio']) 	&& is_numeric($_POST['bn_municipio'])) {
									$strCidade	= $conexao->getOne('SELECT bt_nome FROM tb_conteudo_municipio WHERE bn_id = '.$_POST['bn_municipio'].';');
									if(DB::isError($strCidade))	$strCidade = '';
								}
								
								// Busca o nome do ESTADO
								$strEstado 		= '';
								if(isset($_POST['PUBLICA_rel_estado_municipio_bn_municipio']) 	&& $_POST['PUBLICA_rel_estado_municipio_bn_municipio'] > 0) {
									$strEstado	= $conexao->getOne('SELECT bt_nome FROM tb_conteudo_estado WHERE bn_id = '.$_POST['PUBLICA_rel_estado_municipio_bn_municipio'].';');
									if(DB::isError($strEstado))	$strEstado = '';
								}
								
								$to				= $_POST['bt_nome']  . ' <'.$_POST['bt_mail'].'>';
								$from			= html_entity_decode(TITULO,ENT_QUOTES,'UTF-8') . ' <'.EMAIL_PUBLICO.'>';
								$strSubject		= html_entity_decode('Novo cadastro de usu&aacute;rio no portal '.TITULO,ENT_QUOTES);
								$strHTML		= file_get_contents(ROOT_TEMPLATE.'sys/sendMailRegisterUser.html');
								
								$strHTML		= str_replace('#TITULO#'			,TITULO			,$strHTML);
								$strHTML		= str_replace('#DOMINIO#'			,DOMINIO		,$strHTML);
								$strHTML		= str_replace('#tb_pais_bn_id#'		,$strPais		,$strHTML);
								$strHTML		= str_replace('#tb_estado_bn_id#'	,$strEstado		,$strHTML);
								$strHTML		= str_replace('#tb_municipio_bn_id#',$strCidade		,$strHTML);
								$strHTML		= str_replace('#strSubject#'		,'Novo cadastro de usu&aacute;rio no portal',$strHTML);
								foreach($_POST AS $strKey => $strValue) {
									$strHTML = str_replace('#'.$strKey.'#',nl2br($strValue),$strHTML);
								}
								
								if(!EMAIL_AUTH) {
									$strHeaders  = "MIME-Version: 1.0\n";
									$strHeaders .= "Content-type: text/html; charset=utf-8\n";
									$strHeaders .= "From: ".$from."\n";
									$strHeaders .= "Return-Path: ".$from."\n";
									$strHeaders .= "Reply-To: ".$from."\n";
								
									$boolSend	= mail($to,$strSubject,$strHTML,$strHeaders);
								} else {
									include_once 'Mail.php';
									$arrHeaders = array(
											'From' 			=> $from,
											'To'			=> $to,
											'Subject'		=> $strSubject,
											'Return-Path'	=> $from,
											'Reply-To' 		=> $from,
											'MIME-Version' 	=> '1.0',
											'Content-type' 	=> 'text/html; charset=utf-8'
									);
									$arrParams	= array(
											'host' 		=> EMAIL_AUTH_HOST,
											'auth' 		=> true,
											'username' 	=> EMAIL_AUTH_USER,
											'password' 	=> EMAIL_AUTH_PWD
									);
									$objSMTP =& Mail::factory('smtp',$arrParams);
									$objMail = $objSMTP->send($to, $arrHeaders, $strHTML);
								
									if (PEAR::isError($objMail)) {
										$boolSend = false;
									} else {
										$boolSend = true;
									}
								}
								
							}
							
							if(!ADM_USER) {
								$to				= html_entity_decode(TITULO,ENT_QUOTES) .' <'.EMAIL_PUBLICO.'>';
								$from			= html_entity_decode(TITULO,ENT_QUOTES) .' <'.EMAIL_PUBLICO.'>';
								$strSubject		= html_entity_decode('Novo conte&uacute;do cadastrado no sistema administrativo do portal '.TITULO,ENT_QUOTES);
								$strHTML		= file_get_contents(ROOT_TEMPLATE.'sys/sendMailNewContent.html');
								
								
								$strHTML		= str_replace('#TITULO#'			,TITULO						,$strHTML);
								$strHTML		= str_replace('#DOMINIO#'			,DOMINIO					,$strHTML);
								$strHTML		= str_replace('#USUARIO#'			,$_SESSION['it_usuario']	,$strHTML);
								$strHTML		= str_replace('#SECAO#'				,$obSecaoConfig->bt_nome	,$strHTML);
								$strHTML		= str_replace('#CONTEUDO#'			,$_REQUEST['bt_nome']		,$strHTML);
								$strHTML		= str_replace('#LINK#'				,HTTP_DINAMICO.'index.php?'.IN_SECAO.'='.SECAO.'&'.IN_IDIOMA.'='.IDIOMA.'&'.IN_CONTEUDO.'='.$ultimoId	,$strHTML);
								$strHTML		= str_replace('#strSubject#'		,'Novo conte&uacute;do cadastrado no sistema administrativo do portal '.TITULO							,$strHTML);
								foreach($_POST AS $strKey => $strValue) {
									$strHTML = str_replace('#'.$strKey.'#',nl2br($strValue),$strHTML);
								}
								
								if(!EMAIL_AUTH) {
									$strHeaders  = "MIME-Version: 1.0\n";
									$strHeaders .= "Content-type: text/html; charset=utf-8\n";
									$strHeaders .= "From: ".$from."\n";
									$strHeaders .= "Return-Path: ".$from."\n";
									$strHeaders .= "Reply-To: ".$from."\n";
								
									$boolSend	= mail($to,$strSubject,$strHTML,$strHeaders);
								} else {
									include_once 'Mail.php';
									$arrHeaders = array(
											'From' 			=> $from,
											'To'			=> $to,
											'Subject'		=> $strSubject,
											'Return-Path'	=> $from,
											'Reply-To' 		=> $from,
											'MIME-Version' 	=> '1.0',
											'Content-type' 	=> 'text/html; charset=utf-8'
									);
									$arrParams	= array(
											'host' 		=> EMAIL_AUTH_HOST,
											'auth' 		=> true,
											'username' 	=> EMAIL_AUTH_USER,
											'password' 	=> EMAIL_AUTH_PWD
									);
									$objSMTP =& Mail::factory('smtp',$arrParams);
									$objMail = $objSMTP->send($to, $arrHeaders, $strHTML);
								
									if (PEAR::isError($objMail)) {
										$boolSend = false;
									} else {
										$boolSend = true;
									}
								}
															
							}
						}
						unset($query,$rs);
					} else {
						mensagem('insert_erro');
					}
				}
				
				if(!isset($erro)) {
					$str = "formulario.php?it_acao=alterar&in_secao=". SECAO ."&in_idioma=". IDIOMA ."&in_controle=".$ultimoId."&it_mensagem=insert_ok&ib_preview=".$_REQUEST['ib_preview'];
					echo '<script language="javascript" type="text/javascript">window.location.href = "'.$str.'";</script>';
					exit();
				}
			} else {
				mensagem('insert_erro');
			}
		break;
		
		case 'confirmar_alterar':
			// Seta as variveis para data de publicaao e expiraao da matria
			$arrUpdate['bd_publicacao'] = 'bd_publicacao = '.$_REQUEST['bd_publicacao_ano'].$_REQUEST['bd_publicacao_mes'].$_REQUEST['bd_publicacao_dia'].$_REQUEST['bd_publicacao_hora'].$_REQUEST['bd_publicacao_minuto'].'00';
			
			if(isset($_REQUEST['ib_expiracao']) && $_REQUEST['ib_expiracao'] == 1) {
				$arrUpdate['bd_expiracao'] = 'bd_expiracao = 00000000000000';
			} else {
				$arrUpdate['bd_expiracao'] = 'bd_expiracao = '.$_REQUEST['bd_expiracao_ano'].$_REQUEST['bd_expiracao_mes'].$_REQUEST['bd_expiracao_dia'].$_REQUEST['bd_expiracao_hora'].$_REQUEST['bd_expiracao_minuto'].'59';
			}
			
			// Percorre os campos do formulrio dinmico em busca de campos para UPLOAD de arquivos
			if(isset($_FILES) && count($_FILES) > 0) {
				$arrKeys  = array_keys($_FILES);
				foreach($arrKeys AS $keys) {
					if(strpos($keys,'_upload') !== false) {
						if($_FILES[$keys]['name'] != '') {
							/*
							OS ARQUIVOS UPLOADADOS DINAMICAMENTE NAO SERAO DELETADOS POIS A APLICAAO NO WEBSITE PODE CONTER REFERENCIAS A ELES.
							APENAS O REGISTRO NO BANCO DE DADOS, RELACIONANDO O ARQUIVO FSICO AO REGISTRO EM QUESTAO SER ATUALIZADO.
							
							// Deleta o arquivo anterior
							$str = $keys.'_old';
							if(isset($_REQUEST[$str]) && $_REQUEST[$str] != '') {
								unlink($_REQUEST[$str]);
							}
							*/
							
							// Faz o upload do arquivo setado
							$arrUpdate[$keys] = fnUploadSecao($keys,$strDiretorio,10000000,'','',1,$obSecaoConfig->bb_publico);
							if($arrUpdate[$keys] === false) {
								mensagem('update_erro');
								break 2;
							} else {
							//	fnRenameArquivo($arrUpdate[$keys]);
								$arrUpdate[$keys] = $keys .' = "'. $arrUpdate[$keys] .'"';
							}
						} else {
							unset($arrUpdate[$keys]);
						}
					}
				}
			}
			
			// Caso secao OPINIAO DO USUARIO, envia o email ao usuario informando a liberacao do arquivo no website
			if(SECAO == 19) {
				// Busca o status de liberacao do conteudo antes da edicao do registro
				$strQueryLS	= 'SELECT tb_conteudo_usuario_opiniao.bb_publicar, tb_conteudo_usuario_opiniao.bb_aprovacao_simnao, CONCAT(tb_conteudo_usuario.bt_nome," ",tb_conteudo_usuario.bt_sobrenome) AS tb_usuario_bt_nome, tb_conteudo_usuario.bt_mail AS tb_usuario_bt_mail, tb_conteudo_usuario_opiniao.bn_secao, tb_conteudo_usuario_opiniao.tb_idioma_bn_id, tb_conteudo_usuario_opiniao.bn_conteudo FROM tb_conteudo_usuario_opiniao JOIN tb_rel_tb_usuario_opiniao_tb_usuario JOIN tb_conteudo_usuario WHERE tb_rel_tb_usuario_opiniao_tb_usuario.tb_usuario_bn_id = tb_conteudo_usuario.bn_id AND tb_rel_tb_usuario_opiniao_tb_usuario.tb_usuario_opiniao_bn_id = tb_conteudo_usuario_opiniao.bn_id AND tb_conteudo_usuario_opiniao.bn_id = "'.$_REQUEST['in_controle'].'";';
				$objQueryLS	= $conexao->query($strQueryLS);
				
				if(!DB::isError($objQueryLS)) {
					$objRS	= $objQueryLS->fetchRow();
					
					// Caso o status de aprovacao anterior seja diferente do novo, envia o e-mail
					if($objRS->bb_aprovacao_simnao != $arrInsert['bb_aprovacao_simnao'] && $_POST['bb_ativo'] == '1') {
						// Cria as variveis de TERMOS
						$arrSmartyTemp  = array();
						fnTermos($arrSmartyTemp);
						
						// Define o template do email
						if($_POST['bb_aprovacao_simnao'] == 1) {
							$strHTML		= file_get_contents(ROOT_TEMPLATE.'sys/sendMailUserOpinionAproved.html');
							$strSubject		= $arrSmartyTemp['T_TITLE_sendMailUserOpinionAproved'];
						} else {
							$strHTML		= file_get_contents(ROOT_TEMPLATE.'sys/sendMailUserOpinionReproved.html');
							$strSubject		= $arrSmartyTemp['T_TITLE_sendMailUserOpinionReproved'];
						}
						
						// Cria o link para o conteudo indicado
						$strLink	= HTTP_DINAMICO.'/index.php?'.IN_SECAO.'='.$objRS->bn_secao.'&'.IN_IDIOMA.'='.$objRS->tb_idioma_bn_id.'&'.IN_CONTEUDO.'='.$objRS->bn_conteudo;
						
						// Substitui as variaveis constantes no template do email
						$strHTML		= str_replace('#TITULO#'				,TITULO						,$strHTML);
						$strHTML		= str_replace('#tb_usuario_bt_nome#'	,$objRS->tb_usuario_bt_nome	,$strHTML);
						$strHTML		= str_replace('#LINK#'					,$strLink					,$strHTML);
						
						// Substitiu as variaveis de cadastro no template do email
						unsafeCode();
						foreach($_POST AS $strKey => $strValue) {
							$strHTML = str_replace('#'.$strKey.'#',$strValue,$strHTML);
						}
						
						// Substitiu as variaveis de internacionalizacao no template do email
						foreach($arrSmartyTemp AS $strKey => $strValue) {
							$strHTML = str_replace('{$'.$strKey.'}',$strValue,$strHTML);
						}
						
						// Seta as configuracoes para envio do email
						$mail			= $objRS->tb_usuario_bt_mail;
						$to				= html_entity_decode($objRS->tb_usuario_bt_nome,ENT_QUOTES,'UTF-8') .' <'.$mail.'>';
						$from			= html_entity_decode(TITULO,ENT_QUOTES,'UTF-8') .' <'.reset(explode(',',EMAIL_PUBLICO)).'>';
						
						if(!EMAIL_AUTH) {
							$strHeaders  = "MIME-Version: 1.0\n";
							$strHeaders .= "Content-type: text/html; charset=utf-8\n";
							$strHeaders .= "From: ".$from."\n";
							$strHeaders .= "Return-Path: ".$from."\n";
							$strHeaders .= "Reply-To: ".$from."\n";
						
							$boolSend	= mail($to,$strSubject,$strHTML,$strHeaders);
						} else {
							include_once 'Mail.php';
							$arrHeaders = array(
									'From' 			=> $from,
									'To'			=> $to,
									'Subject'		=> $strSubject,
									'Return-Path'	=> $from,
									'Reply-To' 		=> $from,
									'MIME-Version' 	=> '1.0',
									'Content-type' 	=> 'text/html; charset=utf-8'
							);
							$arrParams	= array(
									'host' 		=> EMAIL_AUTH_HOST,
									'auth' 		=> true,
									'username' 	=> EMAIL_AUTH_USER,
									'password' 	=> EMAIL_AUTH_PWD
							);
							$objSMTP =& Mail::factory('smtp',$arrParams);
							$objMail = $objSMTP->send($to, $arrHeaders, $strHTML);
						
							if (PEAR::isError($objMail)) {
								$boolSend = false;
							} else {
								$boolSend = true;
							}
						}
						
					}
				}
			}
			
			// Atualiza os dados
			$query = 'UPDATE
						'.$arrTabelas[0].'
					  SET
						'.implode(', ',$arrUpdate)    .'
						'.(count($arrWhere) > 0 ? 'WHERE '.implode(' AND ',$arrWhere) : '').';'; 
			$rs    = $conexao->query($query);
			if(!DB::isError($rs)) {
				unset($query,$rs);
						
				// Busca o ID do ultimo registro
				$query 	  = "SELECT bn_id FROM ".$arrTabelas[0]." WHERE " . implode(' AND ',$arrWhere) . ";";
				$_REQUEST['in_controle'] = $conexao->getOne($query);
				
				// Busca o nome da seao
				$query = "SELECT bt_nome FROM tb_secao_idioma WHERE tb_secao_bn_id = '".SECAO."' AND tb_idioma_bn_id = '".IDIOMA."';";
				$strSecao = $conexao->getOne($query);
				unset($query);
				
				// Grava o log do registro
				fnLog(2,1,$_SESSION['in_usuario'],$arrInsert['bt_nome'],$strSecao);
				
				// Busca os campos das tabelas de relacionamento
				$query = "SELECT
							bt_campo,
							bt_tabela
						  FROM
						  	tb_secao_relacionamento
						  WHERE
						  	tb_secao_bn_id = '".SECAO."'
			  				".(ADM_USER == 0 ? ' AND tb_secao_relacionamento.bb_administrativo = 0 ' : '').";";
				$rs	   = $conexao->query($query);
				
				$strTableTemp = '';
				while($ob = $rs->fetchRow()) {
					// Deleta os relacionamentos anteriores
					if($strTableTemp != $ob->bt_tabela) {
						$strCampo 		= str_replace('tb_rel_tb_','',$ob->bt_tabela);
						$arrTabelaRel	= explode('_tb_',$strCampo);
						$strCampo 		= 'tb_'.$arrTabelaRel[0].'_bn_id';
						unset($arrTabelaRel);
						
						$queryD = "DELETE FROM ".$ob->bt_tabela." WHERE ".$strCampo." = '".$_REQUEST['in_controle']."';"; 
						$rsD    = $conexao->query($queryD);
						
						if(DB::isError($rsD)) {
							mensagem('insert_erro');
							break;
						}
						
						$strTableTemp = $ob->bt_tabela;
					}
					
					// Define a varivel temporria para os contedos de tabelas relacionadas
					$temp = (!isset($_REQUEST[$ob->bt_campo]) ? array() : (!is_array($_REQUEST[$ob->bt_campo]) ? array($_REQUEST[$ob->bt_campo]) : $_REQUEST[$ob->bt_campo]));
					
					// Insere os contedos relacionados
					foreach($temp AS $valor) {
						if($valor != '') {
							$queryR = "INSERT
										".$ob->bt_tabela."
									  VALUES
										(
										'".$_REQUEST['in_controle']."',
										'".$valor."'
										);"; 
							$rsR    = $conexao->query($queryR);
							if(DB::isError($rsR)) { 
								mensagem('insert_erro');
								break 2;
							}
							unset($queryR,$rsR);
						}
					}
				}
				unset($query,$rs,$ob);
				
				// Insere os registros de DESTAQUE
				if(!$_SESSION['ib_pvtuser']) {
					$queryD	= "DELETE FROM tb_destaque_conteudo WHERE tb_secao_bn_id = '".SECAO."' AND bn_conteudo = '".$_REQUEST['in_controle']."';";
					$rsD = $conexao->query($queryD);
					if(isset($_REQUEST['in_destaque'])) {
						foreach($_REQUEST['in_destaque'] AS $tb_destaque_bn_id) {
							$queryD	= "	INSERT INTO
											tb_destaque_conteudo
											(
											tb_destaque_bn_id,
											tb_secao_bn_id,
											bn_conteudo
											)
										VALUES
											(
											'".$tb_destaque_bn_id."',
											'".SECAO."',
											'".$_REQUEST['in_controle']."'
											);";
							$rsD	= $conexao->query($queryD);
							if(DB::isError($rsD)) { mensagem('update_erro'); break 2; }
						}
					}
				}
				
				fnEstatico(SECAO,IDIOMA,$_REQUEST['in_controle'],1);
				
				fnRSS(SECAO,IDIOMA);
				fnXML(SECAO,IDIOMA);
				
				if($_REQUEST['ib_multitext'] == 0) {
					$strAnchor = '&it_mensagem=update_ok';
				} else {
					$strAnchor = '&'.$_REQUEST['it_multitext'].'='.$_REQUEST[$_REQUEST['it_multitext']].'#anchor_'.str_replace('_opt','',$_REQUEST['it_multitext']);
				}
				
				if(!ADM_USER) {
					$to				= html_entity_decode(TITULO,ENT_QUOTES) . ' <'.EMAIL_PUBLICO.'>';
					$from			= html_entity_decode(TITULO,ENT_QUOTES) . ' <'.EMAIL_PUBLICO.'>';
					$strSubject		= html_entity_decode('Conte&uacute;do atualizado no sistema administrativo do portal '.TITULO,ENT_QUOTES);
					$strHTML		= file_get_contents(ROOT_TEMPLATE.'sys/sendMailUpdateContent.html');
					
					
					$strHTML		= str_replace('#TITULO#'			,TITULO						,$strHTML);
					$strHTML		= str_replace('#DOMINIO#'			,DOMINIO					,$strHTML);
					$strHTML		= str_replace('#USUARIO#'			,$_SESSION['it_usuario']	,$strHTML);
					$strHTML		= str_replace('#SECAO#'				,$obSecaoConfig->bt_nome	,$strHTML);
					$strHTML		= str_replace('#CONTEUDO#'			,$_REQUEST['bt_nome']		,$strHTML);
					$strHTML		= str_replace('#LINK#'				,HTTP_DINAMICO.'index.php?'.IN_SECAO.'='.SECAO.'&'.IN_IDIOMA.'='.IDIOMA.'&'.IN_CONTEUDO.'='.$_REQUEST['in_controle']	,$strHTML);
					$strHTML		= str_replace('#strSubject#'		,'Conte&uacute;do atualizado no sistema administrativo do portal '.TITULO							,$strHTML);
					foreach($_POST AS $strKey => $strValue) {
						$strHTML = str_replace('#'.$strKey.'#',nl2br($strValue),$strHTML);
					}
					
					if(!EMAIL_AUTH) {
						$strHeaders  = "MIME-Version: 1.0\n";
						$strHeaders .= "Content-type: text/html; charset=utf-8\n";
						$strHeaders .= "From: ".$from."\n";
						$strHeaders .= "Return-Path: ".$from."\n";
						$strHeaders .= "Reply-To: ".$from."\n";
					
						$boolSend	= mail($to,$strSubject,$strHTML,$strHeaders);
					} else {
						include_once 'Mail.php';
						$arrHeaders = array(
								'From' 			=> $from,
								'To'			=> $to,
								'Subject'		=> $strSubject,
								'Return-Path'	=> $from,
								'Reply-To' 		=> $from,
								'MIME-Version' 	=> '1.0',
								'Content-type' 	=> 'text/html; charset=utf-8'
						);
						$arrParams	= array(
								'host' 		=> EMAIL_AUTH_HOST,
								'auth' 		=> true,
								'username' 	=> EMAIL_AUTH_USER,
								'password' 	=> EMAIL_AUTH_PWD
						);
						$objSMTP =& Mail::factory('smtp',$arrParams);
						$objMail = $objSMTP->send($to, $arrHeaders, $strHTML);
					
						if (PEAR::isError($objMail)) {
							$boolSend = false;
						} else {
							$boolSend = true;
						}
					}
					
				}
				
				$str = "formulario.php?it_acao=alterar&in_secao=". SECAO ."&in_idioma=". IDIOMA ."&in_controle=".$_REQUEST['in_controle']."&ib_preview=".$_REQUEST['ib_preview'].$strAnchor;
				echo '<script language="javascript" type="text/javascript">window.location.href = "'.$str.'";</script>';
				exit();
				
			} else {
				mensagem('update_erro');
			}
		break;
		
		case '':
		case 'alterar':	
		break;
		
		default:
			header("Location: lista.php");
			exit();
		break;
	}
	
	// Caso nao seja a primeira inclusao do registro, busca o contedo
	if(ACAO == 'alterar' || (isset($_REQUEST['in_materia']) && $_REQUEST['in_materia'] > 0)) {
		$query = 'SELECT
					bn_id,
					'.implode(', ',$arrCampos)    .'
				  FROM
					'.$arrTabelas[0].'
					'.(count($arrWhere) > 0 ? 'WHERE '.implode(' AND ',$arrWhere) : '').';'; 
		$rs    = $conexao->query($query);
		if(!DB::isError($rs)) {
			$ob = $rs->fetchRow();
			$_REQUEST['in_controle'] = $ob->bn_id;
		} else {
			mensagem('select_erro');
		}
		
		if(ACAO == 'alterar' && isset($_REQUEST['it_mensagem'])) {
			$_REQUEST['bt_nome'] = $ob->bt_nome;
			mensagem($_REQUEST['it_mensagem']);
		}
	}
} else {
	mensagem('formato_dados');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php include_once ROOT.'cms/include-sistema/titulo.php'; ?>
	<script language="javascript" type="text/javascript">
		secaoID   = '<?php echo SECAO; ?>';
		idiomaID  = '<?php echo IDIOMA; ?>';
		
		function verifica() {
			erro = '';
			foco = '';
			controle = true;
			
			if(document.form.<?php echo IN_IDIOMA; ?>.value == 0) {
				controle = false;
				erro 	 = erro + "Indique o idioma em que deseja inserir este contedo!\n";
				if(foco == '') { foco = '<?php echo IN_IDIOMA ?>'; }
			}
			
			<?php echo $strJavascript; ?>
			
			if(erro == '') {
				if(document.form.it_acao.value == '') {
					document.form.it_acao.value = 'incluir';
				}
				if(document.form.it_acao.value == 'alterar') {
					document.form.it_acao.value = '<?php echo (isset($ob) ? 'confirmar_alterar' : 'incluir'); ?>';
				}
				return true;
			} else {
				alert(erro);
				if(controle == true && foco != '') {
					eval('document.form.'+ foco +'.focus();');
				}
				return false;
			}
		}
		
		function setaMultitext(valor) {
			document.form.ib_multitext.value	= 1;
			document.form.it_multitext.value	= valor;
			document.form.it_acao.value			= '<?php echo (isset($ob) ? 'confirmar_alterar' : 'alterar'); ?>';
			document.form.submit();
		}
		
		function fnDisplayBusca() {
			if(document.getElementById('tr_busca').style.display == 'none') {
				document.getElementById('tr_busca').style.display = 'inline';
			} else {
				document.getElementById('tr_busca').style.display = 'none';
			}
		}
		
		cityFilter = function(intStateID,strFieldCity,strCombo,intType,intFilter,intWidth) {
			if(isNaN(intStateID)) {
				intFormLength	= $('form').elements.length;
				arrIDField		= new Array();
				
				for(i = 0; i < intFormLength; i++) {
					if($('form').elements[i].id.indexOf(intStateID) === 0 && $('form').elements[i].checked == true) arrIDField.push($('form').elements[i].value);
				}
			} else {
				arrIDField		= new Array(intStateID);
			}
			
			return new Ajax.Updater(strCombo,'../include-sistema/cityFilter.php',{ method: 'post', parameters: 'name='+strFieldCity+'&type='+intType+'&userFilter='+intFilter+'&state='+arrIDField.toString()+'&width='+intWidth, onLoading: loadingIcon, onError: loadingIconHide });
		}
		
		loadingIcon = function(originalRequest) {
			$(originalRequest.request.container.success).innerHTML = 'carregando...';
		}
		
		loadingIconHide = function(originalRequest) {
			$(originalRequest.request.container.success).innerHTML = '';
		}
	</script>
	<script type="text/javascript" 	src="../../FCKeditor/fckeditor.js"></script>
	<script type="text/javascript" 	src="../include-js/prototype.js"></script>
	<script language="javascript" 	src="../include-js/funcoes.js"></script>
	<link href="../include-design/estilos.css" rel="stylesheet" />
</head>
<body topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0">
<form  name="form"   id="form" method="post"      action="" onsubmit="return verifica();" enctype="multipart/form-data">
<input type="hidden" name="<?php echo $in_secao; ?>" value ="<?php echo SECAO; ?>"   />
<input type="hidden" name="it_acao"     	value ="<?php echo fnRequest('it_acao'); ?>" />
<input type="hidden" name="in_materia"  	value ="<?php if(isset($_REQUEST['in_materia'])) { echo $_REQUEST['in_materia']; } elseif(isset($ob->bn_materia)) { echo $ob->bn_materia; } else { echo '0'; } ?>" />
<input type="hidden" name="ib_preview"  	value ="0" />
<input type="hidden" name="ib_publicar"  	value ="1" />
<input type="hidden" name="ib_home"  		value ="<?php echo fnRequest('ib_home'); ?>" />
<input type="hidden" name="ib_multitext"  	value ="0" />
<input type="hidden" name="it_multitext"  	value ="" />
<input type="hidden" name="it_ordem"   		value="<?php echo fnRequest('it_ordem'); ?>" />
<?php if(!isset($_REQUEST['ib_ordem'])) { $_REQUEST['ib_ordem'] = 1; } ?>
<input type="hidden" name="ib_ordem"   value="<?php echo fnRequest('ib_ordem'); ?>" />
<table align="center" cellpadding="5" cellpacing="1" class="tabela" border="0">
	<?php include_once ROOT.'cms/include-sistema/cabecalho_secoes.php'; ?>
	<?php include_once 'busca.php'; ?>
	<?php if(!$_SESSION['ib_pvtuser']) { ?>
	<tr>
		<td colspan="6" class="tb_idioma">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td width="90%">
						<strong>Selecione o idioma:</strong>
						<select name="<?php echo IN_IDIOMA; ?>" <?php if((isset($_REQUEST['in_materia']) && $_REQUEST['in_materia'] > 0) || (isset($ob->bn_materia) && $ob->bn_materia > 0)) { ?>onChange="fnBuscaConteudo(this.value);"<?php } ?>>
							<option value="0">SELECIONE
							<?php
							$rsI	= $conexao->query($queryI);
							if(!DB::isError($rs)) {
								while($obI = $rsI->fetchRow()) {
									echo '<option value="'.$obI->bn_id.'" '.fnCheckInput(IDIOMA,$obI->bn_id,'selected').'>'.$obI->bt_nome."\n";
								}
							}
							?>
						</select>
					</td>
					<td align="right">
						<?php if($permListar == 1) { ?>
						<a href="javascript:fnPreviewConteudo();"><img src="../imagens/bt_preview.png" alt="PREVIEW" title="PREVIEW" border="0"></a>
						<?php
						} else {
							echo '[ preview ]';
						}
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php } else { echo '<input type="hidden" name="'.IN_IDIOMA.'" id="'.IN_IDIOMA.'" value="'.IDIOMA.'">'; } ?>
	<?php if(isset($erro)) { ?>
	<tr>
		<td colspan="3" class="tb_erro_conteudo">
			<?php echo $erro; ?>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="3" class="tb_idioma">
			<em>Campos identificados com <img src="../imagens/ico_obrigatorio.png" alt="OBRIGAT&Oacute;RIO" title="OBRIGAT&Oacute;RIO" align="absmiddle"> s&atilde;o de preenchimento obrigat&oacute;rio!</em><br><br>
			<em>Campos identificados com <a href="javascript:;" onMouseOver="return escape('AQUI VOC&Ecirc; VISUALIZA AS DICAS DE PREENCHIMENTO!');"><img src="../imagens/ico_info.png" alt="INFORMA&Ccedil;&Otilde;ES" title="INFORMA&Ccedil;&Otilde;ES" align="absmiddle" border="0"></a> indicam dicas de preenchimento. Passe o mouse por cima da imagem para visualiz&aacute;-las!</em>
		</td>
	</tr>
	<?php
	$i = 0;
	$strJavascript = '';
	ksort($arrHtml);
	
	foreach($arrHtml AS $ordem => $array) {
		foreach($array AS $campo => $arr) {
			// Exibe os campos dinmicos
			if(!is_numeric($arr['html'])) {
				$strTipo = end(explode('_',$campo));
				
				$html = html_entity_decode($arr['html'],ENT_QUOTES,'UTF-8');
				$html = str_replace('name="','name="'.$campo,$html);
				$html = str_replace("name='","name='".$campo,$html);
				$html = str_replace('id="','id="'.$campo,$html);
				$html = str_replace("id='","id='".$campo,$html);
				
				$valorTemp 		 = true;
				if($strTipo == 'multitext') {
					$valorTemp   = false;
					
					// Cria o array de contedo
					$arrConteudo = array();
					if(ACAO  == 'alterar') {
						$arrConteudo = explode('#MULTITEXT#',$ob->$campo);
					}
					
					// Define o nmero de pginas
					$strTemp 	 = fnRequest($campo.'_opt');
					if(!$strTemp) {
						$strTemp = count($arrConteudo);
					}
					
					// Cria os campos <textarea>
					if($strTemp) {
						for($i 	= 0; $i < $strTemp; $i++) {
							$html .= '<br><br><a name="anchor_'.$campo.'"></a><strong>Pgina #'.($i + 1).'</strong><br><textarea name="'.$campo.'[]" id="'.$campo.'_'.$i.'" cols="70" rows="14">'.( isset($_REQUEST[$campo][$i]) ? $_REQUEST[$campo][$i] : ( isset($arrConteudo[$i]) ? $arrConteudo[$i] : '' ) ).'</textarea>';
							
							$strRichText .= "
							var OB".$campo."_".$i."		 = new FCKeditor('".$campo."_".$i."','',400,'Publica');
							OB".$campo."_".$i.".BasePath = sBasePath;
							OB".$campo."_".$i.".ReplaceTextarea();\n\n";
						}
					}
					$html .= '<script language="javascript" type="text/javascript">document.form.'.$campo.'_opt.selectedIndex = "'.$i.'";</script>';
					unset($strTemp);
				}
				
				// Preenche o valor dos campos em case ALTERAR
				if($valorTemp !== false) {
					if(isset($ob->$campo)) {
						$valorTemp = $ob->$campo;
					} elseif(fnRequest($campo)) {
						$valorTemp = $_REQUEST[$campo];
					} else {
						$valorTemp = false;
					}
					if(!strpos($html,'checkbox') && !strpos($html,'radio') && !strpos($html,'option') && !strpos($html,'textarea')) { // Campo diferente de RADIOBUTTON, CHECKBOX, COMBOBOX (SELECT) ou TEXTAREA
						// Preenche o campo com o valor do banco de dados
						if($strTipo != 'rel') {
							$strValor = $valorTemp;
						} else {
							$strValor = fnValorRelacionamento($valorTemp);
						}
						
						switch($strTipo) {
							case 'rel':
								$html = str_replace('value="','value="'.$strValor,$html);
								$html = str_replace("value='","value='".$strValor,$html);
								$html = str_replace('name="','readonly="true" onBlur="relacionamentoURL(\''.$campo.'\');" name="',$html);
								$html = str_replace("name='","readonly='true' onBlur='relacionamentoURL(\"".$campo."\");' name='",$html);
								
								$html .= '&nbsp; <a href="javascript:popupMateria('.SECAO.','.IDIOMA.',\''.$campo.'\');"><img src="../imagens/bt_procurar.png" alt="PROCURAR" title="PROCURAR" border="0" align="absmiddle" \></a>';
								$html .= '&nbsp; <input type="hidden" name="'.$campo.'_id" value="'.(isset($ob) ? $ob->$campo : '').'">';
							break;
							
							case 'upload':
								if($strValor != '') {
									// Exibe o link de visualizaao da imagem
									$arrArquivo = explode('/',$strValor);
									$n			= count($arrArquivo);
									
									$strFilePath= (is_dir(ROOT_UPLOAD.$strDiretorio) ? ROOT_UPLOAD.$strDiretorio : ROOT_IMAGEM.$strDiretorio).'/';
									
									$html .= '<br>'.$arrArquivo[$n-1].' - <a href="getFile.php?filepath=' . $strFilePath . '&filename=' . $strValor . '" target="_blank"><img src="../imagens/bt_imagem.png" alt="VISUALIZAR" title="VISUALIZAR" border="0" align="absmiddle" \></a>';
									unset($arrArquivo,$n);
									
									// Seta o valor do campo HIDDEN
									$strJavascript .= "document.form.".$campo."_old.value = '".$strValor."';\n";
								}
							break;
							
							case 'fone':
								$strDDD		= substr($strValor,0,2);
								$strFone	= substr($strValor,2,(strlen($strValor) - 2));
								
								$arrForm	= explode('<input',$html);
								$arrForm[1] = '<input'.str_replace('value="','value="'.$strDDD,str_replace("value='","value='".$strDDD,$arrForm[1]));
								$arrForm[2] = '<input'.str_replace('value="','value="'.$strFone,str_replace("value='","value='".$strFone,$arrForm[2]));
								$html		= implode('',$arrForm);
								unset($arrForm,$strDDD,$strFone);
							break;
							
							case 'img':
								$str   = $campo.'_thumbnail';
								$html = str_replace('value="','value="'.$strValor,$html);
								$html = str_replace("value='","value='".$strValor,$html);
								$html .= '&nbsp; <a href="javascript:popupGaleria('.SECAO.','.IDIOMA.',\''.$campo.'\');"><img src="../imagens/bt_procurar.png" alt="PROCURAR" title="PROCURAR" border="0" align="absmiddle" \></a> <a href="javascript:visualizaGaleria(\''.$campo.'\',\''.IMAGEM.'\');"><img src="../imagens/bt_imagem.png" alt="VISUALIZAR" title="VISUALIZAR" border="0" align="absmiddle" \></a><br \>';
								$html .= '<input type="hidden" name="'.$str.'" id="'.$str.'" value="'.(isset($ob) ? $ob->$str : '').'">';
								unset($str);
							break;
							
							case 'video':
								$queryR = "SELECT bn_id, bt_nome FROM tb_video ORDER BY bt_nome ASC;";
								$rsR	= $conexao->query($queryR);
								if(!DB::isError($rsR)) {
									$html  = '<select name="'.$campo.'" id="'.$campo.'"><option value="">SELECIONE</option>';
									while($obR = $rsR->fetchRow()) {
										$html .= '<option value="'.$obR->bn_id.'" '.(isset($ob->$campo) && $ob->$campo == $obR->bn_id ? 'selected' : '').'>'.$obR->bt_nome.'</option>';
									}
									$html .= '</select>';
								} else {
									$html = '<select name="'.$campo.'" id="'.$campo.'" disabled><option value="">SELECIONE</option></select>';
								}
								unset($queryR,$rsR);
							break;
							
							case 'poll':
								$queryR = "SELECT bn_id, bt_nome FROM tb_enquete WHERE ".REFINAMENTO." ORDER BY bt_nome ASC;";
								$rsR	= $conexao->query($queryR);
								if(!DB::isError($rsR)) {
									$html  = '<select name="'.$campo.'" id="'.$campo.'"><option value="">SELECIONE</option>';
									while($obR = $rsR->fetchRow()) {
										$html .= '<option value="'.$obR->bn_id.'" '.(isset($ob->$campo) && $ob->$campo == $obR->bn_id ? 'selected' : '').'>'.$obR->bt_nome.'</option>';
									}
									$html .= '</select>';
								} else {
									$html = '<select name="'.$campo.'" id="'.$campo.'" disabled><option value="">SELECIONE</option></select>';
								}
								unset($queryR,$rsR);
							break;
							
							case 'pais':
								$queryR = "SELECT bn_id, bt_nome FROM tb_conteudo_pais ORDER BY bt_nome ASC;";
								$rsR	= $conexao->query($queryR);
								if(!DB::isError($rsR)) {
									$html  = '<select name="'.$campo.'" id="'.$campo.'"><option value="">SELECIONE</option>';
									while($obR = $rsR->fetchRow()) {
										$html .= '<option value="'.$obR->bn_id.'" '.(isset($ob->$campo) && $ob->$campo == $obR->bn_id ? 'selected' : '').'>'.$obR->bt_nome.'</option>';
									}
									$html .= '</select>';
								} else {
									$html = '<select name="'.$campo.'" id="'.$campo.'" disabled><option value="">SELECIONE</option></select>';
								}
								unset($queryR,$rsR);
							break;
							
							case 'regiao':
								$queryR = "SELECT bn_id, bt_nome FROM tb_conteudo_regiao ORDER BY bt_nome ASC;";
								$rsR	= $conexao->query($queryR);
								if(!DB::isError($rsR)) {
									$html  = '<select name="'.$campo.'" id="'.$campo.'"><option value="">SELECIONE</option>';
									while($obR = $rsR->fetchRow()) {
										$html .= '<option value="'.$obR->bn_id.'" '.(isset($ob->$campo) && $ob->$campo == $obR->bn_id ? 'selected' : '').'>'.$obR->bt_nome.'</option>';
									}
									$html .= '</select>';
								} else {
									$html = '<select name="'.$campo.'" id="'.$campo.'" disabled><option value="">SELECIONE</option></select>';
								}
								unset($queryR,$rsR);
							break;
							
							case 'estado':
								$queryR = "SELECT bn_id, bt_nome FROM tb_conteudo_estado ORDER BY bt_nome ASC;";
								$rsR	= $conexao->query($queryR);
								if(!DB::isError($rsR)) {
									$html  = '<select name="'.$campo.'" id="'.$campo.'"><option value="">SELECIONE</option>';
									while($obR = $rsR->fetchRow()) {
										$html .= '<option value="'.$obR->bn_id.'" '.(isset($ob->$campo) && $ob->$campo == $obR->bn_id ? 'selected' : '').'>'.$obR->bt_nome.'</option>';
									}
									$html .= '</select>';
								} else {
									$html = '<select name="'.$campo.'" id="'.$campo.'" disabled><option value="">SELECIONE</option></select>';
								}
								unset($queryR,$rsR);
							break;
							
							case 'cidade':
								$queryR = "SELECT bn_id, bt_nome FROM tb_conteudo_cidade ORDER BY bt_nome ASC;";
								$rsR	= $conexao->query($queryR);
								if(!DB::isError($rsR)) {
									$html  = '<select name="'.$campo.'" id="'.$campo.'"><option value="">SELECIONE</option>';
									while($obR = $rsR->fetchRow()) {
										$html .= '<option value="'.$obR->bn_id.'" '.(isset($ob->$campo) && $ob->$campo == $obR->bn_id ? 'selected' : '').'>'.$obR->bt_nome.'</option>';
									}
									$html .= '</select>';
								} else {
									$html = '<select name="'.$campo.'" id="'.$campo.'" disabled><option value="">SELECIONE</option></select>';
								}
								unset($queryR,$rsR);
							break;
							
							case 'aeroporto':
								$queryR = "SELECT bn_id, bt_nome FROM tb_conteudo_aeroporto ORDER BY bt_nome ASC;";
								$rsR	= $conexao->query($queryR);
								if(!DB::isError($rsR)) {
									$html  = '<select name="'.$campo.'" id="'.$campo.'"><option value="">SELECIONE</option>';
									while($obR = $rsR->fetchRow()) {
										$html .= '<option value="'.$obR->bn_id.'" '.(isset($ob->$campo) && $ob->$campo == $obR->bn_id ? 'selected' : '').'>'.$obR->bt_nome.'</option>';
									}
									$html .= '</select>';
								} else {
									$html = '<select name="'.$campo.'" id="'.$campo.'" disabled><option value="">SELECIONE</option></select>';
								}
								unset($queryR,$rsR);
							break;
							
							case 'data':
								$html = comboData($campo,$ob,1);
							break;
							
							case 'hora':
								$html = comboHora($campo,$ob,1);
							break;
							
							case 'galeriavideo':
								$queryR = "SELECT bn_id, bt_nome FROM tb_video_categoria WHERE tb_secao_bn_id = '".SECAO."' ORDER BY bt_nome ASC;";
								$rsR	= $conexao->query($queryR);
								if(!DB::isError($rsR)) {
									$html  = '<select name="'.$campo.'" id="'.$campo.'"><option value="">SELECIONE</option>';
									while($obR = $rsR->fetchRow()) {
										$html .= '<option value="'.$obR->bn_id.'" '.(isset($ob->$campo) && $ob->$campo == $obR->bn_id ? 'selected' : '').'>'.$obR->bt_nome.'</option>';
									}
									$html .= '</select>';
								} else {
									$html = '<select name="'.$campo.'" id="'.$campo.'" disabled><option value="">SELECIONE</option></select>';
								}
								unset($queryR,$rsR);
							break;
							
							case 'galeriaimg':
								$queryR = "SELECT bn_id, bt_nome FROM tb_imagem_categoria WHERE tb_secao_bn_id = '".SECAO."' ORDER BY bt_nome DESC;";
								$rsR	= $conexao->query($queryR);
								if(!DB::isError($rsR)) {
									$html  = '<select name="'.$campo.'" id="'.$campo.'"><option value="">SELECIONE</option>';
									while($obR = $rsR->fetchRow()) {
										$html .= '<option value="'.$obR->bn_id.'" '.(isset($ob->$campo) && $ob->$campo == $obR->bn_id ? 'selected' : '').'>'.$obR->bt_nome.'</option>';
									}
									$html .= '</select>';
								} else {
									$html = '<select name="'.$campo.'" id="'.$campo.'" disabled><option value="">SELECIONE</option></select>';
								}
								unset($queryR,$rsR);
							break;
							
							case 'postal':
								// Monta a string com a URL do cartao postal
								$strLink	= 'postal/postal_'.$strValor.'_'.$_REQUEST['in_controle'].'.htm';
								
								// Substitui os valores e variaveis do campo
								$html = str_replace('value="','value="'.$strValor	,$html);
								$html = str_replace("value='","value='".$strValor	,$html);
								
								if(file_exists(ROOT_ESTATICO.$strLink)) {
									$html = str_replace('href=""'	,'href="'.HTTP_ESTATICO.$strLink.'"',$html);
									$html = str_replace('</a>'		,HTTP_ESTATICO.$strLink.'</a>'		,$html);
									$html.= '&nbsp;&nbsp;<a href="'.HTTP_ESTATICO.$strLink.'" target="_blank"><img src="../imagens/bt_imagem.png" alt="VISUALIZAR" title="VISUALIZAR" border="0" align="absmiddle" \></a>';
								} else {
									mensagem('arquivo_removido');
									$html.= $erro;
									unset($erro);
								}
							break;
							
							default:
								$html = str_replace('value="','value="'.$strValor,$html);
								$html = str_replace("value='","value='".$strValor,$html);
							break;
						}
					} elseif(strpos($html,'textarea')) {												// TEXTAREA
						$html = str_replace('</textarea>',$valorTemp.'</textarea>',$html);
					} else {
						if(strpos($html,'checkbox') || strpos($html,'radio')) { 						// Campos CHECKBOX ou RADIOBUTTON
							// Preenche o campo com o valor do banco de dados
							if(!empty($valorTemp) && !is_null($valorTemp) && $valorTemp != 0) {
								$html = str_replace('value="'.$valorTemp.'"','value="'.$valorTemp.'" checked',$html);
								$html = str_replace("value='".$valorTemp."'","value='".$valorTemp."' checked",$html);
							} else {
								switch($valorTemp) {
									case 0:
										$html = str_replace('value="0"','value="0" checked',$html);
										$html = str_replace("value='0'","value='0' checked",$html);
									break;
									
									default:
										$html = str_replace('value=""','value="" checked',$html);
										$html = str_replace("value=''","value='' checked",$html);
									break;
								}
							}
						} elseif(strpos($html,'option')) { 												// Campos COMBOBOX (SELECT)
							// Preenche o campo com o valor do banco de dados
							$html = str_replace('value="'.$valorTemp.'"','value="'.$valorTemp.'" selected',$html);
							$html = str_replace("value='".$valorTemp."'","value='".$valorTemp."' selected",$html);
						}
					}
				}
	?>
	<tr>
		<td valign="top" class="tb_nome_conteudo" colspan="2">
			<div style="float:left;"><?php echo $arr['nome']; ?></div>
			<div style="float:right;">
			<?php
				echo ($arr['tooltip'] 		!= '' 	? '<a href="javascript:;" onMouseOver="return escape(\''.$arr['tooltip'].'\');"><img src="../imagens/ico_info.png" alt="" title="" border="0"></a>' : '');
				echo ($arr['tooltip'] != '' && $arr['obrigatorio'] == 1 ? '<br \><br \>' : '');
				echo ($arr['obrigatorio'] 	== 1 	? '<img src="../imagens/ico_obrigatorio.png" alt="OBRIGAT&Oacute;RIO" title="OBRIGAT&Oacute;RIO" border="0">' : '');
			?>
			</div>
		</td>
		<td class="tb_campo">
			<?php echo $html; ?>
		</td>
	</tr>
	<?php
			// Exibe os campos de relacionamento
			} else {
				// Cria as variveis com o nome das tabelas relacionadas
				$strTabela 		= str_replace('tb_rel_tb_','',$arr['tabela']);
				$arrTabelaRel	= explode('_tb_',$strTabela);
				$strTabela 		= 'tb_conteudo_'.$arrTabelaRel[0];
				$strTabelaRel 	= 'tb_conteudo_'.$arrTabelaRel[1];
				
				// Busca os registros relacionados
				$arrRel = array();
				if($_REQUEST['it_acao'] == 'alterar' && ( (($arr['html'] == 2 || $arr['html'] == 3) && isset($ob->$arr['campo'])) || $arr['html'] == 1) && $campo != 'PUBLICA_rel_estado_municipio_'.$arr['campo']) {
				
					if(($arr['html'] == 2 || $arr['html'] == 3) && is_numeric($ob->$arr['campo']) && $ob->$arr['campo'] > 0) {
						$arrRel[] = $ob->$arr['campo'];
					} else {
						if(isset($_REQUEST['in_controle'])) { 
							$query = "SELECT
										tb_".$arrTabelaRel[1]."_bn_id AS bn_rel
									  FROM
										".$arr['tabela']."
									  WHERE
										tb_".$arrTabelaRel[0]."_bn_id = '".$_REQUEST['in_controle']."';"; 
							$rs    = $conexao->query($query); 
							if(!DB::isError($rs)) { 
								while($obR = $rs->fetchRow()) { 
									$arrRel[] = $obR->bn_rel;
								}
							}
							unset($query,$rs,$obR);
						}
						unset($arrTabelaRel);
					}
				}
				//echo '<pre>'; print_r($arrRel); die();
				
				// Seta o incio da tag <SELECT>
				$str   = '';
				if($arr['html'] == 3) { 
					$str .= '<select name="'.$campo.'" id="'.$campo.'" '.(count($array) > 1 && array_key_exists('PUBLICA_rel_estado_municipio_'.$arr['campo'],$array) ? ($campo == 'PUBLICA_rel_estado_municipio_'.$arr['campo'] ? 'onChange="cityFilter(this.value,\''.$arr['campo'].'\',\'td_'.$arr['campo'].'\',3,0,250);"' : (ACAO == '' ? 'disabled' : '')) : '' ).' style="width:250px">
								<option value="">SELECIONE'."\n";
				}
				
				if(count($array) == 1 || (count($array) > 1 && (!array_key_exists('PUBLICA_rel_estado_municipio_'.$arr['campo'],$array) || (array_key_exists('PUBLICA_rel_estado_municipio_'.$arr['campo'],$array) && (ACAO == 'alterar' || (ACAO == '' && $campo == 'PUBLICA_rel_estado_municipio_'.$arr['campo'])) )))) {
					// Preenche o campo de relacionamento UF - MUNICIPIO
					if($campo == 'PUBLICA_rel_estado_municipio_'.$arr['campo']) {
						if(ACAO == 'alterar' && isset($_REQUEST['in_controle'])) {
							//$queryUFRel	= 'SELECT tb_estado_bn_id FROM tb_rel_tb_municipio_tb_estado WHERE tb_municipio_bn_id IN ('.implode(',',$arrRel).');';
							$queryUFRel	= 'SELECT DISTINCT tb_estado_bn_id FROM tb_rel_tb_municipio_tb_estado WHERE tb_municipio_bn_id IN (SELECT tb_municipio_bn_id FROM '.$arr['tabela'].' WHERE tb_'.$arrTabelaRel[0].'_bn_id = "'.$_REQUEST['in_controle'].'");';
							$arrRel		= $conexao->getCol($queryUFRel);
						}
						$strTabelaRel	= 'tb_conteudo_estado';
					}
					
					// Busca apenas os municipios relacionados aquela UF
					$strFieldUF	= '';
					$strTableUF	= '';
					$strWhereUF	= '';
					$strOrderUF	= '';
					if($strTabelaRel == 'tb_conteudo_municipio') {
						$strFieldUF		.= ', tb_rel_tb_municipio_tb_estado.tb_estado_bn_id, tb_conteudo_estado.bt_nome AS PUBLICA_rel_estado_municipio_name';
						$strTableUF		.= ', tb_rel_tb_municipio_tb_estado, tb_conteudo_estado';
						$strWhereUF		.= 'tb_rel_tb_municipio_tb_estado.tb_municipio_bn_id = tb_conteudo_municipio.bn_id AND tb_rel_tb_municipio_tb_estado.tb_estado_bn_id = tb_conteudo_estado.bn_id AND tb_conteudo_estado.bn_id IN (SELECT tb_estado_bn_id FROM tb_rel_tb_municipio_tb_estado WHERE tb_municipio_bn_id IN ('.implode(',',$arrRel).')) AND ';
						$strOrderUF		.= 'tb_conteudo_estado.bt_nome, ';
					}
					
					$queryRelConteudo  = "SELECT tb_usuario_bn_id, bn_conteudo FROM tb_permissao_secao_conteudo WHERE tb_usuario_bn_id = '".$_SESSION['in_usuario']."' AND tb_secao_bn_id = '".$arr['secao']."' AND tb_idioma_bn_id = '".IDIOMA."';";
					$permRelConteudo = reset($conexao->getAssoc($queryRelConteudo,false,array(),DB_FETCHMODE_ASSOC,true));
					
					// Busca os dados da seao relacionada
					$query = "SELECT DISTINCT
								".$strTabelaRel.".bn_id,
								".$strTabelaRel.".".$arr['estrutura']." AS bt_campo
								".$strFieldUF."
							  FROM
								".$strTabelaRel."
								".$strTableUF."
							  WHERE
							  	".(is_array($permRelConteudo) && count($permRelConteudo) > 0 ? $strTabelaRel.'.bn_id IN ('.implode(',',$permRelConteudo).') AND ' : '')."
							  	".$strWhereUF."
								".$strTabelaRel.".tb_idioma_bn_id	= '".IDIOMA."' 	AND
								".$strTabelaRel.".bb_delete 		= 0				AND
								".$strTabelaRel.".bb_ativo		 	= 1
							  ORDER BY
								".$strOrderUF.$strTabelaRel.".".$arr['estrutura']." ASC;";
					$rs	   = $conexao->query($query);
					if(!DB::isError($rs)) { 
						// Tabula relacionamentos multiplos
						if($arr['html'] < 3) {
							$str .= '<table border="0" cellspacing="10" cellpadding="0">';
						}
						
						// Cria a string com os campos de relacionamento
						$intCount 	= 1;
						$intUF		= 0;
						while($obR = $rs->fetchRow()) {
							switch($arr['html']) {
								case 1: // CHECKBOX
									if($intCount == 1 || ($intCount %10) == 0) {
										// Caso relacionamento UF - MUNICIPIO, identifica a UF
										if(isset($obR->publica_rel_estado_municipio_name) && $intUF != $obR->tb_estado_bn_id) {
											$str 	   .= ($intCount > 1 ? '</tr>' : '').'<tr><td bgcolor="#AEAEAE" colspan="4" height="20" align="center"><strong>'.$obR->publica_rel_estado_municipio_name.'</strong></td></tr>';
											$intUF 		= $obR->tb_estado_bn_id;
											$intCount	= 1;
										}
										
										/*
										if($rs->numRows() > 10) {
											if($intCount == 1) { $str .= '<tr>'; } else { $str .= '</td>'; }
											if(($intCount % 40) == 0) $str .= '</tr><tr><td colspan="4"><hr></td></tr><tr>';
											$str .= '<td valign="top" width="25%">';
										}
										*/
										
										if($intCount == 1 || ($intCount %10) == 0) {
												if($intCount == 1) { $str .= '<tr>'; } else { $str .= '</td>'; }
												if(($intCount % 40) == 0) $str .= '</tr><tr><td colspan="4"><hr></td></tr><tr>';
												$str .= '<td valign="top" width="25%">';
										}
									}
									$str .= '<input type="checkbox" name="'.$campo.'[]" id="'.$campo.'_'.$obR->bn_id.'" value="'.$obR->bn_id.'" '.(in_array($obR->bn_id,$arrRel) ? 'checked' : '').'> <label for="'.$campo.'_'.$obR->bn_id.'">'.html_entity_decode($obR->bt_campo,ENT_QUOTES,'UTF-8').'</label><br>'."\n";								
								break;
								
								case 2: // RADIO
									$str .= '<input type="radio" 	name="'.$campo.'" id="'.$campo.'_'.$obR->bn_id.'" value="'.$obR->bn_id.'" '.(in_array($obR->bn_id,$arrRel) ? 'checked' : '').'> <label for="'.$campo.'_'.$obR->bn_id.'">'.html_entity_decode($obR->bt_campo,ENT_QUOTES,'UTF-8').'</label><br>'."\n";
								break;
								
								case 3: // SELECT
									$str .= '<option value="'.$obR->bn_id.'" '.(in_array($obR->bn_id,$arrRel) || ($arr['obrigatorio'] == 1 && $rs->numRows() == 1) ? 'selected' : '').'>'.html_entity_decode($obR->bt_campo,ENT_QUOTES,'UTF-8')."\n"; 
								break;
							}
							$intCount++;
						}
						
						// Tabula relacionamentos multiplos
						if($arr['html'] < 3) {
							$str .= '</td></tr>';
							if($campo == 'PUBLICA_rel_estado_municipio_'.$arr['campo']) {
								$str .= '<tr><td colspan="4"><input type="button" name="bt_rel_uf_municipio" value=" Filtrar Munic&iacute;pios " onClick="cityFilter(\'PUBLICA_rel_estado_municipio_'.$arr['campo'].'\',\''.$arr['campo'].'\',\'td_'.$arr['campo'].'\','.$arr['html'].',0,250);" /></td></tr>';
							}
							$str .= '</table>';
						} 
					}
				}
			
				// Seta o fim da tag <SELECT>
				if($arr['html'] == 3) {
					$str .= "</select>\n";
				}
	?>
	<tr>
		<td valign="top" class="tb_nome_conteudo" colspan="2">
			<div style="float:left;"><?php echo $arr['nome']; ?></div>
			<div style="float:right;">
			<?php
				echo (isset($arr['tooltip']) 		&& $arr['tooltip'] 		!= '' 	? '<a href="javascript:;" onMouseOver="return escape(\''.$arr['tooltip'].'\');"><img src="../imagens/ico_info.png" alt="" title="" border="0"></a>' : '');
				echo ($arr['tooltip'] != '' && $arr['obrigatorio'] == 1 ? '<br \><br \>' : '');
				echo (isset($arr['obrigatorio']) 	&& $arr['obrigatorio'] 	== 1 	? '<img src="../imagens/ico_obrigatorio.png" alt="OBRIGAT&Oacute;RIO" title="OBRIGAT&Oacute;RIO" border="0">' : '');
			?>
			</div>
		</td>
		<td class="tb_campo" id="td_<?php echo ($campo != 'PUBLICA_rel_estado_municipio_'.$arr['campo'] ? $arr['campo'] : ''); ?>">
			<?php echo $str; ?>
		</td>
	</tr>
	<?php
			}
		}
	}
	?>
	<?php if(!isset($_SESSION['ib_pvtuser']) || (isset($_SESSION['ib_pvtuser']) && $_SESSION['ib_pvtuser'] === false)) include_once ROOT.'cms/include-sistema/box_destaques.php'; ?>
	<?php include_once ROOT.'cms/include-sistema/rodape_secoes.php'; ?>
</table>
</form>
<?php if((ACAO == 'incluir' || ACAO == 'alterar') && $strJavascript != '') { ?>
<script language="javascript" type="text/javascript">
	<?php echo $strJavascript; ?>
</script>
<?php } ?>

<?php if(isset($_REQUEST['ib_preview']) && $_REQUEST['ib_preview'] == 1) { ?>
<script language="javascript" type="text/javascript">
	fnPreviewConteudo();
</script>
<?php } ?>
<script language="javascript" src="../include-js/wz_tooltip.js"></script>
<?php include_once ROOT.'cms/include-js/funcoes.php'; ?>
<script language="javascript" type="text/javascript">
	richText();
	document.form.bt_nome.focus();
</script>
</body>
</html>
