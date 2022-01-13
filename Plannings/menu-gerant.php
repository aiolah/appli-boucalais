<!-- A toi, développeur(-se) héritier de mon travail, n'oublie pas d'insérer <?= $_COOKIE['hide']; ?> dans l'attribut class de nav !! + <?= $user->getPrenom(); ?> pour le Bonjour user + le title du document.. Enfin bref ne fais pas de copier coller direct ! (+ pense à rajouter "http://leboucalais.fr/application/" au début de chaque lien !) -->

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="../Css/planning.css">
    <link rel="stylesheet" href="../Css/style.css">
    <?php
    
    if(basename($_SERVER['PHP_SELF']) == "planning-devis.php")
    {
        $title = "Planning des devis";
    }
    elseif(basename($_SERVER['PHP_SELF']) == "planning-demandes-reservation.php")
    {
        $title = "Planning des demandes de réservations";
    }
    elseif(basename($_SERVER['PHP_SELF']) == "planning.php")
    {
        $title = "Planning";
    }
    
    ?>
    <title><?= $title ?></title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light sticky-top <?= $_COOKIE['hide']; ?>" id="navbar">
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
                    <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/?action=aperçu">Aperçu</a>
                    <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/?action=clients">Tous</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/?action=ajouter-client">Ajouter</a>
                    <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/?action=attribuer-devis">Attribuer un devis</a>
                </div>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Plannings
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/Plannings/planning-devis.php">Planning des devis</a>
                    <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/Plannings/planning-demandes-reservation.php">Planning des demandes de réservation</a>
                    <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/Plannings/planning.php">Planning</a>
                </div>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Paramétrage
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/?action=documents_gerant">Documents</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/?action=prix-devis">Prix devis</a>
                    <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/?action=liste-activite">Activités devis</a>
                    <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/?action=liste-option">Options devis</a>
                </div>
            </li>
        </ul>

        <div>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="color: black;">Bonjour <?= $user->getPrenom(); ?></a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/?action=profil">Mon profil</a>
                        <a class="dropdown-item" href="http://aiolah-vaiti.fr/appli-boucalais/?action=logout">Déconnexion</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>