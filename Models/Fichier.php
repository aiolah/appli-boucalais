<?php

/**
 * Classe Fichier : permet de gérer les informations des fichiers en base de données
 */
class Fichier
{
    private $_nom;
    private $_lien;
    private $_chemin;
    private $_categorie;
    private $_afficher;

    public function __construct(array $donnees)
    {
        $this->hydrate($donnees);
    }

    public function hydrate(array $donnees)
    {
        // Les cases du tableau $donnees ont le nom des colonnes de la base de données
        if (isset($donnees['nom'])) { $this->_nom = $donnees['nom']; }
        if (isset($donnees['lien'])) { $this->_lien = $donnees['lien']; }
        if (isset($donnees['chemin'])) { $this->_chemin = $donnees['chemin']; }
        if (isset($donnees['categorie'])) { $this->_categorie = $donnees['categorie']; }
        if (isset($donnees['afficher']) && $donnees['afficher'] == 0) { $this->_afficher = 'non'; }
        if (isset($donnees['afficher']) && $donnees['afficher'] == 1) { $this->_afficher = 'oui'; }
    }

    // GETTERS
    public function getNom() { return $this->_nom;}
    public function getLien() { return $this->_lien;}
    public function getChemin() { return $this->_chemin;}
    public function getCategorie() { return $this->_categorie;}
    public function getAfficher() { return $this->_afficher;}

    // SETTERS
    public function setNom($nom) { $this->_nom = $nom; }
    public function setLien($lien) { $this->_lien = $lien; }
    public function setChemin($chemin) { $this->_chemin = $chemin; }
    public function setCategorie($categorie) { $this->_categorie = $categorie; }
    public function setAfficher($afficher) { $this->_afficher = $afficher; }    
}

?>
