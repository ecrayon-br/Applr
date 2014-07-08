<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

/**
 * Prints Publica Image Gallery Box
 *
 * @param	array	$params	Variables defined at SMARTY tag; possible values describes at the function scope
 * @param	object	$smarty	SMARTY object
 * @return	void
 */
function smarty_function_PUBLICA_imageGalleryBox($params, &$smarty) {
	global $conexao;
	
	// Checks GALLERY TYPE
	if($params['mediaType'] != 'image' && $params['mediaType'] != 'video' && $params['mediaType'] != 'both') {
		$params['mediaType'] = 'both';
	}
	
	
	// Format SECTION IDs array
	$intTemp_S 	= (isset($smarty->_tpl_vars['SECAO']) ? $smarty->_tpl_vars['SECAO'] : false);
	if(!isset($params['sectionId'])) {
		$params['sectionId'] 			= (is_bool($intTemp_S) ? $intTemp_S : array($intTemp_S));
	} elseif(!is_array($params['sectionId'])) {
		$intTemp = intval($params['sectionId']);
		$arrTemp = explode(',',$params['sectionId']);
		if(count($arrTemp) > 0) {
			$params['sectionId'] = $arrTemp;
		} elseif($intTemp > 0) {
			$params['sectionId'] = array($intTemp);
		} else {
			$params['sectionId'] = array($smarty->_tpl_vars['SECAO']);
		}
	} elseif(count($params['sectionId']) > 0) {
		foreach($params['sectionId'] AS $intKey => $intValue) {
			$params['sectionId'][$intKey] = intval($intValue);
		}
	} else {
		$params['sectionId'] = (is_bool($intTemp_S) ? $intTemp_S : array($intTemp_S));
	}
	unset($intTemp,$arrTemp);
	
	// Defines default variables
	if(!isset($params['returnVarSufix']))	{ $params['returnVarSufix']	= false;			}
	if(!isset($params['tableLimit']))		{ $params['tableLimit']	= 4; 					}
	if(!isset($params['tplBox']))			{ $params['tplBox']	= 'imageGalleryBox.htm'; 	}
	if(!isset($params['getSectionGallery'])){ $params['getSectionGallery']	= false;		}
	
	// Defines MEDIA ID to be shown at first load
	if(!isset($params['thisMediaId']) || is_null($params['thisMediaId']) || empty($params['thisMediaId']) || $params['thisMediaId'] < 0 ) {
		$params['thisMediaId'] = 0;
	}
	
	// If there is no content source, searches database for content-gallery relationships
	if(/*!isset($smarty->_tpl_vars[RESULTADO]) && */isset($params['sectionId']) && intval($params['sectionId']) > 0) {
		$strTable			= $conexao->getOne('SELECT bt_tabela FROM tb_secao WHERE bn_id = "'.$params['sectionId'].'";');
		
		if(isset($params['contentId']) && intval($params['contentId']) > 0) {
			if($params['mediaType'] == 'image' || $params['mediaType'] == 'both') $params['imageGalleryId']	= $conexao->getOne('SELECT bn_galeriaimg FROM '.$strTable.' AS dynTable WHERE bn_id = "'.$params['contentId'].'";');
			if($params['mediaType'] == 'video' || $params['mediaType'] == 'both') $params['videoGalleryId'] = $conexao->getOne('SELECT bn_galeriavideo FROM '.$strTable.' AS dynTable WHERE bn_id = "'.$params['contentId'].'";');
		} elseif(isset($params['contentFilter']) && !empty($params['contentFilter'])) {
			#die('SELECT bn_galeriavideo FROM '.$strTable.' AS dynTable '.$params['contentFilter'].';');
			if($params['mediaType'] == 'image' || $params['mediaType'] == 'both') $params['imageGalleryId']	= $conexao->getOne('SELECT bn_galeriaimg FROM '.$strTable.' AS dynTable '.$params['contentFilter'].';');
			if($params['mediaType'] == 'video' || $params['mediaType'] == 'both') $params['videoGalleryId'] = $conexao->getOne('SELECT bn_galeriavideo FROM '.$strTable.' AS dynTable '.$params['contentFilter'].';');
		}
	}
	
	// Sets CONTENT ID
	if(!isset($params['contentId']))	$params['contentId'] = 0;
	
	// Gets IMAGE and VIDEO gallery IDs related to SECTIONS and SEARCH CONDITION
	$arrSys_ImageGalleries = $conexao->getCol('SELECT bn_id FROM tb_imagem_categoria WHERE bb_default = 0 AND tb_secao_bn_id IN ('.implode(',',$params['sectionId']).')' . (isset($params['imageGalleryLimit']) && $params['imageGalleryLimit'] > 0 ? ' LIMIT ' . $params['imageGalleryLimit'] : ''));
	$arrSys_VideoGalleries = $conexao->getCol('SELECT bn_id FROM tb_video_categoria  WHERE bb_default = 0 AND tb_secao_bn_id IN ('.implode(',',$params['sectionId']).')' . (isset($params['videoGalleryLimit']) && $params['videoGalleryLimit'] > 0 ? ' LIMIT ' . $params['videoGalleryLimit'] : ''));
	
	// Format IMAGE GALLERY IDs array
	$intTemp_I 	= (isset($smarty->_tpl_vars[RESULTADO]['bn_galeriaimg']) ? $smarty->_tpl_vars[RESULTADO]['bn_galeriaimg'] : (isset($smarty->_tpl_vars[RESULTADO][$params['contentId']]['bn_galeriaimg']) ? $smarty->_tpl_vars[RESULTADO][$params['contentId']]['bn_galeriaimg'] : false) );
	
	if(!isset($params['imageGalleryId'])) {
		if(!$intTemp_I && $params['getSectionGallery']) {
			$params['imageGalleryId'] = $arrSys_ImageGalleries;
		} else {
			$params['imageGalleryId'] 		= (is_bool($intTemp_I) ? $intTemp_I : array($intTemp_I));
		}
	} elseif(!is_array($params['imageGalleryId'])) {
		$intTemp = intval($params['imageGalleryId']);
		$arrTemp = explode(',',$params['imageGalleryId']);
		if(count($arrTemp) > 1) {
			$params['imageGalleryId'] 	= $arrTemp;
		} elseif($intTemp > 0) {
			$params['imageGalleryId'] 	= array($intTemp);
		} else {
			$params['imageGalleryId'] 	= false;
		}
	} elseif(count($params['imageGalleryId']) > 0) {
		foreach($params['imageGalleryId'] AS $intKey => $intValue) {
			$params['imageGalleryId'][$intKey] = intval($intValue);
		}
	} else {
		$params['imageGalleryId'] = (is_bool($intTemp_I) ? $intTemp_I : array($intTemp_I));
	}
	#echo '<pre>'; var_dump($params['imageGalleryId']); die();
	unset($intTemp,$arrTemp);
	
	// Format IMAGE GALLERY IDs array EXCLUDED FROM SEARCH
	if(!isset($params['imageGalleryId_Excluded'])) {
		$params['imageGalleryId_Excluded'] 		= false;
	} elseif(!is_array($params['imageGalleryId_Excluded'])) {
		$intTemp = intval($params['imageGalleryId_Excluded']);
		$arrTemp = explode(',',$params['imageGalleryId_Excluded']);
		if(count($arrTemp) > 1) {
			$params['imageGalleryId_Excluded'] 	= $arrTemp;
		} elseif($intTemp > 0) {
			$params['imageGalleryId_Excluded'] 	= array($intTemp);
		} else {
			$params['imageGalleryId_Excluded'] 	= false;
		}
	} elseif(count($params['imageGalleryId_Excluded']) > 0) {
		foreach($params['imageGalleryId_Excluded'] AS $intKey => $intValue) {
			$params['imageGalleryId_Excluded'][$intKey] = intval($intValue);
		}
	} else {
		$params['imageGalleryId_Excluded'] = false;
	}
	
	// Format VIDEO GALLERY IDs array
	$intTemp_V 	= (isset($smarty->_tpl_vars[RESULTADO]['bn_galeriavideo']) ? $smarty->_tpl_vars[RESULTADO]['bn_galeriavideo'] : (isset($smarty->_tpl_vars[RESULTADO][$params['contentId']]['bn_galeriavideo']) ? $smarty->_tpl_vars[RESULTADO][$params['contentId']]['bn_galeriavideo'] : false));
	if(!isset($params['videoGalleryId'])) {
		if(!$intTemp_I && $params['getSectionGallery']) {
			$params['videoGalleryId'] = $arrSys_VideoGalleries;
		} else {
			$params['videoGalleryId'] 		= (is_bool($intTemp_I) ? $intTemp_I : array($intTemp_I));
		}
	} elseif(!is_array($params['videoGalleryId'])) {
		$intTemp = intval($params['videoGalleryId']);
		$arrTemp = explode(',',$params['videoGalleryId']);
		if(count($arrTemp) > 1) {
			$params['videoGalleryId'] 	= $arrTemp;
		} elseif($intTemp > 0) {
			$params['videoGalleryId'] 	= array($intTemp);
		} else {
			$params['videoGalleryId'] 	= false;
		}
	} elseif(count($params['videoGalleryId']) > 0) {
		foreach($params['videoGalleryId'] AS $intKey => $intValue) {
			$params['videoGalleryId'][$intKey] = intval($intValue);
		}
	} else {
		$params['videoGalleryId'] = (is_bool($intTemp_V) ? $intTemp_V : array($intTemp_V));
	}
	#echo '<pre>'; var_dump($params['videoGalleryId']); die();
	unset($intTemp,$arrTemp);
	
	// Format IMAGE GALLERY IDs array EXCLUDED FROM SEARCH
	if(!isset($params['videoGalleryId_Excluded'])) {
		$params['videoGalleryId_Excluded'] 		= false;
	} elseif(!is_array($params['videoGalleryId_Excluded'])) {
		$intTemp = intval($params['videoGalleryId_Excluded']);
		$arrTemp = explode(',',$params['videoGalleryId_Excluded']);
		if(count($arrTemp) > 1) {
			$params['videoGalleryId_Excluded'] 	= $arrTemp;
		} elseif($intTemp > 0) {
			$params['videoGalleryId_Excluded'] 	= array($intTemp);
		} else {
			$params['videoGalleryId_Excluded'] 	= false;
		}
	} elseif(count($params['videoGalleryId_Excluded']) > 0) {
		foreach($params['videoGalleryId_Excluded'] AS $intKey => $intValue) {
			$params['videoGalleryId_Excluded'][$intKey] = intval($intValue);
		}
	} else {
		$params['videoGalleryId_Excluded'] = false;
	}
	
	switch($params['mediaType']) {
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
								'.$strFirstTable.'_categoria_bn_id IN ('.($params['imageGalleryId'] !== false ? implode(',',$params['imageGalleryId']) : 0 ).') AND
								'.$strFirstTable.'_categoria_bn_id NOT IN ('.($params['imageGalleryId_Excluded'] !== false ? implode(',',$params['imageGalleryId_Excluded']) : 0 ).')
							'.($params['tableLimit'] > 0 ? 'LIMIT '.$params['tableLimit'] : '');
			// Gets MEDIA DATA
			$arrResultado		= $conexao->getAssoc($strQuery_Gallery, false, null, DB_FETCHMODE_ASSOC,false);
			if(DB::isError($arrResultado)) { $arrResultado = array(); }
			
			// Defines $params['thisMediaId'] index at $arrResultado
			$thisMediaId_index			= 0;
			foreach($arrResultado AS $intKey => $arrData) {
				if($arrData['bn_id'] == $params['thisMediaId']) {
					$thisMediaId_index = $intKey;
					break;
				}
			}
			
			if(isset($params['imageGalleryId']) && $params['imageGalleryId'] > 0) $arrGalleryId = $params['imageGalleryId'];
			if(isset($params['imageGalleryId_Excluded']) && $params['imageGalleryId_Excluded'] > 0) $arrGalleryId_Excluded = $params['imageGalleryId_Excluded'];
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
								REPLACE(bt_url,"watch?v=","v/") 		AS bt_arquivo,
								bt_thumbnail,
								'.$strFirstTable.'_categoria_bn_id
							FROM 
								'.$strFirstTable.'
							WHERE
								'.$strFirstTable.'_categoria_bn_id IN ('.($params['videoGalleryId'] !== false ? implode(',',$params['videoGalleryId']) : 0 ).') AND
								'.$strFirstTable.'_categoria_bn_id NOT IN ('.($params['videoGalleryId_Excluded'] !== false ? implode(',',$params['videoGalleryId_Excluded']) : 0 ).')
							'.($params['tableLimit'] > 0 ? 'LIMIT '.$params['tableLimit'] : '');
			#die($strQuery_Gallery);
			// Gets MEDIA DATA
			$arrResultado		= $conexao->getAssoc($strQuery_Gallery, false, null, DB_FETCHMODE_ASSOC,false);
			if(DB::isError($arrResultado)) { $arrResultado = array(); }
			
			// Defines $params['thisMediaId'] index at $arrResultado
			$thisMediaId_index			= 0;
			foreach($arrResultado AS $intKey => $arrData) {
				if($arrData['bn_id'] == $params['thisMediaId']) {
					$thisMediaId_index = $intKey;
					break;
				}
			}
			
			if(isset($params['videoGalleryId']) && $params['videoGalleryId'] > 0) $arrGalleryId = $params['videoGalleryId'];
			if(isset($params['videoGalleryId_Excluded']) && $params['videoGalleryId_Excluded'] > 0) $arrGalleryId_Excluded = $params['videoGalleryId_Excluded'];
			#echo '<pre>'; print_r($arrGalleryId); die();
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
								'.$strFirstTable.'_categoria_bn_id IN ('.($params['imageGalleryId'] !== false ? implode(',',$params['imageGalleryId']) : 0 ).') AND
								'.$strFirstTable.'_categoria_bn_id NOT IN ('.($params['imageGalleryId_Excluded'] !== false ? implode(',',$params['imageGalleryId_Excluded']) : 0 ).')
							'.($params['tableLimit'] > 0 ? 'LIMIT '.$params['tableLimit'] : '');
			
			$strQuery_Gallery_V	= '	SELECT
								bn_id,
								bt_nome,
								bt_autor,
								bt_legenda,
								REPLACE(bt_url,"watch?v=","v/") 		AS bt_arquivo,
								bt_thumbnail,
								'.$strSecondTable.'_categoria_bn_id
							FROM 
								'.$strSecondTable.'
							WHERE
								'.$strSecondTable.'_categoria_bn_id IN ('.($params['videoGalleryId'] !== false ? implode(',',$params['videoGalleryId']) : 0 ).') AND
								'.$strFirstTable.'_categoria_bn_id NOT IN ('.($params['videoGalleryId_Excluded'] !== false ? implode(',',$params['videoGalleryId_Excluded']) : 0 ).')
							'.($params['tableLimit'] > 0 ? 'LIMIT '.$params['tableLimit'] : '');
			
			// Gets MEDIA DATA
			$arrResultado_I 	= $conexao->getAll($strQuery_Gallery_I, null, DB_FETCHMODE_ASSOC);
			$arrResultado_V 	= $conexao->getAll($strQuery_Gallery_V, null, DB_FETCHMODE_ASSOC);
			
			if(DB::isError($arrResultado_I)) { $arrResultado_I = array(); } #var_dump($arrResultado_I); die(); }
			if(DB::isError($arrResultado_V)) { $arrResultado_V = array(); } #var_dump($arrResultado_V); die(); }
			
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
			
			// Checks $params['thisMediaType'] consistency
			if(!isset($params['thisMediaType']) || $params['thisMediaType'] == 'image')	{
				$params['thisMediaType']= 'image';
				$arrGalleryId			= (is_array($params['imageGalleryId']) ? $params['imageGalleryId'] : false);
				$arrGalleryId_Excluded 	= (is_array($params['imageGalleryId_Excluded']) ? $params['imageGalleryId_Excluded'] : false);
			} else {
				$params['thisMediaType']= 'video';
				$strFirstTable		= 'tb_video';
				$strSecondTable		= 'tb_imagem';
				$arrGalleryId		= (is_array($params['videoGalleryId']) ? $params['videoGalleryId'] : false);
				$arrGalleryId_Excluded 	= (is_array($params['videoGalleryId_Excluded']) ? $params['videoGalleryId_Excluded'] : false);
			}
			
			// Defines $params['thisMediaId'] index at $arrResultado
			$thisMediaId_index			= 0;
			foreach($arrResultado AS $intKey => $arrData) {
				switch($params['thisMediaType']) {
					case 'video':
						if($arrData['bn_id'] == $params['thisMediaId'] && isset($arrData['tb_video_categoria_bn_id'])) {
							$thisMediaId_index = $intKey;
							break 2;
						}
					break;
					
					case 'image':
						if($arrData['bn_id'] == $params['thisMediaId'] && isset($arrData['tb_imagem_categoria_bn_id'])) {
							$thisMediaId_index = $intKey;
							break 2;
						}
					break;
				}
			}
		break;
		
		default:
			return false;
		break;
	}
	#echo '<pre>'; var_dump($arrGalleryId); die();
	// Gets MEDIA GALLERY data
	$strQueryGallery	=  'SELECT
								'.$strFirstTable.'_categoria.bn_id,
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
								'.$strFirstTable.'_categoria.bb_default = 0 AND
								'.$strFirstTable.'_categoria.bn_id IN ('.($arrGalleryId !== false && !is_null($arrGalleryId) ? implode(',',$arrGalleryId) : 'SELECT bn_id FROM '.$strFirstTable.'_categoria WHERE tb_secao_bn_id IN ('.implode(',',$params['sectionId']).')').') AND
								'.$strFirstTable.'_categoria.bn_id NOT IN ('.($arrGalleryId_Excluded !== false && !is_null($arrGalleryId_Excluded) ? implode(',',$arrGalleryId_Excluded) : 0) . ');';
	$obGallery		= $conexao->getAll($strQueryGallery);
	if(DB::isError($obGallery)) {
		$obGallery	= array();
	}
	unset($query,$rs);
	
	// Defines TPL COMMON variables
	$smarty->assign('TITULO'			,TITULO);
	$smarty->assign('DIRETORIO' 		,DIRETORIO);
	$smarty->assign('XML'				,XML);
	$smarty->assign('ESTATICO'			,ESTATICO);
	$smarty->assign('DINAMICO'			,DINAMICO);
	$smarty->assign('IMAGEM'			,IMAGEM);
	$smarty->assign('PAGINA'			,PAGINA);
	$smarty->assign('LISTA_GALERIA' 	,LISTA_GALERIA);
	$smarty->assign('GALLERY_HEIGHT'	,GALLERY_HEIGHT);
	$smarty->assign('GALLERY_WIDTH'		,GALLERY_WIDTH);
	$smarty->assign('GALLERY_TYPE'		,$params['thisMediaType']);
	
	// Defines SPECIFIC SECTIONS variables
	$smarty->assign('SECAO'				,implode(',',$params['sectionId']));
	$smarty->assign('CONTEUDO'			,$params['contentId']);
	$smarty->assign('IDIOMA'			,IDIOMA);
	
	// Defines PAGING TPL variable
	$smarty->assign('GALERIA_PAGINACAO'	,count($arrResultado));
	
	// Defines GALLERY TPL variables
	$smarty->assign('GALERIA_ID'		,$arrGalleryId);
	$smarty->assign('GALERIA_TITULO'	,$obGallery->bt_nome);
	$smarty->assign('GALERIA_TEXTO'		,$obGallery->bt_descricao);
	$smarty->assign('GALERIA_IMAGEM'	,IMAGEM.$obGallery->bt_imagem);
	$smarty->assign('GALERIA_THUMBNAIL'	,IMAGEM.$obGallery->bt_imagem_thumbnail);
	$smarty->assign('GALERIA_DATA'		,$obGallery->bd_modificacao);
	
	// Defines FIRST MEDIA content vector
	if(isset($arrResultado[$thisMediaId_index])) {
		$smarty->assign('arrMedia',$arrResultado[$thisMediaId_index]);
	} else {
		$smarty->assign('arrMedia',reset($arrResultado));
	}
/*
	echo '<pre>';
	print_r($arrResultado[$thisMediaId_index]);
	print_r(reset($arrResultado));
	print_r($obGallery);
	print_r($arrResultado);	
	echo '</pre>';
*/
	// Assigns MEDIA GALLERY content vector
	$strSufix = ($params['returnVarSufix'] && count($params['imageGalleryId']) > 0 ? '_'.reset($params['imageGalleryId']) : ($params['returnVarSufix'] && count($params['videoGalleryId']) > 0 ? '_'.reset($params['videoGalleryId']) : ''));
	$smarty->assign('arrGallery'.$strSufix,$arrResultado);
	$smarty->assign('objGallery_info'.$strSufix,$obGallery);
	$smarty->assign('imageGallery_arrParams'.$strSufix,$params);
	
	// Check wheter TPL file exists; if false, checks for loop string or return empty
	if(!empty($obGallery->tb_template_bt_arquivo) && file_exists($smarty->template_dir.$obGallery->tb_template_bt_arquivo)) {
		// Displays TPL result
		$smarty->display($obGallery->tb_template_bt_arquivo);
	} elseif(file_exists($smarty->template_dir.$params['tplBox'])) {
		// Displays TPL result
		$smarty->display($params['tplBox']);
	} elseif(is_string($params['loop']) && !empty($params['loop'])) {
		foreach($arrResultado AS $intKey => $objData) {
			// Defines link path
			//$strLink = 'index.php?'.IN_SECAO.'=mediaGallery&'.IN_GALERIA.'='.(isset($objData['tb_imagem_categoria_bn_id']) ? $objData['tb_imagem_categoria_bn_id'].'&mediaType=image&' : $objData['tb_video_categoria_bn_id'].'&mediaType=video&').IN_CONTEUDO.'='.$params['sectionId'][0].'&intMedia='.$objData['bn_id'].'" title="'.$smarty->_tpl_vars['TITULO'].'" params="lightwindow_type=external,lightwindow_width='.GALLERY_WIDTH.',lightwindow_height='.GALLERY_HEIGHT.'" class="lightwindow page-options';
			$strLink = 'index.php?'.IT_ACAO.'=mediaGallery&'.IN_SECAO.'='.$params['sectionId'][0].'&'.IN_CONTEUDO.'='.$params['contentId'].'&'.IN_GALERIA.'='.(isset($objData['tb_imagem_categoria_bn_id']) ? $objData['tb_imagem_categoria_bn_id'].'&thisMediaType=image' : $objData['tb_video_categoria_bn_id'].'&thisMediaType=video').'&thisMediaId='.$objData['bn_id'].'" title="'.$smarty->_tpl_vars['TITULO'].'" params="lightwindow_type=external,lightwindow_width='.GALLERY_WIDTH.',lightwindow_height='.GALLERY_HEIGHT.'" class="lightwindow page-options';
			
			// Replaces loop string variables
			$strTemp = str_replace('#bn_id#'		,$objData['bn_id']										,$params['loop']);
			$strTemp = str_replace('#bt_nome#'		,$objData['bt_nome']									,$strTemp);
			$strTemp = str_replace('#bt_autor#'		,$objData['bt_autor']									,$strTemp);
			$strTemp = str_replace('#bt_legenda#'	,$objData['bt_legenda']									,$strTemp);
			$strTemp = str_replace('#bt_arquivo#'	,(isset($objData['tb_imagem_categoria_bn_id']) ? $smarty->_tpl_vars['IMAGEM'] : '').$objData['bt_arquivo'],$strTemp);
			$strTemp = str_replace('#bt_thumbnail#'	,$smarty->_tpl_vars['IMAGEM'].$objData['bt_thumbnail']	,$strTemp);
			$strTemp = str_replace('#LINK#'			,$strLink												,$strTemp);
			
			echo $strTemp."\n";
		}
	} else { return; }
}
?>
