<?php

/**
 * Classe DocumentClient : permet de gérer les informations des documents propres à un client en base de données
 */
class DocumentsClient
{
    private $_idReservation;
    private $_nom;
    private $_lien;
    private $_chemin;
    private $_id;
    private $_convention;
    private $_conventionChemin;
    private $_factureAcompte;
    private $_factureAcompteChemin;
    private $_conventionSignee;
    private $_conventionSigneeChemin;
    private $_planChambres;
    private $_planChambresChemin;
    private $_planningActivites;
    private $_planningActivitesChemin;
    private $_menus;
    private $_menusChemin;

    public function __construct(array $donnees)
    {
        $this->hydrate($donnees);
    }

    public function hydrate(array $donnees)
    {
        // Les cases du tableau $donnees ont le nom des colonnes de la base de données
        if (isset($donnees['ID_RESERVATION'])) { $this->_idReservation = $donnees['ID_RESERVATION']; }
        if (isset($donnees['NOM'])) { $this->_nom = $donnees['NOM']; }
        if (isset($donnees['LIEN'])) { $this->_lien = $donnees['LIEN']; }
        if (isset($donnees['CHEMIN'])) { $this->_chemin = $donnees['CHEMIN']; }
        if (isset($donnees['ID_DOCUMENT'])) { $this->_id = $donnees['ID_DOCUMENT']; }
        if (isset($donnees['CONVENTION'])) { $this->_convention = $donnees['CONVENTION']; }
        if (isset($donnees['CONVENTION_CHEMIN'])) { $this->_conventionChemin = $donnees['CONVENTION_CHEMIN']; }
        if (isset($donnees['FACTURE_ACOMPTE'])) { $this->_factureAcompte = $donnees['FACTURE_ACOMPTE']; }
        if (isset($donnees['FACTURE_ACOMPTE_CHEMIN'])) { $this->_factureAcompteChemin = $donnees['FACTURE_ACOMPTE_CHEMIN']; }
        if (isset($donnees['CONVENTION_SIGNEE'])) { $this->_conventionSignee = $donnees['CONVENTION_SIGNEE']; }
        if (isset($donnees['CONVENTION_SIGNEE_CHEMIN'])) { $this->_conventionSigneeChemin = $donnees['CONVENTION_SIGNEE_CHEMIN']; }
        if (isset($donnees['PLAN_CHAMBRES'])) { $this->_planChambres = $donnees['PLAN_CHAMBRES']; }
        if (isset($donnees['PLAN_CHAMBRES_CHEMIN'])) { $this->_planChambresChemin = $donnees['PLAN_CHAMBRES_CHEMIN']; }
        if (isset($donnees['PLANNING_ACTIVITES'])) { $this->_planningActivites = $donnees['PLANNING_ACTIVITES']; }
        if (isset($donnees['PLANNING_ACTIVITES_CHEMIN'])) { $this->_planningActivitesChemin = $donnees['PLANNING_ACTIVITES_CHEMIN']; }
        if (isset($donnees['MENUS'])) { $this->_menus = $donnees['MENUS']; }
        if (isset($donnees['MENUS_CHEMIN'])) { $this->_menusChemin = $donnees['MENUS_CHEMIN']; }
    }

    // GETTERS
    public function getIdReservation() { return $this->_idReservation;}
    public function getNom() { return $this->_nom;}
    public function getLien() { return $this->_lien;}
    public function getChemin() { return $this->_chemin;}
    public function getId() { return $this->_id;}
    public function getConvention() { return $this->_convention;}
    public function getConventionChemin() { return $this->_conventionChemin;}
    public function getFactureAcompte() { return $this->_factureAcompte;}
    public function getFactureAcompteChemin() { return $this->_factureAcompteChemin;}
    public function getConventionSignee() { return $this->_conventionSignee;}
    public function getConventionSigneeChemin() { return $this->_conventionSigneeChemin;}
    public function getPlanChambres() { return $this->_planChambres;}
    public function getPlanChambresChemin() { return $this->_planChambresChemin;}
    public function getPlanningActivites() { return $this->_planningActivites;}
    public function getPlanningActivitesChemin() { return $this->_planningActivitesChemin;}
    public function getMenus() { return $this->_menus;}
    public function getMenusChemin() { return $this->_menusChemin;}

    // SETTERS
    public function setIdReservation($idReservation) { $this->_idReservation = $idReservation; }
    public function setNom($nom) { $this->_nom = $nom; }
    public function setLien($lien) { $this->_lien = $lien; }
    public function setChemin($chemin) { $this->_chemin = $chemin; }
    public function setId($id) { $this->_id = $id; }
    public function setConvention($convention) { $this->_convention = $convention; }
    public function setConventionChemin($conventionChemin) { $this->_conventionChemin = $conventionChemin; }
    public function setFactureAcompte($factureAcompte) { $this->_factureAcompte = $factureAcompte; }
    public function setFactureAcompteChemin($factureAcompteChemin) { $this->_factureAcompteChemin = $factureAcompteChemin; }
    public function setConventionSignee($conventionSignee) { $this->_conventionSignee = $conventionSignee; }
    public function setConventionSigneeChemin($conventionSigneeChemin) { $this->_conventionSigneeChemin = $conventionSigneeChemin; }
    public function setPlanChambres($planChambres) { $this->_planChambres = $planChambres; }
    public function setPlanChambresChemin($planChambresChemin) { $this->_planChambresChemin = $planChambresChemin; }
    public function setPlanningActivites($planningActivites) { $this->_planningActivites = $planningActivites; }    
    public function setPlanningActivitesChemin($planningActivitesChemin) { $this->_planningActivitesChemin = $planningActivitesChemin; }    
    public function setMenus($menus) { $this->_menus = $menus; }    
    public function setMenusChemin($menusChemin) { $this->_menusChemin = $menusChemin; }    
}

?>
