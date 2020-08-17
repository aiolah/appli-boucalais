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
     * @param Reservation
     * Ajout des informations d'une réservation passée avec le formulaire dans la base de données
     */
    public function add(Reservation $reservation) {

        $stmt = $this->_db->prepare("SELECT MAX(ID_RESERVATION) AS MAXIMUM FROM LE_BOUCALAIS_RESERVATION");
        $stmt->execute();
		$reservation->setIdReservation($stmt->fetchColumn()+1);
        
		// requete d'ajout de la réservation dans la BD
		$req = "INSERT INTO LE_BOUCALAIS_RESERVATION (DATE_DEBUT,DATE_FIN,DUREE,TYPE_GROUPE,TYPE_PENSION,ACTIVITE,NB_ENFANTS,NB_ADOS,TAILLE_GROUPE,OPTIONS) VALUES (?,?,?,?,?,?,?,?,?,?);";
		$stmt = $this->_db->prepare($req);
		//print_r($reservation->getOptions());
		//print_r($reservation->getActivites());
		$res = $stmt->execute(array($reservation->getDateDebut(), $reservation->getDateFin(), $reservation->getDuree(), $reservation->getTypeGroupe(), $reservation->getPension(), $reservation->getActivites(), $reservation->getNbEnfants(), $reservation->getNbAdos(), $reservation->getTailleGroupe(), $reservation->getOptions()));

		$reqReserve = "INSERT INTO LE_BOUCALAIS_RESERVE (ID_UTILISATEUR,ID_RESERVATION) VALUES (?,?);";
		$stmtReserve = $this->_db->prepare($reqReserve);
		$resReserve = $stmtReserve->execute(array($_SESSION['id'],$reservation->getIdReservation()));
		//var_dump($res);
		
        // pour debuguer les requêtes SQL
        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }
		return $res;
	}
	
	/**
	* suppression d'une réservation dans la base de données
	* @param Reservation
	* @return boolean true si suppression, false sinon
	*/
	public function delete(Reservation $reservation) {
		$req = "DELETE FROM LE_BOUCALAIS_RESERVATION WHERE ID_RESERVATION = ?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($reservation->getIdReservation()));
		//print_r($req);
		return $stmt;
	}

	/**
	* recherche dans la BD des réservations effectuées par une personne
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
	* recherche dans la BD d'une réservation à partir de son id et de sa date de début
	* @param int $idReservation
	* @return Reservation
	*/
	public function getReserv($idUser, $dateDebut) {
		$req = "SELECT LE_BOUCALAIS_RESERVE.ID_RESERVATION,DATE_DEBUT,DUREE,DATE_FIN,LE_BOUCALAIS_UTILISATEUR.TYPE_GROUPE,LE_BOUCALAIS_RESERVATION.TAILLE_GROUPE,NB_ENFANTS,NB_ADOS,TYPE_PENSION,LE_BOUCALAIS_RESERVE.ID_UTILISATEUR,PRENOM,NOM,MAIL FROM LE_BOUCALAIS_RESERVE 
			INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
			INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
			WHERE LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = ? AND DATE_DEBUT = ?";
		$stmt = $this->_db->prepare($req);
		//print_r($idUser.", ".$dateDebut);
		$stmt->execute(array($idUser,strval($dateDebut)));
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		
		while ($donnees = $stmt->fetch()) {
			$reservation[] = new Reservation($donnees);
		}
		//print_r($reservation);
		return $reservation;
	}

	/**
	 * Récupère une réservation depuis son id, retourne l'objet Réservation en question
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
	* retourne l'ensemble des réservations présentes dans la BD
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
	 * Retourne la première réservation d'un jour et d'un secteur passés en paramètre
	 */
	public function isReservationFirstOfDay($date, $secteur)
	{
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE
		INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
		INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
		WHERE LE_BOUCALAIS_RESERVATION.date_debut <= ? AND ? <= LE_BOUCALAIS_RESERVATION.date_fin AND LE_BOUCALAIS_RESERVATION.secteur = ?
		ORDER BY date_debut ASC";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($date, $date, $secteur));
		$result = $stmt->fetch();

		if($result != false)
		{
			$reservation = new Reservation($result);
			return $reservation;
		}
		else
		{
			return false;
		}

		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
	}

	/**
	 * Retourne la première réservation d'un jour passé en paramètre pour les réservations non confirmées
	 */
	public function isReservationFirstOfDayNotConfirmed($date)
	{
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE
		INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
		INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
		WHERE LE_BOUCALAIS_RESERVATION.date_debut <= ? AND ? <= LE_BOUCALAIS_RESERVATION.date_fin AND LE_BOUCALAIS_RESERVATION.statut = 0
		ORDER BY date_debut ASC";
		// Le fait d'avoir enlevé 'ORDER BY date_debut ASC a rajouté un espace au-dessus de la div MON TEST
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($date, $date));
		$result = $stmt->fetch();

		if($result != false)
		{
			$reservation = new Reservation($result);
			return $reservation;
		}
		else
		{
			return false;
		}

		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
	}

	/**
	 * Retourne la première réservation d'un jour passé en paramètre pour toutes les réservations stockées en bdd
	 */
	public function isReservationFirstOfDayAll($date)
	{
		$req = "SELECT * FROM LE_BOUCALAIS_RESERVE
		INNER JOIN LE_BOUCALAIS_UTILISATEUR ON LE_BOUCALAIS_RESERVE.ID_UTILISATEUR = LE_BOUCALAIS_UTILISATEUR.ID_UTILISATEUR
		INNER JOIN LE_BOUCALAIS_RESERVATION ON LE_BOUCALAIS_RESERVE.ID_RESERVATION = LE_BOUCALAIS_RESERVATION.ID_RESERVATION
		WHERE LE_BOUCALAIS_RESERVATION.date_debut <= ? AND ? <= LE_BOUCALAIS_RESERVATION.date_fin
		ORDER BY date_debut ASC";
		// Le fait d'avoir enlevé 'ORDER BY date_debut ASC a rajouté un espace au-dessus de la div MON TEST
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($date, $date));
		$result = $stmt->fetch();

		if($result != false)
		{
			$reservation = new Reservation($result);
			return $reservation;
		}
		else
		{
			return false;
		}

		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
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
	 * Envoie un mail de notification au client comme quoi sa réservation a été confirmée par le gérant
	 * @param mail mail du client
	 */
	public function envoiMailConfirmation($mail)
	{
		// Envoi mail de confirmation de réservation
		date_default_timezone_set('Europe/Paris');

		$objet = "Confirmation de réservation : votre réservation vient d'être confirmée";
		$message = "
		<html>
		<body>
			Bonjour" /*. Prénom de la personne hehe */ . ",<br><br> 
			
			Votre réservation a bien été confirmée par le gérant du centre. Vous recevrez prochainement votre convention ainsi que votre facture d'acompte.<br><br>
			
			Vous pouvez maintenant consulter divers documents administratifs sur votre espace membre de l'application.<br><br>
			
			A bientôt au <a href='http://leboucalais.fr' target='_blank'>Boucalais</a> !

		</body>
		</html>";
		$destinataire = $mail /* 'yoyo31.music@gmail.com' */;
		$headers = "Content-Type: text/html; charset=\"utf-8\"\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Date: " . date(DateTime::RFC2822) . "\n";
		$headers .= "From: \"Le Boucalais\"<contact@leboucalais.fr>\n";
		$headers .= "Reply-To: contact@leboucalais.fr";

		// mail($mail, $objet, $message, $headers)
		mail($destinataire, $objet, $message, $headers);
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