<?php
include_once 'init.php';

$strURL = str_replace(array(URI_DOMAIN,LOCAL_DIR),'',$_SERVER['REQUEST_URI']);
if($strURL == '' || $strURL == '/') $strURL = 'main';

if(!empty($_SERVER['QUERY_STRING'])) $strURL .= '/?' . $_SERVER['QUERY_STRING'];
$strURL = str_replace(array('site/','//'),array('','/'),$strURL);

if(ACTION == 'cms') {
	
	header("Location: " . HTTP . 'cms/index.php?APPLR_friendlyURL=1');
	exit();
	
} elseif(ACTION == 'FB-share') {
	
	include_once 'site/include-sistema/sys/facebookShare.php';
	exit();
	
} elseif(FRAME_VIEW && ACTION != 'FB-share') {
	
	if(DEBUG) {
		echo '<pre>'; print_r($_REQUEST); echo '</pre>';
		echo '<hr />';
		echo $strURL;
		echo '<br />';
		echo HTTP . 'site/conteudo'.$strURL;
		echo '<hr />';

		phpinfo();
		exit();
	} else {
		include_once 'cms/include-sistema/classe_smarty.php';

		$objSmarty = new smartyConnect();
	}
?>
<html>
	<head>
		<?php $objSmarty->display('sys/SEO_meta_content.html'); ?>
		
		<link rel="icon" type="image/png" href="<?php echo HTTP_DINAMICO; ?>img/elements/favicon.png">
	</head>
	<frameset rows="0,*">
		<frame name="supportframe" src="<?php echo HTTP . FRAME_URL; ?>" border=0 frameborder=0 marginwidth=0 marginheight=0 scrolling=no noresize allowTransparency="true"></frame>
		<frame name="content" src="<?php echo HTTP . 'site/conteudo' . $strURL; ?>" border=0 frameborder=0 marginwidth=0 marginheight=0 scrolling=auto noresize allowTransparency="true"></frame>
	</frameset>
	<noframes>
		<body>
			<?php echo file_get_contents(HTTP.'site/conteudo' . $strURL); ?>
		</body>
	</noframes>
</html>
<?php
} else {
	die(HTTP.'site/conteudo/' . $strURL);
	echo file_get_contents(HTTP.'site/conteudo/' . $strURL);
}
?>