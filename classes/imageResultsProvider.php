<?php 

    class ImageResultsProvider{

        private $_con;


        public function __construct($con){

            $this->_con = $con;
        }

        public function getNumResults($term){

            $query =$this->_con->prepare("SELECT COUNT(*) as total
                                        FROM images WHERE title LIKE :term
                                        OR alt LIKE :term
                                        AND broken =0");

            $searchTerm = "%".$term."%";

            $query->bindParam(":term",$searchTerm);
            $query->execute();

            $row = $query->fetch(PDO::FETCH_ASSOC);
            return $row["total"];

        }


        // Méthode pour afficher les résultats
        public function getResultsHtml($page,$pageSize,$term){
           
            // $page = la page courante
            // $pageSize = le nombre de résultats
            // $term = terme de recherche 

            $fromLimit= ($page-1)* $pageSize;

            // page 1 : (1-1)*20 = 0
            // page 2 : (2-1)*20=20
            //page 3 : (3-1)*20=40

            $query = $this->_con->prepare("SELECT *
                                           FROM sites WHERE title LIKE :term
                                           OR url LIKE :term
                                           OR keywords LIKE :term
                                           OR DESCRIPTION LIKE :term
                                           ORDER BY clicks DESC
                                           LIMIT :fromLimit,:pageSize");

            // LIMIT :fromLimit,:pageSize                               

            $searchTerm = "%".$term."%";
            $query->bindParam(":term",$searchTerm);
            $query->bindParam(":fromLimit",$fromLimit,PDO::PARAM_INT);
            $query->bindParam(":pageSize",$pageSize,PDO::PARAM_INT);
            $query->execute();
            $resultHtml = "<div class='siteResults'>";

            while($row=$query->fetch(PDO::FETCH_ASSOC)){

                $id = $row["id"];
                $url = $row["url"];
                $title = $row["title"];
                $description = $row["description"];

                $resultHtml .="<div class='resultContainer'>
                                <h3 class='title'>
                                <a class='result' href='$url' data-linkId='$id'>
                                $title
                                </a>
                                </h3>
                                <p class='url'>$url</p>
                                <p class='description'>$description</p>";
            }

            $resultHtml.="</div>";
            return $resultHtml;

        }
    }


?>