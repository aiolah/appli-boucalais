<?php
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
require_once("./connect.php");
include('./Models/Utilisateur.php');
include('./Modules/UtilisateurManager.php');

$hash = password_hash("al'eau", PASSWORD_BCRYPT);

// echo $hash;

/*$verifie = password_verify('6c6p8q20', $hash);
if($verifie == true)
{
    echo "Mot de passe bon !";
}
else
{
    echo 'Pas bon !';
}*/

/*echo 'Code unique et random : ' . sha1(uniqid(rand())) . "<br/>";
echo 'Code unique et random : ' . uniqid(rand()) . "<br/>";
echo 'Code unique : ' . uniqid() . "<br/>";
echo 'Code random : ' . rand() . "<br/>";*/

/* date_default_timezone_set('Europe/Paris');

echo(date('d-m-Y h:i:s')) . "<br>";

// Plusieurs possibilités : comparer la date stockée + 24 heures avec now → NOW() pour l'enregistrement, SELECT ADDDATE(date_lien)

if(date('d-m-Y h:i:s') < '12-05-2020 17:05:50')
{
    echo "Lien non expiré <br>";
}

$date_lien = date_create('2020-05-10 15:13:50');
$date_ajd = date_create(date('Y-m-d h:i:s'));
$interval = date_diff($date_lien, $date_ajd);
echo $interval->format('%a %H:%i:%s') . "<br>"; */

if(false == NULL)
{
    echo ('Oui');
}

