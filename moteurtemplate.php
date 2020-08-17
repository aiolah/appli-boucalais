<?php
// gestion des templates avec Twig
	require_once('twig/vendor/autoload.php');
    /*
    $loader = new Twig_Loader_Filesystem('Views'); // Dossier contenant les templates
    $twig = new Twig_Environment($loader, array(
      'cache' => false
    ));
*/
    $loader = new \Twig\Loader\FilesystemLoader('Views');
    $twig = new \Twig\Environment($loader, [
    'cache' => false
]);
?>

