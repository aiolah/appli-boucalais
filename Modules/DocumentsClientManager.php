<?php

/**
 * Permet de gérer les documents propres à chaque utilisateur
 */
class DocumentsClientManager
{
    private $db; // Objet de connexion à la base de données

    /**
     * Connexion vers le SGBD
     */
    public function __construct($db) {
        $this->_db = $db;
    }

    /**
     * Enregistre le lien et le chemin du fichier téléversé pour le client en bdd : convention, facture d'acompte, convention signée, plan des chambres, planning d'activités, menus ou document annexe + envoie un mail de notification
     * @param type type du document : convention ou facture d'acompte = colonne pour le lien en bdd
     * @param lien lien du document
     * @param colonne type du document + chemin : convention_chemin ou facture_acompte_chemin = colonne pour le chemin en bdd
     * @param chemin chemin pour accéder au fichier sur le serveur
     * @param idReservation
     * @param client instance de la classe Utilisateur = client pour lequel on a téléversé un fichier
     * @param user instance de la classe Utilisateur = utilisateur connecté
     */
    public function ajouterDocument($type, $lien, $colonne, $chemin, $idReservation, $client, $user)
    {
        // 1 ligne (bdd) par document annexe
        if($type == "documents-annexes")
        {
            $req = "INSERT INTO LE_BOUCALAIS_DOCUMENTS_ANNEXES SET id_reservation = ?, nom = ?, lien = ?, chemin = ?";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array($idReservation, $_POST['nom_fichier'], $lien, $chemin));

            // Envoi mail de notification au client en fonction du fichier transféré
            date_default_timezone_set('Europe/Paris');

            if($user->getRole() == "gerant")
            {
                $objet = "Processus de réservation : réception d'un document annexe'";
                $message = "
                <html>
                <body>
                    Bonjour" /*. Prénom de la personne hehe */ . ",<br><br>    
                
                    Le gérant vient de vous envoyer un document annexe. Vous le trouverez dans votre espace \"Ma convention et facture d'acompte\" sur <a href='http://leboucalais.fr/application/?action=connexion' target='_blank'>l'application</a>.<br>
        
                    A bientôt au <a href='http://leboucalais.fr' target='_blank'>Boucalais</a> !
                </body>
                </html>";
                $destinataire = $client->getMail() /* 'yoyo31.music@gmail.com' */;
                $headers = "Content-Type: text/html; charset=\"utf-8\"\n";
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Date: " . date(DateTime::RFC2822) . "\n";
                $headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
                $headers .= "Reply-To: contact@leboucalais.fr";
        
                mail($destinataire, $objet, $message, $headers);
            }
            elseif($user->getRole() == "prospect")
            {
                $objet = "Processus de réservation : réception d'un document annexe'";
                $message = "
                <html>
                <body>
                    Bonjour" /*. Prénom de la personne hehe */ . ",<br><br>    
                
                    Le responsable du groupe " . $client->getNomGroupe() . " vient de vous envoyer un document annexe. Vous la trouverez sur sa <a href='http://leboucalais.fr/application/?action=fiche-client&id=" . $client->getId() . "' target='_blank'>\"Fiche client\"</a>.<br>
        
                    <a href='http://leboucalais.fr/application' target='_blank'>Se connecter sur l'application</a>
                </body>
                </html>";
                $destinataire = "contact@leboucalais.fr" /* 'yoyo31.music@gmail.com' */;
                $headers = "Content-Type: text/html; charset=\"utf-8\"\n";
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Date: " . date(DateTime::RFC2822) . "\n";
                $headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
                $headers .= "Reply-To: contact@leboucalais.fr";
        
                mail($destinataire, $objet, $message, $headers);
            }
        }
        // 1 ligne (bdd) par réservation
        else
        {
            $req = "UPDATE LE_BOUCALAIS_DOCUMENTS_CLIENT SET $type = ?, $colonne = ? WHERE id_reservation = ?";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array($lien, $chemin, $idReservation));

            if($type == "convention" || $type == "facture_acompte")
            {
                $statut = 6;
            }
            if($type == "convention_signee")
            {
                $statut = 7;
            }
            if($type == "plan_chambres" || $type == "planning_activites" || $type == "menus")
            {
                $statut = 8;
            }
    
