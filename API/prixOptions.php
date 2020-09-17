<?php

/* ---------------------------------------------- AFFICHAGE DES PRIX DE TOUTES LES OPTIONS DE LA BASE DE DONNÉES --------------------------------------------*/

// Url = http://leboucalais.fr/application-dev/API/prixOptions.php

include "../connect.php";

$req = "SELECT ID_OPTION, PRIX_OPTION_UNITE FROM LE_BOUCALAIS_TARIF_OPTIONS";
$stmt = $bdd->prepare($req);
$stmt->execute();

while($donnees = $stmt->fetch(PDO::FETCH_OBJ))
{
    $prixOptions[] = $donnees;
}

echo json_encode($prixOptions);

?>