<?php

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
session_start();
date_default_timezone_set('Europe/Paris');

require_once "connect.php";

require_once "Models/Utilisateur.php";
require_once "Models/Reservation.php";
require_once "Models/Devis.php";
require_once "Models/Activite.php";
require_once "Models/Fichier.php";
require_once "Models/DocumentsClient.php";
require_once "Models/Option.php";

require_once "Modules/UtilisateurManager.php";
require_once "Modules/ReservationManager.php";
require_once "Modules/DevisManager.php";
require_once "Modules/ActiviteManager.php";
require_once "Modules/FichierManager.php";
require_once "Modules/DocumentsClientManager.php";
require_once "Modules/OptionManager.php";

require_once ("moteurtemplate.php");

$UtilisateurManager = new UtilisateurManager($bdd);
$reservationManager = new ReservationManager($bdd);
$devisManager = new DevisManager($bdd);
$activiteManager = new ActiviteManager($bdd);
$FichierManager = new FichierManager($bdd);
$DocumentsClientManager = new DocumentsClientManager($bdd);
$optionManager = new OptionManager($bdd);

if(!isset($_SESSION['acces']))
{
    $_SESSION['acces'] = "non";
}

if(!isset($_SESSION['role']))
{
    $_SESSION['role'] = "";
}

if(isset($_SESSION['id']) && $_SESSION['acces'] == "oui")
{
    $user = $UtilisateurManager->getProfil($_SESSION['id']);
}

