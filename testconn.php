<?php
/*
$conn = mysql_connect('mysql.totalwork.com.br','ertavares','wir584');
var_dump($conn);

$db = mysql_select_db('ertavares',$conn);
var_dump($db);
*/

include 'MDB2.php';
error_reporting(E_ALL);

$arrDSN			= 	array(
		'phptype'	=> 'mysql',
		'hostspec'	=> 'mysql.totalwork.com.br',
		'database'	=> 'ertavares',
		'username'	=> 'ertavares',
		'password'	=> 'wir584',
		'new_link'	=> true
);

$arrOptions		= array	(
		'debug'       	=> 2,
		'portability' 	=> MDB2_PORTABILITY_ALL,
		'persistent'	=> true,
		'seqcol_name'	=> 'id',
		'seqname_format'=> '%s'
);

$conn 	=& MDB2::singleton($arrDSN,$arrOptions);

echo '<pre>';
var_dump($conn);
?>