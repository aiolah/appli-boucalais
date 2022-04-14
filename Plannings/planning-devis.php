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
require "../Models/Devis.php";
require "../Modules/DevisManager.php";
require "../Models/Utilisateur.php";
require "../Modules/UtilisateurManager.php";

$devisManager = new DevisManager($bdd);
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
            <!-------------------------------------------------------- LIGNE DE LA SEMAINE --------------------------------------------------------->
            <tr>
                <?php

                    // Pour chaque jour de la semaine → on fait une case (td)
                    foreach($month->days as $k => $day):
                        $date = (clone $start)->modify(($k + $i * 7) . " days");
                ?>

                <!------------------------------------------------------ CASE DU JOUR ------------------------------------------->
                
                <?php $effectif = $devisManager->effectifTotal($date->format('Y-m-d')); /* var_dump($effectif); */ ?>
                
                <!-- Si la date fait partie du mois, on ne fait rien, sinon on lui donne la classe calendar__othermonth -->
                <td class="<?= $month->withinMonth($date) ? 'day' : 'calendar__othermonth' ; ?> <?= $_COOKIE['compress']; ?> <?= $i == 0 ? ' firstWeek' : '' ; ?>">
                    <!-- On affiche le nom du jour mais uniquement pour la première ligne -->
                    <?php if($i === 0): ?>
                        <div class="calendar__weekday <?= $_COOKIE['compress']; ?>"><?= $day; ?></div>
                    <?php endif; ?>

                    <!-- On affiche le numéro du jour en clonant le précédent lundi et en le modifiant : on lui rajout le numéro de l'élément du tableau $days + le numéro de la boucle * 7. On formate ensuite en nombre de jour. -->
                    <div class="calendar__day <?= $_COOKIE['compress']; ?>"><?= $date->format('j'); ?><span class="effectif"><?= $effectif; ?></span></div>

                <?php endforeach; ?>
            </tr>

            <!------------------------------------ On fait une ligne pour chaque devis de la semaine (tr) --------------------------------------------->
            <?php
            // Premier jour de la semaine
            $date = (clone $start)->modify((0 + $i * 7) . " days");
            $devisWeeks = $devisManager->getListeDevisByWeeks($date->format('Y-m-d'), $date->modify('+ 6 days')->format('Y-m-d'));
            foreach($devisWeeks as $devis):
                
                /* DÉFINITION DU N/M NOMBRE DE DEVIS */
                $client = $UtilisateurManager->getProfilFromDevis($devis->getIdDevis());
                $nbreDevis = $devisManager->getCountDevis($client->getId());

                $devisUser = $devisManager->getDevisFromUser($client->getId());
                $count = countOfDevis($devisUser, $devis);
                
                // Premier jour du devis
                $day1 = true;
                $tdVide = true;

                // Définition du nombre de jours du devis contenus dans la semaine (pour les devis qui sont dans le mois courant)
                $colspan = 0;
                foreach($month->days as $k => $day)
                {
                    $date = (clone $start)->modify(($k + $i * 7) . " days");
                    if($month->withinMonth($date))
                    {
                        if($devis->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') < $devis->getDateFin())
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

                        if(!$month->withinMonth($date)) :
                            if($devis->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') < $devis->getDateFin()): ?>

                                <!-- Div pleine d'un devis qui ne fait pas partie du mois -->
                                <td>
                                    <div class="calendar__devis__weeks devis <?= $month->withinMonth($date) ? '' : 'opacity' ; ?> <?= $_COOKIE['compress']; ?>">
                                        <span><?= $count . "/" . $nbreDevis; ?></span>
                                        <span class="name_group"><a href="https://aiolah-vaiti.fr/appli-boucalais/?action=consulter-devis&devis=<?= $devis->getIdDevis(); ?>"><?= $devis->getOrganisme(); ?></a></span>
                                        <span class="size_group"><?= $devis->getTailleGroupe(); ?></span>
                                    </div>
                                </td>

                            <?php endif; ?>
                        <?php endif;

                        /* Div "pleine" = remplie avec les infos du devis */
                        if($devis->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') < $devis->getDateFin() && $day1 && $month->withinMonth($date)): ?>

                            <td colspan="<?= $colspan; ?>">
                                <div class="calendar__devis__weeks devis <?= $month->withinMonth($date) ? '' : 'opacity' ; ?> <?= $_COOKIE['compress']; ?>">
                                    <span><?= $count . "/" . $nbreDevis; ?></span>
                                    <span class="name_group"><a href="https://aiolah-vaiti.fr/appli-boucalais/?action=consulter-devis&devis=<?= $devis->getIdDevis(); ?>"><?= $devis->getOrganisme(); ?></a></span>
                                    <span class="size_group"><?= $devis->getTailleGroupe(); ?></span>
                                </div>
                            </td>

                            <?php $day1 = false; ?>

                        <!-- Div de départ -->
                        <?php elseif($devis->getDateFin() == $date->format('Y-m-d')): ?>

                            <td>
                                <div class="calendar__devis__weeks devis depart <?= $month->withinMonth($date) ? '' : 'opacity' ; ?> <?= $_COOKIE['compress']; ?>">
                                    <span><?= $count . "/" . $nbreDevis; ?></span>
                                    <span class="name_group" <?= $colspan > 1 ? "style= 'display: none;'" : '' ?>><a href="https://aiolah-vaiti.fr/appli-boucalais/?action=consulter-devis&devis=<?= $devis->getIdDevis(); ?>"><?= $devis->getOrganisme(); ?></a></span>
                                    <span class="size_group"><?= $devis->getTailleGroupe(); ?></span>
                                </div>
                            </td>
                        
                        <!-- Div vide avant la date de début et/ou après la date de fin -->
                        <?php elseif($date->format('Y-m-d') < $devis->getDateDebut() || $date->format('Y-m-d') > $devis->getDateFin()): ?>
                            
                            <td class="calendar__devis__weeks empty"></td>

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

/**
 * Détermine le N/m devis du client : si le devis courant est le même que celui sur lequel on boucle plus haut, on arrête d'incrémenter et on retourne la valeur
 * @param devisUser Tableau des devis de l'utilisateur
 * @param devis Devis sur lequel on est en train de boucler
 * @return j nième devis
 */
function countOfDevis($devisUser, $devis)
{
    $j = 1;
    foreach($devisUser as $oneDevis)
    {
        if($oneDevis->getIdDevis() == $devis->getIdDevis())
        {
            return $j;
        }
        $j++;
    }
}

?>

<!-- Les balises script en fin de body -->
<?php include("./scripts.php"); ?>