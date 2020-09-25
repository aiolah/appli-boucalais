<head>
    <link rel="stylesheet" href="../Css/calendrier.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous"> 
</head>
   
    <nav class="navbar navbar-dark mb-3 bg-primary">
        <a>Mon calendrier</a>
    </nav>


    <?php ini_set( 'display_errors', 1 );
    error_reporting( E_ALL );

    require "../Models/Month.php";

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
        <!-- On fait des endfor pour mieux se repérer au niveau de l'html, c'est plus pratique -->
        <?php for($i = 0; $i < $weeks; $i++): ?>
            <tr>
                <?php
                    foreach($month->days as $k => $day):
                    $date = (clone $start)->modify(($k + $i * 7) . " days")
                ?>
                <!-- Si le date fait partie du mois, on donne la classe calendar__overmonth à la cellule, sinon on ne fait rien -->
                <td class="<?= $month->withinMonth($date) ? '' : 'calendar__othermonth'; ?>">
                    <!-- On affiche le nom du jour mais uniquement pour la première ligne -->
                    <?php if($i === 0): ?>
                        <div class="calendar__weekday"><?= $day; ?></div>
                    <?php endif; ?>
                    <!-- On affiche le numéro du jour en clonant le précédent lundi et en le modifiant : on lui rajout le numéro de l'élément du tableau $days + le numéro de la boucle * 7. On formate ensuite en nombre de jour. -->
                    <div class="calendar__day"><?= $date->format('d'); ?></div>
                </td>
                <?php endforeach; ?>
            </tr>
        <?php endfor; ?>
    
    
    </table>
