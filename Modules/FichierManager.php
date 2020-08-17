<?php

/**
 * Permet de gérer les fichiers documents administratifs, ressources documentaires, pack séjour
 */
class FichierManager
{
    private $db; // Objet de connexion à la base de données

    /**
     * Connexion vers le SGBD
     */
    public function __construct($db) {
        $this->_db = $db;
    }

    /**
     * Ajoute un fichier en base de données
     * @param nom nom du fichier donné par le gérant, celui qui s'affichera pour le client
     * @param lien lien du fichier
     * @param chemin chemin du fichier
     * @param categorie ressources documentaires, documents administratifs ou pack séjour
     */
    public function ajouterFichier($nom, $lien, $chemin, $categorie)
    {
        $req = "INSERT INTO LE_BOUCALAIS_ADMINISTRATIF(nom, lien, chemin, categorie, afficher) values(?, ?, ?, ?, ?)";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($nom, $lien, $chemin, $categorie, 0));
    }

    /**
     * Récupère tous les fichiers affichés pour les clients selon une catégorie donnée
     * @param categorie ressources documentaires, documents administratifs ou pack séjour
     */
    public function getListeFichiersClient($categorie)
    {
        $fichiers = array();

        $req = "SELECT nom, lien, chemin, categorie, afficher FROM LE_BOUCALAIS_ADMINISTRATIF WHERE categorie = ? AND afficher = 1";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($categorie));

        while($donnees = $stmt->fetch())
		{
			$fichiers[] = new Fichier($donnees);
		}
		return $fichiers;
    }

    /**
     * Récupère tous les fichiers téléversés par le gérant selon une catégorie donnée
     * @param categorie ressources documentaires, documents administratifs ou pack séjour
     */
    public function getListeFichiersGerant($categorie)
    {
        $fichiers = array();

        $req = "SELECT nom, lien, chemin, categorie, afficher FROM LE_BOUCALAIS_ADMINISTRATIF WHERE categorie = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($categorie));

        while($donnees = $stmt->fetch())
		{
			$fichiers[] = new Fichier($donnees);
		}
		return $fichiers;
    }

    /**
     * Vérifie si un fichier existe en fonction de son nom
     * @param nom nom du fichier affiché pour le client
     */
    public function fichierExiste($nom)
    {
        $req = "SELECT * FROM LE_BOUCALAIS_ADMINISTRATIF WHERE nom = ?";
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

    /**
     * Définit oui (= 1) pour la colonne afficher de la base de données des fichiers renseignés
     * @param fichiers le chemin des fichiers que l'on veut afficher
     */
    public function afficherFichiers($fichiers)
    {
        foreach($fichiers as $chemin)
        {
            $req = "UPDATE LE_BOUCALAIS_ADMINISTRATIF SET afficher = 1 WHERE chemin = ?";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array($chemin));    
        }
    }

    /**
     * Définit non (= 0) pour la colonne afficher de la base de données des fichiers renseignés
     * @param fichiers le chemin des fichiers que l'on ne veut pas afficher
     */
    public function cacherFichiers($fichiers)
    {
        foreach($fichiers as $chemin)
        {
            $req = "UPDATE LE_BOUCALAIS_ADMINISTRATIF SET afficher = 0 WHERE chemin = ?";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array($chemin));
        }
    }

    /**
     * Supprime les infos des fichiers en base de données
     * @param fichiers le chemin des fichiers que l'on veut supprimer
     */
    public function supprimerFichiers($fichiers)
    {
        foreach($fichiers as $chemin)
        {
            $req = "DELETE FROM LE_BOUCALAIS_ADMINISTRATIF WHERE chemin = ?";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array($chemin));
        }
    }
}

?>