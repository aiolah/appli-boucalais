<?php $options = array('samesite'=>'Strict', 'secure'=>true);
session_set_cookie_params($options);
session_start();
if(!isset($_COOKIE['eye']))
{
    setcookie("eye", "closedBlue", time() + 86400);
}
if(!isset($_COOKIE['hide']))
{
    setcookie("hide", "", time() + 86400);
}
if(!isset($_COOKIE['compression']))
{
    setcookie("compression", "compression", time() + 86400);
}
if(!isset($_COOKIE['compress']))
{
    setcookie("compress", "", time() + 86400);
}

if($_SESSION['acces'] == "oui" && $_SESSION['role'] == "gerant")
{

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

require_once "../connect.php";
require "../Models/Month.php";
require "../Models/Reservation.php";
require "../Modules/ReservationManager.php";
require "../Models/Utilisateur.php";
require "../Modules/UtilisateurManager.php";

$ReservationManager = new ReservationManager($bdd);
$UtilisateurManager = new UtilisateurManager($bdd);
$user = $UtilisateurManager->getProfil($_SESSION['id']);

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

    <!-- En incluant ce fichier, on affiche le menu gérant. Comme ça, pas besoin de copier coller tout le temps le menu dans tous les calendriers différents, 1 seul copier coller suffit :D !!! = inclusion de fichiers php -->
    <?php include("./menu-gerant.php"); ?>

    <div class="d-flex flex-row align-items-center justify-content-between mx-sm-3">
            <!-- < + ? + = est un raccourci pour < + ? + php + echo !!-->
        <h1 class="<?= $_COOKIE['compress']; ?>"><?= $month->showMonthYear(); ?></h1>

        <?php $currentMonth = new Month(date('n'), date('Y')); ?>

        <div id="smallNav" class="<?= $_COOKIE['compress']; ?>">
            <button class="btn icon <?= $_COOKIE['eye']; ?>" id="eye"></button>
            <button class="btn icon <?= $_COOKIE['compression']; ?>" id="compression"></button>
            <a href="?month=<?= $currentMonth->getMonth(); ?>&year=<?= $currentMonth->getYear();  ?>" id="currentMonth" class="btn btn-primary <?= $_COOKIE['compress']; ?>">Revenir à <?= $currentMonth->showMonthYear(); ?></a>
            <a href="?month=<?= $month->previousMonth()->_month; ?>&year=<?= $month->previousMonth()->_year; ?>" class="btn btn-primary">&lt;</a>
            <a href="?month=<?= $month->nextMonth()->_month; ?>&year=<?= $month->nextMonth()->_year; ?>" class="btn btn-primary">&gt;</a>
        </div>
    </div>

    <table class="calendar__table calendar__table--<?= $month->getCountWeeks();?>weeks">
        <thead id="mois" class="<?= $_COOKIE['compress']; ?>">
            <tr>
                <th colspan="7"><?= $month->showMonthYear(); ?></th>    
            </tr>
        </thead>

        <?php 

        // Pour chaque semaine : on fait une ligne (tr)
        for($i = 0; $i < $weeks; $i++): ?>
            <!----------------------------------------------------------- LIGNE DE LA SEMAINE -------------------------------------------------------------->
            <tr>
                <?php
                    // Pour chaque jour de la semaine → on fait une case (td)
                    foreach($month->days as $k => $day):
                        $date = (clone $start)->modify(($k + $i * 7) . " days");
                ?>

                <!---------------------------------------------------------- CASE DU JOUR ---------------------------------------------------------------->
                
                <!-- Si la date fait partie du mois, on ne fait rien, sinon on lui donne la classe calendar__othermonth -->
                <td class="<?= $month->withinMonth($date) ? 'day' : 'calendar__othermonth' ; ?> <?= $_COOKIE['compress']; ?> <?= $i == 0 ? ' firstWeek' : '' ; ?>">
                <!-- On affiche le nom du jour mais uniquement pour la première ligne -->
                <?php if($i === 0): ?>
                    <div class="calendar__weekday <?= $_COOKIE['compress']; ?>"><?= $day; ?></div>
                    <?php endif; ?>
                    
                    <?php $effectif = $ReservationManager->effectifTotalNotConfirmed($date->format('Y-m-d')); ?>
                    <!-- On affiche le numéro du jour en clonant le précédent lundi et en le modifiant : on lui rajout le numéro de l'élément du tableau $days + le numéro de la boucle * 7. On formate ensuite en nombre de jour. -->
                    <div class="calendar__day <?= $_COOKIE['compress']; ?>"><?= $date->format('j'); ?><span class="effectif"><?= $effectif; ?></span></div>
                    <!-- Affichage de la réservation si la date de début est inférieure ou égale à la date de la boucle et si la date de la boucle est inférieure ou égale à la date de fin -->

                <?php endforeach; ?>
            </tr>

            <!---------------------------------------------- 1 LIGNE PAR RÉSERVATION (J'AI RÉUSSI :D !!!!!) -------------------------------------------------->
            <?php
            // Premier jour de la semaine
            $date = (clone $start)->modify((0 + $i * 7) . " days");
            $reservationsWeeks = $ReservationManager->getListeReservationsNotConfirmedByWeeks($date->format('Y-m-d'), $date->modify('+ 6 days')->format('Y-m-d'));
            
            foreach($reservationsWeeks as $reservation):
            
                // Premier jour de la réservation
                $day1 = true;

                // Définition du nombre de jours du devis contenus dans la semaine (pour les devis qui sont dans le mois courant)
                $colspan = 0;
                foreach($month->days as $k => $day)
                {
                    $date = (clone $start)->modify(($k + $i * 7) . " days");
                    if($month->withinMonth($date))
                    {
                        if($reservation->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') < $reservation->getDateFin())
                        {
                            $colspan++;
                        }
                    }
                }

                ?>

                <tr>
                    <!-- Pour chaque jour de la semaine -->
                    <?php foreach($month->days as $k => $day):
                        $date = (clone $start)->modify(($k + $i * 7) . " days");

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

                        if(!$month->withinMonth($date)) :
                            if($reservation->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') < $reservation->getDateFin()): ?>

                                <!-- Div pleine d'un devis qui ne fait pas partie du mois -->
                                <td>
                                    <div class="calendar__reservation__weeks not-confirmed <?= $month->withinMonth($date) ? '' : 'opacity' ; ?> <?= $_COOKIE['compress']; ?>">
                                        <span><?= $count . "/" . $duration; ?></span>
                                        <span class="name_group"><a href="https://aiolah-vaiti.fr/appli-boucalais/?action=infos-reservation&reservation=<?= $reservation->getIdReservation(); ?>"><?= $reservation->getOrganisme(); ?></a></span>
                                        <span class="size_group"><?= $reservation->getTailleGroupe(); ?></span>
                                    </div>
                                </td>

                            <?php endif; ?>
                        <?php endif;

                        /* Div "pleine" = remplie avec les infos de la réservation */
                        if($reservation->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') < $reservation->getDateFin() && $day1 && $month->withinMonth($date)): ?>

                            <td colspan="<?= $colspan; ?>">
                                <div class="calendar__reservation__weeks not-confirmed <?= $month->withinMonth($date) ? '' : 'opacity' ; ?> <?= $_COOKIE['compress']; ?>">
                                    <span><?= $duration; ?></span>
                                    <span class="name_group"><a href="https://aiolah-vaiti.fr/appli-boucalais/?action=infos-reservation&reservation=<?= $reservation->getIdReservation(); ?>"><?= $reservation->getOrganisme(); ?></a></span>
                                    <span class="size_group"><?= $reservation->getTailleGroupe(); ?></span>
                                </div>
                            </td>

                            <?php $day1 = false; ?>

                        <!-- Div de départ -->
                        <?php elseif($reservation->getDateFin() == $date->format('Y-m-d')): ?>

                            <td>
                                <div class="calendar__reservation__weeks not-confirmed depart <?= $month->withinMonth($date) ? '' : 'opacity' ; ?> <?= $_COOKIE['compress']; ?>">
                                    <span><?= $count . "/" . $duration; ?></span>
                                    <span class="name_group" <?= $colspan > 1 ? "style= 'display: none;'" : '' ?>><a href="https://aiolah-vaiti.fr/appli-boucalais/?action=infos-reservation&reservation=<?= $reservation->getIdReservation(); ?>"><?= $reservation->getOrganisme(); ?></a></span>
                                    <span class="size_group"><?= $reservation->getTailleGroupe(); ?></span>
                                </div>
                            </td>

                        <!-- Div vide avant la date de début et/ou après la date de fin -->
                        <?php elseif($date->format('Y-m-d') < $reservation->getDateDebut() || $date->format('Y-m-d') > $reservation->getDateFin()): ?>

                            <td class="calendar__reservation__weeks empty"></td>

                        <?php endif; ?>

                    <?php endforeach; ?>
                </tr>

            <?php endforeach; ?>
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

<!-- Les balises script en fin de body -->
<?php include("./scripts.php"); ?>