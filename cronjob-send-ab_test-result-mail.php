<?php
include 'init.php';

if(empty($_GET)) parse_str(implode('&', array_slice($argv, 1)), $_GET);

$objCampaign = new Campanhas_controller(false);
$objCampaign->send($_GET['permalink'],false);
?>