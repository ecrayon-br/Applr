<?php
/******************************************************/
/******************************************************/
/***												***/
/*** PRODUCTION ENVIRONMENT CONFIG AND CREDENTIALS  ***/
/***												***/
define('URI_DOMAIN_PROD',	(strpos($_SERVER["HTTP_HOST"],'www') !== false ? 'www.' : '') . 'ertavares.com.br');
define('DB_PROD_HOST',		'mysql.totalwork.com.br');
define('DB_PROD_DB',		'ertavares');
define('DB_PROD_USER',		'ertavares');
define('DB_PROD_PWD',		'wir584');


/******************************************************/
/******************************************************/
/***												***/
/***   HOMOLOG ENVIRONMENT CONFIG AND CREDENTIALS   ***/
/***												***/
define('URI_DOMAIN_HOMOLOG',(strpos($_SERVER["HTTP_HOST"],'www') !== false ? 'www.' : '') . 'ecrayon.com.br');
define('DIR_HOMOLOG',		'applr');
define('DB_HOMOLOG_HOST',	'cpmy0041.servidorwebfacil.com');
define('DB_HOMOLOG_DB',		'ecrayon_applr');
define('DB_HOMOLOG_USER',	'ecrayon_projetos');
define('DB_HOMOLOG_PWD',	'sys24ecrayon11');


/******************************************************/
/******************************************************/
/***												***/
/*** DEVELOPMENT ENVIRONMENT CONFIG AND CREDENTIALS ***/
/***												***/
define('URI_DOMAIN_DEVELOP','localhost');
define('DIR_DEVELOP',		'applr');
define('DB_DEVELOP_HOST',	'localhost');
define('DB_DEVELOP_DB',		'applr');
define('DB_DEVELOP_USER',	'root');
define('DB_DEVELOP_PWD',	'');
?>