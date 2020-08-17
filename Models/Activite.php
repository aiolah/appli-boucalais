<?php
class Activite {
    private $_idActivite;
    private $_nomActivite;
    private $_descriptionActivite;
    private $_nbParticipantsGroupe;
    private $_affichageActivite;
    private $_prixActiviteUnite;
    private $_nbSeances;
    private $_nbParticipants;
    private $_prixActivite;

    public function __construct(array $donnees) {
        if(isset($donnees['ID_ACTIVITE'])) { $this->_idActivite = $donnees['ID_ACTIVITE']; }
        if(isset($donnees['NOM_ACTIVITE'])) { $this->_nomActivite = $donnees['NOM_ACTIVITE']; }
        if(isset($donnees['DESCRIPTION_ACTIVITE'])) { $this->_descriptionActivite = $donnees['DESCRIPTION_ACTIVITE']; }
        if(isset($donnees['AFFICHAGE_ACTIVITE'])) { $this->_affichageActivite = $donnees['AFFICHAGE_ACTIVITE']; }
        if(isset($donnees['NB_PARTICIPANTS_GROUPE'])) { $this->_nbParticipantsGroupe = $donnees['NB_PARTICIPANTS_GROUPE']; }
        if(isset($donnees['PRIX_ACTIVITE_UNITE'])) { $this->_prixActiviteUnite = $donnees['PRIX_ACTIVITE_UNITE']; }
        if(isset($donnees['NB_SEANCES'])) { $this->_nbSeances = $donnees['NB_SEANCES']; }
        if(isset($donnees['NB_PARTICIPANTS'])) { $this->_nbParticipants = $donnees['NB_PARTICIPANTS']; }
        if(isset($donnees['PRIX_ACTIVITE'])) { $this->_prixActivite = $donnees['PRIX_ACTIVITE']; }
    }

    // GETTERS
    public function getIdActivite() { return $this->_idActivite; }
    public function getNomActivite() { return $this->_nomActivite; }
    public function getDescriptionActivite() { return $this->_descriptionActivite; }
    public function getNbParticipantsGroupe() { return $this->_nbParticipantsGroupe; }
    public function getAffichageActivite() { return $this->_affichageActivite; }
    public function getPrixActiviteUnite() { return $this->_prixActiviteUnite; }
    public function getNbSeances() { return $this->_nbSeances; }
    public function getNbParticipants() { return $this->_nbParticipants; }
    public function getPrixActivite() { return $this->_prixActivite; }
    
    // SETTERS
    public function setIdActivite($value) { $this->_idActivite = $value; }
    public function setNomActivite($value) { $this->_nomActivite = $value; }
    public function setDescriptionActivite($value) { $this->_descriptionActivite = $value; }
    public function setNbParticipantsGroupe($value) { $this->_nbParticipantsGroupe = $value; }
    public function setAffichageActivite($value) { $this->_affichageActivite = $value; }
    public function setPrixActiviteUnite($value) { $this->_prixActiviteUnite = $value; }
    public function setNbSeances($value) { $this->_nbSeances = $value; }
    public function setNbParticipants($value) { $this->_nbParticipants = $value; }
    public function setPrixActivite($value) { $this->_prixActivite = $value; }
}