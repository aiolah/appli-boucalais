<?php 
class Devis {
    private $_idDevis;
    private $_dateDevis;
    private $_dateDebut;
    private $_dateFin;
    private $_organisme;
    private $_typeGroupe;
    private $_nbAdultes;
    private $_nbEnfants;
    private $_nbAdos;
    private $_duree;
    private $_typePension;
    private $_typeHebergement;
    private $_prixHebergement;
    private $_prixActivites;
    private $_prixOptions;
    private $_prixTotal;
    private $_statut;
    private $_tailleGroupe;

    public function __construct(array $donnees) {
        if(isset($donnees['ID_DEVIS'])) { $this->_idDevis = $donnees['ID_DEVIS'];}
        if(isset($donnees['DATE_DEVIS'])) { $this->_dateDevis = $donnees['DATE_DEVIS'];}
        if(isset($donnees['DATE_DEBUT'])) { $this->_dateDebut = $donnees['DATE_DEBUT']; }
        if(isset($donnees['DATE_FIN'])) { $this->_dateFin = $donnees['DATE_FIN']; }
        if(isset($donnees['NOM_GROUPE'])) { $this->_organisme = $donnees['NOM_GROUPE']; }
        if(isset($donnees['TYPE_GROUPE'])) { $this->_typeGroupe = $donnees['TYPE_GROUPE']; }
        if(isset($donnees['TYPE_SCOLAIRE'])) {
            $this->_typeGroupe = $donnees['TYPE_GROUPE'].", ".$donnees['TYPE_SCOLAIRE'];
        }
        if(isset($donnees['NB_ADULTES']))
        {
            if(isset($donnees['TYPE_SCOLAIRE']) && $donnees['TYPE_SCOLAIRE'] == "lycée")
            {
                $this->_nbEnfants = 0;
                $this->_nbAdultes = $donnees['NB_ENFANTS'] + $donnees['NB_ADULTES'];
            }
            else
            {
                $this->_nbAdultes = $donnees['NB_ADULTES'];
            }
        }
        if(isset($donnees['NB_ENFANTS']))
        {
            if(isset($donnees['TYPE_SCOLAIRE']) && $donnees['TYPE_SCOLAIRE'] == "collège")
            {
                $this->_nbEnfants = 0;
                $this->_nbAdos = $donnees['NB_ENFANTS'];
            }
            else if(isset($donnees['TYPE_SCOLAIRE']) && $donnees['TYPE_SCOLAIRE'] == "lycée")
            {
                $this->_nbEnfants = 0;
            }
            else
            {
                $this->_nbEnfants = $donnees['NB_ENFANTS'];
            }
        }
        if(isset($donnees['NB_ADOS']))
        {
            if(isset($donnees['TYPE_SCOLAIRE']) && ($donnees['TYPE_SCOLAIRE'] == "primaire" || $donnees['TYPE_SCOLAIRE'] == "lycée"))
            {
                $this->_nbAdos = 0;
            }
            else
            {
                $this->_nbAdos = $donnees['NB_ADOS'];
            }
        }
        if(isset($donnees['DUREE'])) { $this->_duree = $donnees['DUREE']; }
        if(isset($donnees['TYPE_PENSION'])) {
            if(isset($donnees['CHAPITEAU']))
            {
                $this->_typePension = $donnees['TYPE_PENSION'].", ".$donnees['CHAPITEAU'];
            }
            else
            {
                $this->_typePension = $donnees['TYPE_PENSION'];
            }
        }
        if(isset($donnees['TYPE_HEBERGEMENT'])) { $this->_typeHebergement = $donnees['TYPE_HEBERGEMENT']; }
        if(isset($donnees['PRIX_HEBERGEMENT'])) { $this->_prixHebergement = $donnees['PRIX_HEBERGEMENT']; }
        if(isset($donnees['PRIX_ACTIVITES'])) { $this->_prixActivites = $donnees['PRIX_ACTIVITES']; }
        if(isset($donnees['PRIX_FRAIS_OPTIONNELS'])) { $this->_prixOptions = $donnees['PRIX_FRAIS_OPTIONNELS']; }
        if(isset($donnees['PRIX_TOTAL'])) { $this->_prixTotal = $donnees['PRIX_TOTAL']; }
        if(isset($donnees['STATUT'])) { $this->_statut = $donnees['STATUT']; }
        if(isset($donnees['TAILLE_GROUPE'])) { $this->_tailleGroupe = $donnees['TAILLE_GROUPE']; }
    }

    // GETTERS
    public function getIdDevis() { return $this->_idDevis; }
    public function getDateDevis() { return $this->_dateDevis; }
    public function getDateDebut() { return $this->_dateDebut; }
    public function getDateFin() { return $this->_dateFin; }
    public function getOrganisme() { return $this->_organisme; }
    public function getTypeGroupe() { return $this->_typeGroupe; }
    public function getNbAdultes() { return $this->_nbAdultes; }
    public function getNbEnfants() { return $this->_nbEnfants; }
    public function getNbAdos() { return $this->_nbAdos; }
    public function getDuree() { return $this->_duree; }
    public function getTypePension() { return $this->_typePension; }
    public function getTypeHebergement() { return $this->_typeHebergement; }
    public function getPrixHebergement() { return $this->_prixHebergement; }
    public function getPrixActivites() { return $this->_prixActivites; }
    public function getPrixOptions() { return $this->_prixOptions; }
    public function getPrixTotal() { return $this->_prixTotal; }
    public function getStatut() { return $this->_statut; }
    public function getTailleGroupe() { return $this->_tailleGroupe; }


    // SETTERS
    public function setIdDevis($id) { $this->_idDevis = $id; }
    public function setDateDevis($date) { $this->_dateDevis = $date; }
    public function setDateDebut($dateD) { $this->_dateDebut = $dateD; }
    public function setDateFin($dateF) { $this->_dateFin = $dateF; }
    public function setOrganisme($organisme) { $this->_organisme = $organisme; }
    public function setTypeGroupe($value) { $this->_typeGroupe = $value; }
    public function setNbAdultes($nombre) { $this->_nbAdultes = $nombre; }
    public function setNbEnfants($nombre) { $this->_nbEnfants = $nombre; }
    public function setNbAdos($nombre) { $this->_nbAdos = $nombre; }
    public function setDuree($duree) { $this->_duree = $duree; }
    public function setTypePension($choix) { $this->_typePension = $choix; }
    public function setTypeHebergement($hebergement) { $this->_typeHebergement = $hebergement; }
    public function setPrixHebergement($prixHebergement) { $this->_prixHebergement = $prixHebergement; }
    public function setPrixActivites($prixActivites) { $this->_prixActivites = $prixActivites; }
    public function setPrixOptions($prixOptions) { $this->_prixOptions = $prixOptions; }
    public function setPrixTotal($prixTotal) { $this->_prixTotal = $prixTotal; }
    public function setStatut(int $nombre) { $this->_statut = $nombre; }
    public function setTailleGroupe(int $nombre) { $this->_tailleGroupe = $nombre; }

}