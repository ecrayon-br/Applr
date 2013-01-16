<?php
if(isset($_REQUEST['sectionID']) && isset($_REQUEST['contentID']) && !empty($_REQUEST['sectionID']) && !empty($_REQUEST['contentID'])) {
	include_once '../../../cms/include-sistema/banco.php';
	
	safeCode();
	
	// Get SECTION configs
	$objSection = fnConfigSecao($_REQUEST['sectionID']);
	
	// Set FILE DIR
	$strDir		= ROOT_UPLOAD.str_replace('tb_conteudo_','',$objSection->bt_tabela).'/';
	
	// Get CONTENT data
	$strFile	= $conexao->getOne('SELECT bt_upload FROM ' . $objSection->bt_tabela . ' WHERE ' . REFINAMENTO . ' AND tb_idioma_bn_id = 1 AND bn_materia = ' . $_REQUEST['contentID']);
	
	if(!DB::isError($strFile) && is_file($strDir.$strFile)) {
		// send the necessary headers.
		// i found that these work well.
		header("Content-Type: application/unknown");
		header("Content-Disposition: filename=" . $strFile);
		
		// open the file for reading and start dumping it to the browser
		if($fp = fopen($strDir.$strFile, "r")) {
			while(!feof($fp)) {
				echo  fgets($fp);
			}
			// close the file
			fclose($fp);
		}
	} else {
		echo '<script type="text/javascript">window.close();</script>';
		exit();
	}
}
?>