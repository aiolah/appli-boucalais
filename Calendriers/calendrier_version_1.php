<?php session_start(); ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous"> 
    <link rel="stylesheet" href="./Css/calendrier.css">
    <link rel="stylesheet" href="./Css/style.css">
    <title>Document</title>
</head>
<body>

<nav class="navbar navbar-toggleable-sm navbar-light sticky-top bg-faded" style="margin-bottom: 0;">
    <div class="navbar-header">
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="/application">
    Le Boucalais
    </a>
    </div>
<div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
<!--<?php //if($_SESSION['role'] == "client" || $_SESSION['role'] == "prospect"): ?>
        <li class="nav-item"><a class="nav-link" href="?action=form-reserv">Formulaire</a></li>
<?php //endif; ?>-->
<?php if($_SESSION['role'] == "gerant"): ?>
        <li class="nav-item"><a class="nav-link" href="/application/calendrier.php">Planning de réservations</a></li>
        <!--<li class="nav-item"> <a class="nav-link" href="?action=clients">Clients</a></li>-->
<?php endif; ?>
<?php if($_SESSION['acces'] == "oui"): ?>
    <li class="nav-item"><a class="nav-link" href="?action=logout">Déconnexion</a></li>
<?php endif; ?>
<?php if($_SESSION['acces'] == "non"): ?>
    <li class="nav-item"><a class="nav-link" href="?action=connexion">Connexion</a></li>       
<?php endif; ?>
    </ul>
</div>
</nav>

<nav class="navbar navbar-dark mb-3 bg-primary">
    <a>Mon calendrier</a>
</nav>

<?php

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

require_once "connect.php";
require "./Models/Month.php";
require "./Models/Reservation.php";
require "./Modules/ReservationManager.php";

$ReservationManager = new ReservationManager($bdd);
$reservations = $ReservationManager->getListeReservationByDate();
$colors = ['gold', 'blue', 'red', 'cyan', 'coral', 'blueviolet', 'aquamarine', 'crimson', 'rebeccapurple', 'green', 'orange', 'magenta', 'indigo', 'maroon', 'deeppink'];

// On teste la création d'une instance Month. Si il y a une exception "lancée" lors de la création de l'instance, on crée automatiquement un Month avec la date d'aujourd'hui
try
{
    $month = new Month($_GET['month'] ?? null, $_GET['year'] ?? null);
    $weeks = $month->getCountWeeks();
    // Contient le numéro du lundi précédent de la semaine
    $start = $month->getFirstDay();
    // Problème si le mois comment un lundi : ça rajoute la semaine d'avant et on ne voit pas tous les jours du mois
    // Du coup : Si le premier jour du mois est égal à 1, on garde le premier jour comme étant égal à 1. Sinon, on commence le mois avec le précédent lundi
    $start = $start->format('N') === '1' ? $start : $month->getFirstDay()->modify('last monday');
    $count = 1;
}
catch (Exception $e)
{
    echo $e->getMessage();
    $month = new Month();
}

if($_SESSION['acces'] == "oui" && $_SESSION['role'] == "gerant")
{

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
        <?php for($i = 0; $i < $weeks; $i++): ?>
            <tr>
                <?php
                    foreach($month->days as $k => $day):
                    $date = (clone $start)->modify(($k + $i * 7) . " days");
                    $firstReservation = $ReservationManager->isReservationFirstOfDayAll($date->format('Y-m-d'));
                    ?>
                <!-- Si la date fait partie du mois, on ne fait rien, sinon on lui donne la classe calendar__othermonth -->
                <td class="<?= $month->withinMonth($date) ? '' : 'calendar__othermonth'; ?>">
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

                        // On définit un index pour chaque réservation lors de la première boucle
                        if($reservation->getIndex() == null)
                        {
                            $reservation->setIndex($index);
                        }

                        $dateFin = new DateTime($reservation->getDateFin());
                        $dateDepart = (clone $dateFin)->modify('+1 day');

                        /* DÉFINITION DE L'OFFSET DU PREMIER JOUR DE RÉSERVATION ET DE CHAQUE DIV RÉSERVATION */
                        // Si c'est le premier jour de la réservation (mais que ça n'est pas un lundi), on définit l'offset du premier jour de réservation et la taille de l'offset (espace au-dessus de lui)
                        if(($day != "Lundi" && is_object($firstReservation) && $firstReservation->getIdReservation() == $reservation->getIdReservation() && $date->format('Y-m-d') != $reservation->getDateDebut()) || ($date->format('Y-m-d') == $dateDepart->format('Y-m-d') && $firstDepart == true))
                        {
                            $firstReservationDayPositionOffset = $reservation->getPosition() + $reservation->getPrevOffset();
                            $number = 33 * ($firstReservationDayPositionOffset- $depart);
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

                        /* AFFICHAGE DES RÉSERVATIONS NON CONFIRMÉES + DÉFINITION DE LA POSITION ET DE L'OFFSET */
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
                                <span class="name_group"><?= $reservation->getOrganisme(); ?></span>
                                <span class="size_group"><?= $reservation->getTailleGroupe(); ?></span>
                            </div>
                        <?php endif;
                        if($dateDepart->format('Y-m-d') == $date->format('Y-m-d')):
                        $depart++; ?>
                            <div class="calendar__reservation" <?= "style='margin-top: " . $number . "px; background-color: " . $colors[$reservation->getIndex()] . "; height: 33px;'" ?>></div>
                        <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- AFFICHAGE DES RÉSERVATIONS CONFIRMÉES -->
                        <div class="reserv_confirmees-tg">
                            <?php foreach($reservations as $index => $reservation):
                                /* -- PE -- */
                                if($reservation->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') <= $reservation->getDateFin()):
                                    if($reservation->getSecteur() == "PE"): ?>
                                        <div class="div__sector">
                                            <span class="sector_name">PE</span>
                                            <span class="calendar_reservation"><?= $reservation->getOrganisme() . " " . $reservation->getTailleGroupe(); ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <?php foreach($reservations as $index => $reservation):
                                /* RDC */
                                if($reservation->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') <= $reservation->getDateFin()):
                                    if($reservation->getSecteur() == "RDC"): ?>
                                        <div class="div__sector">
                                            <span class="sector_name">RDC</span>
                                            <span class="calendar_reservation"><?= $reservation->getOrganisme() . " " . $reservation->getTailleGroupe(); ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                                
                            <?php foreach($reservations as $index => $reservation):
                                /* 1er */
                                if($reservation->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') <= $reservation->getDateFin()):
                                    if($reservation->getSecteur() == "1er"): ?>
                                        <div class="div__sector">
                                            <span class="sector_name">1er</span>
                                            <span class="calendar_reservation"><?= $reservation->getOrganisme() . " " . $reservation->getTailleGroupe(); ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                </td>
                <?php endforeach; ?>
            </tr>
        <?php endfor; ?>
    </table>
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