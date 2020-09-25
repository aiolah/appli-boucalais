<?php
class ReservationManager {
    private $db; // Objet de connexion à la base de données

    /**
     * Connexion vers le SGBD
     */
    public function __construct($db) {
        $this->_db = $db;
    }

    /**
     * Ajout des informations d'une réservation passée avec le formulaire dans la base de données
     * @param Reservation
     */
    public function add(Reservation $reservation) {

        $stmt = $this->_db->prepare("SELECT MAX(ID_RESERVATION) AS MAXIMUM FROM LE_BOUCALAIS_RESERVATION");
        $stmt->execute();
		$reservation->setIdReservation($stmt->fetchColumn()+1);
        
		// requete d'ajout de la réservation dans la BD
		$req = "INSERT INTO LE_BOUCALAIS_RESERVATION (DATE_DEBUT,DATE_FIN,DUREE,TYPE_GROUPE,TYPE_PENSION,ACTIVITE,NB_ENFANTS,NB_ADOS,TAILLE_GROUPE,OPTIONS) VALUES (?,?,?,?,?,?,?,?,?,?);";
		$stmt = $this->_db->prepare($req);
		$res = $stmt->execute(array($reservation->getDateDebut(), $reservation->getDateFin(), $reservation->getDuree(), $reservation->getTypeGroupe(), $reservation->getPension(), $reservation->getActivites(), $reservation->getNbEnfants(), $reservation->getNbAdos(), $reservation->getTailleGroupe(), $reservation->getOptions()));

		$reqReserve = "INSERT INTO LE_BOUCALAIS_RESERVE (ID_UTILISATEUR,ID_RESERVATION) VALUES (?,?);";
		$stmtReserve = $this->_db->prepare($reqReserve);
		$resReserve = $stmtReserve->execute(array($_SESSION['id'],$reservation->getIdReservation()));
		
        // pour debuguer les requêtes SQL
        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }
		return $res;
	}
	
	/**
	* Suppression d'une réservation dans la base de données
	* @param Reservation
	* @return boolean true si suppression, false sinon
	*/
	public function delete(Reservation $reservation) {
		$req = "DELETE FROM LE_BOUCALAIS_RESERVATION WHERE ID_RESERVATION = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($reservation->getIdReservation()));
		return $stmt;
	}

	/**
	* Recherche dans la BD des réservations effectuées par une personne
	* @param idReservation
	* @return reservations ou false
	*/
	public function getReservationsFromUser($idUser) {
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE 
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
			WHERE LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idUser));

		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}

		while($donnees = $stmt->fetch())
		{
			$reservations[] = new Reservation($donnees);
		}
		if(isset($reservations))
		{
			return $reservations;
		}
		else return false;
	}

	/**
	* Récupère les réservations confirmées d'un client
	* @param idReservation
	* @return reservations ou false
	*/
	public function getReservationsConfirmedFromUser($idUser) {
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE 
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
			WHERE LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = ? AND LE_BOUCALAIS_RESERVATION.STATUT = 1";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idUser));

		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}

		while($donnees = $stmt->fetch())
		{
			$reservations[] = new Reservation($donnees);
		}
		if(isset($reservations))
		{
			return $reservations;
		}
		else return false;
	}

	/**
	* recherche dans la BD d'une réservation à partir de son id et de sa date de début
	* @param idUser
	* @param datedebut
	* @return Reservation
	*/
	public function getReserv($idUser, $dateDebut) {
		$req = "SELECT LE_BOUCALAIS_RESERVE.ID_RESERVATION,DATE_DEBUT,DUREE,DATE_FIN,LE_BOUCALAIS_UTILISATEUR.TYPE_GROUPE,LE_BOUCALAIS_RESERVATION.TAILLE_GROUPE,NB_ENFANTS,NB_ADOS,TYPE_PENSION,LE_BOUCALAIS_RESERVE.ID_UTILISATEUR,PRENOM,NOM,MAIL FROM LE_BOUCALAIS_RESERVE 
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
			WHERE LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = ? AND DATE_DEBUT = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idUser,strval($dateDebut)));
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		
		while ($donnees = $stmt->fetch()) {
			$reservation[] = new Reservation($donnees);
		}
		return $reservation;
	}

	/**
	 * Récupère une réservation depuis son id, retourne l'objet Réservation en question
	 * @param idReservation
	 */
	public function getReservationFromIdReserv($idReservation)
	{
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE 
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
			WHERE LE_BOUCALAIS_RESERVATION.ID_RESERVATION = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idReservation));
		$result = $stmt->fetch();
		
		if($result)
		{
			$reservation = new Reservation($result);
			return $reservation;
		}
		else return false;
		
		/* rowCount() compte le nombre de lignes renvoyées par la requête */
		// if($stmt->rowCount() == 0)
		// {
		// 	return 0;
		// }

		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
	}

	/**
	 * Récupère et retourne une réservation depuis l'id du devis correspondant
	 * @param idReservation
	 */
	public function getReservationFromIdDevis($idDevis)
	{
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE 
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
			WHERE LE_BOUCALAIS_RESERVE.ID_DEVIS = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idDevis));
		$result = $stmt->fetch();
		
		if($result)
		{
			$reservation = new Reservation($result);
			return $reservation;
		}
		else return false;
		
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
	}

    /**
	* Retourne l'ensemble des réservations présentes dans la BD
	* @return Reservation[]
	*/
	public function getListeReservation() {
		$reservation = array();
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION";
		$stmt = $this->_db->prepare($req);
		$stmt->execute();
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		// recup des données
		while ($donnees = $stmt->fetch())
		{
			$reservation[] = new Reservation($donnees);
		}
		return $reservation;
	}

	/**
	* Retourne l'ensemble des réservations présentes dans la BD dans l'ordre chronologique (de la date de début)
	* @return Reservation[]
	*/
	public function getListeReservationByDate() {
		$reservations = array();
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
			ORDER BY date_debut ASC";
		$stmt = $this->_db->prepare($req);
		$stmt->execute();
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		// recup des données
		while ($donnees = $stmt->fetch())
		{
			$reservations[] = new Reservation($donnees);
		}
		return $reservations;
	}

	/**
	 * Récupère les réservations non confirmées (= demandes de réservation) pour la semaine
	 * @param lundi Format date : premier jour de la semaine
	 * @param dimanche Format date : dernier jour de la semaine
	 */
	// 3 conditions OR : 1 - si la date de début est antérieure ou égale à lundi et la date de fin supérieure ou égale à dimanche (= pour les réservations sur plusieurs semaines ou de lundi à dimanche), 2 - si la date de début se trouve entre lundi et dimanche inclus (= début d'une réservation sur plusieurs semaines ou réservation contenue dans la semaine), 3 - si la date de fin se trouve entre lundi et dimanche inclus (= fin d'une réservation sur plusieurs semaines ou réservation contenue dans la semaine). [Note : les réservations contenues dans la semaine vérifient donc les 2 dernières conditions !]
	public function getListeReservationsNotConfirmedByWeeks($lundi, $dimanche)
	{
		$reservations = array();
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
			WHERE LE_BOUCALAIS_RESERVATION.statut = 0 AND (date_debut <= ? AND date_fin >= ? OR date_debut BETWEEN ? AND ? OR date_fin BETWEEN ? AND ?)
			ORDER BY date_debut ASC";
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
			$reservations[] = new Reservation($donnees);
		}
		return $reservations;
	}

	/**
	 * Récupère et retourne l'effectif total des réservations non confirmées pour la date passée en paramètre
	 * @param date date pour laquelle on souhaite connaître l'effectif total
	 */
	public function effectifTotalNotConfirmed($date)
	{
		$req = "SELECT SUM(taille_groupe) AS effectif_total FROM LE_BOUCALAIS_RESERVATION WHERE date_debut <= ? AND ? < date_fin AND LE_BOUCALAIS_RESERVATION.statut = 0";
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
	 * Récupère les réservations confirmées pour la semaine
	 * @param lundi Format date : premier jour de la semaine
	 * @param dimanche Format date : dernier jour de la semaine
	 */
	// 3 conditions OR : 1 - si la date de début est antérieure ou égale à lundi et la date de fin supérieure ou égale à dimanche (= pour les réservations sur plusieurs semaines ou de lundi à dimanche), 2 - si la date de début se trouve entre lundi et dimanche inclus (= début d'une réservation sur plusieurs semaines ou réservation contenue dans la semaine), 3 - si la date de fin se trouve entre lundi et dimanche inclus (= fin d'une réservation sur plusieurs semaines ou réservation contenue dans la semaine). [Note : les réservations contenues dans la semaine vérifient donc les 2 dernières conditions !]
	public function getListeReservationsConfirmedByWeeks($lundi, $dimanche)
	{
		$reservations = array();
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
			WHERE LE_BOUCALAIS_RESERVATION.statut = 1 AND (date_debut <= ? AND date_fin >= ? OR date_debut BETWEEN ? AND ? OR date_fin BETWEEN ? AND ?)
			ORDER BY date_debut ASC";
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
			$reservations[] = new Reservation($donnees);
		}
		return $reservations;
	}

	/**
	 * Récupère et retourne l'effectif total des réservations confirmées pour la date passée en paramètre
	 * @param date date pour laquelle on souhaite connaître l'effectif total
	 */
	public function effectifTotalConfirmed($date)
	{
		$req = "SELECT SUM(taille_groupe) AS effectif_total FROM LE_BOUCALAIS_RESERVATION WHERE date_debut <= ? AND ? < date_fin AND LE_BOUCALAIS_RESERVATION.statut = 1";
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
	 * Récupère toutes les demandes de réservation (= réservations non confirmées)
	 */
	public function getListeReservationNotConfirmed()
	{
		$reservations = array();
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
			WHERE LE_BOUCALAIS_RESERVATION.statut = 0
			ORDER BY date_debut ASC";
		$stmt = $this->_db->prepare($req);
		$stmt->execute();
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		// recup des données
		while ($donnees = $stmt->fetch())
		{
			$reservations[] = new Reservation($donnees);
		}
		return $reservations;
	}

	/**
	 * Retourne les réservations selon le secteur entré en paramètre
	 *  @param secteur PE, RDC, 1er, B1, B2, B3, B4, B5, M1, M2 ou M3
	 */
	public function getListeReservationBySector($secteur)
	{
		$reservations = array();
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
			WHERE LE_BOUCALAIS_RESERVATION.secteur = ?
			ORDER BY date_debut ASC";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($secteur));
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		// recup des données
		while ($donnees = $stmt->fetch())
		{
			$reservations[] = new Reservation($donnees);
		}
		return $reservations;
	}
	 
	/**
	 * Enregistre le nombre de personnes dans chaque secteur spécifié (HEBERGEMENT) et renseigne ledit secteur dans RESERVATION
	 * @param id_reservation
	 * @param POST : tableau $_POST contenant le(s) secteur(s) et le nombre de personnes pour chacun d'entre eux
	 */
	public function confirmerReservation($id_reservation, $POST)
	{
		// Si un seul secteur est sélectionné | 2 pour le nom du secteur et la valeur du submit : 'confirmer'
		if(count($POST) == 2)
		{
			// On récupère l'index de la première case = nom du secteur
			$secteur = key($POST);
			// Les noms de colonnes ne peuvent pas être passés sous forme de variable avec execute() dont il faut les concaténer avant.. (sinon mettre toute la requête dans le execute() ?)
			$req = "INSERT INTO LE_BOUCALAIS_HEBERGEMENT(id_reservation, " . $secteur . ") values (?, ?)";
			$stmt = $this->_db->prepare($req);
			$stmt->execute(array($id_reservation, $POST[$secteur]));

			$req = "UPDATE LE_BOUCALAIS_RESERVATION SET secteur = ?, statut = 1 WHERE id_reservation = ?";
			$stmt = $this->_db->prepare($req);
			$stmt->execute(array($secteur, $id_reservation));

			// On récupère l'id_utilisateur à partir de l'id_reservation
			$req = "SELECT LE_BOUCALAIS_RESERVE.id_utilisateur FROM LE_BOUCALAIS_RESERVE
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVATION.id_reservation = LE_BOUCALAIS_RESERVE.id_reservation
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_UTILISATEUR.id_utilisateur = LE_BOUCALAIS_RESERVE.id_utilisateur
			WHERE LE_BOUCALAIS_RESERVE.id_reservation = ?";
			$stmt = $this->_db->prepare($req);
			$stmt->execute(array($id_reservation));
			$resultat = $stmt->fetch();
			$id_utilisateur = $resultat['id_utilisateur'];
			
			// Pour actualiser le statut utilisateur à 5
			$req = "UPDATE LE_BOUCALAIS_UTILISATEUR SET statut = 5 WHERE id_utilisateur = ?";
			$stmt = $this->_db->prepare($req);
			$stmt->execute(array($id_utilisateur));

			// pour debuguer les requêtes SQL
			$errorInfo = $stmt->errorInfo();
			if ($errorInfo[0] != 0) {
				print_r($errorInfo);
			}
		}
		// Sinon, on insère une ligne vide et on la modifie ensuite pour chaque secteur
		else
		{
			// Le secteur d'une réservation pour laquelle le gérant a choisi plusieurs hébergements correpond au premier sélectionné
			$secteur = key($POST);
			$req = "INSERT INTO LE_BOUCALAIS_HEBERGEMENT(id_reservation) values (?)";
			$stmt = $this->_db->prepare($req);
			$stmt->execute(array($id_reservation));

			$req = "UPDATE LE_BOUCALAIS_RESERVATION SET secteur = ?, statut = 1 WHERE id_reservation = ?";
			$stmt = $this->_db->prepare($req);
			$stmt->execute(array($secteur, $id_reservation));

			// On récupère l'id_utilisateur à partir de l'id_reservation
			$req = "SELECT LE_BOUCALAIS_RESERVE.id_utilisateur FROM LE_BOUCALAIS_RESERVE
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVATION.id_reservation = LE_BOUCALAIS_RESERVE.id_reservation
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_UTILISATEUR.id_utilisateur = LE_BOUCALAIS_RESERVE.id_utilisateur
			WHERE LE_BOUCALAIS_RESERVE.id_reservation = ?";
			$stmt = $this->_db->prepare($req);
			$stmt->execute(array($id_reservation));
			$resultat = $stmt->fetch();
			$id_utilisateur = $resultat['id_utilisateur'];
			
			// Pour actualiser le statut utilisateur à 5
			$req = "UPDATE LE_BOUCALAIS_UTILISATEUR SET statut = 5 WHERE id_utilisateur = ?";
			$stmt = $this->_db->prepare($req);
			$stmt->execute(array($id_utilisateur));	

			// pour debuguer les requêtes SQL
			$errorInfo = $stmt->errorInfo();
			if ($errorInfo[0] != 0) {
				print_r($errorInfo);
			}

			foreach($POST as $index=>$valeur)
			{
				// Pour éviter de boucler sur le submit
				if(is_numeric($valeur))
				{
					$req = "UPDATE LE_BOUCALAIS_HEBERGEMENT SET " . $index. " = ? WHERE id_reservation = ?";
					$stmt = $this->_db->prepare($req);
					$stmt->execute(array($valeur, $id_reservation));

					// pour debuguer les requêtes SQL
					$errorInfo = $stmt->errorInfo();
					if ($errorInfo[0] != 0) {
						print_r($errorInfo);
					}
				}
			}
		}
	}

	/**
	 * Confirme la réservation en passant son statut à 1. Pas de gestion de l'hébergement
	 * @param id_reservation
	 */
	public function confirmerReservationSansHebergement($id_reservation)
	{
		$req = "UPDATE LE_BOUCALAIS_RESERVATION SET statut = 1 WHERE id_reservation = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($id_reservation));

		// On récupère l'id_utilisateur à partir de l'id_reservation
		$req = "SELECT LE_BOUCALAIS_RESERVE.id_utilisateur FROM LE_BOUCALAIS_RESERVE
		INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVATION.id_reservation = LE_BOUCALAIS_RESERVE.id_reservation
		INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_UTILISATEUR.id_utilisateur = LE_BOUCALAIS_RESERVE.id_utilisateur
		WHERE LE_BOUCALAIS_RESERVE.id_reservation = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($id_reservation));
		$resultat = $stmt->fetch();
		$id_utilisateur = $resultat['id_utilisateur'];
		
		// Pour actualiser le statut utilisateur à 5
		$req = "UPDATE LE_BOUCALAIS_UTILISATEUR SET statut = 5 WHERE id_utilisateur = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($id_utilisateur));

		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
	}

	/**
	 * Envoie un mail de notification au client comme quoi sa réservation a été confirmée par le gérant
	 * @param mail mail du client
	 */
	public function envoiMailConfirmation($mail)
	{
		date_default_timezone_set('Europe/Paris');

		$objet = "Confirmation de réservation : votre réservation vient d'être confirmée";
		$message = "
		<html>
		<body>
			Bonjour,<br><br> 
			
			Votre réservation a bien été confirmée par le gérant du centre. Vous recevrez prochainement votre convention ainsi que votre facture d'acompte.<br><br>
			
			Vous pouvez maintenant consulter divers documents administratifs sur votre espace membre de l'application.<br><br>
			
			A bientôt au <a href='http://leboucalais.fr' target='_blank'>Boucalais</a> !

		</body>
		</html>";
		$destinataire = $mail;
		$headers = "Content-Type: text/html; charset=\"utf-8\"\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Date: " . date(DateTime::RFC2822) . "\n";
		$headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
		$headers .= "Reply-To: contact@leboucalais.fr";

		mail($destinataire, $objet, $message, $headers);
	}

	/**
	 * Récupère les types d'hébergement et le nombre de personnes pour chacun d'entre eux
	 * @param idReservation
	 */
	public function getHebergement($idReservation)
	{
		$req = "SELECT PE, RDC, 1er, M1, M2, M3, B1, B2, B3, B4, B5 FROM LE_BOUCALAIS_HEBERGEMENT WHERE LE_BOUCALAIS_HEBERGEMENT.id_reservation = ? ";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idReservation));
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Modifie les dates d'une réservation
	 * @param idReservation
	 * @param dateDebut
	 * @param dateFin
	 */
	public function modifierDatesReservation($idReservation, $dateDebut, $dateFin)
	{
		$req = "UPDATE LE_BOUCALAIS_RESERVATION SET DATE_DEBUT = ?, DATE_FIN = ? WHERE ID_RESERVATION = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($dateDebut, $dateFin, $idReservation));
		return $stmt;
	}
	
    /**
	* Nombre de réservations dans la base de données
	* @return int le nombre de réservations
	*/
	public function count() {
		$stmt = $this->_db->prepare('SELECT COUNT(*) FROM LE_BOUCALAIS_RESERVATION');
		$stmt->execute();
		return $stmt->fetchColumn();
	}
}
?>