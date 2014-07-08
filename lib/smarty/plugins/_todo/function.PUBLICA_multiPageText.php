<?php
/***************************/
/****** A T E N C A O ******/
/***************************/

// O layout da paginação deve ser setado dinamicamente! CORRIGIR!

function smarty_function_multitext($params, &$smarty)
{
	if($params['var'] == '') {
        return;
    }
	
	if($params['name'] == '') {
        return;
    } else {
		$strName = $params['name'];
	}
	
	$arrTemp	 = explode('#MULTITEXT#',$params['var']);
	$strTotal	 = count($arrTemp);
	$strTemp	 = '';
	$strPag 	 = '';
	
	foreach($arrTemp AS $indice => $valor) {
		$strTemp .= '<span id="MT_'.$strName.'_'.($indice + 1).'" style="display:none;">'.$valor."</span>\n";
		if($strTotal > 1) {
			$strPag	 .= '<a href="javascript:fnPaginacaoItemMT(\''.$strName.'\','.($indice + 1).');">['.($indice + 1).']</a>&nbsp;&nbsp;';
		}
	}
	if($strTotal > 1) {
		$strTemp	 .= '
		<div class="paginacao">
			<span id="lk_next"><strong class="red fright pgs-navega"><a href="javascript:fnPaginacaoMT(\''.$strName.'\',1);">pr&oacute;xima</a></strong></span>
			<span id="lk_preview"><strong class="red fright pgs-navega"><a href="javascript:fnPaginacaoMT(\''.$strName.'\',0);">anterior</a></strong></span>
			'.$strPag.'
		</div>';
	}
	$strTemp .= '
	<script>
		MT_'.$strName.'_index = 1;
		MT_'.$strName.'_total = '.(is_numeric($strTotal) ? $strTotal : 0).';
		fnPaginacaoItemMT("'.$strName.'",MT_'.$strName.'_index);
	</script>';
	
	return $strTemp;
}

/* vim: set expandtab: */

?>
