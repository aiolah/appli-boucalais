<?php
class DevisManager {
    private $db; // Objet de connexion à la base de données

    /**
     * Connexion vers le SGBD
     */
    public function __construct($db) {
        $this->_db = $db;
    }

    /**
     * Ajout des informations d'un devis passé avec le formulaire dans la base de données
     * @param Devis
	 * @param POST tableau $_POST
	 * @param user objet Utilisateur du client qui a composé le devis ou pour lequel le gérant a attribué un devis
     */
    public function add(Devis $devis, $POST, $user) {

		// Sélectionne l'ID le plus haut, incrémente et l'attribue au devis
        $stmt = $this->_db->prepare("SELECT MAX(ID_DEVIS) AS MAXIMUM FROM LE_BOUCALAIS_DEVIS");
        $stmt->execute();
		$devis->setIdDevis($stmt->fetchColumn()+1);

		// Requête d'ajout du devis dans la BD pour basse saison
		if($devis->getTypeHebergement() == NULL)
		{
			$req = "INSERT INTO LE_BOUCALAIS_DEVIS(ID_DEVIS, DATE_DEVIS, DATE_DEBUT, DATE_FIN, DUREE, TYPE_GROUPE, TYPE_PENSION, PRIX_HEBERGEMENT, PRIX_ACTIVITES, PRIX_FRAIS_OPTIONNELS, PRIX_TOTAL, NB_ADULTES, NB_ENFANTS, NB_ADOS, STATUT, TAILLE_GROUPE) 
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)";
			$stmtSave = $this->_db->prepare($req);
			$res = $stmtSave->execute(array($devis->getIdDevis(), date('Y-m-j'), $devis->getDateDebut(), $devis->getDateFin(), $devis->getDuree(), $devis->getTypeGroupe(), $devis->getTypePension(), $devis->getPrixHebergement(), $devis->getPrixActivites(), $devis->getPrixOptions(), $devis->getPrixTotal(), $devis->getNbAdultes(), $devis->getNbEnfants(), $devis->getNbAdos(), $devis->getTailleGroupe()));
		}
		// Puis pour la haute → type d'hébergement != null
		else
		{
			$req = "INSERT INTO LE_BOUCALAIS_DEVIS(ID_DEVIS, DATE_DEVIS, DATE_DEBUT, DATE_FIN, DUREE, TYPE_GROUPE, TYPE_PENSION, TYPE_HEBERGEMENT, PRIX_HEBERGEMENT, PRIX_ACTIVITES, PRIX_FRAIS_OPTIONNELS, PRIX_TOTAL, NB_ADULTES, NB_ENFANTS, NB_ADOS, STATUT, TAILLE_GROUPE) 
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)";
		$stmtSave = $this->_db->prepare($req);
		$res = $stmtSave->execute(array($devis->getIdDevis(), date('Y-m-j'), $devis->getDateDebut(), $devis->getDateFin(), $devis->getDuree(), $devis->getTypeGroupe(), $devis->getTypePension(), $devis->getTypeHebergement(), $devis->getPrixHebergement(), $devis->getPrixActivites(), $devis->getPrixOptions(), $devis->getPrixTotal(), $devis->getNbAdultes(), $devis->getNbEnfants(), $devis->getNbAdos(), $devis->getTailleGroupe()));
		}

		// Requête de liaison du devis à son client
		$reqDevis = "INSERT INTO LE_BOUCALAIS_EFFECTUE_DEVIS (ID_UTILISATEUR,ID_DEVIS) VALUES (?,?)";
		$stmtDevis = $this->_db->prepare($reqDevis);
		$stmtDevis->execute(array($user->getId(),$devis->getIdDevis()));

		if($user->getStatut() == 2)
		{
			// Change le statut de l'utilisateur au moment de la sauvegarde du devis
			$reqStatut = "UPDATE LE_BOUCALAIS_UTILISATEUR SET STATUT = 3 WHERE ID_UTILISATEUR = ?";
			$stmtStatut = $this->_db->prepare($reqStatut);
			$stmtStatut->execute(array($_SESSION['id']));
		}

		// On ajoute les activités en transmettant l'id du devis
		if($devis->getPrixActivites() != 0)
		{
			$this->addActivites($POST, $devis->getIdDevis());
		}

		// On ajoute les options en transmettant l'id du devis si le prix des options n'est pas égal à 0 ou si la programmation activités est sélectionnée
		if(!empty($POST['OPTIONS']))
		{
			if($devis->getPrixOptions() != 0 || in_array(3, $POST['OPTIONS']))
			{
				$this->addOptions($POST, $devis->getIdDevis());
			}
		}
		
        // pour debuguer les requêtes SQL
        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
		}
		
        $errorInfo = $stmtDevis->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
		}
		
        $errorInfo = $stmtSave->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
		}

		return $stmtDevis;
	}

	/**
	 * Ajoute les activités sélectionnées dans le formulaire de devis
	 * @param POST tableau $_POST
	 * @param idDevis id du devis
	 */
	public function addActivites($POST, $idDevis)
	{
		$i = 0;
		while($i < count($POST['ACTIVITES']))
		{
			$reqActiviteDevis = "INSERT INTO LE_BOUCALAIS_CHOIX_ACTIVITE(ID_DEVIS, ID_ACTIVITE, NB_SEANCES, NB_PARTICIPANTS, PRIX_ACTIVITE) VALUES(?, ?, ?, ?, ?)";
			$stmtActiviteDevis = $this->_db->prepare($reqActiviteDevis);
			$stmtActiviteDevis->execute(array($idDevis, $POST['ACTIVITES'][$i], $POST['ACTIVITE_SEANCES'][$i], $POST['ACTIVITE_PARTICIPANTS'][$i], $POST['PRIX_ACTIVITE'][$i]));
			$i++;
		}
	}

	/**
	 * Ajoute les frais optionnels sélectionnés dans le formulaire de devis
	 * @param POST tableau $_POST
	 * @param idDevis id du devis
	 */
	public function addOptions($POST, $idDevis)
	{
		$i = 0;
		while($i < count($POST['OPTIONS']))
		{
			$reqOptionDevis = "INSERT INTO LE_BOUCALAIS_CHOIX_OPTION(ID_DEVIS, ID_OPTION, PRIX_OPTION) VALUES(?, ?, ?)";
			$stmtOptionDevis = $this->_db->prepare($reqOptionDevis);
			$stmtOptionDevis->execute(array($idDevis, $POST['OPTIONS'][$i], $POST['PRIX_OPTION'][$i]));
			$i++;
		}
	}
	
	/**
	* suppression d'un ou plusieurs devis dans la base de données
	* @param POST tableau $_POST contenant les id des devis
	* @return boolean true si suppression, false sinon
	*/
	public function delete($POST)
	{
		foreach($POST as $devis)
		{
			$this->deleteActivitesDevis($devis);
			$this->deleteOptionsDevis($devis);

			$req = "DELETE FROM LE_BOUCALAIS_EFFECTUE_DEVIS WHERE ID_DEVIS = ? AND ID_UTILISATEUR = ?";
			$stmt = $this->_db->prepare($req);
			$stmt->execute(array($devis, $_SESSION['id']));

			if($stmt == true)
			{
				$req = "DELETE FROM LE_BOUCALAIS_DEVIS WHERE ID_DEVIS = ?";
				$stmt = $this->_db->prepare($req);
				$stmt->execute(array($devis));
			}
			else return false;
		}
		return $stmt;
	}

	/**
	 * Supprime les activités relatives au devis
	 * @param devis id du devis
	 */
	public function deleteActivitesDevis($devis)
	{
		// On sélectionne les id des devis pour lesquels des activités ont été réservées
		$req = "SELECT ID_DEVIS FROM LE_BOUCALAIS_CHOIX_ACTIVITE WHERE ID_DEVIS = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($devis));

		// Puis pour les résultats différents de false, on supprime les lignes !
		while($donnees = $stmt->fetch())
		{
			$req = "DELETE FROM LE_BOUCALAIS_CHOIX_ACTIVITE WHERE ID_DEVIS = ?";
			$stmt = $this->_db->prepare($req);
			$stmt->execute(array($donnees['ID_DEVIS']));	
		}
	}

	/**
	 * Supprime les options relatives au devis
	 * @param devis id du devis
	 */
	public function deleteOptionsDevis($devis)
	{
		$req = "SELECT ID_DEVIS FROM LE_BOUCALAIS_CHOIX_OPTION WHERE ID_DEVIS = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($devis));

		while($donnees = $stmt->fetch())
		{
			$req = "DELETE FROM LE_BOUCALAIS_CHOIX_OPTION WHERE ID_DEVIS = ?";
			$stmt = $this->_db->prepare($req);
			$stmt->execute(array($donnees['ID_DEVIS']));	
		}
	}

	/**
	* recherche dans la BD des devis effectuées par une personne
	* @param int $idUser
	* @return Devis[] si non null, null sinon
	*/
	public function getDevisFromUser($idUser) {
		$req = "SELECT LE_BOUCALAIS_EFFECTUE_DEVIS.ID_DEVIS, DATE_DEVIS, DATE_DEBUT,DATE_FIN,DUREE,LE_BOUCALAIS_DEVIS.TYPE_GROUPE,TYPE_PENSION,
				LE_BOUCALAIS_EFFECTUE_DEVIS.ID_UTILISATEUR,PRENOM,NOM,MAIL,TELEPHONE,LE_BOUCALAIS_DEVIS.STATUT
			FROM LE_BOUCALAIS_EFFECTUE_DEVIS 
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_DEVIS ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_DEVIS = LE_BOUCALAIS_DEVIS.ID_DEVIS
			WHERE LE_BOUCALAIS_EFFECTUE_DEVIS.ID_UTILISATEUR = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idUser));
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}

		while($donnees = $stmt->fetch())
		{
			$devis[] = new Devis($donnees);
		}

		if(isset($devis) && $devis != null) {
			return $devis;
		}
		else { return null; }
	}

	/**
	 * Récupère un devis depuis l'id de la réservation
	 * @param idReservation
	 * @return Devis instance de la classe Devis
	 */
	public function getDevisFromIdReservation($idReservation)
	{
		$req = "SELECT * FROM LE_BOUCALAIS_DEVIS 
		INNER JOIN LE_BOUCALAIS_RESERVE ON LE_BOUCALAIS_RESERVE.ID_DEVIS = LE_BOUCALAIS_DEVIS.ID_DEVIS
		WHERE LE_BOUCALAIS_RESERVE.ID_RESERVATION = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idReservation));

		$devis = new Devis($stmt->fetch());
		return $devis;
	}

	/**
	* Recherche un devis à partir de l'ID de son utilisateur et de la date de début du séjour
	* @param int $idUser, String $dateDebut
	* @return Devis
	*/
	public function getDevisAtDate($idUser, $dateDebut) {
		$req = "SELECT LE_BOUCALAIS_EFFECTUE_DEVIS.ID_DEVIS, DATE_DEVIS, DATE_DEBUT,DUREE,DATE_FIN,LE_BOUCALAIS_UTILISATEUR.TYPE_GROUPE,NB_ADULTES,NB_ENFANTS,NB_ADOS,TYPE_PENSION,LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR,PRENOM,NOM,MAIL,LE_BOUCALAIS_DEVIS.STATUT FROM LE_BOUCALAIS_EFFECTUE_DEVIS 
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_DEVIS ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_DEVIS = LE_BOUCALAIS_DEVIS.ID_DEVIS
			WHERE LE_BOUCALAIS_EFFECTUE_DEVIS.ID_UTILISATEUR = ? AND DATE_DEBUT = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idUser,strval($dateDebut)));
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		
		while ($donnees = $stmt->fetch()) {
			$devis[] = new Devis($donnees);
		}
		return $devis;
	}

	/**
	 * Retourne le dernier devis passé par un utilisateur
	 * @param int $idUser
	 * @return Object contient les données du devis.
	 */
	public function getLastDevisFromUser($idUser) {
		$req = "SELECT MAX(ID_DEVIS) as MAXDEVIS FROM LE_BOUCALAIS_EFFECTUE_DEVIS WHERE ID_UTILISATEUR = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idUser));
		$idDevis = $stmt->fetch();

		$reqDevis = "SELECT * FROM LE_BOUCALAIS_DEVIS WHERE ID_DEVIS = ?";
		$stmtDevis = $this->_db->prepare($reqDevis);
		$stmtDevis->execute(array($idDevis['MAXDEVIS']));

		$devis = $stmtDevis->fetch();

		return $devis;
	}

    /**
	* Retourne des données sur l'ensemble des devis présents dans la BD
	* @return Devis[]
	*/
	public function getListeDevis() {
		$devis = [];
		$req = "SELECT LE_BOUCALAIS_DEVIS.ID_DEVIS, DATE_DEVIS, LE_BOUCALAIS_DEVIS.TYPE_GROUPE,LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR,
			LE_BOUCALAIS_UTILISATEUR.NOM_GROUPE,LE_BOUCALAIS_UTILISATEUR.NOM,LE_BOUCALAIS_UTILISATEUR.PRENOM,
			LE_BOUCALAIS_UTILISATEUR.MAIL,LE_BOUCALAIS_DEVIS.DATE_DEBUT,LE_BOUCALAIS_DEVIS.TYPE_PENSION,LE_BOUCALAIS_DEVIS.STATUT 
			FROM LE_BOUCALAIS_EFFECTUE_DEVIS
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_DEVIS ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_DEVIS = LE_BOUCALAIS_DEVIS.ID_DEVIS";
		$stmt = $this->_db->prepare($req);
		$stmt->execute();

		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}

		// Recup des données
		$devis = [];
		while ($donnees = $stmt->fetch())
		{
			array_push($devis,$donnees);
		}
		return $devis;
	}

	// 3 conditions OR : 1 - si la date de début est antérieure ou égale à lundi et la date de fin supérieure ou égale à dimanche (= pour les devis sur plusieurs semaines ou de lundi à dimanche), 2 - si la date de début se trouve entre lundi et dimanche inclus (= début d'un devis sur plusieurs semaines ou devis contenu dans la semaine), 3 - si la date de fin se trouve entre lundi et dimanche inclus (= fin d'un devis sur plusieurs semaines ou devis contenu dans la semaine). [Note : les devis contenus dans la semaine vérifient donc les 2 dernières conditions !]
	public function getListeDevisByWeeks($lundi, $dimanche)
	{
		$devis = array();
		$req = "SELECT * FROM LE_BOUCALAIS_EFFECTUE_DEVIS
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_DEVIS ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_DEVIS = LE_BOUCALAIS_DEVIS.ID_DEVIS
			WHERE LE_BOUCALAIS_DEVIS.statut = 0 AND (date_debut <= ? AND date_fin >= ? OR date_debut BETWEEN ? AND ? OR date_fin BETWEEN ? AND ?)
			ORDER BY date_debut, LE_BOUCALAIS_EFFECTUE_DEVIS.ID_DEVIS ASC ";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($lundi, $dimanche, $lundi, $dimanche, $lundi, $dimanche));

		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}

		// recup des données
		while ($donnees = $stmt->fetch())
		{
			$devis[] = new Devis($donnees);
		}
		return $devis;
	}

	public function effectifTotal($date)
	{
		$req = "SELECT SUM(taille_groupe) AS effectif_total FROM LE_BOUCALAIS_DEVIS WHERE date_debut <= ? AND ? < date_fin AND LE_BOUCALAIS_DEVIS.statut = 0";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($date, $date));
	
		$result = $stmt->fetch();

		if($result['effectif_total'] != NULL)
		{
			return $result['effectif_total'];
		}
		else return 0;
	}

	/**
	 * Récupère le nombre de devis pour l'utilisateur passé en paramètre
	 * @param id Id de l'utilisateur
	 */
	public function getCountDevis($id)
	{
		$req = "SELECT COUNT(ID_DEVIS) FROM LE_BOUCALAIS_EFFECTUE_DEVIS WHERE ID_UTILISATEUR = ?";
		$stmt = $this->_db->prepare($req);
        $stmt->execute(array($id));

		$nbreDevis = $stmt->fetch()["COUNT(ID_DEVIS)"];
		return $nbreDevis;
	}

	/**
	 * Récupère le devis avec l'ID fourni en paramètre et vérifie que le devis cherché est rattaché à l'utilisateur connecté.
	 * @param Number $idDevis ID du devis.
	 * @return Devis
	 */
	public function getDevisFromIdUser($id, $idDevis) {
		$req = "SELECT LE_BOUCALAIS_DEVIS.ID_DEVIS, DATE_DEVIS, DATE_DEBUT, DATE_FIN, DUREE, LE_BOUCALAIS_DEVIS.TYPE_GROUPE, TYPE_PENSION, TYPE_HEBERGEMENT, PRIX_HEBERGEMENT, PRIX_ACTIVITES, PRIX_FRAIS_OPTIONNELS, PRIX_TOTAL, NB_ADULTES, NB_ENFANTS, NB_ADOS, LE_BOUCALAIS_DEVIS.TAILLE_GROUPE, LE_BOUCALAIS_DEVIS.STATUT FROM LE_BOUCALAIS_EFFECTUE_DEVIS
			INNER JOIN LE_BOUCALAIS_DEVIS ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_DEVIS = LE_BOUCALAIS_DEVIS.ID_DEVIS
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			WHERE LE_BOUCALAIS_EFFECTUE_DEVIS.ID_UTILISATEUR = ? AND LE_BOUCALAIS_EFFECTUE_DEVIS.ID_DEVIS = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($id, $idDevis));

		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}

		$devis = 0;
		while($donnees = $stmt->fetch())
		{
			$devis = new Devis($donnees);
		}
		if($devis != null)
		{
			return $devis;
		}
		else return false;
	}

	/**
	 * Récupère le devis avec l'ID fourni en paramètre.
	 * @return Devis
	 */
	public function getDevisFromId($idDevis) {
		$req = "SELECT LE_BOUCALAIS_DEVIS.ID_DEVIS, DATE_DEVIS, DATE_DEBUT, DATE_FIN, DUREE, LE_BOUCALAIS_DEVIS.TYPE_GROUPE, TYPE_PENSION, TYPE_HEBERGEMENT, PRIX_HEBERGEMENT, PRIX_ACTIVITES, PRIX_FRAIS_OPTIONNELS, PRIX_TOTAL, NB_ADULTES, NB_ENFANTS, NB_ADOS, LE_BOUCALAIS_DEVIS.TAILLE_GROUPE, LE_BOUCALAIS_DEVIS.STATUT FROM LE_BOUCALAIS_EFFECTUE_DEVIS
			INNER JOIN LE_BOUCALAIS_DEVIS ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_DEVIS = LE_BOUCALAIS_DEVIS.ID_DEVIS
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_EFFECTUE_DEVIS.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			WHERE LE_BOUCALAIS_EFFECTUE_DEVIS.ID_DEVIS = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idDevis));

		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}

		$devis = 0;
		while($donnees = $stmt->fetch())
		{
			$devis = new Devis($donnees);
		}
		if($devis != null)
		{
			return $devis;
		}
		else return false;
	}

	/**
	 * Insère les données du devis dans la table Réservation
	 * @param Devis
	 */
	public function toReservation(Devis $devis/* , $activiteManager, $optionManager */) {

		$req = "INSERT INTO LE_BOUCALAIS_RESERVATION (DATE_RESERVATION, DATE_DEBUT, DATE_FIN, DUREE, TYPE_GROUPE, TYPE_PENSION, TYPE_HEBERGEMENT, PRIX_HEBERGEMENT, PRIX_ACTIVITES, PRIX_FRAIS_OPTIONNELS, PRIX_TOTAL, NB_ENFANTS, NB_ADOS, NB_ADULTES, TAILLE_GROUPE) 
			SELECT ?, DATE_DEBUT, DATE_FIN, DUREE, TYPE_GROUPE, TYPE_PENSION, TYPE_HEBERGEMENT, PRIX_HEBERGEMENT, PRIX_ACTIVITES, PRIX_FRAIS_OPTIONNELS, PRIX_TOTAL, NB_ENFANTS, NB_ADOS, NB_ADULTES, TAILLE_GROUPE
			FROM LE_BOUCALAIS_DEVIS WHERE ID_DEVIS = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array(date('Y-m-d'), $devis->getIdDevis()));
		
		$reqStatutDevis = "UPDATE LE_BOUCALAIS_DEVIS SET STATUT = 1 WHERE ID_DEVIS = ?";
		$stmtStatutDevis = $this->_db->prepare($reqStatutDevis);
		$stmtStatutDevis->execute(array($devis->getIdDevis()));

		$reqStatut = "UPDATE LE_BOUCALAIS_UTILISATEUR SET STATUT = 4 WHERE ID_UTILISATEUR = ?";
		$stmtStatut = $this->_db->prepare($reqStatut);
		$stmtStatut->execute(array($_SESSION['id']));

		$reqReservation = "SELECT MAX(ID_RESERVATION) AS MAX_ID FROM LE_BOUCALAIS_RESERVATION";
		$stmtReservation = $this->_db->prepare($reqReservation);
		$stmtReservation->execute();
		$idReservation = $stmtReservation->fetch();

		/* if($devis->getPrixActivites() != 0)
		{
			$this->addActivitesReservation($devis->getIdDevis(), $activiteManager, $idReservation['MAX_ID']);
		}

		if($devis->getPrixOptions() != 0)
		{
			$this->addOptionsReservation($devis->getIdDevis(), $optionManager, $idReservation['MAX_ID']);
		} */

		$reqReserve = "INSERT INTO LE_BOUCALAIS_RESERVE (ID_UTILISATEUR, ID_RESERVATION, ID_DEVIS) VALUES (?, ?, ?)";
		$stmtReserve = $this->_db->prepare($reqReserve);
		$stmtReserve->execute(array($_SESSION['id'], $idReservation['MAX_ID'], $devis->getIdDevis()));

		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}

		return $stmtReserve;
	}

	/* public function addActivitesReservation($idDevis, $activiteManager, $idReservation)
	{
		$activites = $activiteManager->getActivitesFromDevis($idDevis);
		if($activites != false)
		{
			foreach($activites as $activite)
			{
				$req = "INSERT INTO LE_BOUCALAIS_RESERVATION_ACTIVITE(ID_RESERVATION, ID_DEVIS, NB_SEANCES, NB_PARTICIPANTS, PRIX_ACTIVITE)
				SELECT ?, ID_DEVIS, NB_SEANCES, NB_PARTICIPANTS, PRIX_ACTIVITE FROM LE_BOUCALAIS_CHOIX_ACTIVITE WHERE ID_DEVIS = ? AND ID_ACTIVITE = ?";
				$stmt = $this->_db->prepare($req);
				$stmt->execute(array($idReservation, $idDevis, $activite->getIdActivite()));	
			}
		}
	}

	public function addOptionsReservation($idDevis, $optionManager, $idReservation)
	{
		$options = $optionManager->getOptionsFromDevis($idDevis);
		if($options != false)
		{
			foreach($options as $option)
			{
				$req = "INSERT INTO LE_BOUCALAIS_RESERVATION_OPTION(ID_RESERVATION, ID_DEVIS, NB_SEANCES, NB_PARTICIPANTS, PRIX_ACTIVITE)
				SELECT ?, ID_DEVIS, NB_SEANCES, NB_PARTICIPANTS, PRIX_ACTIVITE FROM LE_BOUCALAIS_CHOIX_ACTIVITE WHERE ID_DEVIS = ? AND ID_ACTIVITE = ?";
				$stmt = $this->_db->prepare($req);
				$stmt->execute(array($idReservation, $idDevis, $option->getIdOption()));	
			}
		}
	} */
	
    /**
	* nombre de devis dans la base de données
	* @return int le nombre de devis
	*/
	public function count() {
		$stmt = $this->_db->prepare('SELECT COUNT(*) FROM LE_BOUCALAIS_DEVIS');
		$stmt->execute();
		return $stmt->fetchColumn();
	}
}
?>