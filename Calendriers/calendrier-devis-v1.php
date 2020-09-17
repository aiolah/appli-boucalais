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
<nav class="navbar navbar-expand-lg navbar-light sticky-top" id="navbar" style="background-color: #e3f2fd;">
        <a class="navbar-brand" href="http://leboucalais.fr/application/?action=accueil">
            Appli Boucalais
        </a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav mr-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Clients
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="?action=aperçu">Aperçu</a>
                        <a class="dropdown-item" href="?action=clients">Tous</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="?action=ajouter-client">Ajouter</a>
                    </div>
                </li>

                <li class="nav-item"><a class="nav-link" href="calendrier.php">Planning des réservations</a></li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Paramétrage
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="?action=documents_gerant">Documents</a>
                        <div class="dropdown-divider"></div>
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

<?php

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

require_once "connect.php";
require "./Models/Month.php";
require "./Models/Devis.php";
require "./Modules/DevisManager.php";
require "./Models/Utilisateur.php";
require "./Modules/UtilisateurManager.php";

$devisManager = new DevisManager($bdd);
$UtilisateurManager = new UtilisateurManager($bdd);

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

        <?php $currentMonth = new Month(date('n'), date('Y')); ?>

        <div>
            <button class="btn icon closedBlue" id="eye"></button>
            <a href="?month=<?= $currentMonth->getMonth(); ?>&year=<?= $currentMonth->getYear();  ?>" class="btn btn-primary">Revenir à <?= $currentMonth->showMonthYear(); ?></a>
            <a href="?month=<?= $month->previousMonth()->_month; ?>&year=<?= $month->previousMonth()->_year; ?>" class="btn btn-primary">&lt;</a>
            <a href="?month=<?= $month->nextMonth()->_month; ?>&year=<?= $month->nextMonth()->_year; ?>" class="btn btn-primary">&gt;</a>
        </div>
    </div>

    <table class="calendar__table calendar__table--<?= $month->getCountWeeks();?>weeks">
        <?php 

        // Pour chaque semaine : on fait une ligne (tr)
        for($i = 0; $i < $weeks; $i++): ?>
            <!--------------------------------------- LIGNE DE LA SEMAINE + RÉSERVATIONS NON CONFIRMÉES ---------------------------------------------------->
            <tr>
                <?php

                    // Pour chaque jour de la semaine → on fait une case (td)
                    foreach($month->days as $k => $day):
                        $date = (clone $start)->modify(($k + $i * 7) . " days");
                ?>

                <!------------------------------------------------------ CASE DU JOUR ------------------------------------------->

                <?php $effectif = $devisManager->effectifTotal($date->format('Y-m-d')); /* var_dump($effectif); */ ?>
                
                <!-- Si la date fait partie du mois, on ne fait rien, sinon on lui donne la classe calendar__othermonth -->
                <td class="<?= $month->withinMonth($date) ? 'day' : 'calendar__othermonth' ; ?><?= $i == 0 ? ' firstWeek' : '' ; ?>">
                    <!-- On affiche le nom du jour mais uniquement pour la première ligne -->
                    <?php if($i === 0): ?>
                        <div class="calendar__weekday"><?= $day; ?></div>
                    <?php endif; ?>

                    <!-- On affiche le numéro du jour en clonant le précédent lundi et en le modifiant : on lui rajout le numéro de l'élément du tableau $days + le numéro de la boucle * 7. On formate ensuite en nombre de jour. -->
                    <div class="calendar__day"><?= $date->format('j'); ?><span class="effectif"><?= $effectif; ?></span></div>

                <?php endforeach; ?>
            </tr>

            <!------------------------------------ On fait une ligne pour chaque réservation de la semaine (tr) --------------------------------------------->
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
                                
            ?>

                <tr>
                    <!-- Pour chaque jour de la semaine -->
                    <?php foreach($month->days as $k => $day):
                        $date = (clone $start)->modify(($k + $i * 7) . " days");

                        if($devis->getDateDebut() <= $date->format('Y-m-d') && $date->format('Y-m-d') < $devis->getDateFin()): ?>

                            <td colspan="<?= $colspan; ?>">
                                <div class="calendar__devis__weeks devis <?= $month->withinMonth($date) ? '' : 'opacity' ; ?>">
                                    <span><?= $count . "/" . $nbreDevis; ?></span>
                                    <span class="name_group"><a href="http://leboucalais.fr/application-dev/?action=consulter-devis&devis=<?= $devis->getIdDevis(); ?>"><?= $devis->getOrganisme(); ?></a></span>
                                    <span class="size_group"><?= $devis->getTailleGroupe(); ?></span>
                                </div>
                            </td>

                        <?php elseif($devis->getDateFin() == $date->format('Y-m-d')): ?>

                            <td>
                                <div class="calendar__devis__weeks devis depart <?= $month->withinMonth($date) ? '' : 'opacity' ; ?>">
                                    <span><?= $count . "/" . $nbreDevis; ?></span>
                                    <span class="name_group"><a href="http://leboucalais.fr/application-dev/?action=consulter-devis&devis=<?= $devis->getIdDevis(); ?>"><?= $devis->getOrganisme(); ?></a></span>
                                    <span class="size_group"><?= $devis->getTailleGroupe(); ?></span>
                                </div>
                            </td>
                            
                            <?php else: ?>
                                
                                <td class="calendar__devis_weeks empty"></td>

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

<?php

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

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src='Js/Popups.js'></script>

<script>

document.getElementById('eye').addEventListener('click', cacher);
function cacher(e)
{
    document.getElementById('navbar').classList.toggle('hide');
    e.target.classList.toggle("openBlue");
    e.target.classList.toggle("closedBlue");
}

</script>