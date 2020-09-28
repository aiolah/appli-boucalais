<?php
include "config.inc.php";
error_reporting(E_ALL);
try
{
	$bdd = new PDO("mysql:host=$server;dbname=$database", $user, $passwd);
	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$bdd->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}
catch (Exception $e)
{
	die('Erreur : ' . $e->getMessage());
}
?>