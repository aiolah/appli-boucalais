<?php
class Option {
    private $_idOption;
    private $_nomOption;
    private $_descriptionOption;
    private $_prixOptionUnite;
    private $_affichageOption;
    private $_prixOption;

    public function __construct(array $donnees) {
        if(isset($donnees['ID_OPTION'])) { $this->_idOption = $donnees['ID_OPTION']; }
        if(isset($donnees['NOM_OPTION'])) { $this->_nomOption = $donnees['NOM_OPTION']; }
        if(isset($donnees['DESCRIPTION_OPTION'])) { $this->_descriptionOption = $donnees['DESCRIPTION_OPTION']; }
        if(isset($donnees['PRIX_OPTION_UNITE'])) { $this->_prixOptionUnite = $donnees['PRIX_OPTION_UNITE']; }
        if(isset($donnees['AFFICHAGE_OPTION'])) { $this->_affichageOption = $donnees['AFFICHAGE_OPTION']; }
        if(isset($donnees['PRIX_OPTION'])) { $this->_prixOption = $donnees['PRIX_OPTION']; }
    }

    // GETTERS
    public function getIdOption() { return $this->_idOption; }
    public function getNomOption() { return $this->_nomOption; }
    public function getDescriptionOption() { return $this->_descriptionOption; }
    public function getPrixOptionUnite() { return $this->_prixOptionUnite; }
    public function getAffichageOption() { return $this->_affichageOption; }
    public function getPrixOption() { return $this->_prixOption; }
    
    // SETTERS
    public function setIdOption($value) { $this->_idOption = $value; }
    public function setNomOption($value) { $this->_nomOption = $value; }
    public function setDescriptionOption($value) { $this->_descriptionOption = $value; }
    public function setPrixOptionUnite($value) { $this->_prixOptionUnite = $value; }
    public function setAffichageOption($value) { $this->_affichageOption = $value; }
    public function setPrixOption($value) { $this->_prixOption = $value; }
}