<?php session_start(); 

if($_SESSION['acces'] == "oui" && $_SESSION['role'] == "gerant")
{
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="./Css/calendrier.css">
    <link rel="stylesheet" href="./Css/style.css">
    <title>Document</title>
</head>
<body>

    <nav class="navbar navbar-expand-md navbar-toggleable-sm navbar-light sticky-top navbar navbar-light" style="background-color: #e3f2fd;">
		<div class="navbar-header">
			<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
            <a class="navbar-brand" href="http://leboucalais.fr/application/?action=accueil">
                Appli Boucalais
            </a>
		</div>
		<div class="collapse navbar-collapse">
			<ul class="navbar-nav mr-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Clients
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="http://leboucalais.fr/application/?action=aperçu">Aperçu</a>
                        <a class="dropdown-item" href="http://leboucalais.fr/application/?action=ajouter-client">Ajouter</a>
                        <a class="dropdown-item" href="http://leboucalais.fr/application/?action=clients">Tous</a>
                    </div>
                </li>

                <li class="nav-item"><a class="nav-link" href="calendrier.php">Planning des réservations</a></li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Paramétrage
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="?action=documents_gerant">Documents</a>
                        <a class="dropdown-item" href="?action=liste-activite">Activités devis</a>
                        <a class="dropdown-item" href="?action=liste-option">Options devis</a>
                    </div>
                </li>
			</ul>

            <div>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="color: black;">Bonjour François</a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="http://leboucalais.fr/application/?action=profil">Mon profil</a>
                            <a class="dropdown-item" href="http://leboucalais.fr/application/?action=logout">Déconnexion</a>
                        </div>
                    </li>
                </ul>
            </div>
		</div>
	</nav>

    <!-- <nav class="navbar navbar-dark mb-3 bg-primary">
        <a>Planning de réservations</a>
    </nav> -->


<?php

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

require_once "connect.php";
require "./Models/Month.php";
require "./Models/Reservation.php";
require "./Modules/ReservationManager.php";

$ReservationManager = new ReservationManager($bdd);
$reservations = $ReservationManager->getListeReservationNotConfirmed();
$reservationsPE = $ReservationManager->getListeReservationBySector("PE");
$reservationsRDC = $ReservationManager->getListeReservationBySector("RDC");
$reservations1er = $ReservationManager->getListeReservationBySector("1er");
$reservationsM1 = $ReservationManager->getListeReservationBySector("M1");
$reservationsM2 = $ReservationManager->getListeReservationBySector("M2");
$reservationsM3 = $ReservationManager->getListeReservationBySector("M3");
$reservationsB1 = $ReservationManager->getListeReservationBySector("B1");
$reservationsB2 = $ReservationManager->getListeReservationBySector("B2");
$reservationsB3 = $ReservationManager->getListeReservationBySector("B3");
$reservationsB4 = $ReservationManager->getListeReservationBySector("B4");
$reservationsB5 = $ReservationManager->getListeReservationBySector("B5");
$colors = ['gold', 'blue', 'red', 'cyan', 'coral', 'blueviolet', 'aquamarine', 'crimson', 'rebeccapurple', 'green', 'orange', 'magenta', 'indigo', 'maroon', 'deeppink'];
$greens = ['#80ffa3', '#42ff77', '#00fa45', '#00c236', '#00992a', '#006b1e', '#386d00', '#00d521', '#00a305', '#00992a', '#006b1e', '#386d00'];
$blues = ['#2982ff', '#0052c7', '#002cdb', '#0096d2', '#2982ff', '#0052c7', '#002cdb', '#0096d2'];
$pinks = ['#ff1a88', '#db006a', '#f000d6', '#cd0080', '#ff1a88', '#db006a', '#f000d6', '#cd0080'];
$oranges = ['#ff6e14', '#fc6000', '#d15000', '#ff7d14'];

// On teste la création d'une instance Month. Si il y a une exception "lancée" lors de la création de l'instance, on crée automatiquement un Month avec la date d'aujourd'hui
try
{
    $month = new Month($_GET['month'] ?? null, $_GET['year'] ?? null);
    $weeks = $month->getCountWeeks();
    // Contient le numéro du lundi précédent de la semaine
    $start = $month->getFirstDay();
    // Problème si le mois commence un lundi : ça rajoute la semaine d'avant et on ne voit pas tous les jours du mois
    // Du coup : Si le premier jour du mois est égal à 1, on garde le premier jour comme étant égal à 1. Sinon, on commence le mois avec le précédent lundi
    $start = $start->format('N') === '1' ? $start : $month->getFirstDay()->modify('last monday');
    $count = 1;
}
catch (Exception $e)
{
    echo $e->getMessage();
    $month = new Month();
}

?>

    <div class="d-flex flex-row align-items-center justify-content-between mx-sm-3">
            <!-- < + ? + = est un raccourci pour < + ? + php + echo !!-->
        <h1><?= $month->showMonthYear(); ?></h1>

        <div>
            <a href="?month=<?= $month->previousMonth()->_month; ?>&year=<?= $month->previousMonth()->_year; ?>" class="btn btn-primary">&lt;</a>
            <a href="?month=<?= $month->nextMonth()->_month; ?>&year=<?= $month->nextMonth()->_year; ?>" class="btn btn-primary">&gt;</a>
        </div>
    </div>

    <table class="calendar__table calendar__table--<?= $month->getCountWeeks();?>weeks">
        <?php 
        $iJourPE = 0;
        $iJourRDC = 0;
        $iJour1er = 0;

        for($i = 0; $i < $weeks; $i++): ?>
            <!--------------------------------------- LIGNE DE LA SEMAINE + RÉSERVATIONS NON CONFIRMÉES ---------------------------------------------------->
            <tr>
                <?php
                    $afficherPE=false;
                    $afficherRDC = false;
                    $afficher1er = false;

                    foreach($month->days as $k => $day):
                        $date = (clone $start)->modify(($k + $i * 7) . " days");
                        $firstReservation = $ReservationManager->isReservationFirstOfDayNotConfirmed($date->format('Y-m-d'));

                        foreach($reservationsPE as $reservation)
                        {
                            if($date->format('Y-m-d') >= $reservation->getDateDebut() && $date->format('Y-m-d') <= $reservation->getDateFin() && !$afficherPE)
                            {
                                $afficherPE = true;
                            }
                        }
                        foreach($reservationsRDC as $reservation)
                        {
                            if($date->format('Y-m-d') >= $reservation->getDateDebut() && $date->format('Y-m-d') <= $reservation->getDateFin() && !$afficherRDC)
                            {
                                $afficherRDC = true;
                            }
                        }
                        foreach($reservations1er as $reservation)
                        {
                            if($date->format('Y-m-d') >= $reservation->getDateDebut() && $date->format('Y-m-d') <= $reservation->getDateFin() && !$afficher1er)
                            {
                                $afficher1er = true;
                            }
                        }
                ?>

                <!------------------------------------------------------ CASE DU JOUR ------------------------------------------->

                <!-- Si la date fait partie du mois, on ne fait rien, sinon on lui donne la classe calendar__othermonth -->

                <td class="<?= $month->withinMonth($date) ? 'day' : 'calendar__othermonth' ; ?><?= $i == 0 ? ' firstWeek' : '' ; ?>">
                    <!-- On affiche le nom du jour mais uniquement pour la première ligne -->
                    <?php if($i === 0): ?>
                        <div class="calendar__weekday"><?= $day; ?></div>
                    <?php endif; ?>

                    <!-- On affiche le numéro du jour en clonant le précédent lundi et en le modifiant : on lui rajout le numéro de l'élément du tableau $days + le numéro de la boucle * 7. On formate ensuite en nombre de jour. -->
                    <div class="calendar__day"><?= $date->format('d'); ?></div>
                    <!-- Affichage de la réservation si la date de début est inférieure ou égale à la date de la boucle et si la date de la boucle est inférieure ou égale à la date de fin -->

                    <?php

                    // Position
                    $pos = 0;
                    // Offset du premier jour de réservation
                    $firstReservationDayPositionOffset = 0;
                    // Compte le nombre de départs dans le jour
                    $depart = 0;
                    // Indique s'il y a au moins 1 départ dans le jour
                    $firstDepart = true;

                    foreach($reservations as $index => $reservation):

                        if(!isset($_SESSION["'" . $reservation->getOrganisme() . "'"]))
                        {
                            $_SESSION["'" . $reservation->getOrganisme() . "'"] = $reservation->getIdReservation();
                        }

                        // On définit un index pour chaque réservation lors de la première boucle
                        if($reservation->getIndex() == null)
                        {
                            $reservation->setIndex($index);
                        }

                        /* DÉFINITION DU N/M JOUR DE SÉJOUR */
                        $dateDebut = date_create($reservation->getDateDebut());
                        $dateFin = date_create($reservation->getDateFin());
                        $interval = date_diff($dateDebut, $dateFin);
                        $duration = intval((clone $interval)->format('%a'));

                        if($date->format('Y-m-d') == $reservation->getDateDebut())
                        {
                            $count = 1;
                            $reservation->setN(1);
                        }
                        elseif($date->format('Y-m-d') == $reservation->getDateFin())
                        {
                            $count = $duration;
                        }
                        else
                        {
                            $reservation->addN();
                            $count = $reservation->getN();
                        }

                        /* DÉFINITION DE LA DATE DE DÉPART */
                        // $dateFin = new DateTime($reservation->getDateFin());
                        // $dateDepart = (clone $dateFin)->modify('+1 day');

                        /* DÉFINITION DE L'OFFSET DU PREMIER JOUR DE RÉSERVATION ET DE CHAQUE DIV RÉSERVATION */
                        // Si le jour n'est pas un lundi
                        // Et que c'est le premier jour de la réservation (mais que ça n'est pas un lundi), on définit l'offset du premier jour de réservation et la taille de l'offset (espace au-dessus de lui),
                        // Ou que c'est la date de début de ladite réservation et qu'il y a des départs dans cette journée,
                        // Ou que c'est la date de départ de ladite réservation et que c'est le premier départ du jour,
                        if(($day != "Lundi" && is_object($firstReservation) && $firstReservation->getIdReservation() == $reservation->getIdReservation() && $date->format('Y-m-d') != $reservation->getDateDebut()) || ($day != "Lundi" && is_object($firstReservation) && $firstReservation->getIdReservation() == $reservation->getIdReservation() && $date->format('Y-m-d') == $reservation->getDateDebut() && $depart > 0) || ($day != 'Lundi' && $date->format('Y-m-d') == $reservation->getDateFin() && $firstDepart == true))
                        {
                            // Si la réservation respecte la deuxième condition, alors il faut lui donner sa position et son prevOffset maintenant (et pas plus loin dans le code) pour qu'elle les aie déjà le jour d'après
                            if($day != "Lundi" && is_object($firstReservation) && $firstReservation->getIdReservation() == $reservation->getIdReservation() && $date->format('Y-m-d') == $reservation->getDateDebut() && $depart > 0)
                            {
                                $reservation->setPosition($pos);
                                if($firstReservationDayPositionOffset != 0)
                                {
                                    $reservation->setPrevOffset($firstReservationDayPositionOffset);
                                }
                            }

                            /*print_r('Position réservation : ' . $reservation->getPosition() . ' ');
                            print_r('PrevOffset : ' . $reservation->getPrevOffset() . ' ');*/
                            $firstReservationDayPositionOffset = $reservation->getPosition() + $reservation->getPrevOffset();
                            /*print_r('firstReservationDayPositionOffset : ' . $firstReservationDayPositionOffset . ' ');
                            print_r('Position globale : ' . $pos . ' ');
                            print_r('firstReservation : ');
                            var_dump($firstReservation);*/

                            $number = 33 * ($firstReservationDayPositionOffset - $depart);
                            if($date->format('Y-m-d') == $reservation->getDateFin() && $firstDepart)
                            {
                                $firstDepart = false;
                            }
                            else if(!$firstDepart)
                            {
                                $number = 0;
                            }
                        }
                        // Sinon pas d'offset
                        else
                        {
                            $number = 0;
                        }

                        /* DÉFINITION DE LA POSITION ET DE L'OFFSET + AFFICHAGE DES RÉSERVATIONS NON CONFIRMÉES  */
                        // Si le jour se situe entre la date de début et de fin de la réservation
                        if($reservation->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') < $reservation->getDateFin()):
                            // Si c'est le premier jour de la réservation ou que c'est un lundi = Première initialisation, on sauvegarde sa position
                            if($reservation->getPosition() === -1 || $day == 'Lundi')
                            {
                                $reservation->setPosition($pos);
                                if($firstReservationDayPositionOffset != 0)
                                {
                                    $reservation->setPrevOffset($firstReservationDayPositionOffset);
                                }
                                if($day == 'Lundi')
                                {
                                    $reservation->setPrevOffset(0);
                                }
                            }

                            $pos++; ?>

                            <div class="calendar__reservation not-confirmed" <?= "style='margin-top: " . $number . "px;'" ?>>
                                <span><?= $count . "/" . $duration; ?></span>
                                <span class="name_group"><a href="http://leboucalais.fr/application-dev/?action=infos_reservation&reservation=<?= $reservation->getIdReservation(); ?>"><?= $reservation->getOrganisme(); ?></a></span>
                                <span class="size_group"><?= $reservation->getTailleGroupe(); ?></span>
                            </div>

                        <?php endif;

                        if($reservation->getDateFin() == $date->format('Y-m-d')):
                        $pos++;
                        $depart++; ?>
                            <div class="calendar__reservation not-confirmed depart" <?= "style='margin-top: " . $number . "px;'" ?>>
                                <span class="name_group depart"><a href="http://leboucalais.fr/application-dev/?action=infos_reservation&reservation=<?= $reservation->getIdReservation(); ?>"><?= $reservation->getOrganisme(); ?></a></span>
                            </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                </td>
                <?php endforeach; ?>
            </tr>

            <!------------------- LIGNES DES RÉSERVATIONS CONFIRMÉES PE, RDC, 1er (+ M1, M2, M3, B1, B2, B3, B4, B5 pour juillet/août) -------------------->

            <?php

            if($afficherPE)
            {
                ligneReservationsSecteur("PE", $reservationsPE, $i, $month, $start, $ReservationManager, $bdd, $greens);
            }
            if($afficherRDC)
            {
                ligneReservationsSecteur("RDC", $reservationsRDC, $i, $month, $start, $ReservationManager, $bdd, $blues);
            }
            if($afficher1er)
            {
                ligneReservationsSecteur("1er", $reservations1er, $i, $month, $start, $ReservationManager, $bdd, $pinks);
            }
            if($month->getMonth() == 'Juillet' || $month->getMonth() == 'Août')
            {
                ligneReservationsSecteur("M1", $reservationsM1, $i, $month, $start, $ReservationManager, $bdd, $oranges);
                ligneReservationsSecteur("M2", $reservationsM2, $i, $month, $start, $ReservationManager, $bdd, $oranges);
                ligneReservationsSecteur("M3", $reservationsM3, $i, $month, $start, $ReservationManager, $bdd, $oranges);
                ligneReservationsSecteur("B1", $reservationsB1, $i, $month, $start, $ReservationManager, $bdd, $oranges);
                ligneReservationsSecteur("B2", $reservationsB2, $i, $month, $start, $ReservationManager, $bdd, $oranges);
                ligneReservationsSecteur("B3", $reservationsB3, $i, $month, $start, $ReservationManager, $bdd, $oranges);
                ligneReservationsSecteur("B4", $reservationsB4, $i, $month, $start, $ReservationManager, $bdd, $oranges);
                ligneReservationsSecteur("B5", $reservationsB5, $i, $month, $start, $ReservationManager, $bdd, $oranges);
            }
            ?>

        <?php endfor; ?>
    </table>

    <!-- <table class="mt-5">

        <tr>
        <?php for($i = 1; $i < $month->getCountDays(); $i++): 
            $date = (clone $start)->modify(($k + $i * 7) . " days");?>

            <td style="width: 1px; border: 1px solid gray"><?= $date->format('d'); ?></td>

        <?php endfor; ?>
        </tr>

    </table> -->
</body>
</html>

<?php

}
else
{

?>

<p class="message red">Vous n'avez pas accès à ce contenu</p>
<p><a href="/application/?action=connexion">Connectez-vous</a> pour accéder à votre compte.</p>

<?php

}

?>

<?php

/* ------------------------------- FONCTION QUI GÉNERE 1 LIGNE PAR SEMAINE POUR CHAQUE SECTEUR PASSÉ EN PARAMETRE ----------------------------- */

function ligneReservationsSecteur($secteur, $reservationsParSecteur, $i, $month, $start, $ReservationManager, $bdd, $colors)
{

?>

<tr id="avec_fonction">
    <?php
        foreach($month->days as $k => $day):
        $date = (clone $start)->modify(($k + $i * 7) . " days");
        //echo $date->format('Y-m-d');
        $firstReservation = $ReservationManager->isReservationFirstOfDay($date->format('Y-m-d'), $secteur); ?>
    <td>

    <?php
        // Position
        $pos = 0;
        // Offset du premier jour de réservation
        $firstReservationDayPositionOffset = 0;

        // Compte le nombre de départs dans le jour
        $depart = 0;
        // Indique s'il y a au moins 1 départ dans le jour
        $firstDepart = true;

        foreach($reservationsParSecteur as $index => $reservation):
            if(!isset($_SESSION["'" . $reservation->getOrganisme() . "'"]))
            {
                $_SESSION["'" . $reservation->getOrganisme() . "'"] = $reservation->getIdReservation();
            }

            //print_r('Départ : ' . $depart);
            if($reservation->getIndex() == null)
            {
                $reservation->setIndex($index);
            }

            /* DÉFINITION DU N/M JOUR DE SÉJOUR */
            $dateDebut = date_create($reservation->getDateDebut());
            $dateFin = date_create($reservation->getDateFin());
            $interval = date_diff($dateDebut, $dateFin);
            $duration = intval((clone $interval)->format('%a')) + 1;
            if($date->format('Y-m-d') == $reservation->getDateDebut())
            {
                $count = 1;
                $reservation->setN(1);
            }
            elseif($date->format('Y-m-d') == $reservation->getDateFin())
            {
                $count = $duration;
            }
            else
            {
                $reservation->addN();
                $count = $reservation->getN();
            }

            /* DÉFINITION DE LA DATE DE DÉPART */
            $dateFin = new DateTime($reservation->getDateFin());
            $dateDepart = (clone $dateFin)->modify('+1 day');

            /* DÉFINITION DE L'OFFSET DU PREMIER JOUR DE RÉSERVATION ET DE CHAQUE DIV RÉSERVATION */
            // Si le jour n'est pas un lundi
            // Et que c'est le premier jour de la réservation (mais que ça n'est pas un lundi), on définit l'offset du premier jour de réservation et la taille de l'offset (espace au-dessus de lui),
            // Ou que c'est la date de début de ladite réservation et qu'il y a des départs dans cette journée,
            // Ou que c'est la date de départ de ladite réservation et que c'est le premier départ du jour,
            if(($day != "Lundi" && is_object($firstReservation) && $firstReservation->getIdReservation() == $reservation->getIdReservation() && $date->format('Y-m-d') != $reservation->getDateDebut()) || ($day != "Lundi" && is_object($firstReservation) && $firstReservation->getIdReservation() == $reservation->getIdReservation() && $date->format('Y-m-d') == $reservation->getDateDebut() && $depart > 0) || ($day != 'Lundi' && $date->format('Y-m-d') == $dateDepart->format('Y-m-d') && $firstDepart == true))
            {
                // Si la réservation respecte la deuxième condition, alors il faut lui donner sa position et son prevOffset maintenant (et pas plus loin dans le code) pour qu'elle les aie déjà le jour d'après
                if($day != "Lundi" && is_object($firstReservation) && $firstReservation->getIdReservation() == $reservation->getIdReservation() && $date->format('Y-m-d') == $reservation->getDateDebut() && $depart > 0)
                {
                    //echo 'COUCOU';
                    $reservation->setPosition($pos);
                    if($firstReservationDayPositionOffset != 0)
                    {
                        $reservation->setPrevOffset($firstReservationDayPositionOffset);
                    }
                }
                /*print_r('Position réservation : ' . $reservation->getPosition() . ' ');

                print_r('PrevOffset : ' . $reservation->getPrevOffset() . ' ');*/
                $firstReservationDayPositionOffset = $reservation->getPosition() + $reservation->getPrevOffset();

                /*print_r('firstReservationDayPositionOffset : ' . $firstReservationDayPositionOffset . ' ');

                print_r('Position globale : ' . $pos . ' ');

                print_r('firstReservation : ');

                var_dump($firstReservation);*/

                $number = 33 * ($firstReservationDayPositionOffset - $depart);
                if($date->format('Y-m-d') == $dateDepart->format('Y-m-d') && $firstDepart)
                {
                    $firstDepart = false;
                }
                else if(!$firstDepart)
                {
                    $number = 0;
                }
            }
            // Sinon pas d'offset
            else
            {
                $number = 0;
            }
            //var_dump($number);

            /* AFFICHAGE DES RÉSERVATIONS PE + DÉFINITION DE LA POSITION ET DE L'OFFSET */
            // Si le jour se situe entre la date de début et de fin de la réservation
            if($reservation->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') <= $reservation->getDateFin()):
                // Si c'est le premier jour de la réservation ou que c'est un lundi = Première initialisation, on sauvegarde sa position
                if($reservation->getPosition() === -1 || $day == 'Lundi')
                {
                    $reservation->setPosition($pos);
                    if($firstReservationDayPositionOffset != 0)
                    {
                        $reservation->setPrevOffset($firstReservationDayPositionOffset);
                    }
                    if($day == 'Lundi')
                    {
                        $reservation->setPrevOffset(0);
                    }
                }
                $pos++; ?>
                <div class="calendar__reservation" <?= "style='margin-top: " . $number . "px; background-color: " . $colors[$reservation->getIndex()] . ";'" ?>>
                    <span><?= $count . "/" . $duration; ?></span>
                    <span class="name_group"><a href="http://leboucalais.fr/application-dev/?action=infos_reservation&reservation=<?= $reservation->getIdReservation(); ?>"><?= $reservation->getOrganisme(); ?></a></span>
                    <span class="size_group"><?= $reservation->getTailleGroupe(); ?></span>
                </div>
            <?php endif;

            if($dateDepart->format('Y-m-d') == $date->format('Y-m-d')):
            $depart++;
            //print_r('Départ : ' . $depart);
            if($depart > 0)
            {
                $pos++;
            } ?>
                <div class="calendar__reservation" <?= "style='margin-top: " . $number . "px; background-color: " . $colors[$reservation->getIndex()] . "; height: 33px;'" ?>></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </td>
    <?php endforeach; ?>
</tr>

<?php

}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src='js/Popups.js'></script>

<script>

// On supprime toutes les ligne qui sont vides. On utilise la méthode trim() pour supprimer les sauts de ligne et les espaces.
/*function removeEmptyLines()
{
    for(let tr of document.getElementsByTagName('tr'))
    {
        console.log(tr.inn);
        if(tr.textContent == '')
        {
            document.getElementsByTagName('tbody')[0].removeChild(tr);
        }
        if(tr.textContent.trim() == '')
        {
            document.getElementsByTagName('tbody')[0].removeChild(tr);
        }
    }
}
setTimeout(removeEmptyLines, 3000);*/

</script>