if(isset($_GET['action']))
{
    $action = $_GET['action'];
    switch($action) {

        // Formulaire d'inscription, uniquement pour les prospects
        case "inscription" :

            if(isset($_POST['inscription']))
            {
                $prospect = $UtilisateurManager->verificationEmail($_POST['email']);
                if($prospect == "Plus de 2 comptes")
                {
                    $_SESSION['acces'] = 'non';
                    $message = "Vous ne pouvez pas avoir 2 comptes avec la même adresse email.";
                    echo $twig->render('inscription.html.twig', array('acces'=>$_SESSION['acces'], 'message'=>$message));
                }
                elseif($prospect == "Déjà inscrit")
                {
                    $_SESSION['acces'] = 'non';
                    $message = "Votre compte existe déjà. Vous ne pouvez pas vous inscrire de nouveau.";
                    echo $twig->render('inscription.html.twig', array('acces'=>$_SESSION['acces'], 'message'=>$message));
                }
                elseif(!$prospect)
                {
                    $_SESSION['acces'] = 'non';
                    $message = "Votre mail ne correspond pas à celui auquel nous vous avons envoyé le mail d'inscription !";
                    echo $twig->render('inscription.html.twig', array('acces'=>$_SESSION['acces'], 'message'=>$message));
                }
                elseif(is_object($prospect) || $prospect == "Ajouté par un gérant")
                {
                    $prospect = $UtilisateurManager->getProfilFromEmail($_POST['email']);
                    $UtilisateurManager->register($prospect->getId(), $_POST['mdp']);
                    $message = "Votre inscription est réussie ! Vous allez bientôt être redirigé vers la page d'accueil.";
                    $_SESSION['acces'] = "oui";
                    $_SESSION['role'] = $prospect->getRole();
                    $_SESSION['id'] = $prospect->getId();
                    $nomGroupe = $prospect->getNomGroupe();

                    // On renvoie sur la page d'inscription qui affiche un message de confirmation mais qui redirige ensuite vers la page d'accueil
                    echo $twig->render('inscription.html.twig', array('acces'=>$_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'client'=>$prospect));
                }
            }
            elseif($_SESSION['acces'] == "oui")
            {
                $message = "Vous vous êtes déjà inscrit, vous n'avez plus accès à cette page.";
                echo $twig->render('index.html.twig', array('acces'=>$_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'message'=>$message, 'alert'=>"danger"));
            }
            else
            {
                echo $twig->render('inscription.html.twig', array('acces'=>$_SESSION['acces']));
            }

        break;

        // Formulaire de connexion, pour tous les utilisateurs de l'application
        case "connexion" :

            if(isset($_POST['connexion']))
            {
                $user = $UtilisateurManager->connexion($_POST['email'], $_POST['mdp']);
                if(is_object($user))
                {
                    $_SESSION['acces'] = 'oui';
                    $_SESSION['role'] = $user->getRole();
                    $_SESSION['id'] = $user->getId();
                    $message = "Connexion réussie !";

                    // On renvoie sur la page de connexion qui affiche un message mais qui redirige ensuite vers la page d'accueil
                    echo $twig->render('connexion.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'user'=>$user));
                }
                if($user == "mdp_error")
                {
                    $_SESSION['acces'] = 'non';
                    $message = "Mot de passe incorrect, veuillez réessayer";
                    echo $twig->render('connexion.html.twig', array('acces'=> $_SESSION['acces'], 'message'=>$message));
                }

                if($user == "mail_error")
                {
                    $_SESSION['acces'] = 'non';
                    $message = "Email incorrect, veuillez réessayer";
                    echo $twig->render('connexion.html.twig', array('acces'=> $_SESSION['acces'], 'message'=>$message));
                }
            }
            else
            {
                echo $twig->render('connexion.html.twig', array('acces'=> $_SESSION['acces']));
            }

        break;

        case "recuperer-mdp" :

            // Formulaire de récupération du mot de passe : renseigner l'adresse email
            if(isset($_POST['recuperation']))
            {
                $user = $UtilisateurManager->recupererMdp($_POST['email']);
                if($user)
                {
                    $message = "Veuillez consulter votre boîte mail pour réinitialiser votre mot de passe.";
                    echo $twig->render('recuperer_mdp.html.twig', array('acces'=>$_SESSION['acces'], 'message'=>$message, 'succesMail'=>'oui', 'token'=>'non', 'pageRecupererMdp'=>'yes'));
                }
                elseif(!$user)
                {
                    $message = "L'adresse email ne correspond à aucun compte existant.";
                    echo $twig->render('recuperer_mdp.html.twig', array('acces'=>$_SESSION['acces'], 'message'=>$message, 'succesMail'=>'non', 'token'=>'non', 'pageRecupererMdp'=>'yes'));
                }
            }
            // Vérification du token entré dans l'URL et affichage du formulaire de réinitialisation du mot de passe
            elseif(isset($_GET['token']))
            {
                $user = $UtilisateurManager->verificationJeton($_GET['token']);
                if(is_object($user))
                {
                    // Si la date d'expiration (date enregistrée en base de données (= date à laquelle l'utilisateur a demandé le lien) + 24 heures) n'a pas été dépassée, donc si elle est supérieure à la date d'aujourd'hui
                    if($user->getDateLien() > date('Y-m-d H:i:s'))
                    {
                        $_SESSION['id'] = $user->getId();
                        echo $twig->render('recuperer_mdp.html.twig', array('acces'=>$_SESSION['acces'], 'succesLien'=>'oui', 'token'=>'oui', 'user'=>$user, 'pageRecupererMdp'=>'yes'));
                    }
                    else
                    {
                        echo $twig->render('recuperer_mdp.html.twig', array('acces'=>$_SESSION['acces'], 'succesLien'=>'non', 'token'=>'oui', 'pageRecupererMdp'=>'yes'));
                    }
                }
                elseif($user == false)
                {
                    echo $twig->render('recuperer_mdp.html.twig', array('acces'=>$_SESSION['acces'], 'token'=>'oui', 'validité'=>'non', 'pageRecupererMdp'=>'yes'));
                }
            }
            // Formulaire de réinitilisation du mot de passe : renseigner un nouveau mot de passe
            elseif(isset($_POST['reinitialisation']))
            {
                $UtilisateurManager->reinitialiserMdp($_POST['mdp'], $_SESSION['id']);
                $message = "Votre mot de passe a bien été réinitialisé ! Vous allez bientôt être redirigé vers la page de connexion.";
                echo $twig->render('recuperer_mdp.html.twig', array('acces'=>$_SESSION['acces'], 'message'=>$message, 'succes'=>'oui', 'succesNouveauMdp'=>'oui', 'pageRecupererMdp'=>'yes'));
            }
            else
            {
                // Affichage du formulaire pour renseigner l'adresse email
                echo $twig->render('recuperer_mdp.html.twig', array('acces'=>$_SESSION['acces'], 'token'=>'non', 'pageRecupererMdp'=>'yes'));
            }

        break;

        case "accueil" :

            connectYourself($twig);
            // On récupère les informations de l'utilisateur pour, en fonction de son rôle, afficher sa page d'accueil
            $user = $UtilisateurManager->getProfil($_SESSION['id']);
            if($user->getRole() == "client" || $user->getRole() == "prospect")
            {
                echo $twig->render('accueil_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$user, 'user'=>$user));
            }
            elseif($user->getRole() == "gerant")
            {
                $clients1 = $UtilisateurManager->getUsersFromStatut("1");
                $clients2 = $UtilisateurManager->getUsersFromStatut("2");
                $clients3 = $UtilisateurManager->getUsersFromStatut("3");
                $clients4 = $UtilisateurManager->getUsersFromStatut("4");
                $clients5 = $UtilisateurManager->getUsersFromStatut("5");
                $clients6 = $UtilisateurManager->getUsersFromStatut("6");
                $clients7 = $UtilisateurManager->getUsersFromStatut("7");
                $clients8 = $UtilisateurManager->getUsersFromStatut("8");
                echo $twig->render('aperçu_clients.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'clients1'=>$clients1, 'clients2'=>$clients2, 'clients3'=>$clients3, 'clients4'=>$clients4, 'clients5'=>$clients5, 'clients6'=>$clients6, 'clients7'=>$clients7, 'clients8'=>$clients8, 'yes'=>'yes', 'user'=>$user));
            }
            
        break;

        // Affichage du profil de l'utilisateur connecté
        case "profil" :

            if($_SESSION['acces'] == "oui")
            {
                echo $twig->render('profil.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user));
            }
            connectYourself($twig);

        break;

        // Formulaire de modification du profil
        case "modifier-profil" :

            if(isset($_POST['modifier-profil']) && !empty($_POST['prenom']) && !empty($_POST['nom']) && !empty($_POST['nom_organisme']) && !empty($_POST['email']) && !empty($_POST['telephone']))
            {
                $prospect = $UtilisateurManager->verificationEmail($_POST['email']);
                if($prospect != false)
                {
                    // Si l'adresse email ne change pas
                    if($user->getMail() == $_POST['email'])
                    {
                        $ok = $UtilisateurManager->updateProfile($_POST['nom_organisme'], $_POST['prenom'], $_POST['nom'], $_POST['email'], $_POST['telephone'], $_SESSION['id']);
                        if($ok)
                        {
                            $message = "Le profil a bien été modifié.";
                            $user = $UtilisateurManager->getProfil($_SESSION['id']);
                            echo $twig->render('profil.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'user'=>$user, 'succes'=>'oui'));
                        }
                        else
                        {
                            $message = "Les modifications n'ont pu être enregistrées.";
                            echo $twig->render('modifier_profil.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'user'=>$user, 'succes'=>'non'));
                        }    
                    }
                    // Si elle change et qu'un autre utilisateur de l'application l'a déjà
                    else
                    {
                        $message = "Un utilisateur avec cette adresse email existe déjà.";
                        echo $twig->render('modifier_profil.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'user'=>$user, 'succes'=>'non'));
                    }
                }
                else
                {
                    // Si l'adresse email est nouvelle et qu'aucun utilisateur ne l'a
                    $ok = $UtilisateurManager->updateProfile($_POST['nom_organisme'], $_POST['prenom'], $_POST['nom'], $_POST['email'], $_POST['telephone'], $_SESSION['id']);
                    if($ok)
                    {
                        $message = "Le profil a bien été modifié.";
                        $user = $UtilisateurManager->getProfil($_SESSION['id']);
                        echo $twig->render('profil.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'user'=>$user, 'succes'=>'oui'));
                    }
                    else
                    {
                        $message = "Les modifications n'ont pu être enregistrées.";
                        echo $twig->render('modifier_profil.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'user'=>$user, 'succes'=>'non'));
                    }
                }
            }
            else
            {
                connectYourself($twig);
                echo $twig->render('modifier_profil.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user));
            }

        break;

        // Suppression du compte client par le client lui-même
        case "supprimer-compte" :

            if(isset($_POST['supprimer-compte']))
            {
                $ok = $UtilisateurManager->deleteProfile($_SESSION['id'], dirname(__FILE__), $user, 0);
                if($ok)
                {
                    $message = "Votre compte a bien été supprimé.";
                    $_SESSION['acces'] = "non";
                    unset($_SESSION['id']);
                    $_SESSION['role'] = "";        
                    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'supprimerCompte'=>"yes", 'message'=>$message, 'alert'=>"success"));
                }
                else
                {
                    $message = "Votre compte n'a pu être supprimé.";
                    echo $twig->render('profil.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'user'=>$user, 'succes'=>'non'));
                }
            }

        break;

        // Formulaire pour modifier le mot de passe
        case "modifier-mdp" :

            if(isset($_POST['modifier-mdp']))
            {
                $user = $UtilisateurManager->modifierMdp($_POST['ancien-mdp'], $_POST['nouveau-mdp'], $_SESSION['id']);
                if($user == true)
                {
                    $message = "Mot de passe modifié avec succès !";
                    $user = $UtilisateurManager->getProfil($_SESSION['id']);
                    echo $twig->render('modifier_mdp.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'succes'=>'oui', 'user'=>$user));
                }
                else
                {
                    $message = "L'ancien mot de passe ne correspond pas.";
                    $user = $UtilisateurManager->getProfil($_SESSION['id']);
                    echo $twig->render('modifier_mdp.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'succes'=>'non', 'user'=>$user));
                }
            }
            else
            {
                connectYourself($twig);
                echo $twig->render('modifier_mdp.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user));
            }

        break;

        // Formulaire de devis
        case "form-devis" :

            if($_SESSION['role'] != "gerant")
            {
                // Enregistrement du devis
                if(isset($_POST['commande']))
                {
                    $devis = new Devis($_POST);
                    $ok = $devisManager->add($devis, $_POST, $user);
    
                    if($ok) {
                        $message = "Le devis a bien été enregistré. Vous pouvez le consulter sur la page <a href='?action=liste-devis-client' class='alert-link'>Mes devis</a>.";
                        echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"success", 'commande'=>$devis, 'user'=>$user));
                    }
    
                    else {
                        $message = "Erreur lors de l'enregistrement du devis, veuillez réessayer plus tard";
                        echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"danger", 'user'=>$user));
                    }
                }
                else
                {
                    connectYourself($twig);
                    $user = $UtilisateurManager->getProfil($_SESSION['id']);
                    $activites = $activiteManager->getActivites('form-devis');
                    $options = $optionManager->getOptions("form-devis");
                    $option3 = $optionManager->getOptionById(3);
                    echo $twig->render('formulaire_devis.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'activites'=>$activites, 'options'=>$options, 'option3'=>$option3, 'client'=>$user, 'user'=>$user));
                }
            }

        break;

        // Liste de toutes les réservations envoyées par le biais du formulaire
        case "liste-devis" :

            if($_SESSION['acces'] == "oui")
            {
                $devis = $devisManager->getListeDevis();
                echo $twig->render('liste_devis.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'devis'=>$devis, 'user'=>$user));
            }
            connectYourself($twig);

        break;

        // Liste de toutes les devis d'un client
        case "liste-devis-client" :

            if(isset($_POST['supprimer-devis-client']))
            {
                $devis = $devisManager->delete($_POST['devis']);
                if($devis)
                {
                    $devis = $devisManager->getDevisFromUser($_SESSION['id']);
                    echo $twig->render('liste_devis_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'devis'=>$devis, 'succes'=>'oui', 'nbreDevis'=>count($_POST['devis']), 'user'=>$user));
                }
                elseif($devis)
                {
                    $devis = $devisManager->getDevisFromUser($_SESSION['id']);
                    echo $twig->render('liste_devis_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'devis'=>$devis, 'succes'=>'non', 'user'=>$user));
                }
            }
            else
            {
                connectYourself($twig);
                $devis = $devisManager->getDevisFromUser($_SESSION['id']);
                echo $twig->render('liste_devis_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'devis'=>$devis, 'user'=>$user));
            }

        break;

        case "consulter-devis":

            // Validation du devis
            if(isset($_POST['valider-devis']))
            {
                if(!empty($_POST["confirmation"]))
                {
                    $devis = $devisManager->getDevisFromIdUser($_SESSION['id'], $_POST['ID_DEVIS']);
                    $client = $UtilisateurManager->getProfil($_SESSION['id']);
                    $valideDevis = $devisManager->toReservation($devis, $client);
                    if($valideDevis)
                    {
                        $message = "Le devis a bien été validé, félicitations, vous venez d'effectuer votre réservation ! Le gérant va maintenant consulter votre demande. Vous recevrez un mail une fois qu'il l'aura confirmée.";
                        echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"success", 'user'=>$user));
                    }
                    else
                    {
                        $message = "Erreur lors de la validation du devis.";
                        echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"danger", 'user'=>$user));
                    }
                }
                else
                {
                    $message = "Vous devez reconnaître que la signature d'un devis est un engagement juridique pour pouvoir valider votre devis. Veuillez cocher la case \"J'ai lu et compris, j'ai pouvoir à signer ce devis.\"";
                    echo $twig->render('consulter_devis.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'confirmation'=>'non', 'user'=>$user));
                }
            }
            elseif($_SESSION['role'] == "gerant")
            {
                $devis = $devisManager->getDevisFromId($_GET['devis']);
                $activites = $activiteManager->getActivitesFromDevis($_GET['devis']);
                $options = $optionManager->getOptionsFromDevis($_GET['devis']);
                $client = $UtilisateurManager->getProfilFromDevis($_GET['devis']);
                if($devis != false)
                {
                    echo $twig->render('consulter_devis_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'devis'=>$devis, 'activites'=>$activites, 'options'=>$options, 'nomGroupe'=>$client->getNomGroupe(), 'idClient'=>$client->getId(), 'user'=>$user));
                }
            }
            else
            {
                connectYourself($twig);
                $devis = $devisManager->getDevisFromIdUser($_SESSION['id'], $_GET['devis']);
                $reservation = $reservationManager->getReservationFromIdDevis($_GET['devis']);
                $activites = $activiteManager->getActivitesFromDevis($_GET['devis']);
                $options = $optionManager->getOptionsFromDevis($_GET['devis']);
                $user = $UtilisateurManager->getProfil($_SESSION['id']);
                if($devis != false)
                {
                    echo $twig->render('consulter_devis.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'devis'=>$devis, 'activites'=>$activites, 'options'=>$options, 'nomGroupe'=>$user->getNomGroupe(), 'reservation'=>$reservation, 'user'=>$user));
                }
                else
                {
                    $message = "Vous n'avez pas accès à ce devis.";
                    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'message'=>$message, 'alert'=>"danger"));
                }
            }

        break;

        case "prix-devis":

            if($_SESSION['role'] == "gerant")
            {
                echo $twig->render('prix_devis.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user));
            }
            connectYourself($twig);

        break;

        case "liste-activite":

            if($_SESSION['role'] == "gerant")
            {
                $activites = $activiteManager->getActivites('liste-activite');
                echo $twig->render('liste_activite.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'activitesAffichees'=>$activites[0], 'activitesCachees'=>$activites[1], 'user'=>$user));
            }
            connectYourself($twig);

        break;

        case "ajouter-activite":

            if(isset($_POST['ajout-activite']))
            {
                $activite = new Activite($_POST);
                if($activite->getDescriptionActivite() == '')
                {
                    $activite->setDescriptionActivite(NULL);
                }
                $ok = $activiteManager->addActivite($activite);
                if($ok)
                {
                    $message = "Activité ajoutée avec succès!";
                    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'],'message'=>$message, 'alert'=>"success", 'user'=>$user));
                }
                else
                {
                    $message = "Erreur lors de l'ajout de l'activité";
                    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'],'message'=>$message, 'alert'=>"danger", 'user'=>$user));
                }
            }
            else
            {
                connectYourself($twig);
                if($_SESSION['role'] == "gerant")
                {
                    echo $twig->render('ajouter_activite.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user));
                }
            }

        break;

        case "modifier-activite":

            // Action du bouton Sauvegarder sur la page de modification des activités
            if(isset($_POST['modification-activite']))
            {
                $activite = new Activite($_POST);
                if($activite->getDescriptionActivite() == '')
                {
                    $activite->setDescriptionActivite(NULL);
                }
                $ok = $activiteManager->updateActivite($activite);
                if($ok)
                {
                    $message = "Modification de l'activité effectuée avec succès!";
                    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"success", 'user'=>$user));
                }
                else
                {
                    $message = "Erreur lors de l'enregistrement de l'activité";
                    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"danger", 'user'=>$user));
                }
            }
            else
            {
                connectYourself($twig);
                if($_SESSION['role'] == "gerant")
                {
                    if(isset($_GET['activite']))
                    {
                        $activite = $activiteManager->getActiviteById($_GET['activite']);
                        if(!$activite)
                        {
                            $message = "L'activité n'existe pas.";
                            echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'],'message'=>$message, 'alert'=>"warning", 'user'=>$user));
                        }
                        else
                        {
                            echo $twig->render('modification_activite.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'activite'=>$activite, 'user'=>$user));
                        }
                    }
                    else
                    {
                        $message = "Activité non trouvée.";
                        echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'],'message'=>$message, 'alert'=>"warning", 'user'=>$user));
                    }
                }
            }

        break;

        case "liste-option":

            if($_SESSION['role'] == "gerant")
            {
                $options = $optionManager->getOptions("modifier-option");
                echo $twig->render('liste_option.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'optionsAffichees'=>$options[0], 'optionsCachees'=>$options[1], 'user'=>$user));
            }
            connectYourself($twig);

        break;

        case "ajouter-option":

            if(isset($_POST['ajout-option']))
            {
                $option = new Option($_POST);
                $ok = $optionManager->addOption($option);
                if($ok)
                {
                    $message = "Option ajoutée avec succès!";
                    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"success", 'user'=>$user));
                }
                else
                {
                    $message = "Erreur lors de l'ajout de l'option";
                    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"danger", 'user'=>$user));
                }
            }
            else
            {
                connectYourself($twig);
                if($_SESSION['role'] == "gerant")
                {
                    echo $twig->render('ajouter_option.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user));
                }
            }

        break;

        case "modifier-option":

            // Action du bouton Sauvegarder sur la page de modification des options
            if(isset($_POST['modification-option']))
            {
                $option = new Option($_POST);
                $ok = $optionManager->updateOption($option);
                if($ok)
                {
                    $message = "Modification de l'option effectuée avec succès!";
                    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"success", 'user'=>$user));
                }
                else
                {
                    $message = "Erreur lors de l'enregistrement de l'option";
                    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"danger", 'user'=>$user));
                }
            }
            else
            {
                connectYourself($twig);
                if($_SESSION['role'] == "gerant")
                {
                    if(isset($_GET['option']))
                    {
                        $option = $optionManager->getOptionById($_GET['option']);
                        if(!$option)
                        {
                            $message = "L'option n'existe pas.";
                            echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"warning", 'user'=>$user));
                        }
                        else
                        {
                            echo $twig->render('modification_option.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'option'=>$option, 'user'=>$user));
                        }
                    }
                    else
                    {
                        $message = "Option non trouvée.";
                        echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'alert'=>"warning", 'user'=>$user));
                    }
                }
            }

        break;

        case "infos-reservation" :

            if($_SESSION['role'] == "gerant")
            {
                if(isset($_POST['confirmer']))
                {
                    $client = $UtilisateurManager->getProfilFromReservation($_GET['reservation']);
                    // $reservationManager->confirmerReservation($_GET['reservation'], $_POST, $client);
                    $reservationManager->confirmerReservationSansHebergement($_GET['reservation'], $client);
                    $reservationManager->envoiMailConfirmation($client->getMail());
                    // Création du dossier client
                    $idReservation = $_GET['reservation'];
                    creerDossierClient($idReservation);
                    // Création de la ligne en bdd
                    $UtilisateurManager->dossierClientBdd($idReservation);
    
                    // On "re-récupère" le client pour avoir son statut actualisé !!
                    $client = $UtilisateurManager->getProfilFromReservation($_GET['reservation']);
                    $reservation = $reservationManager->getReservationFromIdReserv($_GET['reservation']);
                    $devis = $devisManager->getDevisFromIdReservation($reservation->getIdReservation());
                    $nbreDevis = $devisManager->getCountDevis($client->getId());
                    $allDevis = $devisManager->getDevisFromUser($client->getId());
                    echo $twig->render('infos_reservation.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'devis'=>$devis, 'reservation'=>$reservation, 'confirmation'=>'oui'));
                }
                elseif(isset($_GET['reservation']) && !isset($_POST['confirmer']))
                {
                    $reservation = $reservationManager->getReservationFromIdReserv($_GET['reservation']);
                    if(!$reservation)
                    {
                        $message = "La réservation n'existe pas.";
                        echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'message'=>$message, 'alert'=>"warning"));
                    }
                    else
                    {
                        $devis = $devisManager->getDevisFromIdReservation($reservation->getIdReservation());
                        $client = $UtilisateurManager->getProfilFromReservation($_GET['reservation']);
                        $nbreDevis = $devisManager->getCountDevis($client->getId());
                        $allDevis = $devisManager->getDevisFromUser($client->getId());
                        // Si la réservation n'a pas été confirmée
                        if($reservation->getStatut() == 0)
                        {
                            echo $twig->render('infos_reservation.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'devis'=>$devis, 'reservation'=>$reservation, 'confirmation'=>'non'));
                        }
                        // Si la réservation a été confirmée
                        elseif($reservation->getStatut() == 1) 
                        {
                            // $hebergement = $reservationManager->getHebergement($reservation->getIdReservation());
                            $documentClient = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                            $documentsAnnexes = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
                            echo $twig->render('infos_reservation.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'devis'=>$devis, 'reservation'=>$reservation, /* 'hebergement'=>$hebergement, */ 'documentClient'=>$documentClient, 'documentsAnnexes'=>$documentsAnnexes, 'confirmation'=>'non'));
                        }    
                    }
                }
            }
            connectYourself($twig);

        break;

        case "modifier-dates":

            if(isset($_POST['modifier-dates']))
            {
                $ok = $reservationManager->modifierDatesReservation($_POST['idReservation'], $_POST['DATE_DEBUT'], $_POST['DATE_FIN']);
                if($ok)
                {
                    $message = "Les dates ont bien été modifiées.";
                    $reservation = $reservationManager->getReservationFromIdReserv($_GET['reservation']);
                    echo $twig->render("modifier_dates.html.twig", array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'reservation'=>$reservation, 'message'=>$message, 'succes'=>"oui"));
                }
                else
                {
                    $message = "Erreur : les dates n'ont pas pu être modifiées. Veuillez réessayer.";
                    echo $twig->render("modifier_dates.html.twig", array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'reservation'=>$reservation, 'message'=>$message, 'succes'=>"non"));
                }
            }
            elseif($_SESSION['role'] == "gerant")
            {
                $reservation = $reservationManager->getReservationFromIdReserv($_GET['reservation']);
                echo $twig->render("modifier_dates.html.twig", array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'reservation'=>$reservation));
            }
            connectYourself($twig);

        break;

        case "aperçu" :

            if($_SESSION['role'] == "gerant")
            {
                $clients1 = $UtilisateurManager->getUsersFromStatut("1");
                $clients2 = $UtilisateurManager->getUsersFromStatut("2");
                $clients3 = $UtilisateurManager->getUsersFromStatut("3");
                $clients4 = $UtilisateurManager->getUsersFromStatut("4");
                $clients5 = $UtilisateurManager->getUsersFromStatut("5");
                $clients6 = $UtilisateurManager->getUsersFromStatut("6");
                $clients7 = $UtilisateurManager->getUsersFromStatut("7");
                $clients8 = $UtilisateurManager->getUsersFromStatut("8");
                echo $twig->render('aperçu_clients.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'clients1'=>$clients1, 'clients2'=>$clients2, 'clients3'=>$clients3, 'clients4'=>$clients4, 'clients5'=>$clients5, 'clients6'=>$clients6, 'clients7'=>$clients7, 'clients8'=>$clients8, 'yes'=>'yes', 'user'=>$user));
            }
            connectYourself($twig);
            
        break;

        case "ajouter-client" :

            if($_SESSION['role'] == "gerant")
            {
                $gerant = $UtilisateurManager->getProfil($_SESSION['id']);
                if(isset($_POST['ajouter-client']) && !empty($_POST['prenom']) && !empty($_POST['nom']) && !empty($_POST['nom_organisme']) && !empty($_POST['type_groupe']) && !empty($_POST['email']) && !empty($_POST['telephone']) && !empty($_POST['nombre_personnes']) && !empty($_POST['nombre_nuits']) && !empty($_POST['moyen']) && !empty($_POST['statut']))
                {
                    $prospect = $UtilisateurManager->verificationEmail($_POST['email']);
                    if($prospect != false)
                    {
                        $message = "Un utilisateur avec cette adresse email existe déjà.";
                        echo $twig->render('ajouter_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'prenom'=>$gerant->getPrenom(), 'message'=>$message, 'user'=>$user));
                    }
                    else
                    {
                        $ok = $UtilisateurManager->ajouterProspect($_POST['prenom'], $_POST['nom'], $_POST['nom_organisme'], $_POST['type_groupe'], $_POST['email'], $_POST['telephone'], $_POST['nombre_personnes'], $_POST['nombre_nuits'], $_POST['moyen'], $_POST['statut']);
                        if($ok)
                        {
                            $client = $UtilisateurManager->getProfilFromEmail($_POST['email']);
    
                            // Envoi mail de notification au client
                            date_default_timezone_set('Europe/Paris');
    
                            $objet = "Application du Boucalais : ouverture de votre compte par le gérant";
                            $mail = "
                            <html>
                            <body>
                                Bonjour,<br><br>
                            
                                Le gérant du centre vient de commencer votre inscription sur l'application du Boucalais. Pour la finaliser, veuillez vous inscrire sur le lien suivant en utilisant l'adresse avec laquelle vous consultez cet email (rappel : ".$client->getMail().") : <a href='http://leboucalais.fr/application/?action=inscription'>http://leboucalais.fr/application/?action=inscription</a>. Dans le cas contraire, votre inscription sera refusée.<br>
                                L'application vous mettra ensuite en relation avec le gérant du centre pour continuer vos démarches de réservation.<br><br>
    
                                A bientôt au <a href='http://leboucalais.fr' target='_blank'>Boucalais</a> !
                            </body>
                            </html>";
                            $destinataire = $client->getMail();
                            $headers = "Content-Type: text/html; charset=\"utf-8\"\n";
                            $headers .= "MIME-Version: 1.0\n";
                            $headers .= "Date: " . date(DateTime::RFC2822) . "\n";
                            $headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
                            $headers .= "Reply-To: contact@leboucalais.fr";
    
                            mail($destinataire, $objet, $mail, $headers);
    
                            $nbreDevis = $devisManager->getCountDevis($client->getId());
                            if($client->getStatut() == 4)
                            {
                                $message = "Le client <strong>" . $client->getNomGroupe() . "</strong> et sa demande de réservation ont bien été ajoutés.";
                                $nbreDevis = $devisManager->getCountDevis($client->getId());
                                $reservations = $reservationManager->getReservationsFromUser($client->getId());
                                echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'reservations'=>$reservations, 'message'=>$message, 'succes'=>"oui",));
                            }
                            elseif($client->getStatut() == 5 || $client->getStatut() == 6 || $client->getStatut() == 7 || $client->getStatut() == 8)
                            {
                                $reservations = $reservationManager->getReservationsFromUser($client->getId());

                                // Création du dossier client
                                $idReservation = $reservations[0]->getIdReservation();
                                creerDossierClient($idReservation);
                                // Création de la ligne en bdd
                                $UtilisateurManager->dossierClientBdd($idReservation);

                                foreach($reservations as $reservation)
                                {
                                    $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                                    // $documentsAnnexes = tableau qui contient un tableau de documents annexes pour chaque réservation (= tableau dans un tableau)
                                    $documentssAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
                                }

                                $message = "Le client <strong>" . $client->getNomGroupe() . "</strong> et sa réservation ont bien été ajoutés.";
                                $nbreDevis = $devisManager->getCountDevis($client->getId());
                                echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'reservations'=>$reservations, 'documentsClient'=>$documentsClient, 'documentssAnnexes'=>$documentssAnnexes, 'message'=>$message, 'succes'=>"oui",));
                            }
                            else
                            {
                                $message = "Le client <strong>" . $client->getNomGroupe() . "</strong> a bien été ajouté.";
                                echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'message'=>$message, 'succes'=>"oui", 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user));
                            }
                        }
                        else
                        {
                            $message = "Le client n'a pu être ajouté.";
                            echo $twig->render('ajouter_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'prenom'=>$gerant->getPrenom(), 'message'=>$message, 'succes'=>"non", 'user'=>$user));
                        }
                    }
                }
                else
                {
                    echo $twig->render('ajouter_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'prenom'=>$gerant->getPrenom(), 'user'=>$user));
                }
            }
            connectYourself($twig);

        break;

        case "attribuer-devis":

            if($_SESSION['role'] == "gerant")
            {
                // Stockage des données du devis dans des variables sessions
                if(isset($_POST['commande']))
                {
                    $_SESSION['devis'] = $_POST;
                    $_SESSION['post']['ACTIVITES'] = $_POST['ACTIVITES'];
                    $_SESSION['post']['ACTIVITE_SEANCES'] = $_POST['ACTIVITE_SEANCES'];
                    $_SESSION['post']['ACTIVITE_PARTICIPANTS'] = $_POST['ACTIVITE_PARTICIPANTS'];
                    $_SESSION['post']['PRIX_ACTIVITE'] = $_POST['PRIX_ACTIVITE'];
                    $_SESSION['post']['OPTIONS'] = $_POST['OPTIONS'];
                    $_SESSION['post']['PRIX_OPTION'] = $_POST['PRIX_OPTION'];
                    $clients = $UtilisateurManager->getListeClientsOrderBy("DATE_INSCRIPTION DESC");
                    echo $twig->render('attribuer_devis.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'clients'=>$clients));
                }
                // Enregistrement du devis et attribution de celui-ci au client sélectionné
                elseif(isset($_POST['attribuer-devis']))
                {
                    $devis = new Devis($_SESSION['devis']);
                    $client = $UtilisateurManager->getProfil($_POST['client']);
                    $ok = $devisManager->add($devis, $_SESSION['post'], $client);
                    if($ok)
                    {
                        $message = "Le devis a bien été enregistré et attribué au groupe " . $client->getNomGroupe() . ".";
                        echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'message'=>$message, 'alert'=>"succes"));
                    }
                    else
                    {
                        $message = "Le devis n'a pu être enregistré. Veuillez réessayer.";
                        echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'message'=>$message, 'alert'=>"warning"));
                    }
                }
                else
                {
                    connectYourself($twig);
                    $user = $UtilisateurManager->getProfil($_SESSION['id']);
                    $activites = $activiteManager->getActivites('form-devis');
                    $options = $optionManager->getOptions("form-devis");
                    $option3 = $optionManager->getOptionById(3);
                    echo $twig->render('formulaire_devis.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'activites'=>$activites, 'options'=>$options, 'option3'=>$option3, 'client'=>$user, 'user'=>$user));
                }
            }

        break;

        case "clients" :

            if($_SESSION['role'] == "gerant")
            {
                $clients = $UtilisateurManager->getListeClients();
                echo $twig->render('clients.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'clients'=>$clients, 'user'=>$user)) ;
            }
            connectYourself($twig);

        break;

        case "fiche-client" :
            
            if($_SESSION['role'] == "gerant")
            {
                $client = $UtilisateurManager->getProfil($_GET['id']);
                if($client != false)
                {
                    if(isset($_POST['supprimer-compte-client']))
                    {
                        $ok = $UtilisateurManager->deleteProfile($_GET['id'], dirname(__FILE__), $client, 1);
                        if($ok)
                        {
                            $message = "Le compte client <strong>" . $client->getNomGroupe() ."</strong> a bien été supprimé.";
                            echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'message'=>$message, 'alert'=>"success"));
                        }
                        else
                        {
                            $message = "Le compte client n'a pu être supprimé.";
                            $nbreDevis = $devisManager->getCountDevis($_GET['id']);
                            $allDevis = $devisManager->getDevisFromUser($_GET['id']);
                            $reservations = $reservationManager->getReservationsFromUser($_GET['id']);
                            if($reservations != false && $client->getStatut() < 5)
                            {
                                echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'reservations'=>$reservations, 'message'=>$message, "succes"=>"non"));
                            }
                            elseif($reservations != false && $client->getStatut() >= 5)
                            {
                                foreach($reservations as $reservation)
                                {
                                    $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                                    // $documentsAnnexes = tableau qui contient un tableau de documents annexes pour chaque réservation (= tableau dans un tableau)
                                    $documentssAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
                                }
                                echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'reservations'=>$reservations, 'documentsClient'=>$documentsClient, 'documentssAnnexes'=>$documentssAnnexes, 'message'=>$message, "succes"=>"non"));
                            }
                            else
                            {
                                echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'message'=>$message, "succes"=>"non"));
                            }    
                        }
                    }
                    // Confirmation de la réservation d'un utilisateur rajouté par le gérant avec le statut 4
                    elseif(isset($_POST['confirmer']))
                    {
                        $client = $UtilisateurManager->getProfilFromReservation($_GET['reservation']);
                        // $reservationManager->confirmerReservation($_GET['reservation'], $_POST, $client);
                        $reservationManager->confirmerReservationSansHebergement($_GET['reservation'], $client);
                        $reservationManager->envoiMailConfirmation($client->getMail());
                        // Création du dossier client
                        $idReservation = $_GET['reservation'];
                        creerDossierClient($idReservation);
                        // Création de la ligne en bdd
                        $UtilisateurManager->dossierClientBdd($idReservation);
        
                        // On "re-récupère" le client pour avoir son statut actualisé !!
                        $client = $UtilisateurManager->getProfilFromReservation($_GET['reservation']);
                        $nbreDevis = $devisManager->getCountDevis($_GET['id']);
                        $reservations = $reservationManager->getReservationsFromUser($_GET['id']);
                        $message = "La réservation a bien été confirmée.";
                        $reservationsConfirmed = $reservationManager->getReservationsConfirmedFromUser($_GET['id']);
                        foreach($reservationsConfirmed as $reservation)
                        {
                            // $hebergement = $reservationManager->getHebergement($reservation->getIdReservation());
                            $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                            // $documentsAnnexes = tableau qui contient un tableau de documents annexes pour chaque réservation (= tableau dans un tableau)
                            $documentssAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
                        }
                        echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'reservations'=>$reservations, /* 'hebergement'=>$hebergement, */ 'documentsClient'=>$documentsClient, 'documentssAnnexes'=>$documentssAnnexes, 'message'=>$message, 'succes'=>'oui'));
                    }
                    elseif(isset($_POST['convention']))
                    {
                        if(!empty($_FILES['file']['size'] != 0))
                        {
                            televerserDocumentsClient("convention", $DocumentsClientManager, $devisManager, $reservationManager, $twig, $_POST['idReservation'], $client, $user);
                        }
                    }
                    elseif(isset($_POST['facture-acompte']))
                    {
                        if(!empty($_FILES['file']['size'] != 0))
                        {
                            televerserDocumentsClient("facture_acompte", $DocumentsClientManager, $devisManager, $reservationManager, $twig, $_POST['idReservation'], $client, $user);
                        }
                    }
                    elseif(isset($_POST['documents-annexes']))
                    {
                        if(!empty($_POST['nom_fichier']) && $_FILES['file']['size'] != 0)
                        {
                            televerserDocumentsClient("documents-annexes", $DocumentsClientManager, $devisManager, $reservationManager, $twig, $_POST['idReservation'], $client, $user);
                        }
                    }
                    elseif(isset($_POST['plan-chambres']))
                    {
                        if(!empty($_FILES['file']['size'] != 0))
                        {
                            televerserDocumentsClient("plan_chambres", $DocumentsClientManager, $devisManager, $reservationManager, $twig, $_POST['idReservation'], $client, $user);
                        }
                    }
                    elseif(isset($_POST['planning-activites']))
                    {
                        if(!empty($_FILES['file']['size'] != 0))
                        {
                            televerserDocumentsClient("planning_activites", $DocumentsClientManager, $devisManager, $reservationManager, $twig, $_POST['idReservation'], $client, $user);
                        }
                    }
                    elseif(isset($_POST['menus']))
                    {
                        if(!empty($_FILES['file']['size'] != 0))
                        {
                            televerserDocumentsClient("menus", $DocumentsClientManager, $devisManager, $reservationManager, $twig, $_POST['idReservation'], $client, $user);
                        }
                    }
                    elseif(isset($_GET['id']))
                    {
                        $nbreDevis = $devisManager->getCountDevis($_GET['id']);
                        $allDevis = $devisManager->getDevisFromUser($_GET['id']);
                        $reservations = $reservationManager->getReservationsFromUser($_GET['id']);
                        if($reservations != false && $client->getStatut() < 5)
                        {
                            echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'reservations'=>$reservations));
                        }
                        elseif($reservations != false && $client->getStatut() >= 5)
                        {
                            $reservationsConfirmed = $reservationManager->getReservationsConfirmedFromUser($_GET['id']);
                            foreach($reservationsConfirmed as $reservation)
                            {
                                // $hebergement = $reservationManager->getHebergement($reservation->getIdReservation());
                                $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                                // $documentsAnnexes = tableau qui contient un tableau de documents annexes pour chaque réservation (= tableau dans un tableau)
                                $documentssAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
                            }
                            echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'reservations'=>$reservations, /* 'hebergement'=>$hebergement, */ 'documentsClient'=>$documentsClient, 'documentssAnnexes'=>$documentssAnnexes));
                        }
                        else
                        {
                            echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis));
                        }
                    }
                }
                else
                {
                    $message = "Cet utilisateur n'existe pas.";
                    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'message'=>$message, 'alert'=>"warning"));
                }
            }
            connectYourself($twig);

        break;

        case "convention-facture-acompte" :

            if(isset($_POST['convention-signee']))
            {
                if($_FILES['file']['size'] != 0)
                {
                    televerserDocumentsParClient("convention_signee", $DocumentsClientManager, $reservationManager, $twig, $_POST['idReservation'], $user, $user);
                }
            }
            elseif(isset($_POST['documents-annexes']))
            {
                if(!empty($_POST['nom_fichier']) && $_FILES['file']['size'] != 0)
                {
                    televerserDocumentsParClient("documents-annexes", $DocumentsClientManager, $reservationManager, $twig, $_POST['idReservation'], $user, $user);
                }
            }
            else
            {
                connectYourself($twig);
                $reservations = $reservationManager->getReservationsFromUser($_SESSION['id']);
                if($reservations != false)
                {
                    foreach($reservations as $reservation)
                    {
                        if($reservation->getStatut() == 1)
                        {
                            $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                            $documentssAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
                        }
                    }
                    if(isset($documentsClient))
                    {
                        echo $twig->render('convention_facture_acompte.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'reservations'=>$reservations, 'documentsClient'=>$documentsClient, 'documentssAnnexes'=>$documentssAnnexes));
                    }
                    // Si la réservation n'a pas encore été confirmée
                    else
                    {
                        echo $twig->render('convention_facture_acompte.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'reservations'=>$reservations,));
                    }
                }
                else
                {
                    echo $twig->render('convention_facture_acompte.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'reservations'=>$reservations,));
                }
            }

        break;

        case "documents-administratifs" :

            if($_SESSION['role'] == "prospect")
            {
                $utilisateur = $UtilisateurManager->getProfil($_SESSION['id']);
                $fichiers = $FichierManager->getListeFichiersClient("documents-administratifs");
                echo $twig->render('documents_administratifs.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'statut'=>$utilisateur->getStatut(), 'fichiers'=>$fichiers, 'user'=>$user));
            }
            connectYourself($twig);

        break;
        
        case "pack-sejour" :
            
            if($_SESSION['role'] == "prospect")
            {
                $fichiers = $FichierManager->getListeFichiersClient("pack-sejour");
                $reservations = $reservationManager->getReservationsFromUser($_SESSION['id']);
                if($reservations != false)
                {
                    foreach($reservations as $reservation)
                    {
                        if($reservation->getStatut() == 1)
                        {
                            $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                            $documentssAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
                        }
                    }
                    if(isset($documentsClient))
                    {
                        echo $twig->render('pack_sejour.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'fichiers'=>$fichiers, 'reservations'=>$reservations, 'documentsClient'=>$documentsClient, 'user'=>$user));
                    }
                        // Si la réservation n'a pas encore été confirmée
                    else
                    {
                        echo $twig->render('pack_sejour.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'reservations'=>$reservations, 'fichiers'=>$fichiers));
                    }    
                }
                else
                {
                    echo $twig->render('pack_sejour.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'reservations'=>$reservations, 'fichiers'=>$fichiers));
                }
            }
            connectYourself($twig);
            
        break;
        
        case "ressources-documentaires" :

            if($_SESSION['role'] == "prospect")
            {
                $utilisateur = $UtilisateurManager->getProfil($_SESSION['id']);
                $fichiers = $FichierManager->getListeFichiersClient("ressources-documentaires");
                echo $twig->render('ressources_documentaires.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'statut'=>$utilisateur->getStatut(), 'fichiers'=>$fichiers, 'user'=>$user));
            }
            connectYourself($twig);

        break;
        
        case "documents-gerant" :

            if($_SESSION['role'] == "gerant")
            {
                if(isset($_POST['gerer-ressources-documentaires']))
                {
                    gerer($FichierManager, $twig, $user);
                }
                elseif(isset($_POST['gerer-documents-administratifs']))
                {
                    gerer($FichierManager, $twig, $user);
                }
                elseif(isset($_POST['gerer-pack-sejour']))
                {
                    gerer($FichierManager, $twig, $user);
                }
                elseif(isset($_POST['televerser-ressources-documentaires']))
                {
                    televerserDocuments("ressources-documentaires", $FichierManager, $twig, $user);
                }
                elseif(isset($_POST['televerser-documents-administratifs']))
                {
                    televerserDocuments("documents-administratifs", $FichierManager, $twig, $user);
                }
                elseif(isset($_POST['televerser-pack-sejour']))
                {
                    televerserDocuments("pack-sejour", $FichierManager, $twig, $user);
                }
                else
                {
                    $ressources_documentaires = $FichierManager->getListeFichiersGerant("ressources-documentaires");
                    $documents_administratifs = $FichierManager->getListeFichiersGerant("documents-administratifs");
                    $pack_sejour = $FichierManager->getListeFichiersGerant("pack-sejour");
                    echo $twig->render('documents_gerant.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'ressources_documentaires'=>$ressources_documentaires, 'documents_administratifs'=>$documents_administratifs, 'pack_sejour'=>$pack_sejour, 'user'=>$user));
                }
            }
            connectYourself($twig);
            
        break;

        // Déconnexion
        case "logout" :

            $_SESSION['acces'] = "non";
            unset($_SESSION['id']);
            $_SESSION['role'] = "";
            $message = "Vous vous êtes déconnecté";
            echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'message'=>$message, 'logout'=>"yes", 'alert'=>"info")); 

        break;

        default:

            $message = "Cette page n'existe pas.";
            echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'message'=>$message, 'alert'=>"warning"));
    }
}
// Cas par défaut = page d'accueil si l'utilisateur n'est pas connecté
elseif(!isset($_GET["action"]) && empty($_POST) && $_SESSION['acces'] == "non")
{
    echo $twig->render('index.html.twig', array('acces'=> $_SESSION['acces']));
}
// Page d'accueil pour les personnes connectées sans paramètre dans l'url
elseif(!isset($_GET["action"]) && empty($_POST) && $_SESSION['acces'] == "oui")
{
    if($_SESSION['role'] == "prospect")
    {
        echo $twig->render('accueil_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$user, 'user'=>$user));
    }
    elseif($_SESSION['role'] == "gerant")
    {
        echo $twig->render('accueil_gerant.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user));
    }
}


/**
 * Crée le dossier (sur le serveur) pour un client et crée la ligne relative en bdd (LE_BOUCALAIS_DOCUMENTS_CLIENT)
 * @param idReservation
 */
function creerDossierClient($idReservation)
{
    // $nomDossier = strtolower($idReservation); // on passe la chaîne de caractère en minuscule
    // $nomDossier = strtr($nomDossier, "àäåâôöîïûüéè", "aaaaooiiuuee"); // on remplace les accents
    // $nomDossier = str_replace(' ', '-', $nomDossier); // on remplace les espaces par des tirets

    // On définit la fonction sur index.php car ce fichier est disposé à la racine du fichier application
    // Nom du dossier = identificant réservation
    mkdir(dirname(__FILE__) . "/Documents/documents-clients/" . $idReservation, 0755);
}

/**
 * Gère les documents administratifs, les ressources documentaires et le pack séjour pour le gérant
 * @param FichierManager instance de la classe FichierManager
 * @param twig
 * @param user instance de la classe Utilisateur = profil de l'utilisateur
 */
function gerer($FichierManager, $twig, $user)
{
    if($_POST['action'] == 'afficher')
    {
        $FichierManager->afficherFichiers($_POST['fichiers']);
    }
    elseif($_POST['action'] == 'cacher')
    {
        $FichierManager->cacherFichiers($_POST['fichiers']);
    }
    elseif($_POST['action'] == 'supprimer')
    {
        foreach($_POST['fichiers'] as $chemin)
        {
            unlink($chemin);
            $FichierManager->supprimerFichiers($_POST['fichiers']);
        }
    }
    $ressources_documentaires = $FichierManager->getListeFichiersGerant("ressources-documentaires");
    $documents_administratifs = $FichierManager->getListeFichiersGerant("documents-administratifs");
    $pack_sejour = $FichierManager->getListeFichiersGerant("pack-sejour");    
    echo $twig->render('documents_gerant.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'ressources_documentaires'=>$ressources_documentaires, 'documents_administratifs'=>$documents_administratifs, 'pack_sejour'=>$pack_sejour, 'user'=>$user));
}

/**
 * Téléverse des documents pour documents administratifs, ressources documentaires et pack séjour côté client
 * @param dossierCible documents-administratifs, ressources-documentaires ou pack-sejour
 * @param FichierManager instance de la classe FichierManager
 * @param twig
 * @param user instance de la classe Utilisateur = profil de l'utilisateur
 */
function televerserDocuments($dossierCible, $FichierManager, $twig, $user)
{
    // Le tableau $_FILES contient un tableau (qui contient les infos du fichier) associé à une clé de colonne unique : l'attribut name du champ input → ['tmp_name'], ['name'], ['size'], ['type']
    // pathinfo() retourne des informations sur le chemin + fichier → ['dirname'], ['basename'], ['extension'], ['filename']
    //var_dump($_FILES['file']['name']);

    $nomOrigine = $_FILES['file']['name'];
    $infosChemin = pathinfo($nomOrigine);
    $extensionFichier = $infosChemin['extension'];
    $extensionsAutorisees = array("jpeg", "jpg", "pdf", "JPG", "png", "PNG", "pptx", "ppsx", "xls", "xlsx", "pub", "mp4", "mp3", "docx");
    if(!(in_array($extensionFichier, $extensionsAutorisees)))
    {
        $message = "Le fichier n'a pas l'extension attendue. Sont uniquement autorisées : pdf, jpeg, jpg, JPG, png, PNG, pptx, ppsx, xls, xlsx, pub, mp4, mp3 et docx";
        $ressources_documentaires = $FichierManager->getListeFichiersGerant("ressources-documentaires");
        $documents_administratifs = $FichierManager->getListeFichiersGerant("documents-administratifs");
        $pack_sejour = $FichierManager->getListeFichiersGerant("pack-sejour");    
        echo $twig->render('documents_gerant.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'ressources_documentaires'=>$ressources_documentaires, 'documents_administratifs'=>$documents_administratifs, 'pack_sejour'=>$pack_sejour, 'message'=>$message, 'succes'=>'non', 'user'=>$user));
    }
    else
    {    
        // On récupère le chemin du fichier php courant
        $repertoireDestination = dirname(__FILE__) . "/Documents/" . $dossierCible . "/";
        $nomFichier = strtolower($infosChemin['filename']); // on passe la chaîne de caractère en minuscule
        $table = array('à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u');
        $nomFichier = strtr($nomFichier, $table); // on remplace les accents
        $nomFichier = str_replace(' ', '-', $nomFichier); // on remplace les espaces par des tirets
        $nomFichier = $nomFichier . "." . $extensionFichier;
        
        // Si le fichier de destination existe déjà, il sera écrasé ! YES, that's what we want
        if(move_uploaded_file($_FILES["file"]["tmp_name"], $repertoireDestination.$nomFichier))
        {
            $message =  "Le fichier " . $_FILES['file']['name'] . " a bien été téléversé";
            $fichier = $FichierManager->fichierExiste($_POST['nom_fichier']);
            // Si le fichier n'existe pas déjà en base de données, alors on l'insère dedans, sinon on le téléverse juste
            if(!$fichier)
            {
                $FichierManager->ajouterFichier($_POST['nom_fichier'], "http://leboucalais.fr/application/Documents/" . $dossierCible . "/" . $nomOrigine, $repertoireDestination.$nomFichier, $dossierCible);
            }
            $ressources_documentaires = $FichierManager->getListeFichiersGerant("ressources-documentaires");
            $documents_administratifs = $FichierManager->getListeFichiersGerant("documents-administratifs");
            $pack_sejour = $FichierManager->getListeFichiersGerant("pack-sejour");
            echo $twig->render('documents_gerant.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'ressources_documentaires'=>$ressources_documentaires, 'documents_administratifs'=>$documents_administratifs, 'pack_sejour'=>$pack_sejour, 'message'=>$message, 'succes'=>'oui', 'user'=>$user));
        }
        else
        {
            $message = "Le fichier n'a pas été téléversé. Vérifiez l'existence du répertoire " . $repertoireDestination;
            $ressources_documentaires = $FichierManager->getListeFichiersGerant("ressources-documentaires");
            $documents_administratifs = $FichierManager->getListeFichiersGerant("documents-administratifs");
            $pack_sejour = $FichierManager->getListeFichiersGerant("pack-sejour");
            echo $twig->render('documents_gerant.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'],'ressources_documentaires'=>$ressources_documentaires, 'documents_administratifs'=>$documents_administratifs, 'pack_sejour'=>$pack_sejour, 'message'=>$message, 'succes'=>'non', 'user'=>$user));
        }
    }
}

/**
 * Téléverse des documents propres à un client : convention, facture d'acompte, document annexe, menus, planning des activités
 * @param type type du document téléversé
 * @param DocumentsClientManager
 * @param devisManager
 * @param reservationManager
 * @param twig
 * @param idReservation
 * @param client instance de la classe Utilisateur, correspond au client pour lequel on téléverse un fichier
 * @param user instance de la classe Utilisateur, correspond à la personne connectée (ici le gérant)
 */
function televerserDocumentsClient($type, $DocumentsClientManager, $devisManager, $reservationManager, $twig, $idReservation, $client, $user)
{
    $nomOrigine = $_FILES['file']['name'];
    $infosChemin = pathinfo($nomOrigine);
    $extensionFichier = $infosChemin['extension'];
    $extensionsAutorisees = array("jpeg", "jpg", "pdf", "JPG", "png", "PNG", "pptx", "ppsx", "xls", "xlsx", "pub", "mp4", "mp3", "docx");
    if(!(in_array($extensionFichier, $extensionsAutorisees)))
    {
        $message = "Le fichier n'a pas l'extension attendue. Sont uniquement autorisées : pdf, jpeg, jpg, JPG, png, PNG, pptx, ppsx, xls, xlsx, pub, mp4, mp3 et docx";
        $nbreDevis = $devisManager->getCountDevis($_GET['id']);
        $allDevis = $devisManager->getDevisFromUser($_GET['id']);
        $reservations = $reservationManager->getReservationsFromUser($_GET['id']);
        if($reservations != false && $client->getStatut() < 5)
        {
            echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'message'=>$message, 'succes'=>'non'));
        }
        elseif($reservations != false && $client->getStatut() >= 5)
        {
            foreach($reservations as $reservation)
            {
                $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                $documentsAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
            }
            echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'reservations'=>$reservations, 'documentsClient'=>$documentsClient, 'documentsAnnexes'=>$documentsAnnexes, 'message'=>$message, 'succes'=>"non"));
        }
    }
    else
    {
        // On récupère le chemin du fichier php courant
        $repertoireDestination = dirname(__FILE__) . "/Documents/documents-clients/" . $idReservation . "/";
        // On laisse le même nom de fichier que celui mis par François sauf qu'on met tout en minuscule, on enlève les accents et on remplace les espaces par des tirets
        $nomFichier = strtolower($infosChemin['filename']); // on passe la chaîne de caractère en minuscule
        $table = array('à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u');
        $nomFichier = strtr($nomFichier, $table); // on remplace les accents
        $nomFichier = str_replace(' ', '-', $nomFichier); // on remplace les espaces par des tirets
        $nomFichier = $nomFichier . "." . $extensionFichier;

        // Si le fichier de destination existe déjà, il sera écrasé ! YES, that's what we want
        if(move_uploaded_file($_FILES["file"]["tmp_name"], $repertoireDestination.$nomFichier))
        {
            $message =  "Le fichier " . $_FILES['file']['name'] . " a bien été téléversé";
            $colonne = $type . "_chemin";
            if($type == "documents-annexes")
            {
                $fichier = $DocumentsClientManager->fichierExiste($_POST['nom_fichier']);
                if(!$fichier)
                {
                    $DocumentsClientManager->ajouterDocument($type, "http://leboucalais.fr/application/Documents/documents-clients/" . $idReservation . "/" . $nomFichier, $colonne, $repertoireDestination . $nomFichier, $idReservation, $client, $user);
                }
            }
            else
            {
                $DocumentsClientManager->ajouterDocument($type, "http://leboucalais.fr/application/Documents/documents-clients/" . $idReservation . "/" . $nomFichier, $colonne, $repertoireDestination . $nomFichier, $idReservation, $client, $user);
            }
            $nbreDevis = $devisManager->getCountDevis($_GET['id']);
            $allDevis = $devisManager->getDevisFromUser($_GET['id']);
            $reservations = $reservationManager->getReservationsFromUser($_GET['id']);
            if($reservations != false && $client->getStatut() < 5)
            {
                echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'message'=>$message, 'succes'=>'oui'));
            }
            elseif($reservations != false && $client->getStatut() >= 5)
            {
                foreach($reservations as $reservation)
                {
                    $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                    $documentssAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
                }
                echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'reservations'=>$reservations, 'documentsClient'=>$documentsClient, 'documentssAnnexes'=>$documentssAnnexes, 'message'=>$message, 'succes'=>"oui"));
            }
        }
        else
        {
            $message = "Le fichier n'a pas été téléversé. Vérifiez l'existence du répertoire " . $repertoireDestination;
            $nbreDevis = $devisManager->getCountDevis($_GET['id']);
            $allDevis = $devisManager->getDevisFromUser($_GET['id']);
            $reservations = $reservationManager->getReservationsFromUser($_GET['id']);
            if($reservations != false && $client->getStatut() < 5)
            {
                echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'message'=>$message, 'succes'=>'non'));
            }
            elseif($reservations != false && $client->getStatut() >= 5)
            {
                foreach($reservations as $reservation)
                {
                    $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                    $documentssAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
                }
                echo $twig->render('fiche_client.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'client'=>$client, 'nbreDevis'=>$nbreDevis, 'user'=>$user, 'allDevis'=>$allDevis, 'reservations'=>$reservations, 'documentsClient'=>$documentsClient, 'documentssAnnexes'=>$documentssAnnexes, 'message'=>$message, 'succes'=>"non"));
            }
        }
    }
}

