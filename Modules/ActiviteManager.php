<?php
class ActiviteManager {

    private $db; // Objet de connexion à la base de données

    /**
     * Connexion vers le SGBD
     */
    public function __construct($db) {
        $this->_db = $db;
    }


    /**
     * Récupération des prix
     */
    public function getActivites() {
        $req = 'SELECT * FROM LE_BOUCALAIS_TARIF_ACTIVITES ORDER BY DESCRIPTION_ACTIVITE DESC';
        $stmt = $this->_db->prepare($req);
        $stmt->execute();

        $errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
        }
        
        while ($donnees = $stmt->fetch()) {
			$activites[] = new Activite($donnees);
        }
        return $activites;
    }

    public function getActivitesFromDevis($idDevis)
    {
        $activites = array();
        // PHP est sensible à la casse : écrire les noms des colonnes tels qu'ils sont écrits dans MySQL, sinon ça ne coïncide pas avec les noms pour la classe
        $req = 'SELECT LE_BOUCALAIS_CHOIX_ACTIVITE.NB_SEANCES, LE_BOUCALAIS_CHOIX_ACTIVITE.NB_PARTICIPANTS, LE_BOUCALAIS_CHOIX_ACTIVITE.PRIX_ACTIVITE, LE_BOUCALAIS_TARIF_ACTIVITES.NOM_ACTIVITE, LE_BOUCALAIS_TARIF_ACTIVITES.PRIX_ACTIVITE_UNITE FROM LE_BOUCALAIS_TARIF_ACTIVITES
                INNER JOIN LE_BOUCALAIS_CHOIX_ACTIVITE ON LE_BOUCALAIS_CHOIX_ACTIVITE.id_activite = LE_BOUCALAIS_TARIF_ACTIVITES.id_activite
                WHERE LE_BOUCALAIS_CHOIX_ACTIVITE.ID_DEVIS = ?';
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idDevis));

        $errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
        }
        
        while($donnees = $stmt->fetch())
        {
            $activites[] = new Activite($donnees);
        }
        if($activites != null)
        {
            return $activites;
        }
        else return false;
    }

    /**
     * Récupère une activité selon l'ID passé en paramètre
     */
    public function getActiviteById($id) {
        $req = 'SELECT * FROM LE_BOUCALAIS_TARIF_ACTIVITES WHERE ID_ACTIVITE = ?';
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($id));

        $errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
        }
        
        $donneesActivite = $stmt->fetch();
        return $donneesActivite;
    }

    /**
     * Ajoute une activité en base de données
     * @param Activite
     */
    public function addActivite(Activite $activite) {
        $stmt = $this->_db->prepare("SELECT MAX(ID_ACTIVITE) AS MAXIMUM FROM LE_BOUCALAIS_TARIF_ACTIVITES");
        $stmt->execute();
        $activite->setIdActivite($stmt->fetchColumn()+1);
        
        $req = 'INSERT INTO LE_BOUCALAIS_TARIF_ACTIVITES (NOM_ACTIVITE,PRIX_ACTIVITE_UNITE,DESCRIPTION_ACTIVITE,NB_PARTICIPANTS_GROUPE) VALUES (?,?,?,?)';
        $stmtActivite = $this->_db->prepare($req);
        $resActivite = $stmtActivite->execute(array($activite->getNomActivite(),$activite->getPrixActiviteUnite(),$activite->getDescriptionActivite(),$activite->getNbParticipantsGroupe()));

        // pour debuguer les requêtes SQL
        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }
    }

    /**
     * Supprime une activité de la base de données
     * @param Number $idActivite
     */
    public function deleteActivite($idActivite) {
        $req = "DELETE FROM LE_BOUCALAIS_TARIF_ACTIVITES WHERE ID_ACTIVITE = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idActivite));

        return $stmt;
    }

    /**
     * Met à jour une activité
     * @param Activite
     */
    public function updateActivite(Activite $activite) {
        
        $req = "UPDATE LE_BOUCALAIS_TARIF_ACTIVITES SET NOM_ACTIVITE = :nomActivite, 
                    PRIX_ACTIVITE_UNITE = :prixActiviteUnite,
                    DESCRIPTION_ACTIVITE = :descriptionActivite,
                    NB_PARTICIPANTS_GROUPE = :nbParticipantsGroupe,
                    AFFICHAGE_ACTIVITE = :affichageActivite 
                    WHERE ID_ACTIVITE = :idActivite";

		$stmt = $this->_db->prepare($req);
		$ok = $stmt->execute(array(":nomActivite" => $activite->getNomActivite(),
                                ":prixActiviteUnite" => floatval($activite->getPrixActiviteUnite()),
                                ":descriptionActivite" => $activite->getDescriptionActivite(),
                                ":nbParticipantsGroupe" => intval($activite->getNbParticipantsGroupe()),
                                ":affichageActivite" => $activite->getAffichageActivite(),
                                ":idActivite" => intval($activite->getIdActivite())
                            ));

		return $ok;
    }
}
?>