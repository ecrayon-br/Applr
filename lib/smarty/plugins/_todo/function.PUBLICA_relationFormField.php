<?php
if(file_exists('../../cms/include-sistema/banco.php')) {
	include_once '../../cms/include-sistema/banco.php';
} else {
	include_once '../../../cms/include-sistema/banco.php';
}

/**
 * Creates form field with data from ID related table
 *
 * @param	array	$params	Variables defined at SMARTY tag; possible values describes at the function scope
 * @param	object	$smarty	SMARTY object
 * @return	string
 */
function smarty_function_PUBLICA_relationFormField($params, &$smarty) {
	global $conexao;
	
	// Defines table related ID
	if(!isset($params['id']) || !is_numeric($params['id'])) {
        return;
    }
	
    // Defines form type: 1 => CHECKBOX, 2 => RADIO, 3 => SELECT SINGLE, 4 => SELECT MULTIPLE
	if(!isset($params['type']) || !is_numeric($params['type']) || $params['type'] < 1 || $params['type'] > 4) {
        return;
    }
	
    // Defines form field name
	if(!isset($params['name']) || $params['name'] 	== '' || !is_string($params['name'])) {
        return;
    }
	
    // Defines whether creates HREF LINK to related content
	if(!isset($params['setLink']) || (!is_bool($params['setLink']) && $params['setLink'] !== 0 && $params['setLink'] !== 1)) {
        $params['setLink']	= false;
    }
	
    // Defines whether STATE/PROVINCE and CITY data must be filtered by SYSTEM USER ID
	if(!isset($params['userFilter']) || (!is_bool($params['userFilter']) && $params['userFilter'] !== 0 && $params['userFilter'] !== 1)) {
        $params['userFilter']	= false;
    }
	
    // Defines CITY ID
	if(!isset($params['city']) || $params['city'] == '' || $params['city'] == 0) {
        $params['city']		= false;
    } else {
    	$arrTemp				= explode(',',$params['city']);
    	if(!is_array($arrTemp)) {
    		$params['city'] 	= false;
    	} else {
    		foreach($arrTemp AS $strTemp) {
    			if(strcmp(intval($strTemp),$strTemp)) {
    				$params['city'] = false;
    				break;
    			}
    		}
    	}
    }
	
    // Defines STATE/PROVINCE ID
	if(!isset($params['state']) || $params['state'] == '' || $params['state'] == 0) {
        $params['state']		= false;
    } else {
    	$arrTemp				= explode(',',$params['state']);
    	if(!is_array($arrTemp)) {
    		$params['state'] 	= false;
    	} else {
    		foreach($arrTemp AS $strTemp) {
    			if(strcmp(intval($strTemp),$strTemp)) {
    				$params['state'] = false;
    				break;
    			}
    		}
    	}
    }
	
    // Defines whether form field contains label; if true, searcher for << bt_label >> field in the database table
	if(!isset($params['label']) || (!is_bool($params['label']) && $params['label'] !== 0 && $params['label'] !== 1)) {
        $params['label']		= false;
    }
	
    // Defines SELECT field width
	if(!isset($params['width']) || $params['width'] == '' || $params['width'] == 0) {
        $params['width']		= 265;
    }
	
    // Defined FORM name, for ID form fields
	if(!isset($params['formName']) 	|| $params['formName'] 	== '') {
        $params['formName']		= '';
    }
	
    // Defined VALUE to match
	if(!isset($params['value']) 	|| $params['value'] 	== '') {
        $params['value']		= '';
    }
	
    // Defined CITY FIELD name
	if(!isset($params['cityFieldName']) || $params['cityFieldName'] == '') {
        $params['cityFieldName']		= 'tb_municipio_bn_id';
    }
	
    // Defines CITY SELECT field width
	if(!isset($params['cityWidth']) || $params['cityWidth'] == '' || $params['cityWidth'] == 0) {
        $params['cityWidth']		= $params['width'];
    }
	
    // Defined whether STATE/PROVINCE form field must filter CITY data
	if(!isset($params['cityFilter']) || (!is_bool($params['cityFilter']) && $params['cityFilter'] !== 0 && $params['cityFilter'] !== 1)) {
        $params['cityFilter']	= false;
    }
	
    // Defines form field CSS CLASS
	if(!isset($params['cssClass']) 	|| $params['cssClass'] 	== '') {
        $params['cssClass']		= '';
    }
	
    // Defines form field tabIndex
	if(!isset($params['tabIndex']) 	|| $params['tabIndex'] 	== '') {
        $params['tabIndex']		= '';
    }
	
	// Initializes vars
	$strField		= '';
	$strTable		= '';
	$strFilter		= '';
	$strFilterTable	= '';
	$strOrder		= 'bt_nome ASC';
	$strChange 		= '';
	$strForm		= '';
	
	// Searches for the table related to ID param
	$strTable		= $conexao->getOne('SELECT bt_tabela FROM tb_secao WHERE bn_id = '.$params['id'].';');
	
	// If USERFILTER param is true, identifies ID value and creates relationship between SYSTEM USER and STATE/PROVINCE or CITY tables; if false and STATE param is defined, creates the relationship between STATE/PROVINCE and CITY tables
	if(isset($params['userFilter']) && ($params['userFilter'] === true || $params['userFilter'] == 1 || $params['userFilter'] == 'true')) {
		switch($params['id']) {
			default:
			break;
			
			case 4:
				$strField  		.= ', tb_conteudo_estado.bt_sigla';
				$strFilterTable 	= ',tb_rel_tb_usuario_tb_estado';
				if($params['state'])	$strFilter .= 'tb_rel_tb_usuario_tb_estado.tb_estado_bn_id IN ('.$params['state'].') 	AND ';
				$strFilter		= 'tb_rel_tb_usuario_tb_estado.tb_estado_bn_id = tb_conteudo_estado.bn_id AND ';
			break;
			
			case 5:
				$strField  		.= ', tb_rel_tb_municipio_tb_estado.tb_estado_bn_id, tb_conteudo_estado.bt_nome AS PUBLICA_rel_estado_municipio_name';
				$strFilterTable  	= ', tb_rel_tb_municipio_tb_estado, tb_conteudo_estado, tb_rel_tb_usuario_tb_municipio';
				if($params['city'])	$strFilter .= 'tb_rel_tb_municipio_tb_estado.tb_municipio_bn_id IN ('.$params['city'].') 	AND ';
				if($params['state'])	$strFilter .= 'tb_rel_tb_municipio_tb_estado.tb_estado_bn_id IN ('.$params['state'].') 	AND ';
				$strFilter 		.= 'tb_rel_tb_municipio_tb_estado.tb_municipio_bn_id = tb_rel_tb_usuario_tb_municipio.tb_municipio_bn_id AND tb_rel_tb_usuario_tb_municipio.tb_municipio_bn_id = tb_conteudo_municipio.bn_id AND tb_rel_tb_municipio_tb_estado.tb_estado_bn_id = tb_conteudo_estado.bn_id AND ';
				$strOrder		 = 'tb_conteudo_estado.bt_nome ASC, tb_conteudo_municipio.bt_nome ASC';
			break;
		}
	} else {
		switch($params['id']) {
			default:
			break;
			
			case 4:
				$strField  		.= ', tb_conteudo_estado.bt_sigla';
			break;
			
			case 5:
				$strField  		.= ', tb_rel_tb_municipio_tb_estado.tb_estado_bn_id, tb_conteudo_estado.bt_nome AS PUBLICA_rel_estado_municipio_name';
				$strFilterTable  = ', tb_rel_tb_municipio_tb_estado, tb_conteudo_estado';
				if($params['city'])		$strFilter .= 'tb_rel_tb_municipio_tb_estado.tb_municipio_bn_id IN ('.$params['city'].') 	AND ';
				if($params['state'])	$strFilter .= 'tb_rel_tb_municipio_tb_estado.tb_estado_bn_id IN ('.$params['state'].') 	AND ';
				$strFilter 		.= 'tb_rel_tb_municipio_tb_estado.tb_municipio_bn_id = tb_conteudo_municipio.bn_id AND tb_rel_tb_municipio_tb_estado.tb_estado_bn_id = tb_conteudo_estado.bn_id AND ';
				$strOrder		 = 'tb_conteudo_estado.bt_nome ASC, tb_conteudo_municipio.bt_nome ASC';
			break;
		}
	}
	
	if(!DB::isError($strTable) && !empty($strTable)) {
		// Searches for content
		$query = 'SELECT DISTINCT
					'.$strTable.'.bn_id,
					'.$strTable.'.bt_nome
					'.($params['label'] ? ', '.$strTable.'.bt_label' : '').'
					'.($strField != ''  ? $strField : '').'
				  FROM
					'.$strTable.'
					'.$strFilterTable.'
				  WHERE
					'.$strFilter.'
					
					'.$strTable.'.tb_idioma_bn_id	= '.IDIOMA.' 	AND
					'.$strTable.'.bb_delete 		= 0				AND
					'.$strTable.'.bb_ativo		 	= 1
				  GROUP BY
				  	bn_id
				  ORDER BY
					'.$strOrder.';';
		$rs	   = $conexao->query($query);
		if(!DB::isError($rs)) { 
			// Open SELECT tag
			if($params['type'] == 3 || $params['type'] == 4) { 
				// If STATEFILTER is true, set JS action
				if($params['type'] == 3 && $params['id'] == 4) {
					if($params['cityFilter'])	$strChange = 'onChange="cityFilter('.(!empty($params['formName']) ? "'".$params['formName']."'" : 'this.parent.name').',this.value,\''.$params['cityFieldName'].'\',\''.(isset($params['formName']) && !empty($params['formName']) ? $params['formName'].'_' : '').$params['cityFieldName'].'\','.$params['type'].','.(isset($params['userFilter']) && ($params['userFilter'] === true || $params['userFilter'] == 1 || $params['userFilter'] == 'true') ? 1 : 0).',\''.(isset($params['cityWidth']) ? $params['cityWidth'] : $params['width']).'\');"';
				}
				
				$strForm .= '<select '.($params['type'] == 4 ? 'multiple name="'.$params['name'].'[]"' : 'name="'.$params['name'].'"').' id="'.$params['name'].'" '.$strChange.' style="width:'.$params['width'].'px;" class="'.$params['cssClass'].'" '.(!empty($arrParams['tabIndex']) ? 'tabindex="' . $arrParams['tabIndex'] . '"' : '').'>
							<option value="">Selecione'."\n";
			}
			
			// Shows results in table
			if($params['type'] < 3) {
				$strForm .= '<table border="0" cellspacing="10" cellpadding="0">';
			}
			
			// Create relationship form fields
			$intCount 	= 0;
			$intUF		= 0;
			while($obR = $rs->fetchRow()) {
				switch($params['type']) {
					case 1: // CHECKBOX
						// Caso relacionamento UF - MUNICIPIO, identifica a UF
						if(isset($obR->publica_rel_estado_municipio_name) && $intUF != $obR->tb_estado_bn_id) {
							$strForm   .= ($intCount > 1 ? '</tr>' : '').'<tr><td bgcolor="#AEAEAE" colspan="4" height="20" align="center"><strong>'.$obR->publica_rel_estado_municipio_name.'</strong></td></tr>';
							$intUF 		= $obR->tb_estado_bn_id;
							$intCount	= 1;
						} elseif($intCount == 0) {
							$intCount	= 1;
						}
						
						if($intCount == 1 || ($intCount %10) == 0) {
								if($intCount == 1) { $strForm .= '<tr>'; } else { $strForm .= '</td>'; }
								if(($intCount % 40) == 0) $strForm .= '</tr><tr><td colspan="4"><hr></td></tr><tr>';
								$strForm .= '<td valign="top" width="25%">';
						}
						
						$strForm .= '<input type="checkbox"  '.($params['value'] == $obR->bn_id ? 'checked' : '').' name="'.$params['name'].'[]" id="'.$params['name'].'_'.$obR->bn_id.'" value="'.$obR->bn_id.'" class="'.$params['cssClass'].'"> <label for="'.$params['name'].'_'.$obR->bn_id.'" class="'.$params['cssClass'].'">'.($params['setLink'] ? '<a class="'.$params['cssClass'].'" href="'.DINAMICO.'index.php?'.IN_SECAO.'='.$params['id'].'&'.IN_CONTEUDO.'='.$obR->bn_id.'&'.IN_IDIOMA.'='.IDIOMA.'" target="_blank">' : '').($params['label'] ? ' <img src="/site/arquivos/imagens/geral/ico_info.gif" alt="'.$obR->bt_label.'" title="'.$obR->bt_label.'" border="0" align="absmiddle" width="10" height="10"> ' : '').html_entity_decode($obR->bt_nome,ENT_QUOTES,'UTF-8').($params['setLink'] ? '</a>' : '').'</label><br>'."\n";
					break;
					
					case 2: // RADIO
						$strForm .= '<input type="radio"  '.($params['value'] == $obR->bn_id ? 'checked' : '').' name="'.$params['name'].'" id="'.$params['name'].'_'.$obR->bn_id.'" value="'.$obR->bn_id.'" class="'.$params['cssClass'].'"> <label for="'.$params['name'].'_'.$obR->bn_id.'" class="'.$params['cssClass'].'">'.($params['setLink'] ? '<a class="'.$params['cssClass'].'" href="'.DINAMICO.'index.php?'.IN_SECAO.'='.$params['id'].'&'.IN_CONTEUDO.'='.$obR->bn_id.'&'.IN_IDIOMA.'='.IDIOMA.'" target="_blank">' : '').($params['label'] ? ' <img src="/site/arquivos/imagens/geral/ico_info.gif" alt="'.$obR->bt_label.'" title="'.$obR->bt_label.'" border="0" align="absmiddle" width="10" height="10"> ' : '').html_entity_decode($obR->bt_nome,ENT_QUOTES,'UTF-8').($params['setLink'] ? '</a>' : '').'</label><br>'."\n";
					break;
					
					case 3: // SELECT SINGLE
					case 4: // SELECT MULTIPLE
						$strForm .= '<option value="'.$obR->bn_id.'" '.($params['value'] == $obR->bn_id ? 'selected' : '').'>'.html_entity_decode((isset($obR->$params['showField']) ? $obR->$params['showField'] : $obR->bt_nome),ENT_QUOTES,'UTF-8')."\n"; 
					break;
				} 
				$intCount++;
			}
						
			// Tabula relacionamentos multiplos
			if($params['type'] < 3) {
				$strForm .= '</td></tr>';
				if($params['cityFilter'])	$strForm .= '<tr><td colspan="4"><input type="button" name="bt_rel_uf_municipio" value=" Filtrar Munic&iacute;pios " onClick="cityFilter(this.value,\''.$params['cityFieldName'].'\',\''.(isset($params['formName']) && !empty($params['formName']) ? $params['formName'].'_' : '').$params['cityFieldName'].'\','.$params['type'].','.(isset($params['userFilter']) && ($params['userFilter'] === true || $params['userFilter'] == 1 || $params['userFilter'] == 'true') ? 1 : 0).',\''.(isset($params['cityWidth']) ? $params['cityWidth'] : $params['width']).'\');" /></td></tr>';
				$strForm .= '</table>';
			} 
			
			// Close SELECT tag
			if($params['type'] == 3) {
				$strForm .= "</select>\n";
			}
		}
	}
	
	return $strForm;
}
?>