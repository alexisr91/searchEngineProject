<?php 

    include("config.php");
    include("classes/SiteResultsProvider.php");
    include("classes/ImageResultsProvider.php");
   /*  echo $_GET["term"]; */

    if(isset($_GET["term"])){

        $term = $_GET["term"];
    }else{
        exit(" Vous devez entrer un terme de recherche"); // Arret du code 
    }

    // Detection quel est le type qui est présent dans l'URL pour afficher différement l'onglet actif

    $type = isset($_GET['type']) ? $_GET['type'] :  "sites";

    $page = isset($_GET['page']) ? $_GET['page'] : 1;

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Bienvenue sur MongMongGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins&family=Roboto&display=swap" rel="stylesheet">   
    <link rel="stylesheet" href="assets/css/style.css" type="text/css">
</head>
<body>
    
    <main class="container-fluid searchPage">

        <header class="jumbotron">

            <div class="row">

                <h1><a href="index.php">MongMongGo</a></h1>
                <form action="search.php" method="GET">
                    <input type="hidden" name="type" value="<?php echo $type; ?>">
                    <input type="search" name="term" placeholder="Mot clé" class="form-control" value="<?php echo $term?>">

                    <button type="submit">
                        <img src="assets/img/icons8-search-26.png">
                    </button>
                </form>
            </div>
            <div class="tabs">
                <ul id="tabList" class="row">

                <li class="<?php echo $type == 'sites' ? 'active' : '';?>">  
                    <a href="search.php?term=<?php echo $term ?>&type=sites">Sites</a>
                </li>

                <li class="<?php echo $type == 'images' ? 'active' : '' ?>">
                    <a href="search.php?term=<?php echo $term ?>&type=images">Images</a>
                </li>
                </ul>

            </div>
        </header>
        <section class="resultat">
            <!-- Résultats de la recherche -->

            <?php 
            if($type =="sites"){ 
                $resultProvider = new SiteResultsProvider($con);
                $pageSize=10;
            }else{
                $resultProvider = new ImageResultsProvider($con);
                $pageSize=30;
            }
            
            
            $numResults=$resultProvider->getNumResults($term);

            echo"<h4 class='numberResults'>$numResults résultat(s).</h4>";


            echo $resultProvider->getResultsHtml($page,$pageSize,$term);
            ?>
        </section>

        <section class="pagination">
            <div class="pageBtn">

                <div class="pageNumberContainer">
                    <img src="assets/img/pageStart.png" alt="">
                </div>

                <?php

                    /* $currentPage = 1;
                    $pageLeft = 10; */

                    $pageToShow=10;
                    $numPages = ceil($numResults/$pageSize);
                    $pageLeft = min($pageToShow,$numPages);
                    $currentPage=$page-floor($pageToShow/2);

                    if($currentPage<1){$currentPage=1;}

                    if($currentPage + $pageLeft > $numPages +1){
                        $currentPage = $numPages + 1 - $pageLeft;
                    }

                    while($pageLeft!=0){

                        if($currentPage == $page){

                            echo "<div class='pageNumberContainer'>
                                    <img src='assets/img/pageSelected.png'>
                                    <span class='pageNumber'>$currentPage</span></div>";
                        }

                        else{
                            echo "<div class='pageNumberContainer'>
                            <img src='assets/img/page.png'>
                            <span class='pageNumber'>$currentPage</span></div>";
                        }

                        $currentPage++;
                        $pageLeft--;
                    }

                ?>



                <div class="pageNumberContainer">
                    <img src="assets/img/pageEnd.png" alt="">
                </div>


            </div>
        </section>

    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>