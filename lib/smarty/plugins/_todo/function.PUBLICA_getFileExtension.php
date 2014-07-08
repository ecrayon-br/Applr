<?php
// Returns file's extension from string given in $params['value']
function smarty_function_PUBLICA_getFileExtension($params, &$smarty) {
	// Checks params
	if(!isset($params['value']) || !is_string($params['value'])) {
		return;
	}
	return substr(strrchr($params['value'],'.'),1);
}
?>
