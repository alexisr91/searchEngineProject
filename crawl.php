<?php

    // Pas de classe donc lire de haut en bas
    // Une occurence = la premiere fois que ça arrive/ Apparition
    // Une itération = c'est la répétion d'un bloc de code
    include("config.php");
    include("classes/DomDocumentParser.php");

    $alreadyCrawled = array();
    $crawling = array();
    $alreadyFoundImages = array();

    // eviter les doublons 

    function linkExists($url){

        global $con;

        $query=$con->prepare("SELECT * FROM sites WHERE url= :url");

        $query->bindParam(":url",$url);
        $query->execute();
        return $query->rowCount() !=0;

    }

    // Insertion des liens dans la BDD 

    function insertLink($url,$title,$description,$keywords){
        // On importe $con
        global $con;

        // On fait la requête pour le startUrl 

        $query = $con->prepare("INSERT INTO sites(url,title,description,keywords) VALUES(:url,:title,:description,:keywords)");
        $query->bindParam(":url",$url);
        $query->bindParam(":title",$title);
        $query->bindParam(":description",$description);
        $query->bindParam(":keywords",$keywords);

        return $query->execute();
    }

        // Insertion des images dans la BDD 

        function insertLinkImages($url,$src,$alt,$title){
            // On importe $con
            global $con;
    
            // On fait la requête 
    
            $query = $con->prepare("INSERT INTO images(siteUrl,imageUrl,alt,title,clicks) VALUES(:siteUrl,:imageUrl,:alt,:title,1)");

            $query->bindParam(":siteUrl",$url);
            $query->bindParam(":imageUrl",$src);
            $query->bindParam(":alt",$alt);
            $query->bindParam(":title",$title);

            return $query->execute();
        }

        // Création d'une fonction pour recréer des URL via des filtres où on inclut la fonction substr  dans des conditions 
        function createLinks($src,$url){

        // Vérification 

        // echo "src : $href<br>";
        // echo "URL: $url<br>";

        // Convertir les chemins relatifs en chemin absolu 

        $scheme = parse_url($url)["scheme"]; // Correspond à http ou https

        $host = parse_url($url)["host"]; // Correspond au hostname www.monsite.fr

        // Vérifier si les 2 premiers caractères pour voir si ce sont des //

        if(substr($src,0,2) == "//"){ // Dans les parametres, le 1er est à partir duquel on analyse, le second elle choisit le numéro d'index et le troisième est l'index jusqu'où on va extraire la chaine de caractere
            // On prend SRC et on regarde si // est entre l'index 0 et 2 

            // http://www.monsite.fr
            $src = $scheme.":".$src;
        }

         // Vérifier si le premier caractère pour voir si c'est un /
         else if(substr($src,0,1) == "/"){

            // /categorie/produit/1
            $src = $scheme."://".$host.$src;
        }

        // ./

        else if(substr($src,0,2) == "./"){

            $src = $scheme."://".$host.dirname(parse_url($url)["path"]).substr($src,1);
        }

        // ../
        
        else if(substr($src,0,3) == "../"){

            $src = $scheme."://".$host."/".$src;
        }

        // Vérifier si c'est différent de http ou https : about/about.php

        else if(substr($src,0,5) !=="https" && substr($src,0,4) !== 'http'){

            $src = $scheme."://".$host."/".$src;
        }

        return $src;

    }

    function getDetails($url){

        global $alreadyFoundImages;
        $parser = new DomDocumentParser($url);
        $titleArray = $parser->getTitleTags();
        
        // Il y a pas d'elements sur la premiere ligne du tableau


        if(sizeof($titleArray) == 0 || $titleArray->item(0)==Null){

            return;
        }

        $title = $titleArray->item(0)->nodeValue; // Valeur du noeud en fonction de son type

        // supprimer les sauts de ligne 

        $title = str_replace("\n","",$title);
        // SI il n'y a pas de titre, on ignore le lien 

        if($title ==""){

            return;
        }

        // Description et meta 
        $description = "";
        $keywords = "";

        $metaArray = $parser->getMetaTags();

        foreach($metaArray as $meta){

            if($meta->getAttribute("name")== "description"){
                $description = $meta->getAttribute("content");
            }

            if($meta->getAttribute("name")=="keywords"){
                $keywords = $meta->getAttribute("content");
            }
        }

        // suppression des sauts de ligne

        $description = str_replace("\n","",$description);
        $keywords = str_replace("\n","",$keywords);

        // vérifier si les URL existent

        if(linkExists($url)){

            echo "$url est déjà dans la bdd";
        }

        // insertion liens bdd 
        elseif(insertLink($url,$title,$description,$keywords)){

            echo "succès, lien inséré dans la bdd";
        }

        else{

            echo "Erreur lors de l'insertion du lien dans la bdd";
        }

        $imageArray = $parser->getImageTags();
        
        foreach($imageArray as $image){ 
            $src = $image->getAttribute("src");
            $alt= $image->getAttribute("alt");
            $title =$image->getAttribute("title");

            if(!$title && !$alt){
                continue;
            }
        
            // Création lien absolu avec le chemin relation des images

            $src = createLinks($src,$url);

            if(!in_array($src,$alreadyFoundImages)){
                $alreadyFoundImages[]=$src;

                // Insertion de l'image dans la BDD

                insertLinkImages($url,$src,$alt,$title);
            }

        }

        //echo "Url : $url, <br> Description : $description, <br> Mots clés : $keywords<br><br>";
    }
    
    
    
    function followLinks($url){
        // le mot clé global est une variable déclarée que celle qui sont déclarées un ou plusieurs bloc plus haut ( scope plus large )
        global $alreadyCrawled;
        global $crawling;   
        
        $parser = new DomDocumentParser($url);
        $linkList = $parser->getLinks();
        
        foreach($linkList as $link){

            // récuperation des href

            $href = $link->getAttribute("href");

            // supprimer les lignes en comportant que des #

            if(strpos($href,'#') !==false){
                continue;
            }

            // supprimer les lignes comportant du JS

            else if(substr($href,0,11) == "javascript:"){
                continue;
            }

            
            $href = createLinks($href,$url);
            /* echo $href . '<br>'; */
            // Condition pour savoir si l'URL n'a pas encore été visitée

            if(!in_array($href,$alreadyCrawled)){

                $alreadyCrawled[]=$href;
                $crawling[]=$href;

                // Insertion des données récupérées

                getDetails($href);
            }

            // On passe à la ligne suivante 
            array_shift($crawling);

            // Création d'une boucle pour récuperer les lignes du tableau $crawling

            foreach($crawling as $site){

                followLinks($site); // Recursivité = rappel d'une fonction dans une meme fonction   
                                    // La faculté que possède une fonction à s'appeler elle-même.
            }
        }
    }
$startUrl = "https://www.mcdonalds.com";
followLinks($startUrl);
?>