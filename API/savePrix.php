<?php

/* -------------------------------------------------------- MODIFICATION DU PRIX EN BASE DE DONNÉES --------------------------------------------------------*/

include "../connect.php";

$sejour = json_decode(file_get_contents('php://input'), true);

$req = "UPDATE LE_BOUCALAIS_TARIF_SEJOUR SET PRIX_SEJOUR_UNITE = ? WHERE ID_SEJOUR = ?";
$stmt = $bdd->prepare($req);
$ok = $stmt->execute(array($sejour['prix'], $sejour['id']));

echo '{ "status" : "' . $ok . '"}';

?>