            $req = "UPDATE LE_BOUCALAIS_UTILISATEUR SET statut = ? WHERE id_utilisateur = ?";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array($statut, $client->getId()));
    
            // Envoi mail de notification au client ou au gérant en fonction du fichier transféré
            if($type == "convention")
            {
                $objet = "Processus de réservation : réception de la convention";
                $message = "
                <html>
                <body>
                    Bonjour" /*. Prénom de la personne hehe */ . ",<br><br>    
                
                    Le gérant vient de vous envoyer votre convention. Vous la trouverez dans votre espace \"Ma convention et facture d'acompte\" sur <a href='http://leboucalais.fr/application/?action=connexion' target='_blank'>l'application</a>.<br>
                    Pour continuer votre réservation, veuillez la signer et la renvoyer sur l'espace de dépôt de l'application, dans votre espace \"Ma convention et facture d'acompte\".<br><br>
        
                    A bientôt au <a href='http://leboucalais.fr' target='_blank'>Boucalais</a> !
                </body>
                </html>";
                $destinataire = $client->getMail() /* 'yoyo31.music@gmail.com' */;
                $headers = "Content-Type: text/html; charset=\"utf-8\"\n";
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Date: " . date(DateTime::RFC2822) . "\n";
                $headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
                $headers .= "Reply-To: contact@leboucalais.fr";
        
                mail($destinataire, $objet, $message, $headers);        
            }
            elseif($type == "facture_acompte")
            {
                $objet = "Processus de réservation : réception de la facture d'acompte";
                $message = "
                <html>
                <body>
                    Bonjour" /*. Prénom de la personne hehe */ . ",<br><br>    
                
                    Le gérant vient de vous envoyer votre facture d'acompte. Vous la trouverez dans votre espace \"Ma convention et facture d'acompte\" sur <a href='http://leboucalais.fr/application/?action=connexion' target='_blank'>l'application</a>.<br><br>
        
                    A bientôt au <a href='http://leboucalais.fr' target='_blank'>Boucalais</a> !
                </body>
                </html>";
                $destinataire = $client->getMail() /* 'yoyo31.music@gmail.com' */;
                $headers = "Content-Type: text/html; charset=\"utf-8\"\n";
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Date: " . date(DateTime::RFC2822) . "\n";
                $headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
                $headers .= "Reply-To: contact@leboucalais.fr";
        
                mail($destinataire, $objet, $message, $headers);        
            }
            elseif($type == "convention_signee")
            {
                $req = "UPDATE LE_BOUCALAIS_UTILISATEUR SET statut = 7 WHERE id_utilisateur = ?";
                $stmt = $this->_db->prepare($req);
                $stmt->execute(array($client->getId()));

                $objet = "Processus de réservation : le groupe " . $client->getNomGroupe() . " a déposé la convention signée";
                $message = "
                <html>
                <body>
                    Bonjour" /*. Prénom de la personne hehe */ . ",<br><br>    
                
                    Le responsable du groupe ". $client->getNomGroupe() . " vient de déposer la convention signée. Vous la trouverez sur sa <a href='http://leboucalais.fr/application/?action=fiche-client&id=" . $client->getId() . "' target='_blank'>\"Fiche client\"</a>.<br><br>
        
                    <a href='http://leboucalais.fr/application' target='_blank'>Se connecter sur l'application</a>
                </body>
                </html>";
                $destinataire = "contact@leboucalais.fr"; /* 'yoyo31.music@gmail.com' */
                $headers = "Content-Type: text/html; charset=\"utf-8\"\n";
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Date: " . date(DateTime::RFC2822) . "\n";
                $headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
                $headers .= "Reply-To: contact@leboucalais.fr";
        
                mail($destinataire, $objet, $message, $headers);        
            }
            elseif($type == "plan-chambres" || $type == "planning-activites" || $type == "menus")
            {
                if($type == "plan-chambres")
                {
                    $doc = "le plan des chambres";
                    $docObjet = "du plan des chambres";
                }
                elseif($type == "plannig-activites")
                {
                    $doc = "le planning des activités";
                    $docObjet = "du planning des activités";
                }
                elseif($type == "menus")
                {
                    $doc = "les menus";
                    $docObjet = "des menus";
                }

                $objet = "Processus de réservation : réception " . $docObjet;
                $message = "
                <html>
                <body>
                    Bonjour" /*. Prénom de la personne hehe */ . ",<br><br>    
                
                    Le gérant vient de vous envoyer " . $doc . ". Vous trouverez ce document dans votre espace \"Mon pack séjour\" sur <a href='http://leboucalais.fr/application/?action=connexion' target='_blank'>l'application</a>.<br><br>
        
                    A bientôt au <a href='http://leboucalais.fr' target='_blank'>Boucalais</a> !
                </body>
                </html>";
                $destinataire = $client->getMail() /* 'yoyo31.music@gmail.com' */;
                $headers = "Content-Type: text/html; charset=\"utf-8\"\n";
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Date: " . date(DateTime::RFC2822) . "\n";
                $headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
                $headers .= "Reply-To: contact@leboucalais.fr";
        
                mail($destinataire, $objet, $message, $headers);
            }
        }
    }

    /**
     * Récupère les liens et les chemins de tous les documents rattachés à un client pour la réservation spécifiée
     * @param idReservation
     */
    public function getDocumentsClient($idReservation)
    {
        $req = "SELECT * FROM LE_BOUCALAIS_DOCUMENTS_CLIENT WHERE ID_RESERVATION = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idReservation));

        $documentsClient = new DocumentsClient($stmt->fetch());
        return $documentsClient;
    }

    /**
     * Récupère les noms, liens et les chemins de tous les documents annexes rattachés à un client pour la réservation spécifiée
     * @param idReservation
     */
    public function getDocumentsAnnexes($idReservation)
    {
        $req = "SELECT * FROM LE_BOUCALAIS_DOCUMENTS_ANNEXES WHERE ID_RESERVATION = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idReservation));

        while($donnees = $stmt->fetch())
        {
            $documentsAnnexes[] = new DocumentsClient($donnees);
        }
        if(isset($documentsAnnexes))
        {
            return $documentsAnnexes;
        }
        else return false;
    }

    /**
     * Vérifie si un fichier existe en fonction de son nom
     * @param nom nom du fichier affiché pour le client
     */
    public function fichierExiste($nom)
    {
        $req = "SELECT * FROM LE_BOUCALAIS_DOCUMENTS_ANNEXES WHERE nom = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($nom));
        $result = $stmt->fetch();

        if($result != false)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

?>