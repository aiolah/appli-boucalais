<?php

/**
 * Classe Utilisateur : permet de gérer un client déjà (ou bientôt) présent dans la base de données
 */

class Utilisateur
{
    private $_id;
    private $_prenom;
    private $_nom;
    private $_nomGroupe;
    private $_mail;
    private $_telephone;
    private $_typeGroupe;
    private $_tailleGroupe;
    private $_nombreNuits;
    private $_nomDossier;
    private $_mdp;
    private $_jeton;
    private $_reinitialisation;
    private $_dateLien;
    private $_date;
    private $_dateInscription;
    private $_moyen;
    private $_statut;
    private $_role;

    public function __construct(array $donnees)
    {
        $this->hydrate($donnees);
    }

    public function hydrate(array $donnees)
    {
        // Les cases du tableau $donnees ont le nom des colonnes de la base de données
        if (isset($donnees['ID_UTILISATEUR'])) { $this->_id = $donnees['ID_UTILISATEUR']; }
        if (isset($donnees['PRENOM'])) { $this->_prenom = $donnees['PRENOM']; }
        if (isset($donnees['NOM'])) { $this->_nom = $donnees['NOM']; }
        if (isset($donnees['NOM_GROUPE'])) { $this->_nomGroupe = $donnees['NOM_GROUPE']; }
        if (isset($donnees['MAIL'])) { $this->_mail = $donnees['MAIL']; }
        if (isset($donnees['TELEPHONE'])) { $this->_telephone = $donnees['TELEPHONE']; }
        if (isset($donnees['TYPE_GROUPE'])) { $this->_typeGroupe = $donnees['TYPE_GROUPE']; }
        if (isset($donnees['TAILLE_GROUPE'])) { $this->_tailleGroupe = $donnees['TAILLE_GROUPE']; }
        if (isset($donnees['NOMBRE_NUITS'])) { $this->_nombreNuits = $donnees['NOMBRE_NUITS']; }
        if (isset($donnees['NOM_DOSSIER'])) { $this->_nomDossier = $donnees['NOM_DOSSIER']; }
        if (isset($donnees['MOT_DE_PASSE'])) { $this->_mdp = $donnees['MOT_DE_PASSE']; }
        if (isset($donnees['JETON'])) { $this->_jeton = $donnees['JETON']; }
        if (isset($donnees['REINITIALISATION'])) { $this->_reinitialisation = $donnees['REINITIALISATION']; }
        if (isset($donnees['DATE_LIEN'])) { $this->_dateLien = $donnees['DATE_LIEN']; }
        // On met le nom de la colonne lors de l'affichage du résultat de la requête : ADDDATE(date_lien, INTERVAL +24 HOUR)
        if (isset($donnees['ADDDATE(date_lien, INTERVAL +24 HOUR)'])) { $this->_dateLien = $donnees['ADDDATE(date_lien, INTERVAL +24 HOUR)']; }
        if (isset($donnees['DATE'])) { $this->_date = $donnees['DATE']; }
        if (isset($donnees['DATE_INSCRIPTION'])) { $this->_dateInscription = $donnees['DATE_INSCRIPTION']; }
        if (isset($donnees['MOYEN'])) { $this->_moyen = $donnees['MOYEN']; }
        if (isset($donnees['STATUT'])) { $this->_statut = $donnees['STATUT']; }
        if (isset($donnees['ROLE'])) { $this->_role = $donnees['ROLE']; }
    }

    // GETTERS
    public function getId() { return $this->_id;}
    public function getPrenom() { return $this->_prenom;}
    public function getNom() { return $this->_nom;}
    public function getNomGroupe() { return $this->_nomGroupe;}
    public function getMail() { return $this->_mail;}
    public function getTelephone() { return $this->_telephone;}
    public function getTailleGroupe() { return $this->_tailleGroupe;}
    public function getTypeGroupe() { return $this->_typeGroupe;}
    public function getNombreNuits() { return $this->_nombreNuits;}
    public function getNomDossier() { return $this->_nomDossier;}
    public function getMdp() { return $this->_mdp;}
    public function getJeton() { return $this->_jeton;}
    public function getReinitialisation() { return $this->_reinitialisation;}
    public function getDateLien() { return $this->_dateLien;}
    public function getDate() { return $this->_date;}
    public function getDateInscription() { return $this->_dateInscription;}
    public function getMoyen() { return $this->_moyen;}
    public function getStatut() { return $this->_statut;}
    public function getRole() { return $this->_role;}

    // SETTERS
    public function setId($id) { $this->_id = $id; }
    public function setPrenom($prenom) { $this->_prenom = $prenom; }
    public function setNom($nom) { $this->_nom = $nom; }
    public function setNomGroupe($nomGroupe) { $this->_nomGroupe = $nomGroupe; }
    public function setMail($mail) { $this->_mail = $mail; }
    public function setTelephone($telephone) { $this->_telephone = $telephone; }
    public function setTypeGroupe($typeGroupe) { $this->_typeGroupe = $typeGroupe; }
    public function setTailleGroupe($tailleGroupe) { $this->_tailleGroupe = $tailleGroupe; }
    public function setNombreNuits($nombreNuits) { $this->_nombreNuits = $nombreNuits; }
    public function setNomDossier($nomDossier) { $this->_nomDossier = $nomDossier; }
    public function setMdp($mdp) { $this->_mdp = $mdp; }
    public function setJeton($jeton) { $this->_jeton = $jeton; }
    public function setReinitialisation($reinitialisation) { $this->_reinitialisation = $reinitialisation; }
    public function setDateLien($date_lien) { $this->_dateLien = $date_lien; }
    public function setDate($date) { $this->_date = $date; }
    public function setDateInscription($dateInscription) { $this->_dateInscription = $dateInscription; }
    public function setMoyen($moyen) { $this->_moyen = $moyen; }
    public function setStatut($statut) { $this->_statut = $statut; }
    public function setRole($role) { $this->_role = $role; }
}

?>