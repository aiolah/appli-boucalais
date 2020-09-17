<?php

/**
 * Classe UtilisateurManager : permet de gérer les utilisateurs (prospects, clients, gérant) de l'application, et les interactions avec la base de données
*/

class UtilisateurManager
{
    private $_db;

    /**
    * Constructeur = initialisation de la connexion vers le SGBD
    */
    public function __construct($db)
    {
        $this->_db=$db;
    }

    /**
     * Insère un nouveau prospect en base de données | N'importe quel statut marche ! Il suffit de le préciser
     * @param prenom
     * @param nom
     * @param nom_groupe
     * @param type_groupe
     * @param mail
     * @param telephone
     * @param taille_groupe
     * @param nombre_nuits
     * @param moyen
     * @param statut
     */
    public function ajouterProspect($prenom, $nom, $nom_groupe, $type_groupe, $mail, $telephone, $taille_groupe, $nombre_nuits, $moyen, $statut)
    {
        $req = "INSERT INTO LE_BOUCALAIS_UTILISATEUR(prenom, nom, nom_groupe, type_groupe, mail, telephone, taille_groupe, nombre_nuits, date, moyen, statut, role) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->_db->prepare($req);
        $ok = $stmt->execute(array($prenom, $nom, $nom_groupe, $type_groupe, $mail, $telephone, $taille_groupe, $nombre_nuits, date('Y-m-j'), $moyen, $statut, 'prospect'));

        // if($_POST['statut'] == 5)
        // {
        //     $this->creerDossierClient("blaaa");
        // }

        return $ok;

        // pour debuguer les requêtes SQL
        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }
    }

    /**
     * Ouvre le compte de l'utilisateur et on lui envoie un mail
     * @param mail_prospect mail du futur utilisateur
     */
    public function ouvrirCompte($mail_prospect)
    {
        // Envoi mail de notification au client
        date_default_timezone_set('Europe/Paris');

        $objet = "Demande de devis : ouverture de votre compte";
        $message = "
        <html>
        <body>
            Bonjour" /*. Prénom de la personne hehe */ . ",<br><br>    
        
            Vous avez récemment demandé un devis sur le site leboucalais.fr. Vous allez pouvoir le composer en ligne sur l'application du Boucalais. 
            Celle-ci vous fera profiter de nombreuses fonctionnalités et vous mettra en relation avec le gérant du centre pour faciliter l'organisation de votre séjour.<br><br>

            Pour commencer, veuillez vous inscrire sur le lien suivant en utilisant l'adresse avec laquelle vous consultez cet email (rappel : ".$mail_prospect.") Dans le cas contraire, votre inscription sera refusée.<br><br>

            <a href='http://leboucalais.fr/application/?action=inscription'>http://leboucalais.fr/application/?action=inscription</a><br><br>

            A bientôt au <a href='http://leboucalais.fr target='_blank'>Boucalais</a> !
        </body>
        </html>";
        $destinataire = $mail_prospect /* 'yoyo31.music@gmail.com' */;
        $headers = "Content-Type: text/html; charset=\"utf-8\"\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Date: " . date(DateTime::RFC2822) . "\n";
        $headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
        $headers .= "Reply-To: contact@leboucalais.fr";

        // mail($mail_prospect, $objet, $message, $headers)
        mail($destinataire, $objet, $message, $headers);
    }


    /**
     * Vérifie que l'email renseigné correspond bien à un prospect qui a demandé une demande de devis
     * @param mail mail renseigné dans le formulaire d'inscription
     */
    public function verificationEmail($mail)
    {
        $req = "SELECT COUNT(ID_UTILISATEUR) FROM LE_BOUCALAIS_UTILISATEUR WHERE mail = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($mail));
        $nbreComptes = $stmt->fetch();

        // Si l'utilisateur a rempli 2 fois ou plus le formulaire de demande de devis avec la même adresse mail
        if($nbreComptes["COUNT(ID_UTILISATEUR)"] >= 2)
        {
            return "Plus de 2 comptes";
        }
        // Si c'est la première et seule fois
        elseif($nbreComptes["COUNT(ID_UTILISATEUR)"] == 1)
        {
            $req = "SELECT ID_UTILISATEUR, NOM, PRENOM FROM LE_BOUCALAIS_UTILISATEUR WHERE mail = ? AND statut = 1";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array($mail));

            // Si le mail est rattaché à un prospect de la base de données, alors on crée puis renvoie un objet Client
            if($donnees = $stmt->fetch())
            {
                $prospect = new Utilisateur($donnees);
                return $prospect;
            }
            // Si le statut est différent de 1 → c'est peut-être François qui l'a inscrit ! Donc on vérifie
            else
            {
                $req = "SELECT MOYEN FROM LE_BOUCALAIS_UTILISATEUR WHERE mail = ?";
                $stmt = $this->_db->prepare($req);
                $stmt->execute(array($mail));
    
                $resultat = $stmt->fetch();
                if($resultat['MOYEN'] != "Formulaire de demande de devis" && $resultat['MOYEN'] != NULL)
                {
                    return "Ajouté par un gérant";
                }
                else
                {
                    return "Déjà inscrit";
                }
            }
        }
        // Si aucun mail ne correspond
        elseif($nbreComptes["COUNT(ID_UTILISATEUR)"] == 0)
        {
            return false;
        }

        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }
    }

    /**
     * Chiffre et enregistre le mot de passe du prospect, définit son statut à 2
     * @param id id de l'utilisateur
     * @param mdp mot de passe de l'utilisateur
     */
    public function register($id, $mdp)
    {
        // On hashe le mot de passe avec l'algorithme BCRYPT (MD5 n'est plus sûr car algorithme trop rapide (donc plus facilement crackable))
        $mdp_hash = password_hash($mdp, PASSWORD_BCRYPT);

        // INSERT INTO .. WHERE .. n'est pas possible !! Car INSERT INTO sert à rajouter des lignes qui n'existent pas déjà... Si elles existent et qu'on veut ajouter/modifier des données qu'elles contiennent, il faut utiliser UPDATE .. SET ..
        // On ne peut pas faire "SET .. AND .." !! Séparer les colonnes par des virgules, AND c'est juste pour les conditions !!
        $client = $this->getProfil($id);
        // Si statut différent de 1, alors c'est un gérant qui a ajouté le client manuellement. On ne modifie donc pas le statut de l'utilisateur correspondant
        if($client->getStatut() != 1)
        {
            $req = "UPDATE LE_BOUCALAIS_UTILISATEUR SET mot_de_passe = ?, date_inscription = ? WHERE id_utilisateur = ?";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array($mdp_hash, date('Y-m-j'), $id));
        }
        elseif($client->getStatut() == 1)
        {
            $req = "UPDATE LE_BOUCALAIS_UTILISATEUR SET mot_de_passe = ?, statut = 2, date_inscription = ? WHERE id_utilisateur = ?";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array($mdp_hash, date('Y-m-j'), $id));
        }

        // On récupère des données du prospect pour le connecter à son nouvel espace !
        $req = "SELECT ID_UTILISATEUR, NOM, PRENOM, NOM_GROUPE, STATUT, ROLE FROM LE_BOUCALAIS_UTILISATEUR WHERE id_utilisateur = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($id));
        
        $prospect = new Utilisateur($stmt->fetch());
        return $prospect;

        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }
    }

    /**
     * Vérifie que les informations de connexion (mail + mot de passe) correspondent à ceux rentrés par l'utilisateur
     * @param mail mail entré par l'utilisateur
     * @param mdp mot de passe entré par l'utilisateur
     */
    public function connexion($mail, $mdp)
    {
        $req = "SELECT mot_de_passe FROM LE_BOUCALAIS_UTILISATEUR WHERE MAIL = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($mail));
        
        $mdp_hash = $stmt->fetch();

        // Si un mot de passe existe pour le mail rentré | ['mot_de_passe'] car fetch() renvoie toujours un tableau !
        if($mdp_hash['mot_de_passe'] != null)
        {
            // On vérifie si le mot de passe rentré correspond bien à celui hashé en base de données
            $verify = password_verify($mdp, $mdp_hash['mot_de_passe']);
            if($verify == true)
            {
                $req = "SELECT ID_UTILISATEUR, NOM, PRENOM, NOM_GROUPE, STATUT, ROLE FROM LE_BOUCALAIS_UTILISATEUR WHERE MAIL = ?";
                $stmt = $this->_db->prepare($req);
                $stmt->execute(array($mail));
                
                $user = new Utilisateur($stmt->fetch());
                return $user;
            }
            // Erreur sur le mot de passe
            else return "mdp_error";
        }
        // Erreur sur le mail
        else return "mail_error";
    }

    /**
     * Récupère les informations de l'utilisateur depuis son id
     * @param id id de l'utilisateur
     */
    public function getProfil($id)
    {
        $req = "SELECT * FROM LE_BOUCALAIS_UTILISATEUR WHERE ID_UTILISATEUR = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($id));
        
        $user = new Utilisateur($stmt->fetch());
        return $user;
    }

    /**
     * Récupère les informations de l'utilisateur depuis le nom du groupe
     * @param nom nom du groupe
     */
    public function getProfilFromGroupName($nom)
    {
        $req = "SELECT * FROM LE_BOUCALAIS_UTILISATEUR WHERE nom_groupe = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($nom));

        $user = new Utilisateur($stmt->fetch());
        return $user;
    }

    /**
     * Récupère les informations de l'utilisateur depuis son mail
     * @param email mail de l'utilisateur
     */
    public function getProfilFromEmail($email)
    {
        $req = "SELECT * FROM LE_BOUCALAIS_UTILISATEUR WHERE mail = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($email));

        $user = new Utilisateur($stmt->fetch());
        return $user;
    }

    /**
     * Récupère les informations de l'utilisateur depuis une de ses réservations
     * @param idReservation
     */
    public function getProfilFromReservation($idReservation)
    {
        $req = "SELECT * FROM LE_BOUCALAIS_UTILISATEUR
        INNER JOIN LE_BOUCALAIS_RESERVE ON LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR = LE_BOUCALAIS_RESERVE.ID_UTILISATEUR
        WHERE id_reservation = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idReservation));

        $user = new Utilisateur($stmt->fetch());
        return $user;
    }

    /**
     * Récupère les informations de l'utilisateur depuis un de ses devis
     * @param idDevis
     */
    public function getProfilFromDevis($idDevis)
    {
        $req = "SELECT * FROM LE_BOUCALAIS_UTILISATEUR
        INNER JOIN LE_BOUCALAIS_EFFECTUE_DEVIS ON LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR = LE_BOUCALAIS_EFFECTUE_DEVIS.ID_UTILISATEUR
        WHERE id_devis = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idDevis));

        $user = new Utilisateur($stmt->fetch());
        return $user;
    }

    /**
     * Crée la ligne dans la table LE_BOUCALAIS_DOCUMENTS_CLIENT
     * @param idReservation
     */
    public function dossierClientBdd($idReservation)
    {
        $req = "INSERT INTO LE_BOUCALAIS_DOCUMENTS_CLIENT(id_reservation) values(?)";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idReservation));
    }

    /**
     * Enregistrement des modifications du profil client
     * @param nomGroupe nom du groupe
     * @param prenom prénom du client
     * @param nom nom du client
     * @param mail mail du client
     * @param telephone téléphone du client
     * @param id id du client
     */
    public function updateProfile($nomGroupe, $prenom, $nom, $mail, $telephone, $id)
    {
        $req = "UPDATE LE_BOUCALAIS_UTILISATEUR SET nom_groupe = ?, prenom = ?, nom = ?, mail = ?, telephone = ? WHERE id_utilisateur = ?";
        $stmt = $this->_db->prepare($req);
        $ok = $stmt->execute(array($nomGroupe, $prenom, $nom, $mail, $telephone, $id));

        return $ok;
    }

    /**
     * Supprimer le profil d'un utilisateur, ses éventuels devis et réservations
     * @param id id de l'utilisateur
     * @param chemin chemin du fichier index.php
     * @param user instance de la classe Utilisateur, contient le profil du client
     * @param mail booléen : définit si le mail de suppression du compte doit être envoyé ou non
     */
    public function deleteProfile($id, $chemin, $user, $mail)
    {
        $req = "SELECT id_reservation FROM LE_BOUCALAIS_RESERVE WHERE id_utilisateur = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($id));

        while($donnees = $stmt->fetch())
        {
			$reservations[] = $donnees;
		}

        // On fait ça pour éviter que le foreach ne s'exécute sur une variable qui n'est pas un tableau. Même si le tableau est vide ce n'est pas grave, ce qui compte c'est que la variable transmise ne soit pas autre chose qu'un tableau (boolean → false)
        if(isset($reservations) && is_array($reservations))
        {
            // Ne pas faire de fetch() avant le foreach : sinon il lit la première ligne et se positionne à la deuxième
            foreach($reservations as $reservation)
            {
                $dossierReservation = $chemin . "/Documents/documents-clients/" . $reservation['id_reservation'];
                // On ouvre le dossier de la réservation
                $repertoire = opendir($dossierReservation);
                if(!$repertoire)
                {
                    return false;
                }

                // Tant que l'on peut lire des fichiers dans le répertoire
                while(false !== ($fichier = readdir($repertoire)))
                {
                    $chemin = $dossierReservation . "/" . $fichier;
                    // Si le fichier n'est pas le dossier supérieur, le dossier courant ou un dossier, on le supprime
                    if($fichier != ".." && $fichier != "." && !is_dir($fichier))
                    {
                        if(!unlink($chemin))
                        {
                            return false;
                        }
                    }
                }

                // On ferme le répertoire
                closedir($repertoire);
                // On va dans le dossier parent
                if(!chdir("../"))
                {
                    return false;
                }
                // Et on supprime le dossier de la réservation
                if(!rmdir($dossierReservation))
                {
                    return false;
                }

                $req = "DELETE FROM LE_BOUCALAIS_RESERVE WHERE id_reservation = ?";
                $stmt2 = $this->_db->prepare($req);
                // Mettre le nom de la case du tableau, sinon ça marche pas
                $stmt2->execute(array($reservation['id_reservation']));

                $req = "DELETE FROM LE_BOUCALAIS_HEBERGEMENT WHERE id_reservation = ?";
                $stmt2 = $this->_db->prepare($req);
                $stmt2->execute(array($reservation['id_reservation']));

                $req = "DELETE FROM LE_BOUCALAIS_DOCUMENTS_CLIENT WHERE id_reservation = ?";
                $stmt = $this->_db->prepare($req);
                $stmt->execute(array($reservation['id_reservation']));

                $req = "DELETE FROM LE_BOUCALAIS_DOCUMENTS_ANNEXES WHERE id_reservation = ?";
                $stmt = $this->_db->prepare($req);
                $stmt->execute(array($reservation['id_reservation']));
        
                $req = "DELETE FROM LE_BOUCALAIS_RESERVATION WHERE id_reservation = ?";
                $stmt2 = $this->_db->prepare($req);
                $stmt2->execute(array($reservation['id_reservation']));
            }
        }

        $req = "SELECT id_devis FROM LE_BOUCALAIS_EFFECTUE_DEVIS WHERE id_utilisateur = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($id));

        while($donnees = $stmt->fetch())
        {
			$deviss[] = $donnees;
		}

        if(isset($deviss) && is_array($deviss))
        {
            foreach($deviss as $devis)
            {
                $req = "DELETE FROM LE_BOUCALAIS_CHOIX_ACTIVITE WHERE id_devis = ?";
                $stmt2 = $this->_db->prepare($req);
                $stmt2->execute(array($devis['id_devis']));

                $req = "DELETE FROM LE_BOUCALAIS_CHOIX_OPTION WHERE id_devis = ?";
                $stmt2 = $this->_db->prepare($req);
                $stmt2->execute(array($devis['id_devis']));

                $req = "DELETE FROM LE_BOUCALAIS_EFFECTUE_DEVIS WHERE id_devis = ?";
                $stmt = $this->_db->prepare($req);
                $stmt->execute(array($devis['id_devis']));

                $req = "DELETE FROM LE_BOUCALAIS_DEVIS WHERE id_devis = ?";
                $stmt2 = $this->_db->prepare($req);
                $stmt2->execute(array($devis['id_devis']));
            }
        }

        // Envoi d'un mail de notification au client dont le compte va être supprimé
        date_default_timezone_set('Europe/Paris');

        if($mail == 1)
        {
            $objet = "Appli Boucalais : suppression de votre compte";
            $message = "
            <html>
            <body>
                Bonjour" /*. Prénom de la personne hehe */ . ",<br><br>    
            
                Votre compte sur l'Appli Boucalais a été supprimé par le gérant, car votre séjour est maintenant passé ou parce que vous n'avez pas donné suite au devis réalisé.<br><br>
    
                A bientôt au <a href='http://leboucalais.fr' target='_blank'>Boucalais</a> !
            </body>
            </html>";
            $destinataire = $user->getMail() /* 'yoyo31.music@gmail.com' */;
            $headers = "Content-Type: text/html; charset=\"utf-8\"\n";
            $headers .= "MIME-Version: 1.0\n";
            $headers .= "Date: " . date(DateTime::RFC2822) . "\n";
            $headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
            $headers .= "Reply-To: contact@leboucalais.fr";
    
            mail($destinataire, $objet, $message, $headers);
        }

        $req = "DELETE FROM LE_BOUCALAIS_UTILISATEUR WHERE id_utilisateur = ?";
        $stmt = $this->_db->prepare($req);
        $ok = $stmt->execute(array($id));

        return $ok;
    }

    /**
     * Récupère la liste des clients présents en bdd
     */
    public function getListeClients()
    {
        $clients = array();

        $req = "SELECT * FROM LE_BOUCALAIS_UTILISATEUR WHERE role = 'prospect'";
        $stmt = $this->_db->prepare($req);
        $stmt->execute();

        while($donnees = $stmt->fetch())
        {
            $clients[] = new Utilisateur($donnees);
        }
        return $clients;
    }

    /**
     * Récupère les utilisateurs en fonction du statut spécifié
     * @param statut statut désiré (de 1 à 8)
     */
    public function getUsersFromStatut($statut)
    {
        $clients = array();

        $req = "SELECT * FROM LE_BOUCALAIS_UTILISATEUR WHERE statut = ? and role != 'gerant' ORDER BY date DESC";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($statut));

        while($donnees = $stmt->fetch())
        {
            $clients[] = new Utilisateur($donnees);
        }
        return $clients;
    }

    /**
     * Vérifie si l'ancien mot de passe rentré par l'utilisateur correspond bien et si oui, le modifie
     * @param ancien_mdp ancien mot de passe
     * @param nouveau_mdp nouveau mot de passe
     * @param id_utilisateur id de l'utilisateur
     */
    public function modifierMdp($ancien_mdp, $nouveau_mdp, $id_utilisateur)
    {
        $req = "SELECT MOT_DE_PASSE FROM LE_BOUCALAIS_UTILISATEUR WHERE ID_UTILISATEUR = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($id_utilisateur));
        
        $ancien_mdp_hash = $stmt->fetch();

        // On vérifie si l'ancien mot de passe rentré correspond bien à celui hashé en base de données
        $verify = password_verify($ancien_mdp, $ancien_mdp_hash['MOT_DE_PASSE']);
        if($verify == true)
        {
            $nouveau_mdp_hash = password_hash($nouveau_mdp, PASSWORD_BCRYPT);

            $req = "UPDATE LE_BOUCALAIS_UTILISATEUR SET MOT_DE_PASSE = ? WHERE id_utilisateur = ?";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array($nouveau_mdp_hash, $id_utilisateur));

            return true;
        }
        else return false;
    }

    /**
     * Vérifie si l'adresse email est bien rattachée à un utilisateur en base de données
     * @param mail mail de l'utilisateur
     */
    public function recupererMdp($mail)
    {
        $req = "SELECT id_utilisateur, nom, prenom FROM LE_BOUCALAIS_UTILISATEUR WHERE mail = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($mail));

        // Si le mail est rattaché à un prospect de la base de données, alors on crée un jeton et on lui envoie un lien par mail
        if($donnees = $stmt->fetch())
        {
            // On génère un jeton unique (uniqid = identifiant unique en fonction de l'heure et de la date, rand = valeur aléatoire)
            $token = sha1(uniqid(rand()));

            date_default_timezone_set('Europe/Paris');
            // Génération de la date + heure à insérer dans la requête (pour avoir une durée de vie du lien de 24 heures)
            $date_lien = date('Y-m-d H:i:s');

            $req = "UPDATE LE_BOUCALAIS_UTILISATEUR SET jeton = ?, reinitialisation = 0, date_lien = ? WHERE mail = ?";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array($token, $date_lien, $mail));

            // Envoi mail de réinitialisation du mot de passe au client.
            $objet = "Réinitialisation de votre mot de passe";
            $message = "
            <html>
            <body>
                Bonjour" /*. Prénom de la personne hehe */ . ",<br><br>    
            
                Pour définir un nouveau mot de passe, veuillez <a href='http://leboucalais.fr/application/?action=recuperer-mdp&token=" . $token . "' target='_blank'>cliquer sur ce lien</a>.<br><br>" .

                /* Pour composer votre devis ou consulter l'état de votre réservation, rendez-vous sur <a>leboucalais.fr/application/</a>. */

                "A bientôt au <a href='http://leboucalais.fr' target='_blank'>Boucalais</a> !
            </body>
            </html>";
            $destinataire = $mail /* 'yoyo31.music@gmail.com' */;
            $headers = "Content-Type: text/html; charset=\"utf-8\"\n";
            $headers .= "MIME-Version: 1.0\n";
            $headers .= "Date: " . date(DateTime::RFC2822) . "\n";
            $headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
            $headers .= "Reply-To: contact@leboucalais.fr";

            // mail($mail, $objet, $message, $headers)
            mail($destinataire, $objet, $message, $headers);

            return true;
        }
        // Sinon, on renvoie false        
        else return false;

        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }

    }

    /**
     * Récupère les informations de l'utilisateur dont le token est passé en paramètre, vérifie que la date à laquelle l'utilisateur a demandé le lien est inférieure à la date actuelle
     * @param token token récupéré dans l'URL
     */
    public function verificationJeton($token)
    {
        $req = "SELECT ID_UTILISATEUR, JETON, ADDDATE(date_lien, INTERVAL +24 HOUR) FROM LE_BOUCALAIS_UTILISATEUR WHERE jeton = ? AND reinitialisation = 0";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($token));

        // Si le token existe alors on renvoie un objet Utilisateur avec les informations correspondantes
        if($donnees = $stmt->fetch())
        {
            $user = new Utilisateur($donnees);
            return $user;
        }
        else
        {
            return false;
        }

        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }
    }

    /**
     * Définit un nouveau mot de passe en base de données
     * @param mdp nouveau mot de passe
     * @param id id de l'utilisateur
     */
    public function reinitialiserMdp($mdp, $id)
    {
        $mdp_hash = password_hash($mdp, PASSWORD_BCRYPT);

        $req = "UPDATE LE_BOUCALAIS_UTILISATEUR SET mot_de_passe = ?, reinitialisation = 1 WHERE id_utilisateur = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($mdp_hash, $id));

        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }
    }
}