/**
 * Téléverse des documents par un client : convention signée ou document annexe
 * @param type type du document téléversé
 * @param DocumentsClientManager
 * @param reservationManager
 * @param twig
 * @param idReservation
 * @param client instance de la classe Utilisateur, correspond au client connecté (ici, client = user)
 * @param user instance de la classe Utilisateur, correspond à la personne connectée (ici le client)
 */
function televerserDocumentsParClient($type, $DocumentsClientManager, $reservationManager, $twig, $idReservation, $client, $user)
{
    $nomOrigine = $_FILES['file']['name'];
    $infosChemin = pathinfo($nomOrigine);
    $extensionFichier = $infosChemin['extension'];
    $extensionsAutorisees = array("jpeg", "jpg", "pdf", "JPG", "png", "PNG", "pptx", "ppsx", "xls", "xlsx", "pub", "mp4", "mp3", "docx");
    if(!(in_array($extensionFichier, $extensionsAutorisees)))
    {
        $message = "Le fichier n'a pas l'extension attendue. Sont uniquement autorisées : pdf, jpeg, jpg, JPG, png, PNG, pptx, ppsx, xls, xlsx, pub, mp4, mp3 et docx";
        $reservations = $reservationManager->getReservationsFromUser($_SESSION['id']);
        if($reservations != false && $user->getStatut() < 5)
        {
            echo $twig->render('convention_facture_acompte.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'message'=>$message, 'succes'=>'non'));
        }
        elseif($reservations != false && $user->getStatut() >= 5)
        {
            foreach($reservations as $reservation)
            {
                $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                $documentsAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
            }
            echo $twig->render('convention_facture_acompte.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'reservations'=>$reservations, 'documentsClient'=>$documentsClient, 'documentsAnnexes'=>$documentsAnnexes, 'message'=>$message, 'succes'=>"non"));
        }
    }
    else
    {
        // On récupère le chemin du fichier php courant
        $repertoireDestination = dirname(__FILE__) . "/Documents/documents-clients/" . $idReservation . "/";
        // On laisse le même nom de fichier que celui mis par François sauf qu'on met tout en minuscule, on enlève les accents et on remplace les espaces par des tirets
        $nomFichier = strtolower($infosChemin['filename']); // on passe la chaîne de caractère en minuscule
        $table = array('à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u');
        $nomFichier = strtr($nomFichier, $table); // on remplace les accents
        $nomFichier = str_replace(' ', '-', $nomFichier); // on remplace les espaces par des tirets
        $nomFichier = $nomFichier . "." . $extensionFichier;

        // Si le fichier de destination existe déjà, il sera écrasé ! YES, that's what we want
        if(move_uploaded_file($_FILES["file"]["tmp_name"], $repertoireDestination.$nomFichier))
        {
            $message =  "Le fichier " . $_FILES['file']['name'] . " a bien été téléversé";
            $colonne = $type . "_chemin";
            if($type == "documents-annexes")
            {
                $fichier = $DocumentsClientManager->fichierExiste($_POST['nom_fichier']);
                if(!$fichier)
                {
                    $DocumentsClientManager->ajouterDocument($type, "http://leboucalais.fr/application/Documents/documents-clients/" . $idReservation . "/" . $nomFichier, $colonne, $repertoireDestination . $nomFichier, $idReservation, $client, $user);
                }
            }
            else
            {
                $DocumentsClientManager->ajouterDocument($type, "http://leboucalais.fr/application/Documents/documents-clients/" . $idReservation . "/" . $nomFichier, $colonne, $repertoireDestination . $nomFichier, $idReservation, $client, $user);
            }
            $reservations = $reservationManager->getReservationsFromUser($_SESSION['id']);
            if($reservations != false && $user->getStatut() < 5)
            {
                echo $twig->render('convention_facture_acompte.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'message'=>$message, 'succes'=>'oui'));
            }
            elseif($reservations != false && $user->getStatut() >= 5)
            {
                foreach($reservations as $reservation)
                {
                    $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                    $documentssAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
                }
                echo $twig->render('convention_facture_acompte.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'reservations'=>$reservations, 'documentsClient'=>$documentsClient, 'documentssAnnexes'=>$documentssAnnexes, 'message'=>$message, 'succes'=>"oui"));
            }
        }
        else
        {
            $message = "Le fichier n'a pas été téléversé. Vérifiez l'existence du répertoire " . $repertoireDestination;
            $reservations = $reservationManager->getReservationsFromUser($_SESSION['id']);
            if($reservations != false && $user->getStatut() < 5)
            {
                echo $twig->render('convention_facture_acompte.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'message'=>$message, 'succes'=>'non'));
            }
            elseif($reservations != false && $user->getStatut() >= 5)
            {
                foreach($reservations as $reservation)
                {
                    $documentsClient[] = $DocumentsClientManager->getDocumentsClient($reservation->getIdReservation());
                    $documentssAnnexes[] = $DocumentsClientManager->getDocumentsAnnexes($reservation->getIdReservation());
                }
                echo $twig->render('convention_facture_acompte.html.twig', array('acces'=> $_SESSION['acces'], 'role'=>$_SESSION['role'], 'user'=>$user, 'reservations'=>$reservations, 'documentsClient'=>$documentsClient, 'documentssAnnexes'=>$documentssAnnexes, 'message'=>$message, 'succes'=>"non"));
            }
        }
    }
}

/**
 * Affiche la page index si l'utilisateur n'est pas connecté et qu'il tente d'accéder à une page existante de l'application
 * @param twig
 */
function connectYourself($twig)
{
    if($_SESSION['acces'] == "non")
    {
        echo $twig->render("index.html.twig", array('acces'=> $_SESSION['acces']));
        exit;
    }
}