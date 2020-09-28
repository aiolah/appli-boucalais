<?php

/**
 * Permet de gérer les options stockées en base de données
 */
class OptionManager {

    private $db; // Objet de connexion à la base de données

    /**
     * Connexion vers le SGBD
     */
    public function __construct($db) {
        $this->_db = $db;
    }


    /**
     * Récupération de toutes les options
     * @param page nom de la page sur laquelle on va afficher les options
     */
    public function getOptions($page)
    {
        if($page == 'form-devis')
        {
            $req = 'SELECT * FROM LE_BOUCALAIS_TARIF_OPTIONS';
            $stmt = $this->_db->prepare($req);
            $stmt->execute();

            $errorInfo = $stmt->errorInfo();
            if ($errorInfo[0] != 0) {
                print_r($errorInfo);
            }
            
            while ($donnees = $stmt->fetch()) {
                $options[] = new Option($donnees);
            }
            return $options;
        }
        else
        {
            $req = 'SELECT * FROM LE_BOUCALAIS_TARIF_OPTIONS WHERE AFFICHAGE_OPTION = 1';
            $stmt = $this->_db->prepare($req);
            $stmt->execute();

            $req = 'SELECT * FROM LE_BOUCALAIS_TARIF_OPTIONS WHERE AFFICHAGE_OPTION = 0';
            $stmt2 = $this->_db->prepare($req);
            $stmt2->execute();

            $errorInfo = $stmt->errorInfo();
            if ($errorInfo[0] != 0) {
                print_r($errorInfo);
            }
            
            $optionsAffichees = array();
            $optionsCachees = array();

            while($donnees = $stmt->fetch()) {
                $optionsAffichees[] = new Option($donnees);
            }

            while($donnees = $stmt2->fetch()) {
                $optionsCachees[] = new Option($donnees);
            }

            // Et oui on peut retourner des tableaux en php :O !!!
            return array($optionsAffichees, $optionsCachees);
        }
    }

    /**
     * Récupération des options d'un devis
     * @param idDevis
     */
    public function getOptionsFromDevis($idDevis)
    {
        $options = array();
        $req = 'SELECT * FROM LE_BOUCALAIS_TARIF_OPTIONS
                INNER JOIN LE_BOUCALAIS_CHOIX_OPTION ON LE_BOUCALAIS_CHOIX_OPTION.id_option = LE_BOUCALAIS_TARIF_OPTIONS.id_option
                WHERE LE_BOUCALAIS_CHOIX_OPTION.ID_DEVIS = ?';
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idDevis));

        $errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
        }
        
        while($donnees = $stmt->fetch())
        {
            $options[] = new Option($donnees);
        }
        if($options != null)
        {
            return $options;
        }
        else return false;
    }

    /**
     * Récupère une option par son ID
     * @param id
     */
    public function getOptionById($id) {
        $req = 'SELECT * FROM LE_BOUCALAIS_TARIF_OPTIONS WHERE ID_OPTION = ?';
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($id));

        $errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
        }
        
        while($donnees = $stmt->fetch())
        {
            $option = new Option($donnees);
            return $option;
        }
    }


    /**
     * Ajoute une option
     * @param Option
     */
    public function addOption(Option $option) {
        $stmt = $this->_db->prepare("SELECT MAX(ID_OPTION) AS MAXIMUM FROM LE_BOUCALAIS_TARIF_OPTIONS");
        $stmt->execute();
        $option->setIdOption($stmt->fetchColumn()+1);
        
        $req = 'INSERT INTO LE_BOUCALAIS_TARIF_OPTIONS(ID_OPTION, NOM_OPTION, PRIX_OPTION_UNITE, DESCRIPTION_OPTION, AFFICHAGE_OPTION) VALUES (?, ?, ?, ?, ?)';
        $stmtOption = $this->_db->prepare($req);
        $resOption = $stmtOption->execute(array($option->getIdOption(), $option->getNomOption(), $option->getPrixOptionUnite(), $option->getDescriptionOption(), $option->getAffichageOption()));

        // pour debuguer les requêtes SQL
        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }

        return $resOption;
    }

    /**
     * Mise à jour d'une option
     * @param Option
     */
    public function updateOption(Option $option) {
        
        $req = "UPDATE LE_BOUCALAIS_TARIF_OPTIONS SET NOM_OPTION = :nomOption, 
                    PRIX_OPTION_UNITE = :prixOptionUnite, 
                    DESCRIPTION_OPTION = :descriptionOption,
                    AFFICHAGE_OPTION = :affichageOption 
                    WHERE ID_OPTION = :idOption";

		$stmt = $this->_db->prepare($req);
		$stmt->execute(array(":nomOption" => $option->getNomOption(),
                                ":prixOptionUnite" => floatval($option->getPrixOptionUnite()),
                                ":descriptionOption" => $option->getDescriptionOption(),
                                ":affichageOption" => $option->getAffichageOption(),
                                ":idOption" => intval($option->getIdOption())
                            ));
					
		return $stmt;
    }
}
?>