<?php 


ob_start(); 
/* PDO::setAttribute => configure un attribut PDO
   PDO::ATTR_ERRMODE : rapports d'erreurs 
   PDO::ERRMODE_WARNING : emet une alerte E_WARNING*/
try{ 

    $con = new PDO("mysql:host=localhost;dbname=squery;charset=utf8",
                    "root",
                    "root");

                    // Si on utilise un port 8889, il faut ajouter le port après le nom de la DB et l'adresse IP du host au lieu du localhost
    $con->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
}

catch(PDOExeption $e){

    echo "Erreur de connexion". $e->getMessage();
}


?>