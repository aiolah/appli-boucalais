<?php 
class Reservation
{
    private $_idReservation;
    private $_idDevis;
    private $_organisme;
    private $_typeGroupe;
    private $_dateDebut;
    private $_dateFin;
    private $_duree;
    private $_pension;
    private $_options;
    private $_activites;
    private $_nbEnfants;
    private $_nbAdos;
    private $_nbAdultes;
    private $_tailleGroupe;
    private $_statut;
    private $_secteur;
    private $_n;
    
    public function __construct(array $donnees) {
        if(isset($donnees['ID_RESERVATION'])) { $this->_idReservation = $donnees['ID_RESERVATION'];}
        if(isset($donnees['ID_DEVIS'])) { $this->_idDevis = $donnees['ID_DEVIS'];}
        if(isset($donnees['DATE_DEBUT'])) { $this->_dateDebut = $donnees['DATE_DEBUT']; }
        if(isset($donnees['DATE_FIN'])) { $this->_dateFin = $donnees['DATE_FIN']; }
        if(isset($donnees['NOM_GROUPE'])) { $this->_organisme = $donnees['NOM_GROUPE']; }
        if(isset($donnees['TYPE_GROUPE'])) { $this->_typeGroupe = $donnees['TYPE_GROUPE']; }
        if(isset($donnees['DUREE'])) { $this->_duree = $donnees['DUREE']; }
        if(isset($donnees['TYPE_PENSION'])) { $this->_pension = $donnees['TYPE_PENSION']; }
        if(isset($donnees['NB_ENFANTS'])) { $this->_nbEnfants = $donnees['NB_ENFANTS']; }
        if(isset($donnees['NB_ADOS'])) { $this->_nbAdos = $donnees['NB_ADOS']; }
        if(isset($donnees['NB_ADULTES'])) { $this->_nbAdultes = $donnees['NB_ADULTES']; }
        if(isset($donnees['TAILLE_GROUPE'])) { $this->_tailleGroupe = $donnees['TAILLE_GROUPE']; }
        if(isset($donnees['STATUT'])) { $this->_statut = $donnees['STATUT']; }
        if(isset($donnees['SECTEUR'])) { $this->_secteur = $donnees['SECTEUR']; }
        $this->_n = 0;
    }

    // GETTERS
    public function getIdReservation() { return $this->_idReservation; }
    public function getIdDevis() { return $this->_idDevis; }
    public function getOrganisme() { return $this->_organisme; }
    public function getTypeGroupe() { return $this->_typeGroupe; }
    public function getDateDebut() { return $this->_dateDebut; }
    public function getDateFin() { return $this->_dateFin; }
    public function getDuree() { return $this->_duree; }
    public function getPension() { return $this->_pension; }
    public function getOptions() { return $this->_options; }
    public function getActivites() { return $this->_activites; }
    public function getNbEnfants() { return $this->_nbEnfants; }
    public function getNbAdos() { return $this->_nbAdos; }
    public function getNbAdultes() { return $this->_nbAdultes; }
    public function getTailleGroupe() { return $this->_tailleGroupe; }
    public function getStatut() { return $this->_statut; }
    public function getSecteur() { return $this->_secteur; }
    public function getN() { return $this->_n; }

    // SETTERS
    public function setIdReservation($idReservation) { $this->_id = $idReservation; }
    public function setIdDevis($idDevis) { $this->_id = $idDevis; }
    public function setOrganisme($orgName) { $this->_organisme = $orgName; }
    public function setTypeGroupe($orgType) { $this->_typeGroupe = $orgType; }
    public function setDateDebut($dateD) { $this->_dateDebut = $dateD; }
    public function setDateFin($dateF) { $this->_dateFin = $dateF; }
    public function setDuree($duree) { $this->_duree = $duree; }
    public function setPension($choix) { $this->_pension = $choix; }
    public function setOptionsDivers(array $options) { $this->_options = $options; }
    public function setActivites(array $activite) { $this->_activites = $activite; }
    public function setNbEnfants($nombre) { $this->_nbEnfants = $nombre; }
    public function setNbAdos($nombre) { $this->_nbAdos = $nombre; }
    public function setNbAdultes($nombre) { $this->_nbAdultes = $nombre; }
    public function setTailleGroupe($nombre) { $this->_tailleGroupe = $nombre; }
    public function setStatut($statut) { $this->_statut = $statut; }
    public function setSecteur($secteur) { $this->_secteur = $secteur; }
    public function setN($n) { $this->_n = $n; }

    /**
     * Incrémente l'attribut n de la classe Réservation et le retourne
     */
    public function addN()
    {
        return $this->_n++;
    }

}