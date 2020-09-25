<?php

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
require_once("./connect.php");
include('./Models/Utilisateur.php');
include('./Modules/UtilisateurManager.php');

// Définit le fuseau horaire à appliquer sur les fonctions dates du script (Paris !!)
date_default_timezone_set('Europe/Paris');

// PRÉPARATION MAIL DE NOTIFICATION
$objet = "Nouvelle demande de devis : " . $_POST['nom_organisme'] . ", " . $_POST['prenom'] . " " . $_POST['nom'];
$message = "
<html>
<body>
        <p><span style='font-weight: bold;'>Type de groupe : </span>" . $_POST['type_groupe'] . "</p>
        <p><span style='font-weight: bold;'>Nom de l'organisme : </span>" . $_POST['nom_organisme'] . "</p>
        <p><span style='font-weight: bold;'>Coordonnées du responsable</span></p>
        <p><span style='font-weight: bold;'>Prénom : </span>" . $_POST['prenom'] . "</p>
        <p><span style='font-weight: bold;'>Nom : </span>" . $_POST['nom'] . "</p>
        <p><span style='font-weight: bold;'>Téléphone : </span>" . $_POST['telephone'] . "</p>
        <p><span style='font-weight: bold;'>Email : </span>" . $_POST['email'] . "</p>
        <p><span style='font-weight: bold;'>Taille du groupe : </span>" . $_POST['nombre_personnes'] . "</p>
        <p><span style='font-weight: bold;'>Nombre de nuits : </span>" . $_POST['nombre_nuits'] . "</p>
</body>
</html>";

$destinataire = "contact@leboucalais.fr";
$headers = "Content-Type: text/html; charset=\"utf-8\"\n";
$headers .= "MIME-Version: 1.0\n";
$headers .= "Date: " . date(DateTime::RFC2822) . "\n";
$headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
$headers .= "Reply-To: contact@leboucalais.fr";

// VÉRIFICATION DU TEST GOOGLE CAPTCHA
$secretKey  = "6LdUtuwUAAAAACGmjxeE0cHVdS2qpGFAzY5BiFRA";

if(isset($_POST['captcha-response']) && !empty($_POST['captcha-response']))
{
        // Get verify response data
        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['captcha-response']);
        $responseData = json_decode($verifyResponse);
        if($responseData->success)
        {
                // Insertion des données du client en base de données
                $UtilisateurManager = new UtilisateurManager($bdd);
                $UtilisateurManager->ajouterProspect($_POST['prenom'], $_POST['nom'], $_POST['nom_organisme'], $_POST['type_groupe'], $_POST['email'], $_POST['telephone'], $_POST['nombre_personnes'], $_POST['nombre_nuits'], $_POST['moyen'], 1);

                // ENVOI MAIL DE NOTIFICATION
                mail($destinataire, $objet, $message, $headers);

                // ENVOI MAIL D'INSCRIPTION AU CLIENT
                $UtilisateurManager->ouvrirCompte($_POST['email']);

                // Redirection puis avec javascript : affichage message confirmation
                header('Location: http://leboucalais.fr/formulaire-devis/?demande=envoyee');
                exit;
        }
        else
        {
                // Redirection puis avec javascript : affichage message erreur captcha
                header('Location: http://leboucalais.fr/formulaire-devis/?captcha=echec#erreur');
                exit;
        }
}
?>