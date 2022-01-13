<?php

/* ------------------------------------------------ AFFICHAGE DES PRIX DE TOUS LES SÉJOURS DE LA BASE DE DONNÉES --------------------------------------------*/

// Url = https://aiolah-vaiti.fr/appli-boucalais/API/prixSejours.php

include "../connect.php";

$req = "SELECT ID_SEJOUR, PRIX_SEJOUR_UNITE FROM LE_BOUCALAIS_TARIF_SEJOUR WHERE ID_SEJOUR <= 35";
$stmt = $bdd->prepare($req);
$stmt->execute();

while($donnees = $stmt->fetch(PDO::FETCH_OBJ))
{
    $prixSejours[] = $donnees;
}

echo json_encode($prixSejours);

?>